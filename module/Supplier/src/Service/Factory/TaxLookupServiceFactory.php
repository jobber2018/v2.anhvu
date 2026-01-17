<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:48 AM
 *
 */

namespace Supplier\Service\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as HttpClient;
use Interop\Container\ContainerInterface;
use Supplier\Service\TaxLookupService;


class TaxLookupServiceFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $client = new HttpClient();
        return new TaxLookupService($client);
    }
}