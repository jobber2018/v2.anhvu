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
 * @orm\Table(name="supplier_payment_file")
 */

class SupplierPaymentFile
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
     * @orm\ManyToOne(targetEntity="Supplier\Entity\SupplierPayment", inversedBy="files" )
     * @orm\JoinColumn(name="supplier_payment_id", referencedColumnName="id")
     */
    private $supplierPayment;

    /** @orm\Column(type="string", name="`path`") */
    private $path;

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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
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
     * @return mixed
     */
    public function getSupplierPayment()
    {
        return $this->supplierPayment;
    }

    /**
     * @param mixed $supplierPayment
     */
    public function setSupplierPayment($supplierPayment): void
    {
        $this->supplierPayment = $supplierPayment;
    }

}