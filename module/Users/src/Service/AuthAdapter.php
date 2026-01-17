<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/17/19 3:20 PM
 *
 */


namespace Users\Service;


use Users\Entity\Privileges;
use Users\Entity\Roles;
use Users\Entity\User;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\Exception\ExceptionInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Zend\Session\SessionManager;

class AuthAdapter implements AdapterInterface
{
    private $entityManager;
    private $mobile;
    private $email;
    private $password;

    public function __construct($entityManager){
        $this->entityManager = $entityManager;
    }
    public function setMobile($mobile){
        $this->mobile = $mobile;
    }
    public function setEmail($email){
        $this->email = $email;
    }
    public function setPassword($password){
        $this->password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @return Result
     * @throws ExceptionInterface
     *     If authentication cannot be performed
     */
    public function authenticate()
    {

//        $user = $this->entityManager->getRepository(User::class)->findOneBy(array("username"=>$this->username));
        if($this->mobile){
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(array("mobile"=>$this->mobile));
        }else{
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(array("email"=>$this->email));
        }
        if(!$user){
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['Không tìm thấy thông tin đăng nhập, vui lòng liên hệ admin!']);
        }else{
            $bcrypt = new Bcrypt();

            $userPassword = $this->password; //pw do người dừng nhập
            $passwordHash = $user->getPassword(); // pw đã lưu trong db
            if($bcrypt->verify($userPassword,$passwordHash)){
                //get privilege of role
                $roles = $this->entityManager->getRepository(Roles::class)->findOneBy(array("code"=>$user->getRole()));
                $privileges = array();
                if($roles){
                    foreach ($roles->getPrivileges() as $privilege)
                        $privileges[$privilege->getId()] = $this->getPrivilegeResult($privilege);
                }

                //set all privilege allow=all;
                $privilegePublic = $this->entityManager->getRepository(Privileges::class)->findBy(array("allow" => 'all'));
                foreach ($privilegePublic as $privilege)
                    $privileges[$privilege->getId()] = $this->getPrivilegeResult($privilege);

                //set đặc quyền dành cho user
                $userPrivatePrivileges=$user->getPrivatePrivileges();
                foreach ($userPrivatePrivileges as $privilege)
                    $privileges[$privilege->getId()] = $this->getPrivilegeResult($privilege);

                //set all privilege to session
                $user->setPrivileges($privileges);
                return new Result(Result::SUCCESS,
                    $user,
                    ['Logged in successfully.']
                );
            }

        }
    }

    private function getPrivilegeResult($privilege){
        return array(
            "id"=>$privilege->getId()
            ,"controller"=>$privilege->getController()
            ,"action"=>$privilege->getAction()
            ,'allow'=>$privilege->getAllow()
            ,'name'=>$privilege->getName()
            ,'url'=>$privilege->getUrl()
            ,'parent'=>$privilege->getParent()
            ,'icon'=>$privilege->getIcon()
            ,'menu_display'=>$privilege->getMenuDisplay()
            ,'menu_sort'=>$privilege->getMenuSort()
            ,'dashboard_display'=>$privilege->getDashboardDisplay()
        );
    }
}