<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Supplier\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use Sulde\Service\Common\Common;

/**
 * @orm\Entity
 * @orm\Table(name="supplier_debt_ledger")
 */

class SupplierDebtLedger
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
     * @orm\ManyToOne(targetEntity="Supplier\Entity\Supplier", inversedBy="debtLadger" )
     * @orm\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;

    /** @orm\Column(type="string", name="reference_type") */
    private $reference_type;

    /** @orm\Column(type="string", name="reference_id") */
    private $reference_id;

    /** @orm\Column(type="string", name="direction") */
    private $direction;

    /** @orm\Column(type="date", name="apply_date") */
    private $apply_date;

    /** @orm\Column(type="integer", name="amount") */
    private $amount;

    /** @orm\Column(type="string", name="note") */
    private $note;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column(type="string", name="created_by") */
    private $created_by;

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
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param mixed $supplier
     */
    public function setSupplier($supplier): void
    {
        $this->supplier = $supplier;
    }

    /**
     * @return mixed
     */
    public function getReferenceType()
    {
        return $this->reference_type;
    }

    /**
     * @param mixed $reference_type
     */
    public function setReferenceType($reference_type): void
    {
        $this->reference_type = $reference_type;
    }

    /**
     * @return mixed
     */
    public function getReferenceId()
    {
        return $this->reference_id;
    }

    /**
     * @param mixed $reference_id
     */
    public function setReferenceId($reference_id): void
    {
        $this->reference_id = $reference_id;
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param mixed $direction
     */
    public function setDirection($direction): void
    {
        $this->direction = $direction;
    }

    /**
     * @return mixed
     */
    public function getApplyDate()
    {
        return $this->apply_date;
    }

    /**
     * @param mixed $apply_date
     */
    public function setApplyDate($apply_date): void
    {
        $this->apply_date = $apply_date;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
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
     * --------------------More function ------------------------------
     */

    /**
     * @return array
     */
    public function serialize(){
        return [
            'id' => $this->getId(),
            'reference_type' => $this->getReferenceType(),
            'reference_id' => $this->getReferenceId(),
            'direction' => $this->getDirection(),
            'amount' => $this->getAmount(),
            'note' => $this->getNote(),
            'apply_date' =>Common::formatDateTime($this->getApplyDate()),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate())
        ];
    }
}