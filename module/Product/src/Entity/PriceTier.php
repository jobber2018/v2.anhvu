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
 * @orm\Table(name="product_price_tier")
 */

class PriceTier
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
     * @orm\ManyToOne(targetEntity="Product\Entity\Variants", inversedBy="priceTier")
     * @orm\JoinColumn(name="variant_id", referencedColumnName="id")
     */
    private $variant;

    /** @orm\Column(type="integer", name="min_qty") */
    private $min_qty;

    /** @orm\Column(type="integer", name="max_qty") */
    private $max_qty;

    /** @orm\Column(type="decimal", precision=10, scale=2, name="price") */
    private $price;

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
    public function setId($id): void
    {
        $this->id = $id;
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
    public function getMinQty()
    {
        return $this->min_qty;
    }

    /**
     * @param mixed $min_qty
     */
    public function setMinQty($min_qty): void
    {
        $this->min_qty = $min_qty;
    }

    /**
     * @return mixed
     */
    public function getMaxQty()
    {
        return $this->max_qty;
    }

    /**
     * @param mixed $max_qty
     */
    public function setMaxQty($max_qty): void
    {
        $this->max_qty = $max_qty;
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
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param mixed $created_by
     */
    public function setCreatedBy($created_by): void
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
    public function setCreatedDate($created_date): void
    {
        $this->created_date = $created_date;
    }



    //--------------------------------------------------
    //          More function
    //--------------------------------------------------

    public function serialize() {
        return [
            'id' => $this->id,
            'min_qty'=>$this->getMinQty(),
            'max_qty'=>$this->getMaxQty(),
            'price'=>$this->getPrice(),
            'created_by'=>$this->getCreatedBy(),
            'created_date'=>Common::formatDateTime($this->getCreatedDate()),
        ];
    }
}