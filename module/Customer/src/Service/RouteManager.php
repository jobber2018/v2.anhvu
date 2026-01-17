<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Customer\Service;

use Customer\Entity\Route;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class RouteManager
{

    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }
    /**
     * @param $p_id
     * @return Route
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Route::class)->find($p_id);
    }

    /**
     * @return Route
     */
    public function getAll()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Route::class, 'r')
            ->orderBy('r.name', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }
}