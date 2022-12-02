<?php

namespace Admin;

use Admin\Controller\Plugins\Acl;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'adminError' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/error',
                    'defaults' => [
                        'controller' => Controller\ErrorController::class,
                        'action' => 'forbidden',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'forbidden' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/forbidden',

                            'defaults' => [
                                'action' => 'forbidden',

                            ]
                        ]
                    ],
                    'noResource' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/no-resource',
                            'defaults' => [
                                'action' => 'noResource',
                            ]
                        ]
                    ], 'noRole' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/no-role',
                            'defaults' => [
                                'action' => 'noRole',
                            ]
                        ]
                    ],
                ]
            ],
            'admin' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/admin/index.phtml',
                    'defaults' => [
                        'module' => 'admin',
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'sair' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/sair',
                    'defaults' => [
                        'module' => 'admin',
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'trocarSenha' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/trocar-senha',
                    'defaults' => [
                        'module' => 'admin',
                        'controller' => Controller\AuthController::class,
                        'action' => 'changePassword',
                    ],
                ],
            ],
            'auth' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/auth',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'auth',
                        'module' => 'admin',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'auth' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/authenticate',
                            'defaults' => [
                                'action' => 'authenticate',
                            ]
                        ]
                    ],
                    'authenticateJson' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/authenticate-json',
                            'defaults' => [
                                'action' => 'authenticateJson',
                            ]
                        ]
                    ],
                    'renewToken' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/renew-token',
                            'defaults' => [
                                'action' => 'renewToken',
                            ]
                        ]
                    ],
                ]
            ],
            'user' =>[
                'type' => Segment::class,
                'options' => [
                    'route' => '/api/users[/:id]',
                    'constraints' => [
                        'id' => '[a-zA-Z0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                    ],
                ]

            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\AuthController::class => InvokableFactory::class,
            Controller\ErrorController::class => InvokableFactory::class,
            Controller\UserController::class => InvokableFactory::class,

        ],
    ],

    'view_manager' => [
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        'template_path_stack' => [
        ],
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ],
            ],
        ]
    ],
    'controller_plugins' => array(
        'invokables' => array(
            Acl::class => Acl::class,
        )
    ),
    // permissooes do sistema
    'permission' => [
        'resources' => [
            ['name' => 'admin:index'],
            ['name' => 'admin:error'],
            ['name' => 'admin:auth'],
            ['name' => 'admin:user'],
        ],
        'acl' => [
            ['roles' => ['anonymous'], 'resources' => ['admin:auth','admin:error'], 'privileges' => null, 'allow' => true],
            ['roles' => ['anonymous'], 'resources' => ['admin:index'], 'privileges' => null, 'allow' => false],
            ['roles' => ['anonymous'], 'resources' => ['admin:user'], 'privileges' => null, 'allow' => false],
        ]
    ]

];
