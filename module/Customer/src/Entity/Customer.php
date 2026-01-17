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
use Sulde\Service\Common\Common;
use Sulde\Service\HasPublicId;


/**
 * @orm\Entity
 * @orm\Table(name="customer")
 * @orm\HasLifecycleCallbacks
 */

class Customer
{
    use HasPublicId;
    public function __construct() {
        $this->address = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Customer\Entity\Group", inversedBy="customer" )
     * @orm\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @orm\ManyToOne(targetEntity="Customer\Entity\Route", inversedBy="customer" )
     * @orm\JoinColumn(name="route_id", referencedColumnName="id")
     */
    private $route;

    /**
     * @orm\OneToMany(targetEntity="Customer\Entity\Address", mappedBy="customer", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="customer_id")
     */
    private $address;

    /**
     * @orm\OneToMany(targetEntity="Customer\Entity\ZaloApp", mappedBy="customer", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="customer_id")
     */
    private $zalo_app;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="name_alias") */
    private $name_alias;

    /** @orm\Column(type="string", name="owner_name") */
    private $ownerName;

    /** @orm\Column(type="string", name="mobile") */
    private $mobile;

    /** @orm\Column(type="integer", name="status") */
    private $status;

    /** @orm\Column(type="string", name="image") */
    private $image;

    /** @orm\Column(type="string", name="delivery_note") */
    private $delivery_note;

    /** @orm\Column(type="string", name="note") */
    private $note;

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
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route): void
    {
        $this->route = $route;
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
    public function getNameAlias()
    {
        return $this->name_alias;
    }

    /**
     * @param mixed $name_alias
     */
    public function setNameAlias($name_alias): void
    {
        $this->name_alias = $name_alias;
    }

    /**
     * @return mixed
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * @param mixed $ownerName
     */
    public function setOwnerName($ownerName): void
    {
        $this->ownerName = $ownerName;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile): void
    {
        $this->mobile = $mobile;
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
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getDeliveryNote()
    {
        return $this->delivery_note;
    }

    /**
     * @param mixed $delivery_note
     */
    public function setDeliveryNote($delivery_note): void
    {
        $this->delivery_note = $delivery_note;
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
    public function setNote($note): void
    {
        $this->note = $note;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group): void
    {
        $this->group = $group;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress(ArrayCollection $address): void
    {
        $this->address = $address;
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

    /**
     * @return ZaloApp
     */
    public function getZaloApp()
    {
        return $this->zalo_app;
    }

    /**
     * @param mixed $zalo_app
     */
    public function setZaloApp($zalo_app): void
    {
        $this->zalo_app = $zalo_app;
    }

    //--------------------------------------------
    //              More function
    //--------------------------------------------
    public function addAddress(Address $address)
    {
        if (!$this->address->contains($address)) {
            $this->address->add($address);
        }
        return $this;
    }

    public function serialize() {
        $address = $this->getAddress();
        $addressTmp=array();
        foreach ($address as $addressItem)
            $addressTmp[] = $addressItem->serialize();

        return [
            'id' => $this->getId(),
            'public_id' => $this->getPublicId(),
            'group_id' => $this->getGroup()->getId(),
            'route_id' => $this->getRoute()->getId(),
            'name' => $this->getName(),
            'name_alias' => $this->getNameAlias(),
            'owner_name' => $this->getOwnerName(),
            'mobile' => $this->getMobile(),
            'status' => $this->getStatus(),
            'image' => $this->getImage(),
            'delivery_note' => $this->getDeliveryNote(),
            'note' => $this->getNote(),
            'created_by' => $this->getCreatedBy(),
            'created_date' => Common::formatDateTime($this->getCreatedDate()),
            'modified_by' => $this->getModifiedBy(),
            'modified_date' => Common::formatDateTime($this->getModifiedDate()),
            'address' => $addressTmp
        ];
    }
}