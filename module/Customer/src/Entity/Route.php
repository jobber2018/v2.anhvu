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
use Users\Entity\User;

/**
 * @orm\Entity
 * @orm\Table(name="customer_route")
 */

class Route
{

    public function __construct() {
        $this->customer = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Users\Entity\User", inversedBy="user" )
     * @orm\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @orm\OneToMany(targetEntity="Customer\Entity\Customer", mappedBy="route")
     * @orm\JoinColumn(name="id", referencedColumnName="route_id")
     * @orm\OrderBy({"id" = "ASC"})
     */
    private $customer;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="integer", name="day") */
    private $day;

    /** @orm\Column(type="string", name="polygon") */
    private $polygon;

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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getCustomer(): ArrayCollection
    {
        return $this->customer;
    }

    public function setCustomer(ArrayCollection $customer): void
    {
        $this->customer = $customer;
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
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param mixed $day
     */
    public function setDay($day): void
    {
        $this->day = $day;
    }

    /**
     * @return mixed
     */
    public function getPolygon()
    {
        return $this->polygon;
    }

    /**
     * @param mixed $polygon
     */
    public function setPolygon($polygon): void
    {
        $this->polygon = $polygon;
    }

}