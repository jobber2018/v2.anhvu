<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2024-07-21
 * Time: 23:49
 */

namespace Purchase\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use Product\Entity\Product;
use Product\Entity\Variants;
use Ramsey\Uuid\Uuid;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\HasPublicId;

/**
 * @orm\Entity
 * @orm\Table(name="purchase_return")
 * @ORM\HasLifecycleCallbacks
 */

class PurchaseReturn
{
    use HasPublicId;

    public function __construct() {
        $this->purchaseReturnDetail = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Purchase\Entity\Purchase", inversedBy="purchaseReturn" )
     * @orm\JoinColumn(name="purchase_id", referencedColumnName="id")
     */
    private $purchase;

    /**
     * @orm\OneToMany(targetEntity="Purchase\Entity\PurchaseReturnDetail", mappedBy="purchaseReturn", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="purchase_return_id")
     */
    protected $purchaseReturnDetail;

    /** @orm\Column(type="string", name="status") */
    private $status;

    /** @orm\Column(type="string", name="note") */
    private $note;

    /** @orm\Column(type="string", name="created_by") */
    private $created_by;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column(type="string", name="approved_by") */
    private $approved_by;

    /** @orm\Column(type="datetime", name="approved_date") */
    private $approved_date;

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
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * @param mixed $purchase
     */
    public function setPurchase($purchase): void
    {
        $this->purchase = $purchase;
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
    public function getApprovedBy()
    {
        return $this->approved_by;
    }

    /**
     * @param mixed $approved_by
     */
    public function setApprovedBy($approved_by): void
    {
        $this->approved_by = $approved_by;
    }

    /**
     * @return mixed
     */
    public function getApprovedDate()
    {
        return $this->approved_date;
    }

    /**
     * @param mixed $approved_date
     */
    public function setApprovedDate($approved_date): void
    {
        $this->approved_date = $approved_date;
    }

    public function getPurchaseReturnDetail()
    {
        return $this->purchaseReturnDetail;
    }

    public function setPurchaseReturnDetail(ArrayCollection $purchaseReturnDetail): void
    {
        $this->purchaseReturnDetail = $purchaseReturnDetail;
    }

    /**
     * --------------------------------------------------------------------------
     * More function
     * --------------------------------------------------------------------------
     */

    public function addPurchaseReturnDetail(PurchaseReturnDetail $purchaseReturnDetail)
    {
        if (!$this->purchaseReturnDetail->contains($purchaseReturnDetail)) {
            $this->purchaseReturnDetail->add($purchaseReturnDetail);
        }
        return $this;
    }

    public function serialize()
    {
        return [
            'purchase_return_id' => $this->getId(),
            'public_id' => $this->getPublicId(),
            'status' => $this->getStatus(),
            'note' => $this->getNote(),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate()),
            'approved_by' => $this->getApprovedBy(),
            'approved_date' =>Common::formatDateTime($this->getApprovedDate()),
            'amount_info'=>$this->getAmountInfo(),
            'total_amount_return'=>$this->getTotalAmountReturn(),
            'supplier' => array('id'=>$this->getPurchase()->getSupplier()->getId(),'name'=>$this->getPurchase()->getSupplier()->getName())
        ];
    }

    public function getCode()
    {
        return 'RPO'.$this->getId();
    }

    /**
     *  tinh tong so tien cac hang muc trong don hang tra lai
     * @return float[]|int[]
     */
    public function getAmountInfo(): array
    {
        $totalProductAmount=0;//thành tiền của sản phẩm = qty*price
        $totalProductDiscountAmount=0;
        $totalProductVatAmount=0;
//        $productPayable=0;
        foreach ($this->getPurchaseReturnDetail() as $purchaseReturnDetail) {
            $qty=$purchaseReturnDetail->getQty();
            $totalProductAmount += $purchaseReturnDetail->getPrice()*$qty;
            $totalProductDiscountAmount += $purchaseReturnDetail->getDiscountAmount()*$qty;
            $totalProductVatAmount += $purchaseReturnDetail->getVatAmount()*$qty;
        }
        $productPayable=$totalProductAmount-$totalProductDiscountAmount+$totalProductVatAmount;
        return array(
            'total_product_amount'=>$totalProductAmount,
            'total_product_discount_amount'=>$totalProductDiscountAmount,
            'total_product_vat_amount'=>$totalProductVatAmount,
            'total_product_payable'=>$productPayable
        );
    }
}