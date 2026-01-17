<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Supplier\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Supplier\Controller\PaymentController;
use Supplier\Service\PaymentManager;


class PaymentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $paymentManager = $container->get(PaymentManager::class);
        $translator      = $container->get('MvcTranslator'); // <- lấy translator
        return new PaymentController($entityManager, $paymentManager,$translator);
    }

}