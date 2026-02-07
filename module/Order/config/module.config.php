<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/24/19 10:57 AM
 *
 */


namespace Order;

use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'order-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/order/admin[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'order-api' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/order/api[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
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
            Controller\ApiController::class => Controller\Factory\ApiControllerFactory::class
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
            Service\OrderManager::class  => Service\Factory\OrderManagerFactory::class,
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