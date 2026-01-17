<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;

/**
 * @orm\Entity
 * @orm\Table(name="product_variants")
 */

class Variants
{

    public function __construct() {
        $this->price = new ArrayCollection();
//        $this->priceSchedules = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Product", inversedBy="variants")
     * @orm\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\Price", mappedBy="variants", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="variants_id")
     * @orm\OrderBy({"created_date" = "DESC"})
     */
    private $price;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\PriceSchedule", mappedBy="variant", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="variant_id")
     * @orm\OrderBy({"created_date" = "DESC"})
     */
    private $schedulePrices;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\PriceGroup", mappedBy="variant", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="variant_id")
     */
    private $groupPrices;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\PriceTier", mappedBy="variant", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="variant_id")
     */
    private $tierPrices;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Unit", inversedBy="variants" )
     * @orm\JoinColumn(name="unit_id", referencedColumnName="id")
     */
    private $unit;

    /**
     * @orm\OneToMany(targetEntity="Purchase\Entity\PurchaseDetail", mappedBy="variants")
     * @orm\JoinColumn(name="id", referencedColumnName="variants_id")
     */
    private $purchaseDetail;

    /**
     * @orm\OneToMany(targetEntity="Purchase\Entity\PurchaseReturnDetail", mappedBy="variant")
     * @orm\JoinColumn(name="id", referencedColumnName="variant_id")
     */
    private $purchaseReturnDetail;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="barcode") */
    private $barcode;

    /** @orm\Column(type="string", name="barcode_backup") */
    private $barcode_backup;

    /** @orm\Column(type="integer", name="conversion_rate") */
    private $conversion_rate;

    /** @orm\Column(type="integer", name="status") */
    private $status;

    /** @orm\Column(type="string", name="created_by") */
    private $created_by;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return ArrayCollection
     */
    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getSchedulePrices()
    {
        return $this->schedulePrices;
    }

    public function setSchedulePrices(ArrayCollection $schedulePrices): void
    {
        $this->schedulePrices = $schedulePrices;
    }

    /**
     * @return mixed
     */
    public function getGroupPrices()
    {
        return $this->groupPrices;
    }

    /**
     * @param mixed $groupPrices
     */
    public function setGroupPrices($groupPrices): void
    {
        $this->groupPrices = $groupPrices;
    }

    /**
     * @return mixed
     */
    public function getTierPrices()
    {
        return $this->tierPrices;
    }

    /**
     * @param mixed $tierPrices
     */
    public function setTierPrices($tierPrices): void
    {
        $this->tierPrices = $tierPrices;
    }

    /**
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return mixed
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param mixed $barcode
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * @return mixed
     */
    public function getBarcodeBackup()
    {
        return $this->barcode_backup;
    }

    /**
     * @param mixed $barcode_backup
     */
    public function setBarcodeBackup($barcode_backup): void
    {
        $this->barcode_backup = $barcode_backup;
    }

    /**
     * @return mixed
     */
    public function getConversionRate()
    {
        return $this->conversion_rate;
    }

