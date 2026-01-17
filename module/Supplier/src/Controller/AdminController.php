<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Supplier\Controller;


use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Supplier\Entity\Supplier;
use Supplier\Form\SupplierForm;
use Supplier\Service\SupplierManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeAdminController;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $supplierManager;
    private $_user;
    public function __construct(EntityManager $entityManager, SupplierManager $supplierManager)
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
    }
    public function dashboardAction()
    {
        $privilegeId=5;
        return new ViewModel([
            'privilegeId'=>$privilegeId,
            'privileges'=>$this->userLogin->getPrivileges()
        ]);
    }

    /**
     * @return ViewModel
     */
    public function listAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $keyword = $this->params()->fromPost('search')['value'];
            $length = $this->params()->fromPost('length',Define::ITEM_PAGE_COUNT);
            $start = $this->params()->fromPost('start',0);
            $draw = $this->params()->fromPost('draw',1);

            $suppliers = $this->supplierManager->search($keyword,$length,$start);
            $supplierResult = array();
            foreach ($suppliers as $supplierItem){
                $supplierResult[]=$supplierItem->serialize();
            }
            $result['draw']=$draw;
            $result['recordsTotal']=count($suppliers);
            $result['recordsFiltered']=count($suppliers);
            $result['data']=$supplierResult;
            return new JsonModel($result);
        }
        return new ViewModel();
    }

    public function addAction(){
        $form =new SupplierForm("add");
        $request = $this->getRequest();
        if($request->isPost()){
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($data);
            $result['status']=1;
            if($form->isValid()){
                try{
                    $tax_code=$data["tax_code"];
                    if($tax_code) {
                        $supplierTaxCode = $this->supplierManager->getByTaxCode($tax_code);
                        if($supplierTaxCode) throw new \Exception("Mã số thuế $tax_code đang sử dụng: ".$supplierTaxCode->getName());
                    }

                    $short_name = ($data["short_name"])?$data["short_name"]:$this->_getShortName($data["name"]);
                    $supplier = new Supplier();
                    $supplier->setName($data["name"]);
                    $supplier->setShortName($short_name);
                    $supplier->setMobile($data["mobile"]);
                    $supplier->setAddress($data["address"]);
                    $supplier->setTaxCode($tax_code);
                    $supplier->setNotes($data["notes"]);
                    $supplier->setContactPerson($data["contact_person"]);
                    if($data['email'] && Common::verifyEmail($data["email"]))
                        $supplier->setEmail($data['email']);

                    $supplier->setCreatedBy($this->userLogin->getUsername());
                    $supplier->setCreatedDate(new \DateTime());

                    $this->entityManager->persist($supplier);
                    $this->entityManager->flush();

//                    return $this->redirect()->toRoute('supplier-admin',['action'=>'list']);
                    $result['status']=1;
                    $result['supplier']=$supplier->serialize();
                }catch (\Exception $e){
                    $message = $e->getMessage();
                    $result['status']=0;
                    $result['message']=$message;
                }
            }
            else{
                $result['status']=0;
                $result['message']=$form->getMessages();
            }
            return new JsonModel($result);
        }
        return new ViewModel(['form'=>$form]);
    }

    public function editAction(){
        $supplierId = $this->params()->fromRoute('id',0);
        $form =new SupplierForm("edit");

        $supplier = $this->supplierManager->getByPublicId($supplierId);

        $request = $this->getRequest();
        if($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($data);

            if ($form->isValid()) {
                try {

                    $short_name = ($data["short_name"])?$data["short_name"]:$this->_getShortName($data["name"]);

                    $supplier->setName($data["name"]);
                    $supplier->setShortName($short_name);
                    $supplier->setMobile($data["mobile"]);
                    $supplier->setAddress($data["address"]);
                    $supplier->setTaxCode($data["tax_code"]);
                    $supplier->setNotes($data["notes"]);
                    $supplier->setContactPerson($data["contact_person"]);
                    if($data['email'] && Common::verifyEmail($data["email"]))
                        $supplier->setEmail($data['email']);

                    $this->entityManager->persist($supplier);
                    $this->entityManager->flush();

//                    $this->flashMessenger()->addSuccessMessage('Đã sửa thông tin: ' . $data["name"]);
                    return $this->redirect()->toRoute('supplier-admin',['action'=>'list']);
                } catch (\Exception $e) {
                    $message = $e->getMessage();
//                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }else{
            $data=[
                    "name"=>$supplier->getName(),
                    "mobile"=>$supplier->getMobile(),
                    "address"=>$supplier->getAddress(),
                    "tax_code"=>$supplier->getTaxCode(),
                    "notes"=>$supplier->getNotes(),
                    "contact_person"=>$supplier->getContactPerson(),
                    "email"=>$supplier->getEmail()
                ];
            $form->setData($data);
        }
        return new ViewModel(['form'=>$form]);
    }

    public function autocompleteAction(){
        try{
            $keyword = $this->params()->fromQuery('q','');
            $suppliers = $this->supplierManager->search($keyword,Define::ITEM_PAGE_COUNT, 0);
            $searchResult = array();
            foreach ($suppliers as $supplierItem){
                $searchResult[]=$supplierItem->serialize();
            }
            $result['status']=1;
            $result['data']=$searchResult;
        }catch (\Exception $e){
            $result['status']=0;
            $result['message']=$e->getMessage();
        }
        return new JsonModel($result);
    }


    /**
     * Lấy tên định danh chính của công ty
     */
    private function _getShortName(string $companyName): string
    {
        // Chuẩn hóa khoảng trắng & chữ hoa
        $name = mb_strtoupper(trim($companyName), 'UTF-8');

        // Danh sách tiền tố cần loại bỏ (theo thực tế VN)
        $prefixes = [
            'CÔNG TY',
            'TNHH',
            'TRÁCH NHIỆM HỮU HẠN',
            'CỔ PHẦN',
            'CP',
            'TẬP ĐOÀN',
            'GROUP',
            'JSC',
            'LTD',
            'THƯƠNG MẠI',
            'DỊCH VỤ',
            'VÀ',
            'HỘ KINH DOANH'
        ];

        // Loại bỏ tiền tố
        foreach ($prefixes as $prefix) {
            $name = preg_replace(
                '/\b' . preg_quote($prefix, '/') . '\b/u',
                '',
                $name
            );
        }

        // Xóa khoảng trắng dư
        $name = preg_replace('/\s+/u', ' ', trim($name));

        return $name;
    }
}