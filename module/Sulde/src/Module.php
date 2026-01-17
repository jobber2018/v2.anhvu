<?php
/**
 * Copyright (c) 2019.
 * Created by   : TruongHM
 * Created date: 7/13/19 12:33 PM
 *
 */


namespace Sulde;


use Zend\Authentication\AuthenticationService;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Zend\Session\SessionManager;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }


    public function init(ModuleManager $moduleManager)
    {
        $events = $moduleManager->getEventManager();

        // Registering a listener at default priority, 1, which will trigger
        // after the ConfigListener merges config.
        $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'onMergeConfig'));
    }

    public function onMergeConfig(ModuleEvent $e)
    {
        //https://framework.zend.com/manual/2.4/en/tutorials/config.advanced.html#manipulating-merged-configuration
        $configListener = $e->getConfigListener();
        $config         = $configListener->getMergedConfig(false);
        //session_start();
        //$dbName=@$_SESSION['_schema'];
//        $userInfo=@$_SESSION['userInfo'];
        //$config['doctrine']['connection']['orm_default']['params']['dbname'] = $dbName;
//        print_r($config['doctrine']['connection']);
        // Pass the changed configuration back to the listener:
        $configListener->setMergedConfig($config);
    }

    public function onBootstrap(MvcEvent $mvcEvent)
    {

        //$sessionManager = $mvcEvent->getApplication()->getServiceManager()->get('Zend\Session\SessionManager');
        //$this->forgetInvalidSession($sessionManager);
        //$dbName=@$_SESSION['_schema'];

        $eventManager = $mvcEvent->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $serviceManager = $mvcEvent->getApplication()->getServiceManager();

//        $eventManager->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'onMergeConfig'));
//        $sl=$mvcEvent->getTarget()->getServiceLocator();
//        $config = $serviceManager->get('Config');
//        $config['doctrine']['connection']['orm_default']['params']['dbname'] = 'xmart';
//        $serviceManager->getMergedConfig('Config',$config);
//        $sl->setEntityManager($config);
        // Tập hợp toàn bộ các ngôn ngữ trên site
        $langArr = array(
            'vi' => 'vi_VN', // tiếng việt
            'en' => 'en_US' // tiếng anh
        );
        //refer: http://kienthucweb.net/chuyen-doi-qua-lai-2-ngon-ngu-trong-zend-2.html?fbclid=IwAR3px8J_P73IlOvwPxhP5YQRxoJNdYragZgiwdbtNIoHfbalNSTd9LmhD8Q
        // 1. Trường hợp lấy từ $_GET với tham số lang. Example : http://example.com/?lang=vi
//        $codeLang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
        // 2. Trường hợp lấy từ route. Example : http://example.com/lang/vi
        $router = $serviceManager->get('router');
        $request = $serviceManager->get('request');
        /* @var $routeMatch \Zend\Mvc\Router\Http\RouteMatch */
        $routeMatch = $router->match($request);

        try {
            //$codeLang = $routeMatch->getParam('lang', 'vi');
            $codeLang = isset($_GET['lang']) ? $_GET['lang'] : 'vi';
        }catch (\Exception $e){
            $codeLang ="vi";
        }

        // Đối tượng ngôn ngữ mặc định trong hệ thống zend 2
        $translateObj = $serviceManager->get('MvcTranslator');
        $translateObj->setLocale($langArr[$codeLang]);
    }


    protected function forgetInvalidSession($sessionManager)
    {
        try {
            $sessionManager->start();
            return;
        } catch (\Exception $e) {
        }
        /**
         * Session validation failed: toast it and carry on.
         */
        // @codeCoverageIgnoreStart
        session_unset();
        // @codeCoverageIgnoreEnd
    }
}