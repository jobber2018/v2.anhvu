<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Purchase\Controller;

use Doctrine\ORM\EntityManager;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Product\Entity\History;
use Product\Service\ProductManager;
use Product\Service\VariantManager;
use Purchase\Entity\Purchase;
use Purchase\Entity\PurchaseDetail;
use Purchase\Entity\PurchaseInvoice;
use Purchase\Entity\PurchaseReturn;
use Purchase\Entity\PurchaseReturnDetail;
use Purchase\Service\PurchaseManager;
use Purchase\Service\PurchaseReturnManager;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeAdminController;
use Supplier\Entity\SupplierDebtLedger;
use Supplier\Service\SupplierManager;

class ReturnController extends SuldeAdminController
{
    private $entityManager;
    private $purchaseReturnManager;

    private TranslatorInterface $translator;

    public function __construct(EntityManager $entityManager, PurchaseReturnManager $purchaseReturnManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->purchaseReturnManager = $purchaseReturnManager;
        $this->translator     = $translator;
    }
    public function listAction()
    {
        $request = $this->getRequest();

        if($request->isPost()) {
            $length = $this->params()->fromPost('length', Define::ITEM_PAGE_COUNT);
            $start = $this->params()->fromPost('start', 0);
            $draw = $this->params()->fromPost('draw', 1);
            $keyword = $this->params()->fromPost('search')['value'];

            $purchaseReturn = $this->purchaseReturnManager->searchPurchaseReturn($keyword,$length, $start);
            $purchaseResult = array();
            foreach ($purchaseReturn as $purchaseReturnItem) {
                $serialize = $purchaseReturnItem->serialize();
                $serialize['status_label']=$this->translator->translate($serialize['status']);;
                $purchaseResult[] = $serialize;
            }
            $result['draw'] = $draw;
            $result['recordsTotal'] = count($purchaseReturn);
            $result['recordsFiltered'] = count($purchaseReturn);
            $result['data'] = $purchaseResult;
            return new JsonModel($result);
        }
        return new ViewModel();
    }

    public function detailAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            $purchaseReturnId = $this->params()->fromPost('id', 0);
            $purchaseReturn = $this->purchaseReturnManager->getById($purchaseReturnId);

