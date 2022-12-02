<?php

namespace Admin;

use Admin\Auth\Authentication;
use Admin\Auth\Events\AbstractEvent;
use Admin\Controller\Plugins\Acl as AclPlugins;
use Admin\Service\Acl;
use Admin\Service\AuthUser;
use Admin\Service\UsuarioService;
use Admin\Storage\Session;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;


class Module
{
    const VERSION = '3.0.2dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    // added for Acl   ###################################
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('route', array($this, 'loadConfiguration'), 2);
        //you can attach other function need here...
    }

    public function loadConfiguration(MvcEvent $e)
    {
        $application = $e->getApplication();
        $sm = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $router = $sm->get('router');
        $request = $sm->get('request');

        $matchedRoute = $router->match($request);

        if (null !== $matchedRoute) {
            $sharedManager->attach(\Zend\Mvc\Controller\AbstractActionController::class, 'dispatch',
                function ($e) use ($sm) {
                    /** @var AclPlugins $aclPlugin */
                    $aclPlugin = $sm->get('ControllerPluginManager')->get(AclPlugins::class);
                    $aclPlugin->setAcl($sm->get(Acl::class))
                        ->setAuthService($sm->get(AuthenticationService::class));

                    $aclPlugin->doAuthorization($e, $sm); //pass to the plugin...
                }, 2
            );
            $sharedManager->attach(\Zend\Mvc\Controller\AbstractRestfulController::class, 'dispatch',
                function ($e) use ($sm) {
                    /** @var AclPlugins $aclPlugin */
                    $aclPlugin = $sm->get('ControllerPluginManager')->get(AclPlugins::class);
                    $aclPlugin->setAcl($sm->get(Acl::class))
                        ->setAuthService($sm->get(AuthenticationService::class));

                    $aclPlugin->doAuthorization($e, $sm); //pass to the plugin...
                }, 2
            );
        }
    }


    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                Session::class => function ($sm) {
                    return new Session('Auth');
                },
                \Admin\Service\Session::class => function ($sm) {
                    return new \Admin\Service\Session($sm);
                },

                AuthenticationService::class => function ($sm) {
                    //My assumption, you've alredy set dbAdapter
                    //and has users table with columns : user_name and pass_word
                    //that password hashed with md5
                    $dbAdapter = new Authentication($sm);


                    $config = @$sm->get('config')['events'] [Authentication::class];
                    if (!empty($config)) {
                        foreach ($config as $item) {
                            if (method_exists($item, "getInstance")) {
                                $dbAdapter->addEvent($item::getInstance($sm));

                            }
                        }
                    }


                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbAdapter);
                    $authService->setStorage($sm->get(Session::class));

                    return $authService;
                },
                Acl::class => function (ServiceLocatorInterface $sm) {
                    $config = $sm->get('config');


                    return new Acl($config['permission']['roles'], $config['permission']['resources'], $config['permission']['acl']);
                },
                AuthUser::class => function (ServiceLocatorInterface $sm) {
                    return new AuthUser($sm);
                },
                UsuarioService::class => function (ServiceLocatorInterface $sm) {
                    return new UsuarioService($sm);
                }
            ),
        );
    }
}
