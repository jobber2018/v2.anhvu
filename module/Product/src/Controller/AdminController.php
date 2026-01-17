<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Product\Controller;

use Exception;
use Laminas\Diactoros\UploadedFile;
use Laminas\Session\Service\StorageFactory;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Product\Entity\Variants;
use Product\Entity\Image;
use Product\Entity\Product;
use Product\Entity\Price;
use Product\Form\ProductForm;
use Product\Service\ProductManager;
use Doctrine\ORM\EntityManager;
use Product\Service\VariantManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Sulde\Service\FileUploader;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeAdminController;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $productManager;

    public function __construct(EntityManager $entityManager, ProductManager $productManager)
    {
        $this->entityManager = $entityManager;
        $this->productManager = $productManager;
    }


    /**
     * @return ViewModel
     */
    public function dashboardAction(){
        $privilegeId=3;
        return new ViewModel([
            'privilegeId'=>$privilegeId,
            'privileges'=>$this->userLogin->getPrivileges()
        ]);
    }

    /**
     * @return ViewModel
     */
    public function detailAction(){
        $productPublicId = $this->params()->fromRoute('id',0);

        $product = $this->productManager->getPublicById($productPublicId);

        return new ViewModel(['product'=>$product]);
    }

    /**
     * @return JsonModel|ViewModel
     */
    public function listAction(){
        $request = $this->getRequest();

        if($request->isPost()) {
            $keyword = $this->params()->fromPost('search')['value'];
            $length = $this->params()->fromPost('length',Define::ITEM_PAGE_COUNT);
            $start = $this->params()->fromPost('start',0);
            $draw = $this->params()->fromPost('draw',1);

            /*$tableColumns=$this->params()->fromPost('columns');
            $orderColumnIndex=$this->params()->fromPost('order')[0]['column'];

            $orderDir=$this->params()->fromPost('order')[0]['dir'];//asc or desc
            $orderColumnName=$tableColumns[$orderColumnIndex]['name'];

            if(!$orderColumnName){
                $orderColumnName='created_date';
                $orderDir='desc';
            }*/

            $products = $this->productManager->searchProducts($keyword, $length, $start);
//            $output = $this->productManager->getProductsWithEffectivePrices($keyword, $length, $start);

            $productResult=array();
            foreach ($products as $productItem){
                $productResult[]=$productItem->serialize();
            }
            $result['draw']=$draw;
            $result['recordsTotal']=count($products);
            $result['recordsFiltered']=count($products);
            $result['data']=$productResult;
            return new JsonModel($result);
        }
        return new ViewModel();
    }

    public function editAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try {
                $data = array_merge_recursive(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
                );
                //check barcode attribute exits
                if (!empty($data['vBarcode']) && is_array($data['vBarcode'])){
                    $variantManager = new VariantManager($this->entityManager);
                    foreach ($data['vBarcode'] as $key=>$vBarcode){
                        if($vBarcode){
                            $productTmp = $variantManager->getByBarcode($vBarcode);
                            if($productTmp && $productTmp->getId()!=$data['vVariantsId'][$key]){
                                $result['data']=array('variant'=>'vBarcode[]','index'=>$key);
                                throw new \Exception($productTmp->getProduct()->getName().'|'.$productTmp->getName(). ' đang sử dụng!');
                            }
                        }
                    }
                }

                //check vConversionRate >0
                if (!empty($data['vConversionRate']) && is_array($data['vConversionRate'])){
                    foreach ($data['vConversionRate'] as $key=>$vConversionRate){
                        if(!$vConversionRate || $vConversionRate<=0){
                            $result['data']=array('variant'=>'vConversionRate[]','index'=>$key);
                            throw new \Exception('Đơn vị quy đổi phải lớn hơn 0!');
                        }
                    }
                }

                //check variant name is not null
                if (!empty($data['vName']) && is_array($data['vName'])){
                    foreach ($data['vName'] as $key=>$vName){
                        if(!$vName){
                            $result['data']=array('variant'=>'vName[]','index'=>$key);
                            throw new \Exception('Quy cách thuộc tính không được trống!');
                        }
                    }
                }

                $productName=trim($data['name']);
                if(!$productName)
                    throw new \Exception('Tên sản phẩm không được để trống!');

                $product = $this->productManager->getById($data['product_id']);

                $product->setName($productName);
                $product->setSku(trim($data['sku']));
                $product->setVatValue($data['vat_value']);
                $product->setVatOption($data['vat_option']);
                if($data['import_tax'])
                    $product->setImportTax($data['import_tax']);
                if($data['export_tax'])
                    $product->setExportTax($data['export_tax']);
                $product->setNormMin($data['norm_min']);
                $product->setNormMax($data['norm_max']);
                $product->setNormInput($data['norm_input']);
                $product->setNote(trim($data['note']));
                $product->setUpdatedBy($this->userLogin->getUsername());
                $product->setUpdatedDate(new \DateTime());
                $product->setKeyword($data['keyword']);

                $unit = $this->productManager->getUnitById($data['unit']);
                $product->setUnit($unit);

                $categories = $this->productManager->getCategoriesById($data['categories']);
                $product->setCategories($categories);

                if (!empty($data['vVariantsId']) && is_array($data['vVariantsId'])){
                    foreach ($data['vVariantsId'] as $key=>$vVariantsId){
                        $vName = @$data['vName'][$key];
                        $vBarcode = @$data['vBarcode'][$key];
                        $vUnitId = @$data['vUnit'][$key];
                        $vConversionRate = @$data['vConversionRate'][$key];
                        $productUnit = $this->productManager->getUnitById($vUnitId);

                        $attribute = $product->isVariantsExist($vVariantsId);
                        if($attribute){
                            $attribute->setProduct($product);
                            $attribute->setBarcode($vBarcode);
                            $attribute->setName($vName);
                            $attribute->setUnit($productUnit);
                            $attribute->setConversionRate($vConversionRate);
                        }else{
                            $attribute = new Variants();
                            $attribute->setProduct($product);
                            $attribute->setBarcode($vBarcode);
                            $attribute->setName($vName);
                            $attribute->setUnit($productUnit);
                            $attribute->setConversionRate($vConversionRate);
                            $attribute->setStatus(Define::DEFAULT_UN_ACTIVE);
                            $attribute->setCreatedBy($this->userLogin->getUsername());
                            $attribute->setCreatedDate(new \DateTime());
                            $product->addVariants($attribute);
                        }
                    }
                }
                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $result['status']=1;
                $result['message']='Đã cập nhật dữ liệu sản phẩm!';

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);

        }else{
            $productPublicId = $this->params()->fromRoute('id',0);
            $categoriesList = $this->productManager->getAllCategories();
            $categoriesData=array();
            foreach ($categoriesList as $item) {
                $categoriesData[$item->getId()] = $item->getName();
            }
            $unitList = $this->productManager->getAllUnit();
            $unitData=array();
            foreach ($unitList as $item) {
                $unitData[$item->getId()] = $item->getName();
            }
            $form =new ProductForm($categoriesData,$unitData);

            $product = $this->productManager->getPublicById($productPublicId);
            $data = $product->serialize();
            $data['unit']=$product->getUnit()->getId();
            $data['categories']=$product->getCategories()->getId();
            $form->setData($data);
            return new ViewModel(['form'=>$form,'unitList'=>$unitList,'product'=>$product]);
        }
    }

    public function addAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            $data = $request->getPost()->toArray();
            try {
                if (empty($data['vBarcode']))
                    throw new \Exception('Sản phẩm phải có ít nhất một thuộc tính!');

                //check barcode exits
                if (!empty($data['vBarcode']) && is_array($data['vBarcode'])){
                    $variantManager = new VariantManager($this->entityManager);
                    foreach ($data['vBarcode'] as $key=>$vBarcode){
                        if($vBarcode){
                            $productTmp = $variantManager->getByBarcode($vBarcode);
                            if($productTmp){
                                $result['data']=array('variant'=>'vBarcode[]','index'=>$key);
                                throw new \Exception($productTmp->getProduct()->getName().'|'.$productTmp->getName(). ' đang sử dụng!');
                            }
                        }
                    }
                }
                //check vConversionRate >0
                if (!empty($data['vConversionRate']) && is_array($data['vConversionRate'])){
                    foreach ($data['vConversionRate'] as $key=>$vConversionRate){
                        if(!$vConversionRate || $vConversionRate<=0){
                            $result['data']=array('variant'=>'vConversionRate[]','index'=>$key);
                            throw new \Exception('Đơn vị quy đổi phải lớn hơn 0!');
                        }
                    }
                }

                //check variant name is not null
                if (!empty($data['vName']) && is_array($data['vName'])){
                    foreach ($data['vName'] as $key=>$vName){
                        if(!$vName){
                            $result['data']=array('variant'=>'vName[]','index'=>$key);
                            throw new \Exception('Quy cách thuộc tính không được trống!');
                        }
                    }
                }

                $productName=trim($data['name']);
                if(!$productName)
                    throw new \Exception('Tên sản phẩm không được để trống!');

                $product = new Product();
                $product->setName($productName);
                $product->setSku(trim(@$data['sku']));
                $product->setVatValue(@$data['vat_value']);
                $product->setVatOption(@$data['vat_option']);
                $product->setImportTax(@$data['import_tax']);
                $product->setExportTax(@$data['export_tax']);
                $product->setNormMin(@$data['norm_min']);
                $product->setNormMax(@$data['norm_max']);
                $product->setNormInput(@$data['norm_input']);
                $product->setNote(trim(@$data['note']));
                $product->setCreatedBy($this->userLogin->getUsername());
                $product->setCreatedDate(new \DateTime());
                $product->setStatus(Define::DEFAULT_UN_ACTIVE);
                $product->setKeyword(@$data['keyword']);
                $product->setCost(0);

                $unit = $this->productManager->getUnitById($data['unit']);
                $product->setUnit($unit);

                $categories = $this->productManager->getCategoriesById($data['categories']);
                $product->setCategories($categories);


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
                    $uploadPath = '/assets/product/images/';
                    $fileUploader = new FileUploader($uploadPath);
                    $resultUpload = $fileUploader->upload($uploadedFile);

                    if ($resultUpload['success']){
                        $productImage = new Image();
                        $productImage->setProduct($product);
                        $productImage->setPath($uploadPath.$resultUpload['filename']);
                        $productImage->setDefault(1);
                        $product->addImage($productImage);
                    }
                }
                //insert variants
                if (!empty($data['vBarcode']) && is_array($data['vBarcode'])){
                    foreach ($data['vBarcode'] as $key=>$vBarcode){
                        $vName = @$data['vName'][$key];
                        $vUnitId = @$data['vUnit'][$key];
                        $vConversionRate = @$data['vConversionRate'][$key];
                        $productUnit = $this->productManager->getUnitById($vUnitId);

                        $variant = new Variants();
                        $variant->setProduct($product);
                        $variant->setBarcode($vBarcode);
                        $variant->setName($vName);
                        $variant->setUnit($productUnit);
                        $variant->setConversionRate($vConversionRate);
                        $variant->setStatus(Define::DEFAULT_UN_ACTIVE);
                        $variant->setCreatedBy($this->userLogin->getUsername());
                        $variant->setCreatedDate(new \DateTime());

                        $product->addVariants($variant);
                    }
                }

                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $result['status']=1;
