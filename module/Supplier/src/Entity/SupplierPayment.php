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
use Sulde\Service\HasPublicId;

/**
 * @orm\Entity
 * @orm\Table(name="supplier_payment")
 * @orm\HasLifecycleCallbacks
 */

class SupplierPayment
{
    use HasPublicId;
    public function __construct() {
        $this->files = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Supplier\Entity\Supplier", inversedBy="supplierPayment" )
     * @orm\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;

    /**
     * @orm\OneToMany(targetEntity="Supplier\Entity\SupplierPaymentFile", mappedBy="supplierPayment", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="supplier_payment_id")
     */
    private $files;

    /** @orm\Column(type="integer", name="amount") */
    private $amount;

    /** @orm\Column(type="string", name="method") */
    private $method;

    /** @orm\Column(type="date", name="date") */
    private $date;

    /** @orm\Column(type="string", name="note") */
    private $note;

    /** @orm\Column(type="string", name="`status`") */
    private $status;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column(type="string", name="created_by") */
    private $created_by;

    /** @orm\Column(type="datetime", name="approval_date") */
    private $approval_date;

    /** @orm\Column(type="string", name="approval_by") */
    private $approval_by;

    /** @orm\Column(type="datetime", name="confirm_date") */
    private $confirm_date;

    /** @orm\Column(type="string", name="confirm_by") */
    private $confirm_by;

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
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
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

    public function getFiles()
    {
        return $this->files;
    }

    public function setFiles($files): void
    {
        $this->files = $files;
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
    public function getApprovalDate()
    {
        return $this->approval_date;
    }

    /**
     * @param mixed $approval_date
     */
    public function setApprovalDate($approval_date): void
    {
        $this->approval_date = $approval_date;
    }

    /**
     * @return mixed
     */
    public function getApprovalBy()
    {
        return $this->approval_by;
    }

    /**
     * @param mixed $approval_by
     */
    public function setApprovalBy($approval_by): void
    {
        $this->approval_by = $approval_by;
    }

    /**
     * @return mixed
     */
    public function getConfirmDate()
    {
        return $this->confirm_date;
    }

    /**
     * @param mixed $confirm_date
     */
    public function setConfirmDate($confirm_date): void
    {
        $this->confirm_date = $confirm_date;
    }

    /**
     * @return mixed
     */
    public function getConfirmBy()
    {
        return $this->confirm_by;
    }

    /**
     * @param mixed $confirm_by
     */
    public function setConfirmBy($confirm_by): void
    {
        $this->confirm_by = $confirm_by;
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
            'public_id' => $this->getPublicId(),
            'status' => $this->getStatus(),
            'amount' => $this->getAmount(),
            'method' => $this->getMethod(),
            'note' => $this->getNote(),
            'date' =>Common::formatDate($this->getDate()),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate()),
            'approval_by' => $this->getApprovalBy(),
            'approval_date' =>Common::formatDateTime($this->getApprovalDate()),
            'confirm_by' => $this->getConfirmBy(),
            'confirm_date' =>Common::formatDateTime($this->getConfirmDate()),
            'supplier' => $this->getSupplier()->serialize()
        ];
    }

    public function addFiles(SupplierPaymentFile $file)
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
        }
        return $this;
    }
}