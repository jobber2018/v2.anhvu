<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Customer\Service;

use Customer\Entity\Group;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class GroupManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Group
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Group::class)->find($p_id);
    }

    /**
     * @return Group
     */
    public function getAll()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Group::class, 'g')
            ->where('g.status=1')
            ->orderBy('g.name', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }
}