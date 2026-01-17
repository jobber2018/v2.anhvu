<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Customer\Service;

use Customer\Entity\Crm;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CrmManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Crm
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Crm::class)->find($p_id);
    }

    /**
     * @param $p_customerId
     * @param $p_keyword
     * @param $length
     * @param $start
     * @return Paginator
     */
    public function search($p_customerId, $p_keyword, $length, $start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from(Crm::class, 'c')
            ->where('c.customer_id = :customerId')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->setParameter('customerId', $p_customerId);

        if(!empty($p_keyword)){
            $queryBuilder->andWhere('LOWER(c.content) LIKE :keyword')
                ->orWhere('LOWER(c.created_by) LIKE :keyword')
                ->setParameter('keyword', '%'.$p_keyword.'%');
            $queryBuilder->orderBy('c.content', 'ASC');
        }else{
            $queryBuilder->orderBy('c.created_date', 'DESC');
        }
        return new Paginator($queryBuilder->getQuery());
    }
}