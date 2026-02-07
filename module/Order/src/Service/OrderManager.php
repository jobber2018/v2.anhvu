<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Order\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sulde\Service\Common\Define;

class OrderManager
{
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
    }

    /**
     * @param $p_id
     * @return Order
     */
    public function getById($p_id)
    {
        return $this->entityManager->getRepository(Order::class)->find($p_id);
    }

}