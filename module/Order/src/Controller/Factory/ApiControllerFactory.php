<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Order\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Order\Controller\ApiController;
use Order\Service\OrderManager;

class ApiControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $orderManager = $container->get(OrderManager::class);
        return new ApiController($entityManager, $orderManager);
    }
}