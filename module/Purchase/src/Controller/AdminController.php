<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Purchase\Controller;

use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\UploadedFile;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Product\Entity\History;
use Product\Form\ProductForm;
use Product\Service\ProductManager;
use Product\Service\VariantManager;
use Purchase\Entity\Purchase;
use Purchase\Entity\PurchaseAdditionalFees;
use Purchase\Entity\PurchaseDetail;
use Purchase\Entity\PurchaseInvoice;
use Purchase\Entity\PurchaseMessage;
use Purchase\Service\PurchaseManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Sulde\Service\FileUploader;
use Sulde\Service\SuldeAdminController;
use Supplier\Entity\SupplierDebtLedger;
use Supplier\Form\SupplierForm;
use Supplier\Service\SupplierManager;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $purchaseManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManager $entityManager, PurchaseManager $purchaseManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->purchaseManager = $purchaseManager;
        $this->translator     = $translator;
    }

    public function dashboardAction()
    {
        $privilegeId=95;
        return new ViewModel([
            'privilegeId'=>$privilegeId,
            'privileges'=>$this->userLogin->getPrivileges()
        ]);
    }

    /**
     * @return JsonModel|ViewModel
     */
    public function listAction()
    {
        $request = $this->getRequest();

        if($request->isPost()) {
            $length = $this->params()->fromPost('length', Define::ITEM_PAGE_COUNT);
            $start = $this->params()->fromPost('start', 0);
            $draw = $this->params()->fromPost('draw', 1);
            $keyword = $this->params()->fromPost('search')['value'];

            $purchases = $this->purchaseManager->search($keyword,$length, $start);
            $purchaseResult = array();
            foreach ($purchases as $purchaseItem) {
                $purchaseArray = $purchaseItem->serialize();
                $purchaseArray['status_name']=$this->translator->translate($purchaseItem->getStatus());
                $purchaseResult[] = $purchaseArray;
            }
            $result['draw'] = $draw;
            $result['recordsTotal'] = count($purchases);
            $result['recordsFiltered'] = count($purchases);
            $result['data'] = $purchaseResult;
            return new JsonModel($result);
        }
        return new ViewModel();
    }

    /**
     * @return JsonModel|ViewModel
     */
    public function editAction(){

        $request = $this->getRequest();
        $supplierManager = new SupplierManager($this->entityManager);
        if($request->isPost()) {
            try {
                $purchaseId=$request->getPost('id',0);
                $variants=$request->getPost('variants',[]);
                $supplierId=$request->getPost('supplier_id',0);
                $additional_fees=$request->getPost('additional_fees',[]);
                $orderDiscount=$request->getPost('discount','');

                $purchase = $this->purchaseManager->getById($purchaseId);

                if(!$purchase)
                    throw new \Exception('Không tìm thấy đơn hàng cần sửa!');

                if($purchase->getStatus()==Define::PURCHASE_APPROVAL)
                    throw new \Exception('Không thể sửa vì đơn hàng đã nhập kho!');

                $supplier = $supplierManager->getById($supplierId);
                if(!$supplier)
                    throw new \Exception('Vui lòng chọn nhà cung cấp dịch vụ!');

                foreach ($additional_fees as $key=>$value){
                    $additionalFeesId=$value['additional_fees_id'];
                    if($additionalFeesId==0){
                        //them moi phu phi
                        $supplierTmp = $supplierManager->getById($value['supplier_id']);

                        $purchaseAdditionalFees = new PurchaseAdditionalFees();
                        $purchaseAdditionalFees->setAmount($value['amount']);
                        $purchaseAdditionalFees->setName($value['name']);
                        $purchaseAdditionalFees->setSupplier($supplierTmp);
                        $purchaseAdditionalFees->setCreatedBy($this->userLogin->getUsername());
                        $purchaseAdditionalFees->setCreatedDate(new \DateTime());
                        $purchaseAdditionalFees->setPurchase($purchase);
                        $purchaseAdditionalFees->setFlag(1);
                        $purchase->addAdditionalFees($purchaseAdditionalFees);
                    }else{
                        //update phu phi
                        $purchaseAdditionalFees = $purchase->additionalFeesIsExitst($additionalFeesId);
                        $purchaseAdditionalFees->setFlag(1);//dung co khong xoa phu phi
                    }

                }


                foreach ($variants as $key=>$value) {
                    $variantId = $value['variant_id'];
                    $qty = $value['qty'];
                    $price = $value['price'];
                    $vat=$value['vat'];
                    $discount=$value['discount']['value'];
                    $discountType=$value['discount']['type'];

                    $variantManager = new VariantManager($this->entityManager);
                    $variantItem=$variantManager->getById($variantId);
                    $purchaseDetail = $purchase->checkVariantsInPurchase($variantItem);
                    if($purchaseDetail==null){
                        $purchaseDetail = new PurchaseDetail();
                        $purchaseDetail->setCreatedBy($this->userLogin->getUsername());
                        $purchaseDetail->setCreatedDate(new \DateTime());
                        $purchaseDetail->setPurchase($purchase);
                        $purchase->addPurchaseDetail($purchaseDetail);
                    }
                    $purchaseDetail->setQty($qty);
                    $purchaseDetail->setPrice($price);
                    $purchaseDetail->setVat($vat);
                    $purchaseDetail->setDiscount($discount);
                    $purchaseDetail->setDiscountType($discountType);
                    $purchaseDetail->setVariants($variantItem);
                    $purchaseDetail->setUnitName($variantItem->getUnit()->getName());
                    $purchaseDetail->setConversionRate($variantItem->getConversionRate());
                    $purchaseDetail->setSort($key);
                    $purchaseDetail->setFlag(1);//co trong don hang
                }

                //Loai bo san pham xoa ra khoi don hang
                foreach ($purchase->getPurchaseDetail() as $purchaseDetailItem){
                    if($purchaseDetailItem->getFlag()==0){
                        $this->entityManager->remove($purchaseDetailItem);
                        $this->entityManager->flush();
                    }
                }

                //loai bo phu phi xoa ra khoi don:
                foreach ($purchase->getAdditionalFees() as $additionalFees) {
                    if ($additionalFees->getFlag() == 0) {
                        $this->entityManager->remove($additionalFees);
                        $this->entityManager->flush();
                    }
                }

                $purchase->setDiscount($orderDiscount['value']);
                $purchase->setDiscountType($orderDiscount['type']);
                $purchase->setSupplier($supplier);
                $purchase->setUpdatedBy($this->userLogin->getUsername());
                $purchase->setUpdatedDate(new \DateTime());

                $this->entityManager->persist($purchase);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['purchaseId'] = $purchase->getPublicId();
                $result['message'] = 'Dữ liệu đã được cập nhật!';
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
        else
        {
            $purchaseId = $this->params()->fromRoute('id',0);

            //product form
            $productManager = new ProductManager($this->entityManager);
            $categoriesList = $productManager->getAllCategories();
            $categoriesData=array();
            foreach ($categoriesList as $item) {
                $categoriesData[$item->getId()] = $item->getName();
            }
            $unitList = $productManager->getAllUnit();
            $unitData=array();
            foreach ($unitList as $item) {
                $unitData[$item->getId()] = $item->getName();
            }
            $productForm =new ProductForm($categoriesData,$unitData);
            $supplierForm =new SupplierForm("add");


            $purchase = $this->purchaseManager->getPublicById($purchaseId);

            $purchaseDetails=array();
            foreach ($purchase->getPurchaseDetail() as $purchaseDetailItem) {

                $purchaseDetail = $purchaseDetailItem->serialize();

                $variantItem=$purchaseDetailItem->getVariants();
                $purchaseDetail['variant']=$variantItem->serialize();
                $purchaseDetail['product']=$variantItem->getProduct()->serialize();

                $purchaseDetails[]=$purchaseDetail;
            }

            return new ViewModel([
                'userLogin'=>$this->userLogin,
                'purchase'=>$purchase,
                'purchaseDetails'=>$purchaseDetails,
                'productForm'=>$productForm,
                'unitList'=>$unitList,
                'supplierForm'=>$supplierForm
            ]);
        }
    }

    /**
     * @return JsonModel|ViewModel
     */
    public function addAction(){
        $request = $this->getRequest();

        $supplierManager = new SupplierManager($this->entityManager);

        if($request->isPost()) {
            try {
                $variants=$request->getPost('variants',[]);
                $supplierId=$request->getPost('supplier_id',0);
                $note=$request->getPost('note','');
                $additional_fees=$request->getPost('additional_fees',[]);
                $discount=$request->getPost('discount','');

                $supplier = $supplierManager->getById($supplierId);

                $purchase = new Purchase();
                $purchase->setSupplier($supplier);
                $purchase->setStatus(Define::STATUS_PROCESS);
                $purchase->setCreatedBy($this->userLogin->getUsername());
                $purchase->setCreatedDate(new \DateTime());
                $purchase->setDiscount($discount['value']);
                $purchase->setDiscountType($discount['type']);

                foreach ($additional_fees as $key=>$value){
                    $supplierTmp = $supplierManager->getById($value['supplier_id']);
                    $purchaseAdditionalFees = new PurchaseAdditionalFees();
                    $purchaseAdditionalFees->setAmount($value['amount']);
                    $purchaseAdditionalFees->setName($value['name']);
                    $purchaseAdditionalFees->setSupplier($supplierTmp);
                    $purchaseAdditionalFees->setCreatedBy($this->userLogin->getUsername());
                    $purchaseAdditionalFees->setCreatedDate(new \DateTime());
                    $purchaseAdditionalFees->setPurchase($purchase);
                    $purchase->addAdditionalFees($purchaseAdditionalFees);
                }

                if($note){
                    $purchaseMessage = new PurchaseMessage();
                    $purchaseMessage->setMessage($note);
                    $purchaseMessage->setCreatedBy($this->userLogin->getUsername());
                    $purchaseMessage->setCreatedDate(new \DateTime());
                    $purchaseMessage->setPurchase($purchase);
                    $purchase->addMessage($purchaseMessage);
                }

                $variantManager = new VariantManager($this->entityManager);
                foreach ($variants as $key=>$value){
                    $variantId=$value['variant_id'];
                    $qty=$value['qty'];
                    $price=$value['price'];
                    $vat=$value['vat'];
                    $discount=$value['discount']['value'];
                    $discountType=$value['discount']['type'];

                    $variantsItem = $variantManager->getById($variantId);

                    $purchaseDetail = new PurchaseDetail();
                    $purchaseDetail->setVariants($variantsItem);
                    $purchaseDetail->setQty($qty);
                    $purchaseDetail->setPrice($price);
                    $purchaseDetail->setVat($vat);
                    $purchaseDetail->setDiscount($discount);
                    $purchaseDetail->setDiscountType($discountType);
                    $purchaseDetail->setUnitName($variantsItem->getUnitName());
                    $purchaseDetail->setConversionRate($variantsItem->getConversionRate());
                    $purchaseDetail->setCreatedBy($this->userLogin->getUsername());
                    $purchaseDetail->setCreatedDate(new \DateTime());
                    $purchaseDetail->setSort($key);
                    $purchaseDetail->setPurchase($purchase);
                    $purchase->addPurchaseDetail($purchaseDetail);
                }

                $this->entityManager->persist($purchase);
                $this->entityManager->flush();

                $message='Thêm mới thành công phiếu nhập hàng!';
                $result['purchaseId']=$purchase->getPublicId();
                $result['status']=1;
                $result['message']=$message;
            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);
        }else{

//            $suppliers = $supplierManager->getAll();
//            $this->layout()->setTemplate('semantic');

            //product form
            $productManager = new ProductManager($this->entityManager);
            $categoriesList = $productManager->getAllCategories();
            $categoriesData=array();
            foreach ($categoriesList as $item) {
                $categoriesData[$item->getId()] = $item->getName();
            }
            $unitList = $productManager->getAllUnit();
            $unitData=array();
            foreach ($unitList as $item) {
                $unitData[$item->getId()] = $item->getName();
            }
            $productForm =new ProductForm($categoriesData,$unitData);
            $supplierForm =new SupplierForm("add");
            return new ViewModel(['productForm'=>$productForm,'unitList'=>$unitList,'supplierForm'=>$supplierForm]);
        }
    }
    public function deleteAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $purchaseId = $request->getPost("id",0);
                if($purchaseId==0)
                    throw new \Exception('Không tìm thấy đơn hàng!');

                $purchase = $this->purchaseManager->getById($purchaseId);
                if($purchase->getStatus()==Define::PURCHASE_APPROVAL)
                    throw new \Exception('Không thể xoá đơn hàng đã nhập kho!');

                //remove file on server
                $invoices=$purchase->getInvoice();
                if($invoices){
                    foreach ($invoices as $invoiceItem) {
                        if (file_exists(ROOT_PATH.$invoiceItem->getPath())) {
                            unlink(ROOT_PATH.$invoiceItem->getPath());
                        }
                    }
                }

                $this->entityManager->remove($purchase);
                $this->entityManager->flush();
                $result['message'] = 'Đã xoá đơn hàng!';
                $result['status'] = 1;
            } catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    /**
     * Nhap Kho
     * @return JsonModel
     */
    public function approvalAction()
    {
        $request = $this->getRequest();

        if($request->isPost()) {
            try {
                $purchaseId = $request->getPost("id",'0');
                if(!$purchaseId) throw new \Exception('Không tìm thấy đơn hàng!');

                $purchase = $this->purchaseManager->getPublicById($purchaseId);

                if($purchase->getStatus()==Define::PURCHASE_APPROVAL)
                    throw new \Exception('Đơn hàng đã được nhập kho bởi '.$purchase->getApprovedBy());

                if(!count($purchase->getPurchaseDetail()))
                    throw new \Exception('Không có sản phẩm nào trong đơn hàng');


                $purchase->setApprovedBy($this->userLogin->getUsername());
                $purchase->setApprovedDate(new \DateTime());
                $purchase->setStatus(Define::PURCHASE_APPROVAL);

                /**
                 * return array(
                 * 'total_product_amount'=>$totalProductAmount,
                 * 'total_product_discount_amount'=>$totalProductDiscountAmount,
                 * 'total_product_vat_amount'=>$totalProductVatAmount,
                 * 'total_product_payable'=>$productPayable,
                 * 'purchase_discount_amount'=>$purchaseDiscountAmount,
                 * 'total_additional_fees_amount'=>$totalAdditionalFeesAmount,
                 * 'purchase_payable'=>$purchasePayable
                 * );
                 */
                $purchaseAmountInfo=$purchase->getAmountInfo();

//                $totalOrderAmount = $purchaseAmountInfo['total_product_amount'];

                $orderDiscountAmount=$purchaseAmountInfo['purchase_discount_amount'];


//                $totalOrderAdditionalFeesAmount=$purchase->getTotalAdditionalFeesAmount();
                $totalOrderAdditionalFeesAmount=$purchaseAmountInfo['total_additional_fees_amount'];

//                $totalOrderVatAmount=$purchaseAmountInfo['total_product_vat_amount'];

                //tổng tiền sản phẩm phải trả trong đơn (không bao gồm giảm giá theo đơn)
                $totalProductPayable=$purchaseAmountInfo['total_product_payable'];

                //số tiền đơn hàng phải thanh toán = tổng đơn - discount + thuế + phụ phí
//                $totalOrderPayableAmount = $purchaseAmountInfo['purchase_payable'];

                foreach ($purchase->getPurchaseDetail() as $purchaseDetailItem) {
                    $qty=$purchaseDetailItem->getQty();//sl nhap
                    $price=$purchaseDetailItem->getPrice();//gia nhap
                    $discountAmount = $purchaseDetailItem->getDiscountAmount();
                    $vatAmount=$purchaseDetailItem->getVatAmount();

                    $variant=$purchaseDetailItem->getVariants();
                    $product=$variant->getProduct();

                    if($product->getStatus()==Define::DEFAULT_UN_ACTIVE)
                        throw new \Exception('Không thể nhập kho do '.$product->getName().' đang dừng giao dịch!');
                    if(!$qty)
                        throw new \Exception('Số lượng '.$product->getName().' không hợp lệ!');
                    if(!$price)
                        throw new \Exception('Giá nhập '.$product->getName().' không hợp lệ!');

                    /**
                     * tính giá phải trả cho 1 sản phẩm nhập
                     * 1. productPayable =giá nhập * số lượng - tiền chiết khấu + tiền thuế xuất
                     * 2. Tính số tỉ lệ % sản phẩm chiếm bao nhiêu / tổng đơn hàng phải trả
                     *       =>$percentProductInOrder = (số tiền sản phẩm phải trả (chưa tính chiết khẩu đơn và phụ phí)/Tổng đơn phải trả)*100
                     * 3. Nếu đơn hàng được giảm giá
                     *      => Tính số tiền sản phẩm được giảm thêm khi đơn hàng được giảm giá
                     *      => $productDiscountOrderAmount = $percentProductInOrder*$totalOrderDiscountAmount
                     * 4. Nếu đơn có phụ phí
                     *      => Tính số tiền phụ phí sản phảm phải chịu trong tổng phụ phí
                     *      =>$productAdditionalFeesAmount = $percentProductInOrder*$totalOrderAdditionalFeesAmount
                     * 5. Số tiền sản phẩm phải chịu sau cùng
                     *      = productPayableFinal = $productPayable - $productDiscountOrderAmount + $productAdditionalFeesAmount
                     */

                    //số tiền phải trả của sản phẩm (chưa tính chiết khẩu đơn và phụ phí)
                    // = tiền sản phẩm - tiền dícount + vat
                    $productPayable = $qty*($price - $discountAmount + $vatAmount);

                    //Tính số tiền phải trả của sản phẩm chiếm bao nhiêu % tổng tiền sản phẩm phải trả trong đơn
                    $percentProductInOrder=$productPayable/$totalProductPayable;

                    //nếu có giảm giá theo đơn hàng => Tính số tiền sản phẩm được giảm thêm khi đơn hàng được giảm giá
                    $productDiscountOrderAmount=0;
                    if($orderDiscountAmount>0){
                        //Số tiền sản phẩm được giảm theo đơn
                        $productDiscountOrderAmount=$percentProductInOrder*$orderDiscountAmount;
                    }

                    //nếu có phụ phí => Tính số tiền phụ phí sản phẩm phải chịu trong tổng phụ phí
                    $productAdditionalFeesAmount=0;
                    if($totalOrderAdditionalFeesAmount>0){
                        //số tiền sản phẩm phải chịu phụ phí
                        $productAdditionalFeesAmount=$percentProductInOrder*$totalOrderAdditionalFeesAmount;
                    }

                    $productPayableFinal=$productPayable - $productDiscountOrderAmount + $productAdditionalFeesAmount;
                    /**
                     * ------------------------------------------
                     */

                    $productNumberUnit=$qty*$purchaseDetailItem->getConversionRate();
                    $productCostUnit=$productPayableFinal/$productNumberUnit;

                    $inventory=$product->getInventory();
                    $productOldCostUnit=$product->getCost();

                    $averageCost = ($inventory*$productOldCostUnit + $productNumberUnit*$productCostUnit)/($inventory+$productNumberUnit);

                    $product->setCost($averageCost);
                    $product->setCostOld($productOldCostUnit);

                    //add product history
                    $productHistory = new History();
                    $productHistory->setProduct($product);
                    $productHistory->setChange($productNumberUnit);
                    $productHistory->setInventory($inventory);
                    $productHistory->setNote(Define::PURCHASE);
                    $productHistory->setType(Define::PURCHASE_CODE);
                    $productHistory->setUrl('/purchase/admin/detail/'.$purchase->getPublicId());
                    $productHistory->setCreatedBy($this->userLogin->getUsername());
                    $productHistory->setCreatedDate(new \DateTime());

                    $this->entityManager->persist($productHistory);
                }

                /*
                 * Ghi nhận công nợ
                 * 1. Số tiền sản phẩm phải trả trong đơn đuọc ghi nhận cho nhà cung cấp sản phẩm
                 *  = tổng tiền sản phẩm phải thanh toán - tiền chiết khấu theo đơn.
                 * 2. Phụ phí (ghi nhận công nợ theo NCC tương ứng)
                 */

                $totalProductPayableFinal=$totalProductPayable-$orderDiscountAmount;

                $supplierDebtLedger = new SupplierDebtLedger();
                $supplierDebtLedger->setSupplier($purchase->getSupplier());
                $supplierDebtLedger->setReferenceType(Define::PURCHASE_CODE);
                $supplierDebtLedger->setReferenceId($purchase->getPublicId());
                $supplierDebtLedger->setDirection(Define::DEBT_IN);
                $supplierDebtLedger->setAmount($totalProductPayableFinal);
                $supplierDebtLedger->setApplyDate(new \DateTime());
                $supplierDebtLedger->setNote(Define::PURCHASE);
                $supplierDebtLedger->setCreatedBy($this->userLogin->getUsername());
                $supplierDebtLedger->setCreatedDate(new \DateTime());
                $this->entityManager->persist($supplierDebtLedger);

                //cong no phu phi
                $additional_fees = $purchase->getAdditionalFees();
                foreach ($additional_fees as $additionalFee) {
                    $supplierDebtLedger = new SupplierDebtLedger();
                    $supplierDebtLedger->setSupplier($additionalFee->getSupplier());
                    $supplierDebtLedger->setReferenceType(Define::PURCHASE_CODE);
                    $supplierDebtLedger->setReferenceId($purchase->getPublicId());
                    $supplierDebtLedger->setDirection(Define::DEBT_IN);
                    $supplierDebtLedger->setAmount($additionalFee->getAmount());
                    $supplierDebtLedger->setApplyDate(new \DateTime());
                    $supplierDebtLedger->setNote($additionalFee->getName());
                    $supplierDebtLedger->setCreatedBy($this->userLogin->getUsername());
                    $supplierDebtLedger->setCreatedDate(new \DateTime());
                    $this->entityManager->persist($supplierDebtLedger);
                }


                $this->entityManager->persist($purchase);
                $this->entityManager->flush();

                $result['status']=1;
                $result['message']='Nhập kho thành công!';
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

    /**
     * @return ViewModel
     */
    public function detailAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            $purchaseId = $this->params()->fromPost('id', 0);
            $purchase = $this->purchaseManager->getById($purchaseId);

//            $result=array();

            $result=$purchase->serialize();

            foreach ($purchase->getPurchaseDetail() as $purchaseDetailItem) {

                $variant=$purchaseDetailItem->getVariants();
                $product = $variant->getProduct();

                $detailItem=$purchaseDetailItem->serialize();
                $detailItem['variant']=$variant->serialize();
                $detailItem['product']=$product->serialize();

                $purchaseDetailLatest=null;
                foreach ($product->getVariants() as $variantTmp) {
                    if(empty($purchaseDetailLatest))
                        $purchaseDetailLatest=$variantTmp->getPurchaseDetailLatest();
                }

                if(!empty($purchaseDetailLatest)){
                    $purchaseDetailSerialize = $purchaseDetailLatest->serialize();
                    $purchaseDetailSerialize['base_unit_price']=$purchaseDetailLatest->getPrice()/$purchaseDetailLatest->getConversionRate();
                    $purchaseDetailSerialize['supplier']=$purchaseDetailLatest->getPurchase()->getSupplier()->serialize();
                    $detailItem['product']['purchase_detail_latest']=$purchaseDetailSerialize;
                }

                $result['purchase_detail'][]=$detailItem;
            }
            return new JsonModel($result);
        }
        else{
            $purchaseId = $this->params()->fromRoute('id',0);
            $purchase = $this->purchaseManager->getPublicById($purchaseId);
            return new ViewModel(['purchase'=>$purchase,'userLogin'=>$this->userLogin]);
        }
    }

    public function uploadInvoiceAction(){

        $purchaseId = $this->params()->fromRoute('id',0);

        $request = $this->getRequest();

        if($request->isPost()) {
            try {
                if(!$purchaseId) throw new \Exception('Không tìm thấy thanh toán!');

                $fileArray=$request->getFiles()->toArray();

                if(empty($fileArray)) throw new \Exception('Không tìm thấy file chứng từ!');

                $uploadedFile = new UploadedFile(
                    $fileArray['file']['tmp_name'],
                    $fileArray['file']['size'],
                    $fileArray['file']['error'],
                    $fileArray['file']['name'],
                    $fileArray['file']['type']
                );
                $uploadPath = '/assets/purchase/invoice/';
                $fileUploader = new FileUploader($uploadPath);
                $resultUpload = $fileUploader->upload($uploadedFile);
                if(!$resultUpload['success']) throw new \Exception($resultUpload['message']);

                $purchase = $this->purchaseManager->getById($purchaseId);

                $invoice = new PurchaseInvoice();
                $invoice->setPath($uploadPath.$resultUpload['filename']);
                $invoice->setCreatedDate(new \DateTime());
                $invoice->setPurchase($purchase);
                $invoice->setCreatedBy($this->userLogin->getUsername());
                $purchase->addInvoice($invoice);

                $this->entityManager->persist($purchase);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['id'] = $invoice->getId();
                $result['url'] = $uploadPath.$resultUpload['filename'];
                $result['message'] = 'Upload thành công chứng từ!';

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
        }else{
            $result['status'] = 0;
            $result['message'] = 'Phương thức gửi file không đúng!';
        }
        return new JsonModel($result);
    }

    public function deleteInvoiceAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $invoiceId = $request->getPost("id");
            try{
                if($invoiceId){

                    $invoice = $this->entityManager->getRepository(PurchaseInvoice::class)->find($invoiceId);

                    if($invoice->getPurchase()->getStatus()==Define::PURCHASE_APPROVAL)
                        throw new \Exception('Không thể xoá file khi đơn hàng đã duyệt!');
                    //remove file on server
                    if (file_exists(ROOT_PATH.$invoice->getPath())) {
                        unlink(ROOT_PATH.$invoice->getPath());
                    }

                    //remove file in data
                    $this->entityManager->remove($invoice);
                    $this->entityManager->flush();

                    $result=[
                        'status' => '1',
                        'message'=>''
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
     * get danh sach file cua invoice
     * @return JsonModel
     */
    public function fileListAction()
    {
        $purchaseId = $this->params()->fromRoute('id',0);
        $files=array();
        try {
            $purchase = $this->purchaseManager->getById($purchaseId);
            $files=array();
            foreach ($purchase->getInvoice() as $invoiceFile) {
                $file['id']=$invoiceFile->getId();
                $file['name']='';
                $file['type']=Common::getFileExtension($invoiceFile->getPath());
                $file['size']=Common::getFileSize($invoiceFile->getPath());
                $file['url']=$invoiceFile->getPath();
                $files[]=$file;
            }
        }catch (\Exception $e) {}
        return new JsonModel($files);
    }

    public function addMessageAction()
    {
        $request = $this->getRequest();
        $result['status'] = 0;
        if($request->isPost()) {
            try {
                $purchaseId = $request->getPost("purchaseId", '0');
                $message = $request->getPost("message", '');
                if (!$purchaseId) throw new \Exception('Không tìm thấy đơn hàng!');
                if (!$message) throw new \Exception('Nhập nội dung ghi chú!');

                $purchase = $this->purchaseManager->getById($purchaseId);
                if (!$purchase) throw new \Exception('Không tìm thấy đơn hàng!');

                $purchaseMessage = new PurchaseMessage();
                $purchaseMessage->setMessage($message);
                $purchaseMessage->setCreatedBy($this->userLogin->getUsername());
                $purchaseMessage->setCreatedDate(new \DateTime());
                $purchaseMessage->setPurchase($purchase);
                $purchase->addMessage($purchaseMessage);

                $this->entityManager->flush();

                $result['status'] = 1;
                $result['data'] = $purchaseMessage->serialize();
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
        }
        return new JsonModel($result);
    }

    public function deleteProductAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $purchaseDetailId = $request->getPost("id",'');
            try{
                if(!$purchaseDetailId)
                    throw new \Exception('Không tìm thấy sản phẩm cần xoá!');

                $purchaseDetail = $this->entityManager->getRepository(PurchaseDetail::class)->find($purchaseDetailId);
                if($purchaseDetail->getPurchase()->getStatus()==Define::STATUS_APPROVED)
                    throw new \Exception('Không thể xoá sản phẩm khi đơn hàng đã duyệt!');

                $this->entityManager->remove($purchaseDetail);
                $this->entityManager->flush();

                $result['status']=1;
                $result['message']='Đã xoá '.$purchaseDetail->getVariants()->getProduct()->getName();

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['message']=$message;
                $result['status']=0;
            }
        }else{
            $result['message']='Phương thức không đúng!';
            $result['status']=0;
        }
        return new JsonModel($result);
    }

    public function deleteAdditionalFeesAction()
    {
        $request = $this->getRequest();

        if($request->isPost()) {
            $additionalFeesId = $request->getPost("id",'');
            try{
                if(!$additionalFeesId)
                    throw new \Exception('Không tìm thấy phụ phí cần xoá!');

                $additionalFees = $this->entityManager->getRepository(PurchaseAdditionalFees::class)->find($additionalFeesId);
                if($additionalFees->getPurchase()->getStatus()==Define::STATUS_APPROVED)
                    throw new \Exception('Không thể xoá phụ phí khi đơn hàng đã duyệt!');

                $this->entityManager->remove($additionalFees);
                $this->entityManager->flush();

                $result['status']=1;
                $result['message']='Đã xoá phụ phí '.$additionalFees->getName();

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['message']=$message;
                $result['status']=0;
            }
        }else{
            $result['message']='Phương thức không đúng!';
            $result['status']=0;
        }
        return new JsonModel($result);
    }
}