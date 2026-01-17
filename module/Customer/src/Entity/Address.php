<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Customer\Entity;

use Sulde\Service\Common\Common;
use Doctrine\ORM\Mapping as orm;
/**
 * @orm\Entity
 * @orm\Table(name="customer_address")
 */

class Address
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
     * @orm\ManyToOne(targetEntity="Customer\Entity\Customer", inversedBy="address")
     * @orm\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /** @orm\Column(type="string", name="address") */
    private $address;
    /** @orm\Column(type="integer", name="is_default") */
    private $is_default;

    /** @orm\Column(type="string", name="lat") */
    private $lat;

    /** @orm\Column(type="string", name="lng") */
    private $lng;

    /** @orm\Column(type="string", name="created_by") */
    private $created_by;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column(type="string", name="modified_by") */
    private $modified_by;

    /** @orm\Column(type="datetime", name="modified_date") */
    private $modified_date;

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
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * @param mixed $is_default
     */
    public function setIsDefault($is_default): void
    {
        $this->is_default = $is_default;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param mixed $lat
     */
    public function setLat($lat): void
    {
        $this->lat = $lat;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param mixed $lng
     */
    public function setLng($lng): void
    {
        $this->lng = $lng;
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

    /**
     * @return mixed
     */
    public function getModifiedBy()
    {
        return $this->modified_by;
    }

    /**
     * @param mixed $modified_by
     */
    public function setModifiedBy($modified_by): void
    {
        $this->modified_by = $modified_by;
    }

    /**
     * @return mixed
     */
    public function getModifiedDate()
    {
        return $this->modified_date;
    }

    /**
     * @param mixed $modified_date
     */
    public function setModifiedDate($modified_date): void
    {
        $this->modified_date = $modified_date;
    }

    //--------------------------------------------
    //              More function
    //--------------------------------------------
    public function serialize() {
        return [
            'id' => $this->getId(),
            'address' => $this->getAddress(),
            'is_default' => $this->getIsDefault(),
            'lat' => $this->getLat(),
            'lng' => $this->getLng(),
            'created_by' => $this->getCreatedBy(),
            'created_date' => Common::formatDateTime($this->getCreatedDate()),
            'modified_by' => $this->getModifiedBy(),
            'modified_date' => Common::formatDateTime($this->getModifiedDate())
        ];
    }
}