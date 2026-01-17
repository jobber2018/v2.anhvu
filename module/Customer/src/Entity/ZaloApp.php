<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Customer\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="zalo_app")
 */

class ZaloApp
{
    public function __construct() {
//        $this->zaloAddress = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Customer\Entity\Customer", inversedBy="zalo_app" )
     * @orm\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /** @orm\Column(type="integer", name="zalo_id") */
    private $zalo_id;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="phone") */
    private $phone;

    /** @orm\Column(type="string", name="avatar") */
    private $avatar;

    /** @orm\Column(type="string", name="source") */
    private $source;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column(type="datetime", name="access_date") */
    private $access_date;

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
    public function getZaloId()
    {
        return $this->zalo_id;
    }

    /**
     * @param mixed $zalo_id
     */
    public function setZaloId($zalo_id): void
    {
        $this->zalo_id = $zalo_id;
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
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source): void
    {
        $this->source = $source;
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
    public function getAccessDate()
    {
        return $this->access_date;
    }

    /**
     * @param mixed $access_date
     */
    public function setAccessDate($access_date): void
    {
        $this->access_date = $access_date;
    }

}