<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Purchase\Controller\Factory;

use Interop\Container\ContainerInterface;
use Purchase\Controller\ApiController;
use Purchase\Service\PurchaseManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class ApiControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $purchaseManager = $container->get(PurchaseManager::class);
        return new ApiController($entityManager, $purchaseManager);
    }
}