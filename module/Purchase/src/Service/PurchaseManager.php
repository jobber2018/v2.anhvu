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

class PurchaseManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_id
     * @return Purchase
     */
    public function getById($p_id)
    {
        return $this->entityManager->getRepository(Purchase::class)->find($p_id);
    }

    /**
     * @param $p_publicId
     * @return Purchase
     */
    public function getPublicById($p_publicId)
    {
        return $this->entityManager->getRepository(Purchase::class)->findOneBy(array('public_id' => $p_publicId));
    }

    public function search($keyword,$length, $start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Purchase::class, 'p')
            ->innerJoin('p.supplier', 's')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy('p.status', 'DESC')
            ->addOrderBy('p.created_date', 'DESC');

        if(!empty($keyword)){
            $queryBuilder->where("s.name LIKE :keyword")
                ->setParameter('keyword', '%'.$keyword.'%');
        }

        return new Paginator($queryBuilder->getQuery());
    }


    /**
     * @param $product_id
     * @param $max_date
     * @return PurchaseDetail
     */
    public function getLatestPrice($product_id,$max_date)
    {
        $configuration = $this->entityManager->getConfiguration();
        $configuration->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('pd')
            ->from(PurchaseDetail::class, 'pd')
            ->innerJoin('pd.purchase', 'p')
            ->innerJoin('pd.variants', 'pv')
            ->where('pv.product = :product_id')
            ->andWhere("p.status = :status")
            ->andWhere("pd.price >0")
            ->andWhere("DATE_FORMAT(pd.created_date,'%Y-%m-%d %H:%i:%s') <=:max_date")
            ->setParameter('max_date',$max_date)
            ->setParameter('product_id',$product_id)
            ->setParameter('status',Define::PURCHASE_APPROVAL)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->orderBy("DATE_FORMAT(pd.created_date,'%Y-%m-%d %H:%i:%s')", 'DESC');
        return $queryBuilder->getQuery()->getResult();
    }
}