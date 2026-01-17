<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Product\Controller;

use Customer\Entity\Group;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Product\Entity\Price;
use Product\Entity\PriceGroup;
use Product\Entity\PriceSchedule;
use Product\Entity\PriceTier;
use Product\Entity\Variants;
use Product\Service\PriceManager;
use Product\Service\ProductManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Sulde\Service\SuldeAdminController;
use Supplier\Service\SupplierManager;

class PriceController extends SuldeAdminController
{
    private $entityManager;
    private $priceManager;
    private $productManager;

    public function __construct(EntityManager $entityManager, PriceManager $priceManager, ProductManager $productManager)
    {
        $this->entityManager = $entityManager;
        $this->priceManager = $priceManager;
        $this->productManager = $productManager;
    }

    public function detailAction(){
        $productPublicId = $this->params()->fromRoute('id',0);

        $product = $this->productManager->getPublicById($productPublicId);

        $supplierManager = new SupplierManager($this->entityManager);
        $supplierGroups = $supplierManager->getGroups();

        return new ViewModel(['product'=>$product,'supplierGroups'=>$supplierGroups]);
    }

    /**
     * them gia dac biet
     * @return JsonModel|void
     */
    public function addSchedulePriceAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $variantId = $request->getPost('id', 0);
                $price = $request->getPost('price', 0);
                $schedule = $request->getPost('schedule', '');
                if (!$variantId)
                    throw new Exception('Không tìm thấy sản phẩm thay đổi!');

                if (!$price)
                    throw new Exception('Vui lòng nhập giá bán!');

                if (!$schedule)
                    throw new Exception('Vui lòng nhập thời gian áp dụng!');

                list($startStr, $endStr) = explode(" - ", $schedule);
                $startDate = DateTime::createFromFormat('d/m/Y h:i A', $startStr);
                $endDate   = DateTime::createFromFormat('d/m/Y h:i A', $endStr);

                $variant = $this->entityManager->getRepository(Variants::class)->find($variantId);

