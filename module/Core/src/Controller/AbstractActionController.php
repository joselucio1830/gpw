<?php

    namespace Core\Controller;

    use Admin\Entity\Usuario;
    use Doctrine\ORM\EntityManager;
    use PhpCollection\ObjectBasics;
    use Zend\Authentication\AuthenticationService;
    use Zend\Log\Logger;
    use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;

    use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

    /**
     *
     */
    class AbstractActionController extends ZendAbstractActionController
    {

        /**
         * @return \Zend\ServiceManager\ServiceLocatorInterface | ObjectBasics
         */
        public function getServiceLocator($name = null)
        {
            if (is_null($name))
                return $this->getEvent()->getApplication()->getServiceManager();
            else
                return $this->getEvent()->getApplication()->getServiceManager()->get($name);
        }

        public function getUsuarioAutenticado()
        {
            $authService = $this->getServiceLocator()->get(AuthenticationService::class);

            if ($authService->hasIdentity()) {
                /** @var Usuario $usuario */
                $usuario = $this->getServiceLocator()->get(EntityManager::class)
                    ->find(Usuario::class, $authService->getIdentity());

                return $usuario;
            }

            return false;
        }

        /**
         * @return EntityManager
         */
        public function getEm()
        {
            return $this->getServiceLocator()->get(EntityManager::class);
        }

        /**
         * @return DoctrineHydrator
         */
        public function getHydrator()
        {

            return new DoctrineHydrator($this->getEm());
        }

        public function getExceptionMsg(\Exception $e)
        {
            $msg = $e->getMessage();
            if (preg_match("/Duplicate entry/", $msg)) {
                return "Item já cadastrado !";
            }

            return $msg;
        }

        public function getJsonInputParams()
        {

            $dataByJson = json_decode(file_get_contents("php://input"), true);
            if (is_array($dataByJson)) {
                return $dataByJson;
            } else {
                return false;
            }
        }

        public function getDataPost()
        {
            if ($this->getRequest()->isPost()) {
                if (!$data = $this->getJsonInputParams()) {
                    $data = $this->getRequest()->getPost()->toArray();
                }
                return $data;
            }
            return [];
        }

        public function getResponseText()
        {
            header("Content-type: text/plain; charset=utf-8");
            return $this->getResponse();
        }

        /***
         * @return Logger|ObjectBasics|\Zend\ServiceManager\ServiceLocatorInterface
         */
        public function getLogger(){
            return $this->getServiceLocator("log");
        }
    }

    ?>