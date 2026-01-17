<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Purchase\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Purchase\Controller\ReturnController;
use Purchase\Service\PurchaseReturnManager;

class ReturnControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $purchaseManager = $container->get(PurchaseReturnManager::class);
        $translator      = $container->get('MvcTranslator'); // <- lấy translator
        return new ReturnController($entityManager, $purchaseManager,$translator);
    }

}