<?php
    /**
     * Created by PhpStorm.
     * User: lenon
     * Date: 19/10/16
     * Time: 16:45
     */

    namespace Admin\Auth;


    use Admin\Auth\Events\AbstractEvent;
    use Admin\Entity\Usuario;
    use Core\Api\Connect;
    use Core\Api\Urls;
    use Doctrine\ORM\EntityManager;
    use Zend\Authentication\Adapter\AdapterInterface;
    use Zend\Authentication\Adapter\Exception\ExceptionInterface;
    use Zend\Authentication\Result;
    use Zend\ServiceManager\ServiceLocatorInterface;


    class Authentication implements AdapterInterface
    {
        /**
         * @var ServiceLocatorInterface
         */
        private $sm;

        private $identity;

        private $credential;

        private $enableOuterCheckLogin = false;


        /** @var AbstractEvent[] */
        private $authEvent = [];


        public function __construct(ServiceLocatorInterface $sm)
        {
            $this->sm = $sm;
        }

        /**
         * @return bool
         */
        public function isEnableOuterCheckLogin(): bool
        {
            return $this->enableOuterCheckLogin;
        }

        /**
         * @param bool $enableOuterCheckLogin
         * @return Authentication
         */
        public function setEnableOuterCheckLogin(bool $enableOuterCheckLogin): Authentication
        {
            $this->enableOuterCheckLogin = $enableOuterCheckLogin;
            return $this;
        }


        /**
         * @return mixed
         */
        public function getIdentity()
        {
            return $this->identity;
        }

        /**
         * @param mixed $identity
         * @return Authentication
         */
        public function setIdentity($identity)
        {
            $this->identity = $identity;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getCredential()
        {
            return $this->credential;
        }

        /**
         * @param mixed $credential
         * @return Authentication
         */
        public function setCredential($credential)
        {
            $this->credential = $credential;
            return $this;
        }


        /**
         * Performs an authentication attempt
         *
         * @return Result
         * @throws ExceptionInterface If authentication cannot be performed
         */
        public function authenticate()
        {

            /** @var EntityManager $em */
            $em = $this->sm->get(EntityManager::class);

            /** @var Usuario $usuario */
            $usuario = $em->find(Usuario::class, $this->getIdentity());
            if ($this->enableOuterCheckLogin) {

                $ret = $this->checkLogin();
                if ($ret instanceof Result) {
                    return $ret;
                }
            }

            if (!($usuario instanceof Usuario)) {
                return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, ["Usuário ou senha inválido! "]);
            } elseif ($usuario->getSenha() === $this->getCredential() && $usuario->isAtivo()) {
                $this->onAutenticate($usuario);
                return new Result(Result::SUCCESS, $usuario, ["Usuario autenticado com sucesso!"]);
            } else {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ["Usuário ou senha inválido! "]);
            }
        }

        /**
         * @param $getIdentity
         * @param $getCredential
         */
        private function checkLogin()
        {

            /** @var EntityManager $em */
            $em = $this->sm->get(EntityManager::class);

            // configura a url de autenticação
            $configUrl = $this->sm->get('config')  ['auth']['remoteCheck'];

            // verifica as configurações da url
            if (empty($configUrl)) {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ["Usuário ou senha inválido! "]);
            }

            // cria a classe de urls
            $class = new class($configUrl) implements Urls
            {
                private $urls = [];
                private $secret = "";

                public function __construct($configUrl)
                {
                    $this->urls = $configUrl['urls'];
                    $this->secret = $configUrl['baseSecret'];
                }

                public function getApiSecret()
                {
                    return md5(date('Y-m-d H') . $this->secret);
                }

                public function getUrl($name)
                {
                    return $this->urls[$name];
                }
            };

            $conect = new Connect($class);


            foreach ($configUrl['urls'] as $k => $config) {

                $ret = $conect->post($k, [
                    'usuario' => $this->getIdentity(),
                    'senha' => $this->getCredential()
                ]);

                if ($ret['success']) {
                    if (!$ret['auth']) {
                        return new Result(Result::FAILURE_UNCATEGORIZED, null, [$ret['msg']]);
                    } else {

                        $usuario = $em->find(Usuario::class, $this->getIdentity());
                        if ($usuario instanceof Usuario) {
                            return new Result(Result::SUCCESS, $usuario, ["Usuário autenticado com sucesso!"]);
                        } else {
                            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, ["Usuário não encontrado, se seu usuário for CPF coloque somente números"]);
                        }

                    }
                }
            }
            return null;
        }

        private function onAutenticate($usuario)
        {
            // verifica se existe evento cadastrado
            if (!empty($this->authEvent)) {
                foreach ($this->authEvent as $item) {
                    // executa o evento
                    $item->onAutenticate($usuario);

                }
            }
        }

        public function addEvent(AbstractEvent $item)
        {
            $this->authEvent[] = $item;
        }
    }