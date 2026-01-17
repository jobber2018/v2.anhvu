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
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Product\Entity\Variants;

class VariantManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_id
     * @return Variants
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Variants::class)->find($p_id);
    }

    /**
     * @param $p_barcode
     * @return Variants
     */
    public function getByBarcode($p_barcode)
    {
        return $this->entityManager->getRepository(Variants::class)->findOneBy(array('barcode' => $p_barcode));
    }
}