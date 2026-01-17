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
 * @orm\Table(name="purchase_detail")
 */

class PurchaseDetail
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
     * @orm\ManyToOne(targetEntity="Product\Entity\Variants", inversedBy="purchaseDetail" )
     * @orm\JoinColumn(name="variants_id", referencedColumnName="id")
     */
    private $variants;

    /**
     * @orm\ManyToOne(targetEntity="Purchase\Entity\Purchase", inversedBy="purchaseDetail")
     * @orm\JoinColumn(name="purchase_id", referencedColumnName="id")
     */
    private $purchase;

    /** @orm\Column(type="integer", name="price") */
    private $price;

    /** @orm\Column(type="decimal", precision=10, scale=2, name="discount") */
    private $discount;

    /** @orm\Column (type="string", name="discount_type") */
    private $discount_type;

    /** @orm\Column(type="decimal", precision=10, scale=2, name="qty") */
    private $qty;

    /** @orm\Column(type="decimal", precision=3, scale=2, name="vat") */
    private $vat;

    /** @orm\Column (type="integer", name="conversion_rate") */
    private $conversion_rate;

    /** @orm\Column (type="string", name="unit_name") */
    private $unit_name;

    /** @orm\Column (type="string", name="note") */
    private $note;

    /** @orm\Column(type="string", name="created_by") */
    private $created_by;
    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column (type="integer", name="`sort`") */
    private $sort;

    private $_flag;

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
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @param mixed $variants
     */
    public function setVariants($variants)
    {
        $this->variants = $variants;
    }

    /**
     * @return mixed
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * @param mixed $purchase
     */
    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
    public function setDiscount($discount)
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
    public function setDiscountType($discount_type)
    {
        $this->discount_type = $discount_type;
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
    public function setQty($qty)
    {
        $this->qty = $qty;
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
    public function setConversionRate($conversion_rate)
    {
        $this->conversion_rate = $conversion_rate;
    }


    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
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
    public function getUnitName()
    {
        return $this->unit_name;
    }

    /**
     * @param mixed $unit_name
     */
    public function setUnitName($unit_name)
    {
        $this->unit_name = $unit_name;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort): void
    {
        $this->sort = $sort;
    }

    //---------------------------------------------------------------------
    //More function
    //---------------------------------------------------------------------
    public function getTotal()
    {
        return $this->getPrice()*$this->getQty();
    }

    /**
     * @return mixed
     */
    public function getFlag()
    {
        return ($this->_flag)?$this->_flag:0;
    }

    /**
     * @param mixed $flag
     */
    public function setFlag($flag): void
    {
        $this->_flag = $flag;
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

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'purchase_id' => $this->getPurchase()->getId(),
            'variant_id' => $this->getVariants()->getId(),
            'price' => $this->getPrice(),
            'qty' => $this->getQty(),
            'total_amount'=>$this->getTotal(),
            'discount' => $this->getDiscount(),
            'discount_type' => $this->getDiscountType(),
            'discount_amount' => $this->getDiscountAmount(),
            'sort'=>$this->getSort(),
            'vat' => $this->getVat(),
            'vat_amount'=>$this->getVatAmount(),
            'unit_name' => $this->getUnitName(),
            'conversion_rate' => $this->getConversionRate(),
            'note' => $this->getNote(),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate())
        ];
    }
}