//                $result['product_public_id']=$product->getPublicId();
                $result['product']=$product->serialize();
                $result['message']='Đã thêm mới sản phẩm!';

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);
        }
        else{
            $categoriesList = $this->productManager->getAllCategories();
            $categoriesData=array();
            foreach ($categoriesList as $item) {
                $categoriesData[$item->getId()] = $item->getName();
            }
            $unitList = $this->productManager->getAllUnit();
            $unitData=array();
            foreach ($unitList as $item) {
                $unitData[$item->getId()] = $item->getName();
            }
            $form =new ProductForm($categoriesData,$unitData);
            return new ViewModel(['form'=>$form,'unitList'=>$unitList]);
        }
    }

    public function deleteAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $productId = $request->getPost("id",0);
                if($productId==0)
                    throw new \Exception('Không tìm thấy sản phẩm!');

                $product = $this->productManager->getById($productId);

                if(count($product->getHistory()->toArray()))
                    throw new \Exception('Sản phẩm đang được sử dụng nên không thể xoá!');

                //remove file on server
                $images=$product->getImage();
                if($images){
                    foreach ($images as $imageItem) {
                        if (file_exists(ROOT_PATH.$imageItem->getPath())) {
                            unlink(ROOT_PATH.$imageItem->getPath());
                        }
                    }
                }

                $this->entityManager->remove($product);
                $this->entityManager->flush();
                $result['message'] = 'Đã xoá sản phẩm '.$product->getName();
                $result['status'] = 1;
            } catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }
            return new JsonModel($result);
        }
    }

    public function autocompleteAction(){
        try{
            $keyword = $this->params()->fromQuery('q','');
            $products=$this->productManager->searchProducts($keyword, Define::ITEM_PAGE_COUNT, 0);
            $searchResult=array();
            foreach ($products as $product){
                $productSerialize = $product->serialize();

                $purchaseDetailLatest=null;
                foreach ($product->getVariants() as $variant) {
                    if(empty($purchaseDetailLatest))
                        $purchaseDetailLatest=$variant->getPurchaseDetailLatest();
                }

                if(!empty($purchaseDetailLatest)){
                    $purchaseDetailSerialize = $purchaseDetailLatest->serialize();
                    $purchaseDetailSerialize['base_unit_price']=$purchaseDetailLatest->getPrice()/$purchaseDetailLatest->getConversionRate();
                    $purchaseDetailSerialize['supplier']=$purchaseDetailLatest->getPurchase()->getSupplier()->serialize();
                    $productSerialize['purchase_detail_latest']=$purchaseDetailSerialize;
                }

                $searchResult[]= $productSerialize;
            }
            $result['status']=1;
            $result['data']=$searchResult;
        }catch (\Exception $e){
            $result['status']=0;
            $result['message']=$e->getMessage();
        }
        return new JsonModel($result);
    }

    public function copyAction(){
        $productId = $this->params()->fromRoute('id',0);
        $categoriesList = $this->productManager->getAllCategories();
        $categoriesData=array();
        foreach ($categoriesList as $item) {
            $categoriesData[$item->getId()] = $item->getName();
        }
        $unitList = $this->productManager->getAllUnit();
        $unitData=array();
        foreach ($unitList as $item) {
            $unitData[$item->getId()] = $item->getName();
        }
        $form =new ProductForm($categoriesData,$unitData);

        $product = $this->productManager->getPublicById($productId);
        $data=$product->serialize();
        $data['keyword']='';
        $data['sku']='';
        $data['note']='';
        $data['unit']=$product->getUnit()->getId();
        $data['categories']=$product->getCategories()->getId();
        $form->setData($data);
        return new ViewModel(['form'=>$form,'unitList'=>$unitList,'product'=>$product]);
    }

    public function lockUnlockAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $productId=$request->getPost("id",0);

                if(!$productId) throw new \Exception('Không tìm thấy dữ liệu cần cập nhật!');

                $product = $this->productManager->getById($productId);

                if($product->getStatus()==1){
                    //khoa all variants
                    foreach ($product->getVariants() as $variant){
                        if($variant->getStatus()==1){
                            $variant->setBarcodeBackup($variant->getBarcode());
                            $variant->setBarcode(null);
                            $variant->setStatus(Define::DEFAULT_UN_ACTIVE);
                        }
                    }
                    $status=0;
                    $message='Đã khoá, sản phẩm không thể giao dịch được nữa!';
                }else{
                    $unlockValid=$product->isUnlockValid();
                    if($unlockValid['valid']==0)
                        throw new \Exception($unlockValid['message']);
                    $status=1;
                    $message='Đã mở khoá, sản phẩm có thể tiếp tục giao dịch!';
                }

                $product->setStatus($status);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['message'] = $message;

            } catch (\Exception $e) {
                $result['status'] = 0;
                $result['message'] = $e->getMessage();
            }

        }else{
            $result['status'] = 0;
            $result['message'] = 'Only method post';
        }
        return new JsonModel($result);
    }

    public function uploadImageAction(){

        $productId = $this->params()->fromRoute('id',0);

        if($productId<=0){
            $this->getResponse()->setStatusCode('404');
            return;
        }
        $request = $this->getRequest();

        if($request->isPost()) {
            try {
                $fileArray=$request->getFiles()->toArray();

                if(empty($fileArray)) throw new \Exception('Không tìm thấy ảnh sản phẩm!');

                $uploadedFile = new UploadedFile(
                    $fileArray['file']['tmp_name'],
                    $fileArray['file']['size'],
                    $fileArray['file']['error'],
                    $fileArray['file']['name'],
                    $fileArray['file']['type']
                );
                $uploadPath = '/assets/product/images/';
                $fileUploader = new FileUploader($uploadPath);
                $resultUpload = $fileUploader->upload($uploadedFile);
                if(!$resultUpload['success'])
                    throw new \Exception($resultUpload['message']);

                $product = $this->productManager->getById($productId);

                $productImage = new Image();
                $productImage->setProduct($product);
                $productImage->setPath($uploadPath.$resultUpload['filename']);
                $productImage->setDefault(1);
                $product->addImage($productImage);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['id'] = $productImage->getId();
                $result['url'] = $uploadPath.$resultUpload['filename'];
                $result['message'] = 'Upload thành công ảnh sản phẩm!';

            }catch (\Exception $e) {
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

    public function deleteImageAction(){
        $request = $this->getRequest();

        $result['status']=Define::DEFAULT_UN_ACTIVE;
        $result['default_id'] = Define::DEFAULT_UN_ACTIVE;

        if($request->isPost()) {
            $imageId = $request->getPost("id");
            if($imageId){

                $image = $this->entityManager->getRepository(Image::class)->find($imageId);

                //thiet lap lai default image neu image bi xoa la default
                if($image->getDefault()==1){
                    $product=$image->getProduct();
                    $images=$product->getImage();
                    if($images){
                        $fistRecord=0;
                        foreach ($images as $img){
                            if($fistRecord==0 && $image->getId()!=$img->getId()){
                                $img->setDefault(Define::DEFAULT_ACTIVE);
                                $this->entityManager->persist($img);
//                                $this->entityManager->flush();
                                $fistRecord=1;
                                $result['default_id'] = $img->getId();
                            }
                        }
                    }
                }

                //remove file on server
                if($image->getPath()){
                    $imgPath = parse_url($image->getPath(), PHP_URL_PATH);
                    if (file_exists(ROOT_PATH.$imgPath)) {
                        unlink(ROOT_PATH.$imgPath);
                    }
                }

                //remove file in data
                $this->entityManager->remove($image);
                $this->entityManager->flush();

                $result['status']=Define::DEFAULT_ACTIVE;
                $result['message']='Đã xoá file ảnh sản phẩm!';
            }else
                $result['message']='Không thể xoá file!';
        }else{
            $result['message']='Phương thức gửi file không đúng!';
        }
        return new JsonModel($result);
    }

    /**
     * get danh sach file cua invoice
     * @return JsonModel
     */
    public function fileListAction()
    {
        $productId = $this->params()->fromRoute('id',0);
        $files=array();
        try {
            $product = $this->productManager->getById($productId);
            $files=array();
            foreach ($product->getImage() as $image) {
                $file['id']=$image->getId();
                $file['name']='';
                $file['type']=Common::getFileExtension($image->getPath());
                $file['size']=Common::getFileSize($image->getPath());
                $file['url']=$image->getPath();
                $file['default']=$image->getDefault();
                $files[]=$file;
            }
        }catch (\Exception $e) {}
        return new JsonModel($files);
    }

    public function costUpdateAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $productId = $request->getPost('id', 0);
                $cost = $request->getPost('cost', 0);
                if (!$productId) throw new Exception('Không tìm thấy sản phẩm thay đổi!');
                if (!$cost) throw new Exception('Giá vốn phải lơn hơn 0!');

                $product = $this->productManager->getById($productId);

                $costOld=$product->getCost();
                $product->setCost($cost);
                $product->setCostOld($costOld);
                $this->entityManager->flush();
                $result['status'] = 1;
                $result['message'] = 'Đã cập nhật giá bán @todo!';

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
    }
    public function scanBarcodeAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $barcode = $request->getPost('barcode', '');
                if (!$barcode) throw new Exception('Không tìm thấy sản phẩm!');

                $variantsItem = $this->productManager->getByBarcode($barcode);
                if (!$variantsItem) throw new Exception('Không tìm thấy sản phẩm!');

                $variant=$variantsItem->serialize();
                $variant['product']=$variantsItem->getProduct()->serialize();

                $result['variant']=$variant;
                $result['status'] = 1;
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
    }
}