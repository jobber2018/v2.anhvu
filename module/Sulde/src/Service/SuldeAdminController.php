<?php
/**
 * Copyright (c) 2019.
 * Created by   : TruongHM
 * Created date: 7/13/19 12:38 PM
 *
 */


namespace Sulde\Service;


use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\MvcEvent;
use Sulde\Service\Common\Common;

class SuldeAdminController extends AbstractActionController
{
    const VERSION = '3.0.3-dev';
//    protected $userInfo;
    protected $userLogin;
    protected $roles;

    public function onDispatch(MvcEvent $e)
    {
        $authManager = $e->getApplication()->getServiceManager()->get(AuthenticationService::class);
        if ($authManager->hasIdentity()) {

            $this->userLogin = $authManager->getIdentity();

            $breadcrumb[]=array(
                'name'=>'Home',
                'url'=>'admin-dashboard',
                'action'=>'index'
            );

            $routeMatch = $e->getRouteMatch();
            $controller = $routeMatch->getParam('controller');
            $action = $routeMatch->getParam('action');

            $isPrivileges = Common::isPermission($controller, $action, $this->userLogin->getPrivileges());

            if($isPrivileges){
                $this->layout()->setTemplate('adminlte');
                //sort role group for tree
                $roles = array();
                foreach ($this->userLogin->getPrivileges() as $privilege){
                   @$roles[$privilege['parent']][]=$privilege;
                }
                $this->roles=$roles;

                $parentPrivileges=@$this->userLogin->getPrivileges()[$isPrivileges['parent']];
                if($parentPrivileges){
                    $breadcrumb[]=array(
                        'name'=>$parentPrivileges['name'],
                        'url'=>$parentPrivileges['url'],
                        'action'=>$parentPrivileges['action']
                    );
                }
                /*$breadcrumb[]=array(
                    'name'=>$isPrivileges['name'],
                    'url'=>$isPrivileges['url'],
                    'action'=>$isPrivileges['action'],
                    'active'=>1,
                );*/

                $e->getViewModel()->setVariable('authIdentity', $this->userLogin);
                $e->getViewModel()->setVariable('roles', $roles);
                $e->getViewModel()->setVariable('breadcrumb', $breadcrumb);
                $e->getViewModel()->setVariable('privilege', $isPrivileges);
            }else{
                $this->flashMessenger()->addErrorMessage("You do not have the permissions to access this function!");
                return $this->redirect()->toRoute('not-authorized');
            }
        }else{
            return $this->redirect()->toRoute('login');
        }
        //Call default dispatch function
        $response = parent::onDispatch($e);

        return $response;

    }
}