<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/24/19 10:57 AM
 *
 */


namespace Supplier;

use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'supplier-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin/supplier[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'supplier-debt' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/supplier/debt[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\DebtController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'supplier-payment' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/supplier/payment[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\PaymentController::class,
                        'action'     => 'index',
                    ]
                ],
            ],
            'supplier-api' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/supplier/api[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'index',
                    ]
                ],
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AdminController::class => Controller\Factory\AdminControllerFactory::class,
            Controller\ApiController::class => Controller\Factory\ApiControllerFactory::class,
//            Controller\IndexController::class => Controller\Factory\IndexControllerFactory::class,
            Controller\PaymentController::class => Controller\Factory\PaymentControllerFactory::class,
            Controller\DebtController::class => Controller\Factory\DebtControllerFactory::class,
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
            Service\SupplierManager::class  => Service\Factory\SupplierManagerFactory::class,
            Service\DebtManager::class  => Service\Factory\DebtManagerFactory::class,
            Service\PaymentManager::class  => Service\Factory\PaymentManagerFactory::class,
            Service\TaxLookupService::class  => Service\Factory\TaxLookupServiceFactory::class

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