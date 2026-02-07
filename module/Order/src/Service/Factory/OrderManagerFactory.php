<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:48 AM
 *
 */

namespace Order\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Order\Service\OrderManager;

class OrderManagerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        return new OrderManager($entityManager);
    }
}