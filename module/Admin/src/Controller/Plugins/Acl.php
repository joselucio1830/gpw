<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 24/10/16
 * Time: 18:27
 */

namespace Admin\Controller\Plugins;


use Admin\Entity\Repository\UsuarioRepository;
use Admin\Entity\Usuario;
use Admin\Service\Acl as AclService;
use Admin\Storage\Session;
use Doctrine\ORM\EntityManager;
use http\Client\Curl\User;
use Zend\Authentication\AuthenticationService;
use Zend\Console\Request as ConsoleReques;
use Zend\Http\Header\Authorization;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Parameters;


class Acl extends AbstractPlugin
{
    /** @var AuthenticationService */
    protected $authService;
    /** @var AclService */
    private $acl;

    /** @var  ServiceLocatorInterface */
    private $sm;

    /**
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        return $this->authService;
    }

    /**
     * @param AuthenticationService $authService
     * @return Acl
     */
    public function setAuthService($authService)
    {
        $this->authService = $authService;
        return $this;
    }

    /**
     * @return AclService
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param AclService $acl
     * @return Acl
     */
    public function setAcl($acl)
    {
        $this->acl = $acl;
        return $this;
    }

    private function autenticateUser($username, $senha)
    {

        $authService = $this->getAuthService();
        if (!$authService->hasIdentity()) {
            if ((!empty($username)) && (!empty($senha))) {
                $this->getAuthService()->getAdapter()
                    ->setIdentity($username)
                    ->setCredential(strlen($senha) == 32 ? $senha : md5($senha));

                $result = $this->getAuthService()->authenticate();

                if ($result->isValid()) {
                    $authService->setStorage($this->sm->get(Session::class));
                    $authService->getStorage()->write($username);
                }
            }
        }
    }

    private function autenticate(ServiceLocatorInterface $sm, Parameters $data)
    {

        $authService = $this->getAuthService();
        if (!$authService->hasIdentity()) {
            $username = $data->get('login');
            $senha = $data->get('senha');
            $this->autenticateUser($username, $senha);

        }
    }

    private function autenticateFromApi($token)
    {
        if (!empty($token)) {
            /** @var EntityManager $em */
            $em = $this->sm->get(EntityManager::class);
            /** @var UsuarioRepository $repo */
            $repo = $em->getRepository(Usuario::class);
            $user = $repo->findByApiToken($token);
            if ($user instanceof Usuario && $user->isAtivo()) {
                $this->autenticateUser($user->getLogin(), $user->getSenha());
            } else {
                return false;
            }
        }
        return true;

    }