    /**
     * @param mixed $conversion_rate
     */
    public function setConversionRate($conversion_rate)
    {
        $this->conversion_rate = $conversion_rate;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param mixed $created_by
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * @param mixed $created_date
     */
    public function setCreatedDate($created_date)
    {
        $this->created_date = $created_date;
    }

    /**
     * @return mixed
     */
    public function getPurchaseDetail()
    {
        return $this->purchaseDetail;
    }

    /**
     * @param mixed $purchaseDetail
     */
    public function setPurchaseDetail($purchaseDetail)
    {
        $this->purchaseDetail = $purchaseDetail;
    }

    /**
     * @return mixed
     */
    public function getPurchaseReturnDetail()
    {
        return $this->purchaseReturnDetail;
    }

    /**
     * @param mixed $purchaseReturnDetail
     */
    public function setPurchaseReturnDetail($purchaseReturnDetail): void
    {
        $this->purchaseReturnDetail = $purchaseReturnDetail;
    }

    //--------------------------------------------------
    //          More function
    //--------------------------------------------------

    public function getUnitName()
    {
        return $this->getUnit()->getName();
    }

    /**
     * @return Price
     */
    public function getActivePrice(){
        if($this->getPrice())
            foreach ($this->getPrice() as $item)
                if($item->getActive()==1) return $item;

        return null;
    }

    /**
     * @return int|mixed
     */
    public function getActivePriceValue(){
        $activePriceItem=$this->getActivePrice();
        return $activePriceItem?$activePriceItem->getRetailPrice():0;

    }

    /**
     * @return string
     */
    public function getFullName() {
        if($this->getName())
            return $this->getProduct()->getName().' ('. $this->getName().')';
        else
            return $this->getProduct()->getName();
    }

    /**
     * @return int
     */
    public function getInventory()
    {
        $productInventory=$this->getProduct()->getInventory();
        $conversionRate = $this->getConversionRate();

        return $productInventory/$conversionRate;
    }

    public function serialize() {

        $unitPrices=[];
        if($this->getPrice()){
            foreach ($this->getPrice() as $item){
                $unitPrices[]=$item->serialize();
            }
        }

        //get schedule prices
        $schedulePrices=array();
        if($this->getSchedulePrices()){
            foreach ($this->getSchedulePrices() as $priceSchedule){
                $schedulePrices[]=$priceSchedule->serialize();
            }
        }


        //get group prices
        $groupPrices=array();
        if($this->getGroupPrices()){
            foreach ($this->getGroupPrices() as $groupPrice){
                $groupPrices[]=$groupPrice->serialize();
            }
        }

        //get tier prices
        $tierPrices=array();
        if($this->getTierPrices()){
            foreach ($this->getTierPrices() as $tierPrice){
                $tierPrices[]=$tierPrice->serialize();
            }
        }

        return [
            'id' => $this->getId(),
            'unit_prices'=>$unitPrices,
            'effective_price'=>$this->calculateEffectivePrice(),
            'schedule_prices' => $schedulePrices,
            'group_prices' => $groupPrices,
            'tier_prices' => $tierPrices,
            'name' => $this->getName(),
            'barcode_sub' => ($this->getBarcode()?substr($this->getBarcode(),-6):''),
            'barcode' => $this->getBarcode(),
            'unit'=>array('id'=>$this->getUnit()->getId(),'name'=>$this->getUnit()->getName()),
//            'unit' => $this->getUnitName(),
//            'unit_id' => $this->getUnit()->getId(),
            'conversion_rate' => $this->getConversionRate(),
            'status' => $this->getStatus(),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate())
//            'purchase_latest_price'=>$this->getPurchaseLatestPrice(),
        ];
    }

    public function addPrice(Price $price)
    {
        if (!$this->price->contains($price)) {
            $this->price->add($price);
        }
        return $this;
    }
    public function calculateEffectivePrice(int $qty = 1, int $groupId=0, \DateTime $now = null)
    {
        // Pseudocode rule:
        // special price > group price > tier price > base price

        $now = $now ?: new \DateTime();

        //1. Special
        if($this->getSchedulePrices()){
            $sp = $this->getSchedulePrices()->filter(fn($p) =>
                $p->getStartDate() <= $now && $p->getEndDate() >= $now
            )->first();

            if ($sp) return $sp->getSpecialPrice();
        }

        //2. Group price (Ví dụ nhóm khách hàng mặc định)
        if($this->getGroupPrices()){
            foreach ($this->getGroupPrices() as $gp) {
                if ($gp->getGroup()->getId() === $groupId) {
                    return $gp->getPrice();
                }
            }
        }
//        $groupPrice = $this->getGroupPrices()->first();
//        if ($groupPrice) return $groupPrice->getPrice();

        //3. Tier price
        $bestTier = null;
        if($this->getTierPrices()){
            foreach ($this->getTierPrices() as $tp) {
                if ($qty >= $tp->getMinQty()) {
                    if (!$bestTier || $tp->getMinQty() > $bestTier->getMinQty()) {
                        $bestTier = $tp;
                    }
                }
            }
        }
        if ($bestTier) {
            return $bestTier->getPrice();
        }
//        $tier = $this->getTierPrices()->first();
//        if ($tier) return $tier->getPrice();

        // Default base price
        return $this->getActivePriceValue();
    }

    public function getPurchaseDetailLatest()
    {
        $latest=null;
        foreach ($this->getPurchaseDetail() as $purchaseDetail){
            if (($latest === null || $purchaseDetail->getPurchase()->getApprovedDate()->getTimestamp() > $latest->getPurchase()->getApprovedDate()->getTimestamp())
                && $purchaseDetail->getPurchase()->getStatus()==Define::PURCHASE_APPROVAL) {

                $latest = $purchaseDetail;
            }
        }
        return $latest;
    }
}