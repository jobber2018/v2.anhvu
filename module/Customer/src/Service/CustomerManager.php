<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Customer\Service;

use Customer\Entity\Address;
use Customer\Entity\Customer;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CustomerManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Customer
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Customer::class)->find($p_id);
    }

    /**
     * @param $p_publicId
     * @return Customer
     */
    public function getPublicById($p_publicId)
    {
        return $this->entityManager->getRepository(Customer::class)->findOneBy(array('public_id' => $p_publicId));
    }

    /**
     * @param $p_id
     * @return Address
     */
    public function getAddressById($p_id)
    {
        return $this->entityManager->getRepository(Address::class)->find($p_id);
    }

    /**
     * @param $p_mobile
     * @return Customer
     */
    public function getByMobile($p_mobile)
    {
        return $this->entityManager->getRepository(Customer::class)->findOneBy(array('mobile' => $p_mobile));
    }

    /**
     * @param $p_keyword
     * @param $length
     * @param $start
     * @return Paginator
     */
    public function search($p_keyword, $length, $start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from(Customer::class, 'c')
            ->LeftJoin('c.group', 'g')
            ->LeftJoin('c.route', 'r')
            ->setFirstResult($start)
            ->setMaxResults($length);

        if(!empty($p_keyword)){
            $queryBuilder->where('LOWER(c.keyword) LIKE :keyword')
                ->orWhere('LOWER(c.mobile) LIKE :keyword')
                ->orWhere('LOWER(g.name) LIKE :keyword')
                ->orWhere('LOWER(r.name) LIKE :keyword')
                ->setParameter('keyword', '%'.$p_keyword.'%');
            $queryBuilder->orderBy('c.name', 'ASC');
        }else{
            $queryBuilder->orderBy('c.created_date', 'DESC');
        }
        return new Paginator($queryBuilder->getQuery());
    }
}