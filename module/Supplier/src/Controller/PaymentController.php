<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Supplier\Controller;


use DateTime;
use Laminas\Diactoros\UploadedFile;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Sulde\Service\FileUploader;
use Sulde\Service\ImageUpload;
use Supplier\Entity\SupplierDebtLedger;
use Supplier\Entity\SupplierPayment;
use Supplier\Entity\SupplierPaymentFile;
use Supplier\Form\PaymentForm;
use Supplier\Service\DebtManager;
use Supplier\Service\PaymentManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeAdminController;
use Supplier\Service\SupplierManager;

class PaymentController extends SuldeAdminController
{
    private $entityManager;
    private $paymentManager;
    private TranslatorInterface $translator;
    public function __construct(EntityManager $entityManager, PaymentManager $paymentManager,TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->paymentManager = $paymentManager;
        $this->translator     = $translator;
    }

    public function addAction()
    {
        $request = $this->getRequest();

        $supplierManager = new SupplierManager($this->entityManager);

        if ($request->isPost()) {
            try {
                $data = $request->getPost()->toArray();

                $amount=str_replace(',', '', trim($data['amount']));
                if(!$amount)
                    throw new \Exception('Số tiền không được để trống!');

                $supplierId=$data['supplier'];
                if(!$supplierId)
                    throw new \Exception('Chọn nhà cung cấp!');

                $supplierItem = $supplierManager->getById($supplierId);

                $supplierPayment = new SupplierPayment();
                $supplierPayment->setSupplier($supplierItem);
                $supplierPayment->setAmount($amount);
                $supplierPayment->setStatus(Define::STATUS_PROCESS);
                $supplierPayment->setNote(trim($data['note']));
                $supplierPayment->setMethod(trim($data['method']));

                $date = trim($data['date'] ?? '');
                $supplierPayment->setDate(
                    $date ? Common::createStringToDate($date) : new \DateTime()
                );

                $supplierPayment->setCreatedBy($this->userLogin->getUsername());
                $supplierPayment->setCreatedDate(new \DateTime());

                //insert file
                $fileArray=$request->getFiles()->toArray();
                if(!empty($fileArray['file']['name'])){
                    $uploadedFile = new UploadedFile(
                        $fileArray['file']['tmp_name'],
                        $fileArray['file']['size'],
                        $fileArray['file']['error'],
                        $fileArray['file']['name'],
                        $fileArray['file']['type']
                    );
                    $uploadPath = '/assets/supplier/payment/';
                    $fileUploader = new FileUploader($uploadPath);
                    $resultUpload = $fileUploader->upload($uploadedFile);

                    if(!$resultUpload['success']) throw new \Exception($resultUpload['message']);

                    $supplierPaymentFile = new SupplierPaymentFile();
                    $supplierPaymentFile->setPath($uploadPath.$resultUpload['filename']);
                    $supplierPaymentFile->setSupplierPayment($supplierPayment);
                    $supplierPaymentFile->setCreatedBy($this->userLogin->getUsername());
                    $supplierPaymentFile->setCreatedDate(new \DateTime());
                    $supplierPayment->addFiles($supplierPaymentFile);
                }

                $this->entityManager->persist($supplierPayment);
                $this->entityManager->flush();
                $result['status']=1;
                return $this->redirect()->toRoute('supplier-payment',['action'=>'detail','id'=>$supplierPayment->getPublicId()]);
            }catch
                (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }

        $supplierId = $this->params()->fromRoute('id',0);
        $suppliers = $supplierManager->getAll();
        $supplierData=array();
        $supplierData[] = array(
            'label'=>'...',
            'value'=>0,
            'attributes'=>array(
                'data-total'=>0,
                'data-return-total'=>0,
                'data-payments-total'=>0,
                'data-account-payable'=>0
            )
        );
        foreach ($suppliers as $supplier){
            $debtLedgerInfo=$supplier->getDebtLedgerInfo();
            $supplierData[] = array(
                'label'=>$supplier->getName(),
                'value'=>$supplier->getId(),
                'attributes'=>array(
                    'data-total'=>$debtLedgerInfo['purchase_total'],
                    'data-return-total'=>$debtLedgerInfo['purchase_return_total'],
                    'data-payments-total'=>$debtLedgerInfo['purchase_payments_total'],
                    'data-account-payable'=>$debtLedgerInfo['account_payable'],
                )
            );
        }
        $form =new PaymentForm($supplierData);
        $form->setData(array('supplier'=>$supplierId));
        return new ViewModel(['form'=>$form]);
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

            $keyword=preg_replace('/[^a-zA-Z0-9]/', '', trim($keyword));
            $payments = $this->paymentManager->search($keyword,$length,$start);
            $paymentsResult = array();
            foreach ($payments as $payment){
                $serialize = $payment->serialize();
                $serialize['status_label']=$this->translator->translate($serialize['status']);
                $paymentsResult[]= $serialize;
            }
            $result['draw']=$draw;
            $result['recordsTotal']=count($payments);
            $result['recordsFiltered']=count($payments);
            $result['data']=$paymentsResult;
            return new JsonModel($result);
        }
        return new ViewModel();
    }
    public function detailAction()
    {
        $paymentId = $this->params()->fromRoute('id',0);
        $supplierPayment = $this->paymentManager->getByPublicId($paymentId);

        return new ViewModel(['payment'=>$supplierPayment]);
    }

