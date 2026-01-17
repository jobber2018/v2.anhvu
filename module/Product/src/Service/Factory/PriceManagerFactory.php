<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:48 AM
 *
 */

namespace Product\Service\Factory;


use Laminas\ServiceManager\Factory\FactoryInterface;
use Product\Service\PriceManager;
use Interop\Container\ContainerInterface;

class PriceManagerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        return new PriceManager($entityManager);
    }
}