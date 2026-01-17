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

/**
 * @orm\Entity
 * @orm\Table(name="purchase_message")
 */

class PurchaseMessage
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
     * MANY-TO-ONE BIDIRECTIONAL, OWNING SIDE
     * @orm\ManyToOne(targetEntity="Purchase\Entity\Purchase", inversedBy="message")
     * @orm\JoinColumn(name="purchase_id", referencedColumnName="id")
     */
    private $purchase;

    /** @orm\Column(type="string", name="message") */
    private $message;

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
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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

    //---------------------------------------------------------------------
    //More function
    //---------------------------------------------------------------------
    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'message' => $this->getMessage(),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate()),
            'created_time_ago' =>Common::getTimeAgo($this->getCreatedDate()),
        ];
    }
}