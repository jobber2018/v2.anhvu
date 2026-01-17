<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/17/19 6:30 PM
 *
 */

namespace Users\Controller;
use DateTime;
use EmailTemplate\Entity\EmailTemplate;
use EmailTemplate\Service\EmailTemplateManager;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Google_Client;
use Google_Service_Plus;
use Mustache_Engine;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Users\Service\AuthManager;
use Users\Service\UserManager;
use Zend\Authentication\AuthenticationService;
use Zend\Crypt\Password\Bcrypt;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mail\Message;
use Zend\Math\Rand;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Users\Form\LoginForm;
use Zend\Authentication\Result;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Mime;
use Zend\Mime\Message as MimeMessage;

class AuthController extends AbstractActionController{

    private $entityManager, $userManager, $authManager, $authService;

    /**
     * AuthController constructor.
     * @param $entityManager
     * @param UserManager $userManager
     * @param AuthManager $authManager
     * @param AuthenticationService $authService
     */
    public function __construct($entityManager, UserManager $userManager,AuthManager $authManager,AuthenticationService $authService){
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->authManager = $authManager;
        $this->authService = $authService;
    }

    /**
     * @return Response|ViewModel
     * @throws \Exception
     */

    public function loginAction(){
        $form = new LoginForm;
        if($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $form->setData($data);
            if($form->isValid()){
                /**
                $param["mobile"] = $data["mobile"];
                $param["password"] = $data["password"];
                //Call login api
                $request = new Request();
                $request->getHeaders()->addHeaders(array(
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
                ));
                $loginUri=Define::API_HOST.Define::API_URL_LOGIN;
                $request->setUri($loginUri);
                $request->setMethod('POST');

                $request->setContent(json_encode($param));
                $client = new Client();
                $response = $client->dispatch($request);
                $responseData = json_decode($response->getBody(), true);

                if($responseData["status"]){
//                    $_SESSION['_schema']=$responseData['schema'];
//                    $_SESSION['_username']=$responseData['username'];
                    $_username=$responseData['username'];
                    $auth=$this->authManager->login($_username);
                    if($auth->getCode() == Result::SUCCESS){
                        $loginInfo = $_SERVER ['HTTP_USER_AGENT'];
                        $identity = $auth->getIdentity();
                        $this->userManager->updateLogin($identity,$loginInfo);
                        return $this->redirect()->toRoute('admin-dashboard');
                    }else{
                        $message = current($auth->getMessages());
                        $this->flashMessenger()->addErrorMessage($message);
                        return $this->redirect()->toRoute('login');
                    }
                }else{
                    $this->flashMessenger()->addErrorMessage($responseData["message"]);
                }
                 **/
                $result = $this->authManager->login($data['mobile'], $data['password'], $data['remember']);
                if($result->getCode() == Result::SUCCESS){
                    $loginInfo = $_SERVER ['HTTP_USER_AGENT'];
                    $identity = $result->getIdentity();
                    $this->userManager->updateLogin($identity,$loginInfo);
//                    if($identity->getRole()=='admin')
                    return $this->redirect()->toRoute('admin-dashboard');
//                    elseif ($identity->getRole()=='staff')
//                        return $this->redirect()->toRoute('staff-dashboard');

//                    return $this->redirect()->toRoute('user-admin',array('controller' => 'admin','action' =>  'profile'));
                }
                else{
                   $message = current($result->getMessages());
                   $this->flashMessenger()->addErrorMessage($message);
                   return $this->redirect()->toRoute('login');
                }
            }

        }
        $this->layout()->setTemplate('layoutLogin');
        return new ViewModel(['form'=>$form]);
    }

    public function logoutAction(){
        $this->authManager->logout();
        return $this->redirect()->toRoute('login');
    }

    public function authAction(){
        $_username=@$_SESSION['_username'];
        $auth=$this->authManager->login($_username);
        if($auth->getCode() == Result::SUCCESS){
            $loginInfo = $_SERVER ['HTTP_USER_AGENT'];
            $identity = $auth->getIdentity();
            $this->userManager->updateLogin($identity,$loginInfo);
            return $this->redirect()->toRoute('admin-dashboard');
        }
    }
}
?>