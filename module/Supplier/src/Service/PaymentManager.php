<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Supplier\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Supplier\Entity\SupplierPayment;

class PaymentManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_publicId
     * @return SupplierPayment
     */
    public function getByPublicId($p_publicId){
        return $this->entityManager->getRepository(SupplierPayment::class)->findOneBy(array('public_id' => $p_publicId));;
    }

    /**
     * @param $p_id
     * @return SupplierPayment
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(SupplierPayment::class)->find($p_id);
    }

    /**
     * @param $keyword
     * @param $length
     * @param $start
     * @return Paginator
     */
    public function search($keyword, $length, $start)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('sp')
            ->from(SupplierPayment::class, 'sp')
            ->innerJoin('sp.supplier', 's')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy('sp.created_date', 'DESC');

        if($keyword) {
            $queryBuilder->where("s.name LIKE :keyword 
            OR DATE_FORMAT(sp.date,'%d%m%Y') LIKE :keyword 
            OR sp.confirm_by LIKE :keyword
            OR sp.approval_by LIKE :keyword
            OR sp.created_by LIKE :keyword")
                ->setParameter('keyword', '%'.$keyword.'%');
        }
        return new Paginator($queryBuilder->getQuery());
    }
}