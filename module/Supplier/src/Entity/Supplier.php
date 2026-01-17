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
use Sulde\Service\Common\Define;
use Sulde\Service\HasPublicId;

/**
 * @orm\Entity
 * @orm\Table(name="supplier")
 * @orm\HasLifecycleCallbacks
 */

class Supplier
{

    use HasPublicId;
    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\OneToMany(targetEntity="Supplier\Entity\SupplierDebtLedger", mappedBy="supplier")
     * @orm\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $debtLedger;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="short_name") */
    private $short_name;

    /** @orm\Column(type="string", name="mobile") */
    private $mobile;
    /** @orm\Column(type="string", name="email") */
    private $email;

    /** @orm\Column(type="string", name="tax_code") */
    private $tax_code;

    /** @orm\Column(type="string", name="notes") */
    private $notes;

    /** @orm\Column(type="string", name="contact_person") */
    private $contact_person;

    /** @orm\Column(type="string", name="address") */
    private $address;

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
    public function setId($id)
    {
        $this->id = $id;
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
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->short_name;
    }

    /**
     * @param mixed $short_name
     */
    public function setShortName($short_name): void
    {
        $this->short_name = $short_name;
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
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getTaxCode()
    {
        return $this->tax_code;
    }

    /**
     * @param mixed $tax_code
     */
    public function setTaxCode($tax_code): void
    {
        $this->tax_code = $tax_code;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return mixed
     */
    public function getContactPerson()
    {
        return $this->contact_person;
    }

    /**
     * @param mixed $contact_person
     */
    public function setContactPerson($contact_person): void
    {
        $this->contact_person = $contact_person;
    }

    /**
     * @return mixed
     */
    public function getDebtLedger()
    {
        return $this->debtLedger;
    }

    /**
     * @param mixed $debtLedger
     */
    public function setDebtLedger($debtLedger): void
    {
        $this->debtLedger = $debtLedger;
    }

    /**
     * --------------------More function -----------------------------
     */

    /**
     * ting tin cong no ncc
     * @return array
     */
    public function getDebtLedgerInfo()
    {
        $purchaseTotal=0;
        $purchaseReturnTotal=0;
        $purchasePaymentsTotal=0;
        foreach ($this->getDebtLedger() as $debtLadger) {
            if($debtLadger->getReferenceType()==Define::PURCHASE_CODE) {
                $purchaseTotal += $debtLadger->getAmount();
            }else if($debtLadger->getReferenceType()==Define::PURCHASE_RETURN_CODE){
                $purchaseReturnTotal+=$debtLadger->getAmount();
            }else if($debtLadger->getReferenceType()==Define::PAYMENTS_CODE){
                $purchasePaymentsTotal+=$debtLadger->getAmount();
            }
        }
        return array(
            'purchase_total'=>$purchaseTotal,
            'purchase_return_total'=>$purchaseReturnTotal,
            'purchase_payments_total'=>$purchasePaymentsTotal,
            'account_payable'=>$purchaseTotal+$purchaseReturnTotal+$purchasePaymentsTotal //>0 cong nơ phải tra, <0 cong no phai thu
        );
    }

    public function getNameAlias(){
        if($this->getShortName()) return $this->getShortName();
        return $this->getName();
    }
    /**
     * @return array
     */
    public function serialize(){
        return [
            'id' => $this->getId(),
            'public_id' => $this->getPublicId(),
            'name' => $this->getName(),
            'short_name' => $this->getShortName(),
            'email' => $this->getEmail(),
            'tax_code' => $this->getTaxCode(),
            'notes' => $this->getNotes(),
            'contact_person' => $this->getContactPerson(),
            'address' => $this->getAddress(),
            'mobile' => $this->getMobile(),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate()),
            'debt_ledger_info'=>$this->getDebtLedgerInfo()
        ];
    }
}