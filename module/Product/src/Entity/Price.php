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
 * @orm\Table(name="product_price")
 */

class Price
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
     * @orm\ManyToOne(targetEntity="Product\Entity\Variants", inversedBy="price")
     * @orm\JoinColumn(name="variants_id", referencedColumnName="id")
     */
    private $variants;

    /** @orm\Column(type="decimal", precision=10, scale=2, name="retail_price") */
    private $retail_price;

    /** @orm\Column(type="decimal", precision=10, scale=2, name="wholesale_price") */
    private $wholesale_price;

    /** @orm\Column(type="integer", name="active") */
    private $active;

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
    public function getRetailPrice()
    {
        return Common::formatNumber($this->retail_price);
    }

    /**
     * @param mixed $price
     */
    public function setRetailPrice($price)
    {
        $this->retail_price = $price;
    }

    /**
     * @return mixed
     */
    public function getWholesalePrice()
    {
        return Common::formatNumber($this->wholesale_price);
    }

    /**
     * @param mixed $wholesale_price
     */
    public function setWholesalePrice($wholesale_price): void
    {
        $this->wholesale_price = $wholesale_price;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
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


    //--------------------------------------------------
    //          More function
    //--------------------------------------------------

    public function serialize() {
        return [
            'id' => $this->getId(),
            'retail' => $this->getRetailPrice(),
            'wholesale' => $this->getWholesalePrice(),
            'status' => $this->getActive(),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate())
        ];
    }
}