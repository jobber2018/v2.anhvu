<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2025-10-14
 * Time: 23:49
 */

namespace Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use Ramsey\Uuid\Uuid;
use Sulde\Service\Common\Common;
use Sulde\Service\HasPublicId;

/**
 * @orm\Entity
 * @orm\Table(name="product")
 * @orm\HasLifecycleCallbacks
 */

class Product
{
    use HasPublicId;

    public function __construct() {
        $this->variants = new ArrayCollection();
        $this->image = new ArrayCollection();
        $this->history = new ArrayCollection();
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Categories", inversedBy="product" )
     * @orm\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $categories;

    /**
     * @orm\ManyToOne(targetEntity="Product\Entity\Unit", inversedBy="product" )
     * @orm\JoinColumn(name="unit_id", referencedColumnName="id")
     */
    private $unit;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\Variants", mappedBy="product", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="product_id")
     */
    private $variants;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\History", mappedBy="product", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="product_id")
     * @orm\OrderBy({"created_date" = "DESC"})
     */
    private $history;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\Image", mappedBy="product", cascade={"persist", "remove"})
     * @orm\JoinColumn(name="id", referencedColumnName="product_id")
     */
    private $image;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="sku") */
    private $sku;

    /** @orm\Column(type="integer", name="cost") */
    private $cost;

    /** @orm\Column(type="integer", name="cost_old") */
    private $cost_old;

    /** @orm\Column(type="integer", name="status") */
    private $status;

    /** @orm\Column(type="integer", name="`sort`") */
    private $sort;

    /** @orm\Column(type="integer", name="norm_min") */
    private $norm_min;

    /** @orm\Column(type="integer", name="norm_max") */
    private $norm_max;

    /** @orm\Column(type="integer", name="norm_input") */
    private $norm_input;

    /** @orm\Column(type="text", name="note") */
    private $note;

    /** @orm\Column(type="string", name="keyword") */
    private $keyword;

    /** @orm\Column(type="string", name="created_by") */
    private $created_by;

    /** @orm\Column(type="datetime", name="created_date") */
    private $created_date;

    /** @orm\Column(type="string", name="updated_by") */
    private $updated_by;

    /** @orm\Column(type="datetime", name="updated_date") */
    private $updated_date;

    /** @orm\Column(type="string", name="vat_option") */
    private $vat_option;

    /** @orm\Column(type="decimal", precision=3, scale=2, name="vat_value") */
    private $vat_value;

    /** @orm\Column(type="decimal", precision=3, scale=2, name="import_tax") */
    private $import_tax;

