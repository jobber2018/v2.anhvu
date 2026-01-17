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
 * @orm\Table(name="product_price_schedule")
 */

class PriceSchedule
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
     * @orm\ManyToOne(targetEntity="Product\Entity\Variants", inversedBy="priceSchedules")
     * @orm\JoinColumn(name="variant_id", referencedColumnName="id")
     */
    private $variant;

    /** @orm\Column(type="decimal", precision=10, scale=2, name="special_price") */
    private $special_price;

    /** @orm\Column(type="datetime", name="start_date") */
    private $start_date;

    /** @orm\Column(type="datetime", name="end_date") */
    private $end_date;

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
    public function getSpecialPrice()
    {
        return Common::formatNumber($this->special_price);
    }

    /**
     * @param mixed $special_price
     */
    public function setSpecialPrice($special_price): void
    {
        $this->special_price = $special_price;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param mixed $start_date
     */
    public function setStartDate($start_date): void
    {
        $this->start_date = $start_date;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @param mixed $end_date
     */
    public function setEndDate($end_date): void
    {
        $this->end_date = $end_date;
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
            'special_price'=>$this->getSpecialPrice(),
            'start_date'=>Common::formatDateTime($this->getStartDate()),
            'end_date'=>Common::formatDateTime($this->getEndDate()),
            'created_by'=>$this->getCreatedBy(),
            'created_date'=>Common::formatDateTime($this->getCreatedDate()),
        ];
    }
}