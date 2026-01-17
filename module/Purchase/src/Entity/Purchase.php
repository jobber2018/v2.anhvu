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
use Sulde\Service\Common\Define;
use Sulde\Service\HasPublicId;

/**
 * @orm\Entity
 * @orm\Table(name="purchase")
 * @orm\HasLifecycleCallbacks
 */

class Purchase
{
    use HasPublicId;

    public function __construct() {
        $this->purchaseDetail = new ArrayCollection();
        $this->invoice = new ArrayCollection();
        $this->message = new ArrayCollection();
        $this->additional_fees = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Supplier\Entity\Supplier", inversedBy="purchase" )
     * @orm\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;

    /**
     * @orm\OneToMany(targetEntity="Purchase\Entity\PurchaseDetail", mappedBy="purchase", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="purchase_id")
     * @orm\OrderBy({"sort" = "ASC"})
     */
    protected $purchaseDetail;

    /**
     * @orm\OneToMany(targetEntity="Purchase\Entity\PurchaseReturn", mappedBy="purchase", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="purchase_id")
     */
    protected $purchaseReturn;

    /**
     * @orm\OneToMany(targetEntity="Purchase\Entity\PurchaseInvoice", mappedBy="purchase", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="purchase_id")
     */
    protected $invoice;

    /**
     * @orm\OneToMany(targetEntity="Purchase\Entity\PurchaseMessage", mappedBy="purchase", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="purchase_id")
     */
    protected $message;

    /**
     * @orm\OneToMany(targetEntity="Purchase\Entity\PurchaseAdditionalFees", mappedBy="purchase", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="purchase_id")
     */
    protected $additional_fees;

    /** @orm\Column(type="decimal", precision=10, scale=2, name="discount") */
    private $discount;

    /** @orm\Column (type="string", name="discount_type") */
    private $discount_type;

    /** @orm\Column(type="string", name="status") */
    private $status;

    /** @orm\Column(type="string", name="created_by") */
    private $created_by;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column(type="string", name="approved_by") */
    private $approved_by;

    /** @orm\Column(type="datetime", name="approved_date") */
    private $approved_date;

    /** @orm\Column(type="string", name="updated_by") */
    private $updated_by;
    /** @orm\Column(type="datetime", name="updated_date") */
    private $updated_date;

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

    public function getPublicId(): ?string
    {
        return $this->public_id;
    }

    public function setPublicId(?string $publicId): void
    {
        $this->public_id = $publicId;
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
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return mixed
     */
    public function getPurchaseDetail()
    {
        return $this->purchaseDetail;
    }

    /**
     * @param mixed $purchaseDetail
     */
    public function setPurchaseDetail($purchaseDetail)
    {
        $this->purchaseDetail = $purchaseDetail;
    }

    /**
     * @return ArrayCollection
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return ArrayCollection
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

    public function getAdditionalFees()
    {
        return $this->additional_fees;
    }

    public function setAdditionalFees(ArrayCollection $additional_fees)
    {
        $this->additional_fees = $additional_fees;
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
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param mixed $discount
     */
    public function setDiscount($discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return mixed
     */
    public function getDiscountType()
    {
        return $this->discount_type;
    }

    /**
     * @param mixed $discount_type
     */
    public function setDiscountType($discount_type): void
    {
        $this->discount_type = $discount_type;
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
    public function setApprovedBy($approved_by)
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
    public function setApprovedDate($approved_date)
    {
        $this->approved_date = $approved_date;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updated_by;
    }

    /**
     * @param mixed $updated_by
     */
    public function setUpdatedBy($updated_by)
    {
        $this->updated_by = $updated_by;
    }

    /**
     * @return mixed
     */
    public function getUpdatedDate()
    {
        return $this->updated_date;
    }

    /**
     * @param mixed $updated_date
     */
    public function setUpdatedDate($updated_date)
    {
        $this->updated_date = $updated_date;
    }

    /**
     * @return mixed
     */
    public function getPurchaseReturn()
    {
        return $this->purchaseReturn;
    }

    /**
     * @param mixed $purchaseReturn
     */
    public function setPurchaseReturn($purchaseReturn): void
    {
        $this->purchaseReturn = $purchaseReturn;
    }

    //--------------------------------------------------
    //          More function
    //--------------------------------------------------
    public function addPurchaseDetail(PurchaseDetail $purchaseDetail)
    {
        if (!$this->purchaseDetail->contains($purchaseDetail)) {
            $this->purchaseDetail->add($purchaseDetail);
//            $purchaseDetail->addPurchase($this);
        }
        return $this;
    }
    public function removePurchaseDetail(PurchaseDetail $purchaseDetail)
    {
        if ($this->purchaseDetail->contains($purchaseDetail)) {
            $this->purchaseDetail->removeElement($purchaseDetail);
        }
        return $this;
    }
    public function removeAllPurchaseDetail()
    {
        foreach ($this->purchaseDetail as $purchaseDetail) {
            $this->purchaseDetail->removeElement($purchaseDetail);
        }
        return $this;
    }

    public function addMessage(PurchaseMessage $message)
    {
        if (!$this->message->contains($message)) {
            $this->message->add($message);
        }
        return $this;
    }
    public function removeMessage(PurchaseMessage $message)
    {
        if ($this->message->contains($message)) {
            $this->message->removeElement($message);
        }
        return $this;
    }

    public function addAdditionalFees(PurchaseAdditionalFees $additional_fees)
    {
        if (!$this->additional_fees->contains($additional_fees)) {
            $this->additional_fees->add($additional_fees);
        }
        return $this;
    }

    public function addInvoice(PurchaseInvoice $invoice)
    {
        if (!$this->invoice->contains($invoice)) {
            $this->invoice->add($invoice);
        }
        return $this;
    }
    public function removeInvoice(PurchaseInvoice $invoice)
    {
        if ($this->invoice->contains($invoice)) {
            $this->invoice->removeElement($invoice);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderCode()
    {
        return 'PO'.$this->getId();
    }

    /**
     * tong don hang chua cong vat vaff tru discount
     * @return int
     */
    public function getTotalOrderAmount()
    {
        $total=0;
        foreach ($this->getPurchaseDetail() as $purchaseDetail) {
            $total += $purchaseDetail->getTotal();
        }
        return $total;
    }

    /**
     * tinh tong so tien cac hang muc trong don hang
     * @return array
     */
    public function getAmountInfo()
    {
        $totalProductAmount=0;//thành tiền của sản phẩm = qty*price
        $totalProductDiscountAmount=0;
        $totalProductVatAmount=0;
//        $productPayable=0;
        foreach ($this->getPurchaseDetail() as $purchaseDetail) {
            $qty=$purchaseDetail->getQty();
            $totalProductAmount += $purchaseDetail->getPrice()*$qty;
            $totalProductDiscountAmount += $purchaseDetail->getDiscountAmount()*$qty;
            $totalProductVatAmount += $purchaseDetail->getVatAmount()*$qty;
        }
        $productPayable=$totalProductAmount-$totalProductDiscountAmount+$totalProductVatAmount;

        $purchaseDiscountAmount=0;
        //tong tien san pham phai tra (= tong tien hang - tong tien discount cua san pham)
        $total_product_amount_after_discount=$totalProductAmount-$totalProductDiscountAmount;
        if($this->getDiscount()>0){
            if($this->getDiscountType()==Define::PERCENT){
                $purchaseDiscountAmount=$total_product_amount_after_discount*$this->getDiscount()/100;
            }else{
                $purchaseDiscountAmount=$this->getDiscount();
            }
        }

        $totalAdditionalFeesAmount=$this->getTotalAdditionalFeesAmount();

        $purchasePayable=$productPayable-$purchaseDiscountAmount+$totalAdditionalFeesAmount;

        return array(
            'total_product_amount'=>$totalProductAmount,
            'total_product_discount_amount'=>$totalProductDiscountAmount,
            'total_product_vat_amount'=>$totalProductVatAmount,
            'total_product_payable'=>$productPayable,
            'purchase_discount_amount'=>$purchaseDiscountAmount,
            'total_additional_fees_amount'=>$totalAdditionalFeesAmount,
            'purchase_payable'=>$purchasePayable
        );
    }

    /**
     * Kiểm tra product có trong đơn hàng không? nếu có trả về PurchaseDetail đó, nêu không return null
     * @param Variants $variants
     * @return PurchaseDetail
     */
    public function checkVariantsInPurchase(Variants $variants)
    {
        foreach ($this->getPurchaseDetail() as $purchaseDetail) {
            if ($purchaseDetail->getVariants()->getId() == $variants->getId()) {
                return $purchaseDetail;
            }
        }
        return null;
    }

    /**
     * Kiểm tra additionalFees có trong đơn hàng không? nếu có trả về additionalFees đó, nêu không return null
     * @param $additionalFeesId
     * @return PurchaseAdditionalFees
     */
    public function additionalFeesIsExitst($additionalFeesId)
    {
        foreach ($this->getAdditionalFees() as $additionalFees) {
            if ($additionalFees->getId() == $additionalFeesId) {
                return $additionalFees;
            }
        }
        return null;
    }

    public function getQuantityOfProduct()
    {
        return count($this->getPurchaseDetail());
    }

    /**
     * tinh tien giam gia don hang
     * nếu có giảm giá thì số liệu được tính trên tổng số tiền sản phẩm sau khi đã giảm giá
     * 1. Tính tổng tiền được giảm giá của sản phẩm (nếu có)
     * 2. tính số tiền phải thanh toán của sản phẩm (tiền sau khi tru giảm giá của sản phẩm)
     * 3. tính tiền giảm giá của cả đơn hàng (=tổng tiền phải thanh toán của sản phẩm - số tiền giảm giá của đơn hàng)
     * @return float|int|mixed
     */
    public function getTotalDiscountAmount()
    {
        $order_amount_discount=0;

        //tong tien discount cua san pham
        $total_product_amount_discount=0; //tong tien chiet khau cua san pham trong don
        $total_product_amount=0; //tong tien hang trong don
        $purchaseDetails = $this->getPurchaseDetail();
        foreach ($purchaseDetails as $purchaseDetail) {
            $total_product_amount_discount += $purchaseDetail->getDiscountAmount()*$purchaseDetail->getQty();
            $total_product_amount += $purchaseDetail->getTotal();
        }

        if($this->getDiscount()>0){
            if($this->getDiscountType()==Define::PERCENT){
                //tong tien san pham phai tra (= tong tien hang - tong tien discount cua san pham)
                $total_product_amount_after_discount=$total_product_amount-$total_product_amount_discount;
                $order_amount_discount=$total_product_amount_after_discount*$this->getDiscount()/100;
            }else{
                $order_amount_discount=$this->getDiscount();
            }
        }

        return array(
            'order_discount_amount'=>$order_amount_discount,
            'product_discount_amount'=>$total_product_amount_discount,
            'total'=>$order_amount_discount+$total_product_amount_discount
        );
    }

    /**
     * get tổng tiền thuế phải trả cho đơn hàng
     * @return int
     */
    public function getTotalVatAmount()
    {
        $total_vat_amount=0;
        $purchaseDetails = $this->getPurchaseDetail();
        foreach ($purchaseDetails as $purchaseDetail) {
            $total_vat_amount += $purchaseDetail->getVatAmount()*$purchaseDetail->getQty();
        }
        return $total_vat_amount;
    }

    public function getTotalAdditionalFeesAmount()
    {
        $total_additional_fees_amount=0;
        $additional_fees = $this->getAdditionalFees();
        foreach ($additional_fees as $additionalFee) {
            $total_additional_fees_amount += $additionalFee->getAmount();
        }
        return $total_additional_fees_amount;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'id' => $this->getId(),
            'public_id' => $this->getPublicId(),
            'supplier' => array('id'=>$this->getSupplier()->getId(),'name'=>$this->getSupplier()->getName()),
            'quantity_of_product'=>$this->getQuantityOfProduct(),
            'status' => $this->getStatus(),
            'discount' => $this->getDiscount(),
            'discount_type' => $this->getDiscountType(),
            'discount_amount' => $this->getTotalDiscountAmount(),//tong tien giam gia cua don
            'total_order_amount' => $this->getTotalOrderAmount(),//tong don hang chua cong vat va tru discount
            'total_vat_amount' => $this->getTotalVatAmount(),
            'created_by' => $this->getCreatedBy(),
            'created_date' =>Common::formatDateTime($this->getCreatedDate()),
            'approved_by' => $this->getApprovedBy(),
            'approved_date' =>Common::formatDateTime($this->getApprovedDate()),
            'updated_by' => $this->getUpdatedBy(),
            'updated_date' =>Common::formatDateTime($this->getUpdatedDate()),
            'total_additional_fees_amount'=>$this->getTotalAdditionalFeesAmount()
        ];
    }

}