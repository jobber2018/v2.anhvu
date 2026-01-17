<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Supplier\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Supplier\Controller\DebtController;
use Supplier\Service\DebtManager;
use Interop\Container\ContainerInterface;


class DebtControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $supplierManager = $container->get(DebtManager::class);
        return new DebtController($entityManager, $supplierManager);
    }

}