            $result=array();
            foreach ($purchaseReturn->getPurchaseReturnDetail() as $purchaseReturnDetailItem) {

                $variant=$purchaseReturnDetailItem->getVariant();
                $product = $variant->getProduct();

                $detailItem=$purchaseReturnDetailItem->serialize();
                $detailItem['variant']=$variant->serialize();
                $detailItem['product']=$product->serialize();

                $result[]=$detailItem;
            }
            return new JsonModel($result);
        }
        else{
            $purchaseReturnId = $this->params()->fromRoute('id',0);
            $purchaseReturn = $this->purchaseReturnManager->getPublicById($purchaseReturnId);
            return new ViewModel(['purchaseReturn'=>$purchaseReturn]);
        }
    }

    public function addAction()
    {
        $request = $this->getRequest();
        $purchaseManager = new PurchaseManager($this->entityManager);
        if($request->isPost()) {
            try{
                $purchaseId = $this->params()->fromPost('purchase_id', 0);
                $note = $this->params()->fromPost('note', '');
                $variants = $this->params()->fromPost('variants', []);

                $purchase = $purchaseManager->getById($purchaseId);

                $purchaseReturn = new PurchaseReturn();
                $purchaseReturn->setPurchase($purchase);
                $purchaseReturn->setStatus(Define::STATUS_PROCESS);
                $purchaseReturn->setNote($note);
                $purchaseReturn->setCreatedBy($this->userLogin->getUsername());
                $purchaseReturn->setCreatedDate(new \DateTime());

                $result=array();

                $variantManager = new VariantManager($this->entityManager);

                foreach ($variants as $variant) {
                    $variantId=$variant['variant_id'];
                    $purchaseDetailId=$variant['purchase_detail_id'];
                    $qty=$variant['qty'];
                    $purchaseDetail=$this->_findPurchaseDetail($purchase->getPurchaseDetail(),$purchaseDetailId);

                    $variantItem = $variantManager->getById($variantId);

                    if(!$variantId || !$qty || !$variantItem || !$purchaseDetail)
                        throw new \Exception('Dữ liệu không hợp lệ!');

                    $purchaseReturnDetail = new PurchaseReturnDetail();
                    $purchaseReturnDetail->setPurchaseReturn($purchaseReturn);
                    $purchaseReturnDetail->setPurchaseDetail($purchaseDetail);
                    $purchaseReturnDetail->setVariant($variantItem);
                    $purchaseReturnDetail->setQty($qty);
                    $purchaseReturnDetail->setUnitName($variantItem->getUnitName());

                    $baseUnitPrice=$purchaseDetail->getPrice()/$purchaseDetail->getConversionRate();

                    $productAmountReturn = $baseUnitPrice*$variantItem->getConversionRate();
                    $productVat=$purchaseDetail->getVat();

                    $productDiscountType=$purchaseDetail->getDiscountType();
                    $productDiscount=($productDiscountType==Define::PERCENT)
                        ?$purchaseDetail->getDiscount()
                        :($purchaseDetail->getDiscount()/$purchaseDetail->getConversionRate())*$variantItem->getConversionRate();

                    $purchaseReturnDetail->setPrice($productAmountReturn);
                    $purchaseReturnDetail->setVat($productVat);
                    $purchaseReturnDetail->setDiscountType($productDiscountType);
                    $purchaseReturnDetail->setDiscount($productDiscount);
                    $purchaseReturnDetail->setConversionRate($variantItem->getConversionRate());

                    $purchaseReturn->addPurchaseReturnDetail($purchaseReturnDetail);
                }

                $this->entityManager->persist($purchaseReturn);
                $this->entityManager->flush();

                $result['status']=1;
                $result['purchase_return_id']=$purchaseReturn->getPublicId();
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);
        }
        else{
            $purchaseId = $this->params()->fromRoute('id',0);
            $purchase = $purchaseManager->getPublicById($purchaseId);
            return new ViewModel(['purchase'=>$purchase]);
        }
    }

    public function deleteProductAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $returnDetailId = $request->getPost("return_detail_id");
            try{
                if($returnDetailId){
                    $purchaseReturnDetail = $this->entityManager->getRepository(PurchaseReturnDetail::class)->find($returnDetailId);
                    if($purchaseReturnDetail->getPurchaseReturn()->getStatus()==Define::STATUS_APPROVED)
                        throw new \Exception('Không thể xoá sản phẩm khi đơn hàng đã duyệt!');

                    $this->entityManager->remove($purchaseReturnDetail);
                    $this->entityManager->flush();

                    $result=[
                        'status' => '1',
                        'message'=>'Đã xoá sản phẩm khỏi phiếu trả hàng!'
                    ];

                }else
                    $result=[
                        'status' => '0',
                        'message'=>'Không tìm thấy sản phẩm cần xoá!'
                    ];
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['message']=$message;
            }
        }else{
            $result=[
                'status' => '0',
                'message'=>'Phương thức không đúng!'
            ];
        }
        return new JsonModel($result);
    }

    public function deleteAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $returnId = $request->getPost("purchase_return_id");
            try{
                if($returnId){
                    $purchaseReturn = $this->entityManager->getRepository(PurchaseReturn::class)->find($returnId);
                    if($purchaseReturn->getStatus()==Define::STATUS_APPROVED)
                        throw new \Exception('Không thể xoá phiếu khi đã duyệt!');

                    $this->entityManager->remove($purchaseReturn);
                    $this->entityManager->flush();

                    $result=[
                        'status' => '1',
                        'message'=>'Đã xoá phiếu trả hàng!'
                    ];

                }else
                    $result=[
                        'status' => '0',
                        'message'=>'Không tìm phiếu trả!'
                    ];
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['message']=$message;
            }
        }else{
            $result=[
                'status' => '0',
                'message'=>'Phương thức không đúng!'
            ];
        }
        return new JsonModel($result);
    }

    public function approvalAction()
    {
        $request = $this->getRequest();

        if($request->isPost()) {
            try {
                $purchaseReturnId = $request->getPost("id",'');
                if(!$purchaseReturnId) throw new \Exception('Không tìm thấy phiếu trả!');

                $purchaseReturn = $this->purchaseReturnManager->getById($purchaseReturnId);

                if($purchaseReturn->getStatus()==Define::STATUS_APPROVED)
                    throw new \Exception('Phiếu trả đã được duyệt bởi '.$purchaseReturn->getApprovedBy());

                if(!count($purchaseReturn->getPurchaseReturnDetail()))
                    throw new \Exception('Phiếu trả không có sản phẩm, nên không thể duyệt!');

                $totalAmountReturn=0;
                foreach ($purchaseReturn->getPurchaseReturnDetail() as $purchaseReturnDetailItem) {
                    $qty=$purchaseReturnDetailItem->getQty();//sl nhap
                    $price=$purchaseReturnDetailItem->getPrice();//gia tra lai

                    $variant=$purchaseReturnDetailItem->getVariant();

                    $conversionRate=$purchaseReturnDetailItem->getConversionRate();//quy doi tai thoi diem tao phieu

                    $product=$variant->getProduct();

                    $inventory=$product->getInventory();

//                    $totalAmountReturn+=$qty*$price;

                    //add product history
                    $productHistory = new History();
                    $productHistory->setProduct($product);
                    $productHistory->setChange($qty*$conversionRate*-1);
                    $productHistory->setInventory($inventory);
                    $productHistory->setNote(Define::PURCHASE_RETURN);
                    $productHistory->setType(Define::PURCHASE_RETURN_CODE);
                    $productHistory->setUrl('/purchase/return/admin/detail/'.$purchaseReturn->getPublicId());
                    $productHistory->setCreatedBy($this->userLogin->getUsername());
                    $productHistory->setCreatedDate(new \DateTime());

                    $this->entityManager->persist($productHistory);
                }

                $amountInfo = $purchaseReturn->getAmountInfo();
                $totalProductPayable=$amountInfo['total_product_payable'];
                //ghi nhan cong no
                $supplierDebtLedger = new SupplierDebtLedger();
                $supplierDebtLedger->setSupplier($purchaseReturn->getPurchase()->getSupplier());
                $supplierDebtLedger->setReferenceType(Define::PURCHASE_RETURN_CODE);
                $supplierDebtLedger->setReferenceId($purchaseReturn->getPublicId());
                $supplierDebtLedger->setDirection(Define::DEBT_OUT);
                $supplierDebtLedger->setAmount($totalProductPayable*-1);
                $supplierDebtLedger->setApplyDate(new \DateTime());
                $supplierDebtLedger->setNote($purchaseReturn->getNote());
                $supplierDebtLedger->setCreatedBy($this->userLogin->getUsername());
                $supplierDebtLedger->setCreatedDate(new \DateTime());
                $this->entityManager->persist($supplierDebtLedger);

                $purchaseReturn->setApprovedBy($this->userLogin->getUsername());
                $purchaseReturn->setApprovedDate(new \DateTime());
                $purchaseReturn->setStatus(Define::STATUS_APPROVED);

                $this->entityManager->persist($purchaseReturn);
                $this->entityManager->flush();

                $result['status']=1;
                $result['message']='Đã cập nhật tồn kho và ghi nhận công nợ NCC!';
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
        }else{
            $result['message']='Request POST require!';
        }
        return new JsonModel($result);
    }

    private function _findPurchaseDetail($purchaseDetails, $purchaseDetailId) {
        foreach ($purchaseDetails as $purchaseDetail) {
            if ($purchaseDetail->getId()==$purchaseDetailId) {
                return $purchaseDetail; // trả về phần tử đầu tiên thỏa mãn
            }
        }
        return null;
    }

}