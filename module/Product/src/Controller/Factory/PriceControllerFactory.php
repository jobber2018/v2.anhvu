<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Product\Controller\Factory;


use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Product\Controller\PriceController;
use Product\Service\PriceManager;
use Product\Service\ProductManager;

class PriceControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $productManager = $container->get(ProductManager::class);
        $priceManager = $container->get(PriceManager::class);
        return new PriceController($entityManager, $priceManager,$productManager);
    }

}