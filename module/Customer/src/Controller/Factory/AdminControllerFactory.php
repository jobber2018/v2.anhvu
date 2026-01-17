<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Customer\Controller\Factory;

use Customer\Controller\AdminController;
use Customer\Service\CustomerManager;
use Interop\Container\ContainerInterface;

use Laminas\ServiceManager\Factory\FactoryInterface;

class AdminControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $customerManager = $container->get(CustomerManager::class);
        return new AdminController($entityManager, $customerManager);
    }
}