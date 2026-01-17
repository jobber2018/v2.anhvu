<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */

namespace Admin\Controller\Factory;

use Admin\Controller\AdminController;
use Admin\Controller\StaffController;
use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StaffControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Get current user
        $authService = $container->get(AuthenticationService::class);

        if (! $authService->hasIdentity()) {
            throw new \RuntimeException(
                'It is not possible to create a dynamic entity manager before a user has logged in'
            );
        }

        $user = $authService->getIdentity();
        //$dbName = $user->getGroup()->getDbName();
        $dbName='anhvu';
//        print_r($user->getFullname());

        // Update connection config
        $globalConfig = $container->get('config');
        $globalConfig['doctrine']['connection']['orm_default']['params']['dbname'] = $dbName;

//        $isAllowOverride = $serviceLocator->getAllowOverride();
//        $serviceLocator->setAllowOverride(true);
//        $serviceLocator->setService('config', $globalConfig);
//        $serviceLocator->setAllowOverride($isAllowOverride);
//        print_r($globalConfig['doctrine']['connection']);
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
//        var_dump($entityManager);
        return new StaffController($entityManager);
    }
}