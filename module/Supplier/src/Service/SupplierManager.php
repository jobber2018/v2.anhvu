<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Supplier\Service;


use Customer\Entity\Group;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Hotels\Service\HotelManage;
use Supplier\Entity\Supplier;

class SupplierManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Supplier
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Supplier::class)->find($p_id);
    }

    /**
     * @param $p_publicId
     * @return Supplier
     */
    public function getByPublicId($p_publicId){
        return $this->entityManager->getRepository(Supplier::class)->findOneBy(array('public_id' => $p_publicId));;
    }

    /**
     * @return Supplier
     */
    public function getAll()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('s')
            ->from(Supplier::class, 's')
            ->orderBy('s.created_date', 'DESC');
        return $queryBuilder->getQuery()->getResult();
    }

    public function search($keyword, $length, $start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('s')
            ->from(Supplier::class, 's')
            ->where('1=1')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->orderBy('s.created_date', 'DESC');

        if($keyword) {
            $queryBuilder->andWhere('s.name LIKE :name')
                ->setParameter('name', '%'.$keyword.'%');
        }
        return new Paginator($queryBuilder->getQuery());
    }

    /**
     * @return Group
     */
    public function getGroups()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Group::class, 'g')
            ->orderBy('g.name', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_tax_code
     * @return Supplier
     */
    public function getByTaxCode($p_tax_code)
    {
        return $this->entityManager->getRepository(Supplier::class)->findOneBy(array('tax_code' => $p_tax_code));
    }
}