<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Order\Controller;

use Doctrine\ORM\EntityManager;
use Order\Service\OrderManager;
use Sulde\Service\SuldeFrontController;
use Zend\View\Model\JsonModel;

class ApiController extends SuldeFrontController
{

    private $entityManager;
    private $orderManager;

    public function __construct(EntityManager $entityManager, OrderManager $orderManager)
    {
        $this->entityManager = $entityManager;
        $this->orderManager = $orderManager;
    }
}