    /** @orm\Column(type="decimal", precision=3, scale=2, name="export_tax") */
    private $export_tax;

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
     * @return Categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }
    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
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
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
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
    public function getCost()
    {
        return $this->cost?$this->cost:0;
    }

    /**
     * @param mixed $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return mixed
     */
    public function getCostOld()
    {
        return $this->cost_old?$this->cost_old:0;
    }

    /**
     * @param mixed $cost_old
     */
    public function setCostOld($cost_old)
    {
        $this->cost_old = $cost_old;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return mixed
     */
    public function getNormMin()
    {
        return $this->norm_min;
    }

    /**
     * @param mixed $normMin
     */
    public function setNormMin($normMin)
    {
        $this->norm_min = $normMin;
    }

    /**
     * @return mixed
     */
    public function getNormMax()
    {
        return $this->norm_max;
    }

    /**
     * @param mixed $norm_max
     */
    public function setNormMax($norm_max): void
    {
        $this->norm_max = $norm_max;
    }

    /**
     * @return mixed
     */
    public function getNormInput()
    {
        return $this->norm_input;
    }

    /**
     * @param mixed $norm_input
     */
    public function setNormInput($norm_input)
    {
        $this->norm_input = $norm_input;
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
    public function setNote($note)
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
     * @return ArrayCollection
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @param mixed $variants
     */
    public function setVariants($variants)
    {
        $this->variants = $variants;
    }

    /**
     * @return mixed
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param mixed $history
     */
    public function setHistory($history)
    {
        $this->history = $history;
    }

    /**
     * @return mixed
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param mixed $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
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
    public function getVatOption()
    {
        return $this->vat_option;
    }

    /**
     * @param mixed $vat_option
     */
    public function setVatOption($vat_option): void
    {
        $this->vat_option = $vat_option;
    }

    /**
     * @return mixed
     */
    public function getVatValue()
    {
        return $this->vat_value;
    }

    /**
     * @param mixed $vat_value
     */
    public function setVatValue($vat_value): void
    {
        $this->vat_value = $vat_value;
    }

    /**
     * @return mixed
     */
    public function getImportTax()
    {
        return $this->import_tax;
    }

    /**
     * @param mixed $import_tax
     */
    public function setImportTax($import_tax): void
    {
        $this->import_tax = $import_tax;
    }

    /**
     * @return mixed
     */
    public function getExportTax()
    {
        return $this->export_tax;
    }

    /**
     * @param mixed $export_tax
     */
    public function setExportTax($export_tax): void
    {
        $this->export_tax = $export_tax;
    }

    //--------------------------------------------
    //              More function
    //--------------------------------------------
    public function addVariants(Variants $variants)
    {
        if (!$this->variants->contains($variants)) {
            $this->variants->add($variants);
        }
        return $this;
    }

    public function addHistory(History $history)
    {
        if (!$this->history->contains($history)) {
            $this->history->add($history);
        }
        return $this;
    }

    /**
     * @param Image $image
     * @return $this
     */
    public function addImage(Image $image)
    {
        foreach ($this->getImage() as $item){
            $item->setDefault(0);
        }

        if (!$this->image->contains($image)) {
            $this->image->add($image);
        }

        return $this;
    }
    /**
     * @return String
     */
    public function getDefaultImage()
    {
        foreach ($this->getImage() as $item){
            if($item->getDefault()==1 && $item->getPath() != null) {
                return $item->getPath();
            }
        }
        return '/img/no-image.jpg';
    }
    public function getDefaultImageItem()
    {
        foreach ($this->getImage() as $item){
            if($item->getDefault()==1 && $item->getPath() != null) {
                return $item;
            }
        }
        return null;
    }
    /**
     * @return mixed
     */
    public function getUnitName()
    {
        return $this->getUnit()->getName();
    }

    /**
     * @return mixed
     */
    public function getCategoriesName()
    {
        return $this->getCategories()->getName();
    }

    /**
     * @return false|string[]|null
     */
    public function getKeywordList()
    {
        if($this->getKeyword())
            return explode(",",$this->getKeyword());
        return null;
    }
    public function getInventory()
    {
        $inventory=0;
        foreach ($this->getHistory() as $history)
            $inventory+=$history->getChange();
        return $inventory;
    }

    /**
     * @return array
     */
    public function isUnlockValid()
    {
        try {
            if(count($this->getVariants())==0)
                throw new \Exception('Không thể mở khoá do sản phẩm không có thuộc tính!');
            $variantsValid=0;

            foreach ($this->getVariants() as $variants) {
                if($variants->getStatus()==1 && $variants->getActivePriceValue()>0)
                    $variantsValid=1;
            }
            if($variantsValid==0)
                throw new \Exception('Không thể mở khoá do sản phẩm không có thuộc tính nào hợp lệ!');

            $result['valid'] = 1;
        }catch (\Exception $e) {
            $result['valid'] = 0;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    public function serialize() {
        $variants = $this->getVariants();
        $variantTmp=array();
        foreach ($variants as $variant)
            $variantTmp[] = $variant->serialize();

        return [
            'id' => $this->getId(),
            'public_id' => $this->getPublicId(),
            'sku' => $this->getSku(),
            'vat_option' => $this->getVatOption(),
            'vat_value' => $this->getVatValue(),
            'import_tax' => $this->getImportTax(),
            'export_tax' => $this->getExportTax(),
            'name' => $this->getName(),
            'cost' => $this->getCost(),
            'cost_old' => $this->getCostOld(),
            'status' => $this->getStatus(),
            'sort' => $this->getSort(),
            'norm_min' => $this->getNormMin(),
            'norm_max' => $this->getNormMax(),
            'norm_input' => $this->getNormInput(),
            'inventory' => $this->getInventory(),
            'note' => $this->getNote(),
            'keyword' => $this->getKeyword(),
            'default_image'=>$this->getDefaultImage(),
            'created_by' => $this->getCreatedBy(),
            'created_date' => Common::formatDateTime($this->getCreatedDate()),
            'updated_by' => $this->getCreatedBy(),
            'updated_date' => Common::formatDateTime($this->getCreatedDate()),
            'variants'=>$variantTmp,
            'category' => array('id'=>$this->getCategories()->getId(),'name'=>$this->getCategories()->getName()),
            'unit' => array('id'=>$this->getUnit()->getId(),'name'=>$this->getUnit()->getName())
        ];
    }

    /**
     * @param $variantsId
     * @return Variants|null
     */
    public function isVariantsExist($variantsId)
    {
        foreach ($this->getVariants() as $variant){
            if($variant->getId()==$variantsId) return $variant;
        }
        return null;
    }
}
