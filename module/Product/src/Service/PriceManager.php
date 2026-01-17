<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Product\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Product\Entity\Price;
use Product\Entity\Variants;
use Product\Entity\Image;
use Product\Entity\Product;
use Product\Entity\Categories;
use Product\Entity\Unit;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sulde\Service\Common\SessionManager;

class PriceManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_id
     * @return Price
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Price::class)->find($p_id);
    }
}