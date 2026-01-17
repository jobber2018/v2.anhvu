<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-24
 * Time: 23:49
 */

namespace Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity
 * @orm\Table(name="product_unit")
 */

class Unit
{

    public function __construct() {
    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

    /** @orm\Column(type="string", name="name") */
    private $name;

    /** @orm\Column(type="string", name="code") */
    private $code;

    /**
     * @orm\OneToMany(targetEntity="Product\Entity\Variants", mappedBy="unit")
     * @orm\JoinColumn(name="id", referencedColumnName="unit_id")
     */
    private $variants;

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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @param mixed $attribute
     */
    public function setVariants($variants)
    {
        $this->variants = $variants;
    }

    public function serialize() {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'name' => $this->getName()
        ];
    }
}