    public function doAuthorization(MvcEvent $e, ServiceLocatorInterface $sm)
    {

        if ($e->getRequest() instanceof ConsoleReques) {

            return true;
        }

        $this->sm = $sm;
        $controller = $e->getTarget();

        if (!$this->checkRequestAuthorization($e->getRequest()->getHeaders('Authorization', null), $e)) {
            return false;
        }

        $this->autenticateFromApi($e->getRequest()->getQuery()->get('api_token'));
        $this->autenticateFromToken($e->getRequest()->getQuery()->get('token'));


        $this->autoAutenticateDebug();

        $controllerClass = get_class($controller);
        $moduleName = strtolower(substr($controllerClass, 0, strpos($controllerClass, '\\')));
        $roles = $this->getAutenticaitonRules();
        $routeMatch = $e->getRouteMatch();

        $actionName = $routeMatch->getParam('action', 'not-found'); // get the action name
        $controllerName = $routeMatch->getParam('controller', 'not-found');     // get the controller name
        $controllerNameArray = explode('\\', $controllerName);
        $controllerName = str_replace("controller", "", strtolower(array_pop($controllerNameArray)));

        $resource = "{$moduleName}:{$controllerName}";
        $rname = $e->getRouteMatch()->getMatchedRouteName();


        foreach ($roles as $role) {
            if (!$this->acl->hasRole($role)) {
                if ($rname == 'adminError/noRole') {
                    return false;
                }
                $router = $e->getRouter();
                // $url    = $router->assemble(array(), array('name' => 'Login/auth')); // assemble a login route
                $url = $router->assemble(array(
                    'role' => $role
                ), array('name' => 'adminError/noRole', 'query' => ['role' => $role]));
                $response = $e->getResponse();
                $response->setStatusCode(302);
                // redirect to login page or other page.
                $response->getHeaders()->addHeaderLine('Location', $url);
                $e->stopPropagation();
            }
        }

        if (!$this->acl->hasResource($resource)) {
            $router = $e->getRouter();
            if ($rname == 'adminError/noResource') {
                return false;
            }

            // $url    = $router->assemble(array(), array('name' => 'Login/auth')); // assemble a login route
            $url = $router->assemble(array(
                'query' => ['resource' => $resource]
            ), array('name' => 'adminError/noResource', 'query' => ['resource' => $resource]));
            $response = $e->getResponse();
            $response->setStatusCode(302);
            // redirect to login page or other page.
            $response->getHeaders()->addHeaderLine('Location', $url);
            $e->stopPropagation();
        }

        $isAllow = false;
        foreach ($roles as $role) {

            if ($this->acl->isAllowed($role, $resource, $actionName)) {
                $isAllow = true;
            }
        }
        if (!$isAllow) {
            $router = $e->getRouter();
            if ($rname == 'adminError/forbidden') {
                return false;
            }

            // $url    = $router->assemble(array(), array('name' => 'Login/auth')); // assemble a login route
            $url = $router->assemble(array(
                'resource' => $resource,
                'roles' => $roles
            ), array('name' => 'adminError/forbidden', 'query' => ['resource' => $resource,
                'controller' => $controllerName,
                'module' => $moduleName,
                'action' => $actionName,
                'roles' => $roles]));
            $response = $e->getResponse();
            $response->setStatusCode(302);
            // redirect to login page or other page.
            $response->getHeaders()->addHeaderLine('Location', $url);
            $e->stopPropagation();
        }

    }

    private function getAutenticaitonRules()
    {
        if ($this->authService->hasIdentity()) {
            /** @var Usuario $usuario */
            $usuario = $this->sm->get(EntityManager::class)
                ->find(Usuario::class, $this->authService->getIdentity());
            return explode(',', $usuario->getPerfil());

        } else {
            return ['anonymous'];
        }
    }

    private function autoAutenticateDebug()
    {
        $config = $this->sm->get('config');

        if (!empty($config['acl'])) {
            if (!empty($config['acl']['autoautenticate']) && $config['acl']['autoautenticate']) {
                if (!empty($config['acl']['login']) && !empty($config['acl']['senha'])) {
                    $params = new Parameters();
                    $params->set('login', $config['acl']['login']);
                    $params->set('senha', $config['acl']['senha']);

                    $this->autenticate($this->sm, $params);

                }
            }
        }
    }

    /**
     * @param $token
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @todo REFATORAR PARA SISTEMA COM SESSIONS
     */
    private function autenticateFromToken($token)
    {
        if (!empty($token)) {
            /** @var EntityManager $em */
            $em = $this->sm->get(EntityManager::class);

            /** @var UsuarioRepository $repo */
            $repo = $em->getRepository(Usuario::class);

            $sessionApi = $repo->findByToken($token);

            if ($sessionApi instanceof Usuario) {
                $user = $em->find(Usuario::class, $sessionApi->getLogin());
                if ($user instanceof Usuario && $user->isAtivo()) {
                    $this->autenticateUser($user->getLogin(), $user->getSenha());
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Authorization $autorization
     * @param MvcEvent $e
     */
    private function checkRequestAuthorization($autorization, MvcEvent $e)
    {
        if ($autorization)// verifica se os dados da autorização
            if ($autorization->getFieldValue() !== null) {
                list($campo, $value) = explode("=", $autorization->getFieldValue());

                switch ($campo) {
                    case "token":
                        if (!$this->autenticateFromToken($value)) {
                            $this->unauthorized($e);
                            return false;
                        }
                        break;
                    case "api_token":
                    case "apiToken":
                        if (!$this->autenticateFromApi($value)) {
                            $this->unauthorized($e);
                            return false;
                        }
                        break;
                }

            }
        return true;
    }

    private function unauthorized(MvcEvent $e)
    {
        $response = $e->getResponse();
        $response->setStatusCode(401);
        $e->stopPropagation();
    }
}
