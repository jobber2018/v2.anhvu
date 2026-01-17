<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Product\Controller;

use Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Product\Entity\Variants;
use Product\Entity\Image;
use Product\Entity\Product;
use Product\Entity\Price;
use Product\Form\ProductForm;
use Product\Service\PriceManager;
use Product\Service\ProductManager;
use Doctrine\ORM\EntityManager;
use Product\Service\VariantManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Sulde\Service\ImageUpload;
use Sulde\Service\SuldeAdminController;

class VariantController extends SuldeAdminController
{
    private $entityManager;
    private $variantManager;

    public function __construct(EntityManager $entityManager, VariantManager $variantManager)
    {
        $this->entityManager = $entityManager;
        $this->variantManager = $variantManager;
    }

    public function lockUnlockAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $variantId=$request->getPost("id",0);

                if(!$variantId) throw new \Exception('Không tìm thấy dữ liệu cần cập nhật!');

                $variant = $this->variantManager->getById($variantId);
                $product=$variant->getProduct();

                //neu variant dang giao dich=>khoa variant va kiem tra sp con variant nao giao dich? neu khong thi tat luon sp
                if($variant->getStatus()==1){
                    $variant->setBarcodeBackup($variant->getBarcode());
                    $variant->setBarcode(null);

                    $productIsActive=0;

                    foreach ($product->getVariants() as $variantTmp){
                        if($variantTmp->getStatus()==1 && $variantTmp->getId()!=$variant->getId())
                            $productIsActive=1;
                    }
                    $product->setStatus($productIsActive);
                    $status=0;
                    $message='Đã khoá, thuộc tính không thể giao dịch được nữa!';
                }else{
                    if(!$variant->getActivePriceValue())
                        throw new \Exception('Không thể mở khoá do thuộc tính chưa có giá bán cơ bản!');

                    //kiem tra co barcode backup? neu co kiem tra barcode backup co dang duoc dung? neu khong dung=>lay lai barcode cho sp
                    if($variant->getBarcodeBackup()){
                        $variantsTmp = $this->variantManager->getByBarcode($variant->getBarcodeBackup());
                        if(!$variantsTmp){
                            $variant->setBarcode($variant->getBarcodeBackup());
                            $variant->setBarcodeBackup(null);
                        }
                    }

                    $product->setStatus(1);
                    $status=1;
                    $message='Đã mở khoá, thuộc tính có thể tiếp tục giao dịch!';
                }

                $variant->setStatus($status);
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

    public function autocompleteAction(){
        try{
            $keyword = $this->params()->fromQuery('q','');

            $variants = $this->productManager->searchVariants($keyword, Define::ITEM_PAGE_COUNT, 0);
            $variantResult = array();
            foreach ($variants as $variantItem){
                $variant=$variantItem->serialize();
                $variant['product']=$variantItem->getProduct()->serialize();
                $variantResult[]=$variant;
            }
            $result['status']=1;
            $result['variants']=$variantResult;
        }catch (\Exception $e){
            $result['status']=0;
            $result['message']=$e->getMessage();
        }
        return new JsonModel($result);
    }
}