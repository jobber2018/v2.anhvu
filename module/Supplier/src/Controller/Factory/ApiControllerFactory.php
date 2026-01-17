<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Supplier\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Supplier\Controller\ApiController;
use Supplier\Service\SupplierManager;
use Supplier\Service\TaxLookupService;

class ApiControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $taxLookupService = $container->get(TaxLookupService::class);
        return new ApiController($entityManager, $taxLookupService);
    }
}