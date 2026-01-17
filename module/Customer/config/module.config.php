<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/24/19 10:57 AM
 *
 */


namespace Customer;

use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'customer-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/customer[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'customer-crm-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/customer/crm[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\CrmController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'customer-user' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/user/customer[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'index',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AdminController::class => Controller\Factory\AdminControllerFactory::class,
            Controller\CrmController::class => Controller\Factory\CrmControllerFactory::class,
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class
        ],
    ],
    'doctrine' => [
        'driver' => [
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            __NAMESPACE__.'_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    // register __NAMESPACE__.'_driver' for any entity under namespace `Hotel\Entity`
                    __NAMESPACE__.'\Entity' => __NAMESPACE__.'_driver',
                ]
            ],
        ],

    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],

    ],
    'service_manager'=>[
        'factories' => [
            Service\CustomerManager::class  => Service\Factory\CustomerManagerFactory::class,
            Service\GroupManager::class  => Service\Factory\GroupManagerFactory::class,
            Service\RouteManager::class  => Service\Factory\RouteManagerFactory::class,
            Service\CrmManager::class  => Service\Factory\CrmManagerFactory::class,
        ]
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ]

];