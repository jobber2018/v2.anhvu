<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Purchase\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;

/**
 * @orm\Entity
 * @orm\Table(name="purchase_return_detail")
 */

class PurchaseReturnDetail
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Purchase\Entity\PurchaseReturn", inversedBy="purchaseReturnDetail")
     * @orm\JoinColumn(name="purchase_return_id", referencedColumnName="id")
     */
    private $purchaseReturn;

    /**
     * @orm\ManyToOne(targetEntity="Purchase\Entity\PurchaseDetail", inversedBy="purchaseReturnDetail")
     * @orm\JoinColumn(name="purchase_detail_id", referencedColumnName="id")
     */
    private $purchaseDetail;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Variants", inversedBy="purchaseReturnDetail" )
     * @orm\JoinColumn(name="variant_id", referencedColumnName="id")
     */
    private $variant;


    /** @orm\Column(type="decimal", precision=10, scale=2, name="price") */
    private $price;

    /** @orm\Column(type="decimal", precision=5, scale=2, name="qty") */
    private $qty;

    /** @orm\Column(type="string", name="unit_name") */
    private $unit_name;

    /** @orm\Column(type="decimal", precision=10, scale=2, name="discount") */
    private $discount;

    /** @orm\Column (type="string", name="discount_type") */
    private $discount_type;

    /** @orm\Column(type="decimal", precision=3, scale=2, name="vat") */
    private $vat;

    /** @orm\Column (type="integer", name="conversion_rate") */
    private $conversion_rate;
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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPurchaseReturn()
    {
        return $this->purchaseReturn;
    }

    /**
     * @param mixed $purchaseReturn
     */
    public function setPurchaseReturn($purchaseReturn): void
    {
        $this->purchaseReturn = $purchaseReturn;
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
    public function setPurchaseDetail($purchaseDetail): void
    {
        $this->purchaseDetail = $purchaseDetail;
    }

    /**
     * @return mixed
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * @param mixed $variant
     */
    public function setVariant($variant): void
    {
        $this->variant = $variant;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param mixed $qty
     */
    public function setQty($qty): void
    {
        $this->qty = $qty;
    }

    /**
     * @return mixed
     */
    public function getUnitName()
    {
        return $this->unit_name;
    }

    /**
     * @param mixed $unit_name
     */
    public function setUnitName($unit_name): void
    {
        $this->unit_name = $unit_name;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param mixed $discount
     */
    public function setDiscount($discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return mixed
     */
    public function getDiscountType()
    {
        return $this->discount_type;
    }

    /**
     * @param mixed $discount_type
     */
    public function setDiscountType($discount_type): void
    {
        $this->discount_type = $discount_type;
    }

    /**
     * @return mixed
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param mixed $vat
     */
    public function setVat($vat): void
    {
        $this->vat = $vat;
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
    public function setConversionRate($conversion_rate): void
    {
        $this->conversion_rate = $conversion_rate;
    }


    /**
     * -------------------------------------------------------------------------------------
     * More function
     * -------------------------------------------------------------------------------------
     */

    public function getTotalAmount()
    {
        return $this->getQty()*$this->getPrice();
    }
    /**
     * tien giam gia cua san pham
     * @return float|int|mixed
     */
    public function getDiscountAmount()
    {
        $discount_amount=0;
        if($this->getDiscount()>0){
            $discount_amount = ($this->getDiscountType()==Define::PERCENT)
                ?$this->getPrice()*$this->getDiscount()/100
                :$this->getDiscount();
        }
        return $discount_amount;
    }

    public function getVatAmount()
    {
        $vatAmount=0;//tien thue
        if($this->getVat()>0){
            $discountAmount = $this->getDiscountAmount();
            $taxableAmount=$this->getPrice()-$discountAmount;//so tien chiu thue
            $vatAmount=$taxableAmount*$this->getVat()/100;
        }

        return $vatAmount;
    }

    public function serialize()
    {
        return [
            'return_detail_id' => $this->id,
            'qty' => $this->getQty(),
            'price' => $this->getPrice(),
            'amount'=>$this->getTotalAmount(),
            'vat'=>$this->getVat(),
            'vat_amount'=>$this->getVatAmount(),
            'discount'=>$this->getDiscount(),
            'discount_type'=>$this->getDiscountType(),
            'discount_amount' => $this->getDiscountAmount(),
            'conversion_rate' => $this->getConversionRate()
        ];
    }
}