                $priceSchedule = new PriceSchedule();
                $priceSchedule->setSpecialPrice($price);
                $priceSchedule->setStartDate($startDate);
                $priceSchedule->setEndDate($endDate);
                $priceSchedule->setCreatedDate(new \DateTime());
                $priceSchedule->setCreatedBy($this->userLogin->getUsername());
                $priceSchedule->setVariant($variant);
                $this->entityManager->persist($priceSchedule);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['data'] = $priceSchedule->serialize();
                $result['message'] = 'Đã thêm giá đặc biệt!';

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
    }

    /**
     * xoa gia dac biet
     * @return JsonModel|void
     */
    public function deleteSchedulePriceAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $scheduleId = $request->getPost('id', 0);

                if (!$scheduleId)
                    throw new Exception('Không tìm thấy dữ liệu cần xoá!');

                $priceSchedule = $this->entityManager->getRepository(PriceSchedule::class)->find($scheduleId);

                $this->entityManager->remove($priceSchedule);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['message'] = 'Đã xoá giá đặc biệt!';

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
    }

    /**
     * them nhom gia ban
     * @return JsonModel|void
     */
    public function addGroupPriceAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $variantId = $request->getPost('id', 0);
                $price = $request->getPost('price', 0);
                $groupId = $request->getPost('group_id', '');
                if (!$variantId)
                    throw new Exception('Không tìm thấy sản phẩm thay đổi!');

                if (!$price)
                    throw new Exception('Vui lòng nhập giá bán!');

                $group = $this->entityManager->getRepository(Group::class)->find($groupId);

                $variant = $this->entityManager->getRepository(Variants::class)->find($variantId);

                $groupPrice = new PriceGroup();
                $groupPrice->setPrice($price);
                $groupPrice->setGroup($group);
                $groupPrice->setCreatedDate(new \DateTime());
                $groupPrice->setCreatedBy($this->userLogin->getUsername());
                $groupPrice->setVariant($variant);
                $this->entityManager->persist($groupPrice);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['data'] = $groupPrice->serialize();
                $result['message'] = 'Đã thêm giá theo nhóm!';

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
    }

    /**
     * Xoa nhoms gia ban
     * @return JsonModel|void
     */
    public function deleteGroupPriceAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $groupPriceId = $request->getPost('id', 0);

                if (!$groupPriceId)
                    throw new Exception('Không tìm thấy dữ liệu cần xoá!');

                $groupPrice = $this->entityManager->getRepository(PriceGroup::class)->find($groupPriceId);

                $this->entityManager->remove($groupPrice);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['message'] = 'Đã xoá nhóm giá!';

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
    }

    /**
     * them gia ban theo so luong
     * @return JsonModel|void
     */
    public function addTierPriceAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $variantId = $request->getPost('id', 0);
                $price = $request->getPost('price', 0);
                $qtyFrom = $request->getPost('min_qty', 0);
                $qtyTo = $request->getPost('max_qty', 0);

                if (!$variantId)
                    throw new Exception('Không tìm thấy sản phẩm thay đổi!');

                if (!$price)
                    throw new Exception('Vui lòng nhập giá bán!');

                if (!$qtyFrom || !$qtyTo || $qtyFrom > $qtyTo)
                    throw new Exception('Số lượng không hợp lệ!');

                $variant = $this->entityManager->getRepository(Variants::class)->find($variantId);

                $groupPrice = new PriceTier();
                $groupPrice->setPrice($price);
                $groupPrice->setMinQty($qtyFrom);
                $groupPrice->setMaxQty($qtyTo);
                $groupPrice->setCreatedDate(new \DateTime());
                $groupPrice->setCreatedBy($this->userLogin->getUsername());
                $groupPrice->setVariant($variant);
                $this->entityManager->persist($groupPrice);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['data'] = $groupPrice->serialize();
                $result['message'] = 'Đã thêm giá theo số lượng!';

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
    }

    /**
     * Xoa gia ban theo so luong
     * @return JsonModel|void
     */
    public function deleteTierPriceAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            try {
                $tierPriceId = $request->getPost('id', 0);

                if (!$tierPriceId)
                    throw new Exception('Không tìm thấy dữ liệu cần xoá!');

                $tierPrice = $this->entityManager->getRepository(PriceTier::class)->find($tierPriceId);

                $this->entityManager->remove($tierPrice);
                $this->entityManager->flush();

                $result['status'] = 1;
                $result['message'] = 'Đã xoá giá bán theo số lượng!';

            } catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status'] = 0;
                $result['message'] = $message;
            }
            return new JsonModel($result);
        }
    }

    /**
     * cap nhat gia ban variant
     * @return JsonModel|void
     */
    public function retailPriceUpdateAction(){
        $request = $this->getRequest();
        if($request->isPost()){
            try {
                $retailPrice=$request->getPost('price',0);
                $variantId=$request->getPost('id',0);
                if(!$variantId)
                    throw new Exception('Không tìm thấy sản phẩm thay đổi!');
                if(!$retailPrice)
                    throw new Exception('Vui lòng nhập giá bán!');

                $variant = $this->entityManager->getRepository(Variants::class)->find($variantId);
                $activePrice=$variant->getActivePrice();
                $retailPrice=Common::formatNumber($retailPrice);
                //Thay doi gia ban
                if($activePrice && $activePrice->getRetailPrice()!=$retailPrice){
                    $activePrice->setActive(Define::DEFAULT_UN_ACTIVE);
                    $newPriceItem=$this->_priceItemInit($retailPrice,$variant);
                    $this->entityManager->persist($variant);
                    $this->entityManager->flush();
                    $result['data']=$newPriceItem->serialize();
                }elseif(!$activePrice && $retailPrice){//nhap moi gia ban
                    $newPriceItem=$this->_priceItemInit($retailPrice,$variant);
                    $this->entityManager->persist($variant);
                    $this->entityManager->flush();
                    $result['data']=$newPriceItem->serialize();
                }

                $result['status']=1;
                $result['message']='Đã cập nhật giá bán!';

            }catch (\Exception $e) {
                $message = $e->getMessage();
                $result['status']=0;
                $result['message']=$message;
            }
            return new JsonModel($result);
        }
    }

    /**
     * @param $p_price
     * @param $p_variantItem
     * @return Price
     */
    private function _priceItemInit($p_price, $p_variantItem)
    {
        $priceItem = new Price();
        $priceItem->setRetailPrice($p_price);
        $priceItem->setActive(Define::DEFAULT_ACTIVE);
        $priceItem->setVariants($p_variantItem);
        $priceItem->setCreatedDate(new \DateTime());
        $priceItem->setCreatedBy($this->userLogin->getUsername());
        $p_variantItem->addPrice($priceItem);

        return $priceItem;
    }
}