    public function uploadFileAction(){
        $paymentId = $this->params()->fromRoute('id',0);

        $request = $this->getRequest();

        if($request->isPost()) {
            try{
                if(!$paymentId) throw new \Exception('Không tìm thấy thanh toán!');

                $fileArray=$request->getFiles()->toArray();

                if(empty($fileArray)) throw new \Exception('Không tìm thấy file chứng từ!');

                $uploadedFile = new UploadedFile(
                    $fileArray['file']['tmp_name'],
                    $fileArray['file']['size'],
                    $fileArray['file']['error'],
                    $fileArray['file']['name'],
                    $fileArray['file']['type']
                );
                $uploadPath = '/assets/supplier/payment/';
                $fileUploader = new FileUploader($uploadPath);
                $resultUpload = $fileUploader->upload($uploadedFile);

                if(!$resultUpload['success']) throw new \Exception($resultUpload['message']);

                $payment = $this->paymentManager->getByPublicId($paymentId);

                $supplierPaymentFile = new SupplierPaymentFile();
                $supplierPaymentFile->setSupplierPayment($payment);
                $supplierPaymentFile->setCreatedBy($this->userLogin->getUsername());
                $supplierPaymentFile->setCreatedDate(new \DateTime());
                $supplierPaymentFile->setPath($uploadPath.$resultUpload['filename']);
                $payment->addFiles($supplierPaymentFile);

                $this->entityManager->persist($supplierPaymentFile);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['id'] = $supplierPaymentFile->getId();
                $result['url'] = $uploadPath.$resultUpload['filename'];
                $result['message'] = 'Upload thành công chứng từ!';
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
        }
        else{
            $result['status'] = 0;
            $result['message'] = 'Phương thức gửi file không đúng!';
        }
        return new JsonModel($result);
    }

    public function deleteFileAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $fileId = $request->getPost("id");
            try{
                if($fileId){

                    $paymentFile = $this->entityManager->getRepository(SupplierPaymentFile::class)->find($fileId);

                    if($paymentFile->getSupplierPayment()->getStatus()==Define::STATUS_PAID)
                        throw new \Exception('Không thể xoá file khi phiếu đã xác nhận!');
                    //remove file on server
                    if (file_exists(ROOT_PATH.$paymentFile->getPath())) {
                        unlink(ROOT_PATH.$paymentFile->getPath());
                    }

                    //remove file in data
                    $this->entityManager->remove($paymentFile);
                    $this->entityManager->flush();

                    $result=[
                        'status' => '1',
                        'message'=>'Đã xoá file!'
                    ];

                }else
                    $result=[
                        'status' => '0',
                        'message'=>'Không thể xoá file!'
                    ];
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['message']=$message;
            }
        }else{
            $result=[
                'status' => '0',
                'message'=>'Phương thức gửi file không đúng!'
            ];
        }
        return new JsonModel($result);
    }

    /**
     * get danh sach file cua phieu thanh toan
     * @return JsonModel
     */
    public function fileListAction()
    {
        $paymentId = $this->params()->fromRoute('id',0);
        $files=array();
        try {
            $supplierPayment = $this->paymentManager->getById($paymentId);
            $files=array();
            foreach ($supplierPayment->getFiles() as $paymentFile) {
                $file['id']=$paymentFile->getId();
                $file['name']='';
                $file['type']=Common::getFileExtension($paymentFile->getPath());
                $file['size']=Common::getFileSize($paymentFile->getPath());
                $file['url']=$paymentFile->getPath();
                $files[]=$file;
            }
        }catch (\Exception $e) {}
        return new JsonModel($files);
    }

    /**
     * Xac nhan thanh toan cho phieu thanh toan
     * @return JsonModel|void
     */
    public function confirmAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $paymentId = $request->getPost("id");
        try {
            $supplierPayment = $this->paymentManager->getById($paymentId);
            if($supplierPayment->getStatus()==Define::STATUS_PAID)
                throw new \Exception('Không thể thanh toán, vì chứng từ đã được thanh toán!');

            $DebtLedgerInfo=$supplierPayment->getSupplier()->getDebtLedgerInfo();
            if($DebtLedgerInfo==null || $DebtLedgerInfo['account_payable']<=0)
                throw new \Exception($supplierPayment->getSupplier()->getName().' không có công nợ, nên không thể xác nhận thanh toán!');

            if($DebtLedgerInfo['account_payable']<$supplierPayment->getAmount())
                throw new \Exception('Số tiền thanh toán '.Common::formatMoney($supplierPayment->getAmount()).' lớn hơn công nợ hiện tại '. Common::formatMoney($DebtLedgerInfo['account_payable']));

            $supplierPayment->setStatus(Define::STATUS_PAID);
            $supplierPayment->setConfirmBy($this->userLogin->getUsername());
            $supplierPayment->setConfirmDate(new \DateTime());

            //ghi nhan cong no
            $supplierDebtLedger = new SupplierDebtLedger();
            $supplierDebtLedger->setSupplier($supplierPayment->getSupplier());
            $supplierDebtLedger->setReferenceType(Define::PAYMENTS_CODE);
            $supplierDebtLedger->setReferenceId($supplierPayment->getPublicId());
            $supplierDebtLedger->setDirection(Define::DEBT_OUT);
            $supplierDebtLedger->setAmount($supplierPayment->getAmount()*-1);
            $supplierDebtLedger->setApplyDate($supplierPayment->getDate());
            $supplierDebtLedger->setNote($supplierPayment->getNote());
            $supplierDebtLedger->setCreatedBy($this->userLogin->getUsername());
            $supplierDebtLedger->setCreatedDate(new \DateTime());
            $this->entityManager->persist($supplierDebtLedger);

            $this->entityManager->flush();

            $result['status']=1;
            $result['message']='Ghi nhận thanh toán thành công!';
        }catch (\Exception $e) {
            $result['status']=0;
            $result['message'] = $e->getMessage();
        }
        return new JsonModel($result);
        }
    }

    /**
     * Huy phieu thanh toan da xac nhan thanh toan
     * @return JsonModel
     */
    public function cancelAction(){
        $request = $this->getRequest();
        $result['status']=0;
        if($request->isPost()) {
            $paymentId = $request->getPost("id");
            try{
                if($paymentId){

                    $payment = $this->paymentManager->getById($paymentId);

                    if($payment->getStatus()!=Define::STATUS_PAID)
                        throw new \Exception('Chỉ thực hiện khi phiếu thanh toán đã xác nhận thanh toán!');

                    $paymentPublicId=$payment->getPublicId();

                    $debtManager = new DebtManager($this->entityManager);
                    $supplierDebtLedger = $debtManager->getByReferenceId($paymentPublicId);
                    $this->entityManager->remove($supplierDebtLedger);

                    $payment->setStatus(Define::STATUS_PROCESS);
                    $payment->setConfirmBy(null);
                    $payment->setConfirmDate(null);
                    $this->entityManager->flush();

                    $result['status'] = 1;
//                    $result['message']='Công nợ <b>'.$payment->getSupplier()->getNameAlias().'</b> đã tăng lên <b>'.Common::formatMoney($payment->getAmount()).'</b>';
                    $result['message']='Huỷ thành công phiếu thanh toán';

                }else
                    $result['message']='Không tìm thấy phiếu thanh toán cần huỷ!';
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['message']='Không thể huỷ thanh toán: '.$message;
            }
        }else{
            $result['message']='Phương thức không đúng!';
        }
        return new JsonModel($result);
    }

    public function deleteAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $paymentId = $request->getPost("id");
            try{
                if($paymentId){
                    $payment = $this->paymentManager->getById($paymentId);

                    if($payment->getStatus()==Define::STATUS_PAID)
                        throw new \Exception('Không thể xoá phiếu thanh toán đã xác nhận!');

                    $this->entityManager->remove($payment);
                    $this->entityManager->flush();

                    $result['status'] = 1;
                    $result['message']='Đã xoá phiếu thanh toán!';

                }else
                    $result['status']=0;
                $result['message']='Không tìm thấy phiếu thanh toán!';
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['message']='Không thể xoá phiếu thanh toán: '.$message;
            }
        }else{
            $result['status']=0;
            $result['message']='Phương thức không đúng!';
        }
        return new JsonModel($result);
    }
}