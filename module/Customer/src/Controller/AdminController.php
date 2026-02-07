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
use Customer\Entity\Customer;
use Customer\Form\CustomerForm;
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


class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $customerManager;

    public function __construct(EntityManager $entityManager, CustomerManager $customerManager)
    {
        $this->entityManager = $entityManager;
        $this->customerManager = $customerManager;
    }


    /**
     * @return ViewModel
     */
    public function dashboardAction(){
        $privilegeId=142;
        return new ViewModel([
            'privilegeId'=>$privilegeId,
            'privileges'=>$this->userLogin->getPrivileges()
        ]);
    }

    public function listAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $keyword = $this->params()->fromPost('search')['value'];
            $length = $this->params()->fromPost('length',Define::ITEM_PAGE_COUNT);
            $start = $this->params()->fromPost('start',0);
            $draw = $this->params()->fromPost('draw',1);

            $customers = $this->customerManager->search($keyword, $length, $start);

            $customerResult=array();
            foreach ($customers as $customerItem){
                $customer = $customerItem->serialize();
                $customer['group']=array(
                    'id'=>$customerItem->getGroup()->getId()
                    ,'name'=>$customerItem->getGroup()->getName()
                    ,'code'=>$customerItem->getGroup()->getCode()
                );
                $customer['route']=array(
                    'id'=>$customerItem->getRoute()->getId()
                    ,'name'=>$customerItem->getRoute()->getName()
                    ,'user'=>array(
                        'id'=>$customerItem->getRoute()->getUser()->getId()
                        ,'name'=>$customerItem->getRoute()->getUser()->getUsername()
                )
                );
                $customerResult[]= $customer;
            }
            $result['draw']=$draw;
            $result['recordsTotal']=count($customers);
            $result['recordsFiltered']=count($customers);
            $result['data']=$customerResult;
            return new JsonModel($result);
        }
        return new ViewModel();
    }

    /**
     * @return ViewModel
     */
    public function detailAction(){
        $publicId = $this->params()->fromRoute('id',0);
        $customer = $this->customerManager->getPublicById($publicId);
        $configManage = new ConfigManager();
        return new ViewModel(['customer'=>$customer,'geoKey'=>$configManage->getGeoKey()]);
    }

    public function addAction()
    {
        $request = $this->getRequest();

        $groupManager = new GroupManager($this->entityManager);
        $routeManager = new RouteManager($this->entityManager);

        if($request->isPost()){
            try{
                $data = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );

                $customerName=trim($data['name']);
                if(!$customerName)
                    throw new \Exception('Tên cửa hàng không được để trống!');

                $keyword = str_replace("-"," ",Common::convertAlias($customerName));

                $customerOwnerName=trim($data['owner_name']);
                if(!$customerOwnerName)
                    throw new \Exception('Tên chủ cửa hàng không được để trống!');

                $customerMobile=Common::verifyMobile(trim($data['mobile']));
                if(!$customerMobile)
                    throw new \Exception('Điện thoại khách hàng không được để trống!');

                $customerAddress=trim($data['address']);
                if(!$customerAddress)
                    throw new \Exception('Nhập địa chỉ khách hàng!');

                $groupId = $data['group'];
                if(!$groupId)
                    throw new \Exception('Vui lòng chọn nhóm khách hàng!');

                $routeId = $data['route'];
                if(!$routeId)
                    throw new \Exception('Vui lòng chọn tuyến chăm sóc cho khách hàng!');

                $groupItem = $groupManager->getById($groupId);
                $routeItem = $routeManager->getById($routeId);

                $customer = new Customer();
                $customer->setName($customerName);
                $customer->setKeyword($keyword);
                $customer->setOwnerName($customerOwnerName);
                $customer->setMobile($customerMobile);
                $customer->setNote($data['note']);
                $customer->setDeliveryNote(trim($data['delivery_note']));
                $customer->setStatus(Define::DEFAULT_ACTIVE);
                $customer->setCreatedDate(new \DateTime());
                $customer->setCreatedBy($this->userLogin->getUsername());
                $customer->setGroup($groupItem);
                $customer->setRoute($routeItem);

                $fileArray=$request->getFiles()->toArray();
                if(!empty($fileArray['imageFile']['name'])) {
                    $uploadedFile = new UploadedFile(
                        $fileArray['imageFile']['tmp_name'],
                        $fileArray['imageFile']['size'],
                        $fileArray['imageFile']['error'],
                        $fileArray['imageFile']['name'],
                        $fileArray['imageFile']['type']
                    );

                    $fileUploader = new FileUploader(Define::CUSTOMER_IMAGE_PATH);
                    $resultUpload = $fileUploader->upload($uploadedFile);

                    if ($resultUpload['success'])
                        $customer->setImage($resultUpload['filename']);
                }

                $address = new Address();
                $address->setCustomer($customer);
                $address->setAddress($customerAddress);
                $address->setLat(0);
                $address->setLng(0);
                $address->setIsDefault(Define::DEFAULT_ACTIVE);
                $address->setCreatedDate(new \DateTime());
                $address->setCreatedBy($this->userLogin->getUsername());

                $customer->addAddress($address);

                $this->entityManager->persist($customer);
                $this->entityManager->flush();

                $result['status']=1;
                $result['data']=$customer->serialize();
                $result['message']='Đã thêm mới khách hàng!';

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

    public function editAction()
    {
        $request = $this->getRequest();
        $groupManager = new GroupManager($this->entityManager);
        $routeManager = new RouteManager($this->entityManager);

        if($request->isPost()){
            try{
                $data = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );

                $customerName=trim($data['name']);
                if(!$customerName)
                    throw new \Exception('Tên cửa hàng không được để trống!');

//                $customerNameAlias = Common::convertAlias($customerName);
                $keyword = str_replace("-"," ",Common::convertAlias($customerName));

                $customerOwnerName=trim($data['owner_name']);
                if(!$customerOwnerName)
                    throw new \Exception('Tên chủ cửa hàng không được để trống!');

                $customerMobile=Common::verifyMobile(trim($data['mobile']));
                if(!$customerMobile)
                    throw new \Exception('Điện thoại khách hàng không được để trống!');

                $groupId = $data['group'];
                if(!$groupId)
                    throw new \Exception('Vui lòng chọn nhóm khách hàng!');

                $routeId = $data['route'];
                if(!$routeId)
                    throw new \Exception('Vui lòng chọn tuyến chăm sóc cho khách hàng!');

                $groupItem = $groupManager->getById($groupId);
                $routeItem = $routeManager->getById($routeId);

                $customerId=trim($data['id']);
                $customer = $this->customerManager->getById($customerId);

                $customer->setName($customerName);
                $customer->setKeyword($keyword);
                $customer->setOwnerName($customerOwnerName);
                $customer->setMobile($customerMobile);
                $customer->setNote($data['note']);
                $customer->setDeliveryNote(trim($data['delivery_note']));
                $customer->setModifiedDate(new \DateTime());
                $customer->setModifiedBy($this->userLogin->getUsername());
                $customer->setGroup($groupItem);
                $customer->setRoute($routeItem);

                //insert default image
                $fileArray=$request->getFiles()->toArray();
                if(!empty($fileArray['imageFile']['name'])) {
                    $uploadedFile = new UploadedFile(
                        $fileArray['imageFile']['tmp_name'],
                        $fileArray['imageFile']['size'],
                        $fileArray['imageFile']['error'],
                        $fileArray['imageFile']['name'],
                        $fileArray['imageFile']['type']
                    );

                    $fileUploader = new FileUploader(Define::CUSTOMER_IMAGE_PATH);
                    $resultUpload = $fileUploader->upload($uploadedFile);

                    if ($resultUpload['success']){
                        //xoa anh cu
                        if($customer->getImage()){
                            $imagePath=Define::CUSTOMER_IMAGE_PATH.$customer->getImage();
                            if (file_exists(ROOT_PATH.$imagePath)) {
                                unlink(ROOT_PATH.$imagePath);
                            }
                        }

                        $customer->setImage($resultUpload['filename']);
                    }
                }

                $this->entityManager->persist($customer);
                $this->entityManager->flush();

                $result['status']=1;
                $result['message']='Đã cập nhật dữ liệu khách hàng!';

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);
        }

        $publicId = $this->params()->fromRoute('id',0);
        $form=new CustomerForm($groupManager->getAll(),$routeManager->getAll());
        $customer = $this->customerManager->getPublicById($publicId);
        $data = $customer->serialize();
        $data['route']=$customer->getRoute()->getId();
        $data['group']=$customer->getGroup()->getId();
        $form->setData($data);

        return new ViewModel(['form'=>$form,'customer'=>$customer]);
    }

    public function updateAddressAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try {
                $addressId= $this->params()->fromPost('id',0);
                $address= $this->params()->fromPost('address','');
                $lat= $this->params()->fromPost('lat',0);
                $lng= $this->params()->fromPost('lng',0);
                $isDefault= $this->params()->fromPost('is_default',0);

                if(empty($addressId))
                    throw new \Exception('Không tìm thấy địa chỉ cần cập nhật!');

                $addressItem = $this->customerManager->getAddressById($addressId);

                $customer=$addressItem->getCustomer();

                foreach ($customer->getAddress() as $addressTmp){
                    if($addressTmp->getId()==$addressId){
                        $addressTmp->setAddress($address);
                        $addressTmp->setLat($lat);
                        $addressTmp->setLng($lng);
                        $addressTmp->setIsDefault($isDefault);
                        $addressTmp->setModifiedBy($this->userLogin->getUsername());
                        $addressTmp->setModifiedDate(new \DateTime());
                    }else if($isDefault==1 && $addressTmp->getIsDefault()==1){
                        $addressTmp->setIsDefault(0);
                    }
                }
                $this->entityManager->persist($customer);
                $this->entityManager->flush();
                $result['status']=1;
                $result['message']='Đã cập nhật địa chỉ!';

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);
        }
    }

    public function addAddressAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try {
                $customerId= $this->params()->fromPost('customer_id',0);
                $address= $this->params()->fromPost('address','');
                $lat= $this->params()->fromPost('lat',0);
                $lng= $this->params()->fromPost('lng',0);
                $isDefault= $this->params()->fromPost('is_default',0);

                if(empty($customerId))
                    throw new \Exception('Không tìm thấy khách hàng!');

                $customer = $this->customerManager->getById($customerId);
                if($isDefault==1)
                    foreach ($customer->getAddress() as $addressTmp)
                        $addressTmp->setIsDefault(0);

                $addressItem = new Address();
                $addressItem->setAddress($address);
                $addressItem->setLat($lat);
                $addressItem->setLng($lng);
                $addressItem->setIsDefault($isDefault);
                $addressItem->setModifiedBy($this->userLogin->getUsername());
                $addressItem->setModifiedDate(new \DateTime());
                $addressItem->setCustomer($customer);
                $customer->addAddress($addressItem);

                $this->entityManager->persist($customer);
                $this->entityManager->flush();
                $result['status']=1;
                $result['message']='Thêm mới thành công địa chỉ!';

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);
        }
    }

    public function deleteAddressAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $addressId = $request->getPost("id",0);
                if($addressId==0)
                    throw new \Exception('Không tìm thấy địa chỉ cần xoá!');

                $address = $this->customerManager->getAddressById($addressId);

                if($address->getIsDefault()==1)
                    throw new \Exception('Không thể xoá địa chỉ mặc định của khách hàng!');

                $this->entityManager->remove($address);
                $this->entityManager->flush();
                $result['message'] = 'Đã xoá địa chỉ: '.$address->getAddress();
                $result['status'] = 1;
            } catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    public function deleteImageAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            $customerId = $request->getPost("key");
            try{
                if(!$customerId)
                    throw new \Exception('Không tim thấy khách hàng!');

                $customer = $this->customerManager->getById($customerId);

                $imageName=$customer->getImage();

                if(!$imageName)
                    throw new \Exception('Khách hàng không có ảnh đại diện!');

                $imagePath=Define::CUSTOMER_IMAGE_PATH.$imageName;

                if (file_exists(ROOT_PATH.$imagePath)) {
                    unlink(ROOT_PATH.$imagePath);
                }

                $customer->setImage(null);
                $this->entityManager->persist($customer);
                $this->entityManager->flush();
                $result['status']=1;
                $result['message']='Đã xoá file ảnh!';
            }catch (\Exception $e) {
                $result['status']=0;
                $result['message']=$e->getMessage();
            }
        }else{
            $result['message']='Phương thức gửi file không đúng!';
        }
        return new JsonModel($result);
    }
}