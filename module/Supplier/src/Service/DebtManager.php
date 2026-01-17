<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Supplier\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Supplier\Entity\SupplierDebtLedger;

class DebtManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return SupplierDebtLedger
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(SupplierDebtLedger::class)->find($p_id);
    }

    /**
     * @param $p_referenceId
     * @return SupplierDebtLedger
     */
    public function getByReferenceId($p_referenceId){
        return $this->entityManager->getRepository(SupplierDebtLedger::class)->findOneBy(array('reference_id' => $p_referenceId));;
    }

    /**
     * @param $keyword
     * @param $length
     * @param $start
     * @return Paginator
     */
    public function search($keyword, $length, $start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('dl')
            ->from(SupplierDebtLedger::class, 'dl')
            ->innerJoin('dl.supplier', 's')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy('dl.created_date', 'DESC');

        if($keyword) {
            $queryBuilder->where('s.name LIKE :name')
                ->setParameter('name', '%'.$keyword.'%');
        }
        return new Paginator($queryBuilder->getQuery());
    }

    /**
     * @return mixed
     */
    public function getAccountsPayableSummary()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('s.id AS supplier_id,s.public_id, s.name, dl.reference_type,sum(dl.amount) as amount')
            ->from(SupplierDebtLedger::class, 'dl')
            ->leftJoin('dl.supplier', 's')
            ->groupBy('s.id, dl.reference_type');
        return $queryBuilder->getQuery()->getResult();
    }
}