<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Purchase\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Purchase\Entity\Purchase;
use Purchase\Entity\PurchaseDetail;
use Purchase\Entity\PurchaseReturn;
use Sulde\Service\Common\Define;

class PurchaseReturnManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_Id
     * @return PurchaseReturn
     */
    public function getById($p_Id)
    {
        return $this->entityManager->getRepository(PurchaseReturn::class)->find($p_Id);
    }

    /**
     * @param $p_publicId
     * @return PurchaseReturn
     */
    public function getPublicById($p_publicId)
    {
        return $this->entityManager->getRepository(PurchaseReturn::class)->findOneBy(array('public_id' => $p_publicId));
    }

    /**
     * @param $keyword
     * @param $length
     * @param $start
     * @return Paginator
     */
    public function searchPurchaseReturn($keyword,$length, $start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('pr')
            ->from(PurchaseReturn::class, 'pr')
            ->innerJoin('pr.purchase', 'p')
            ->innerJoin('p.supplier', 's')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy('pr.status', 'DESC')
            ->addOrderBy('pr.created_date', 'DESC');

        if(!empty($keyword)){
            $queryBuilder->where("s.name LIKE :keyword")
                ->setParameter('keyword', '%'.$keyword.'%');
        }

        return new Paginator($queryBuilder->getQuery());
    }
}