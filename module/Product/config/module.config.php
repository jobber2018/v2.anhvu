<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/24/19 10:57 AM
 *
 */


namespace Product;

use Laminas\Cache\StorageFactory;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'product-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/product[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'product-price-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/product/price[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\PriceController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'product-variant-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/product/variant[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\VariantController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'product-excel-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/product/excel[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\ExcelController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'product-user' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/user/product[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'index',
                    ],
                    'constraints'=>[
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]*'
                    ]
                ],
            ],
            'product-front' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/product[/:action[/:id]].html',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
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
            Controller\PriceController::class => Controller\Factory\PriceControllerFactory::class,
            Controller\VariantController::class => Controller\Factory\VariantControllerFactory::class,
            Controller\ExcelController::class => Controller\Factory\ExcelControllerFactory::class,
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
            Service\ProductManager::class  => Service\Factory\ProductManagerFactory::class,
            Service\PriceManager::class  => Service\Factory\PriceManagerFactory::class,
            Service\VariantManager::class  => Service\Factory\VariantManagerFactory::class,
            // Redis Cache
            /*'ProductCache' => function() {
                return StorageFactory::factory([
                    'adapter' => [
                        'name' => 'redis',
                        'options' => [
                            'server' => [
                                'host' => '127.0.0.1',
                                'port' => 6379
                            ]
                        ]
                    ]
                ]);
            },*/
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