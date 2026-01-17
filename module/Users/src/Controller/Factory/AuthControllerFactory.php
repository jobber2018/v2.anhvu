<?php
namespace Users\Controller\Factory;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceManager;
use Users\Service\UserManager;
use Users\Service\AuthManager;
use Users\Controller\AuthController;
use Zend\Authentication\AuthenticationService;

class AuthControllerFactory implements FactoryInterface{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        //$config = $container->get('config');
        //$config['doctrine']['connection']['orm_default']['params']['dbname'] = 'xmart';

        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);
        $authManager = $container->get(AuthManager::class);
        $authService = $container->get(AuthenticationService::class);

        /*if (!isset($config['mail_options'])) {
            throw new Exception(); // Use a specific exception here.
        }
        $mailOptions = $config['mail_options'];*/

        return new AuthController($entityManager, $userManager,$authManager,$authService);
    }
}


?>