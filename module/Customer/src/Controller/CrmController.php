<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Customer\Controller;

use Admin\Service\AdminManager;
use Customer\Entity\Address;
use Customer\Entity\Crm;
use Customer\Entity\Customer;
use Customer\Form\CustomerForm;
use Customer\Service\CrmManager;
use Customer\Service\CustomerManager;
use Customer\Service\GroupManager;
use Customer\Service\RouteManager;
use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\UploadedFile;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Product\Entity\Image;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\FileUploader;
use Sulde\Service\SuldeAdminController;


class CrmController extends SuldeAdminController
{
    private $entityManager;
    private $crmManager;

    public function __construct(EntityManager $entityManager, CrmManager $crmManager)
    {
        $this->entityManager = $entityManager;
        $this->crmManager = $crmManager;
    }

    public function listAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $customerId = $this->params()->fromPost('customer_id',0);
            $length = $this->params()->fromPost('length',Define::ITEM_PAGE_COUNT);
            $start = $this->params()->fromPost('start',0);
            $draw = $this->params()->fromPost('draw',1);

            $crms = $this->crmManager->search($customerId,'', $length, $start);

            $crmResult=array();
            foreach ($crms as $crmItem){
                $crmResult[]=$crmItem->serialize();
            }
            $result['draw']=$draw;
            $result['recordsTotal']=count($crms);
            $result['recordsFiltered']=count($crms);
            $result['data']=$crmResult;
            return new JsonModel($result);
        }
        return new ViewModel();
    }

    public function addAction()
    {
        $request = $this->getRequest();

        if($request->isPost()){
            try{
                $content=trim($request->getPost('content',''));
                $customerId=trim($request->getPost('customer_id',''));

                if(empty($content))
                    throw new \Exception('Vui lòng nhập ghi chú!');

                if(empty($customerId))
                    throw new \Exception('Không tìm thấy thông tin khách hàng!');

                $crm = new Crm();
                $crm->setContent($content);
                $crm->setCustomerId($customerId);
                $crm->setCreatedDate(new \DateTime());
                $crm->setCreatedBy($this->userLogin->getUsername());

                $this->entityManager->persist($crm);
                $this->entityManager->flush();

                $result['status']=1;
                $result['data']=$crm->serialize();
                $result['message']='Đã thêm ghi chú!';

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);
        }

        $form=new CustomerForm($groupManager->getAll(),$routeManager->getAll());

        return new ViewModel(['form'=>$form]);
    }
}