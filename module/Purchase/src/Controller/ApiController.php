<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Purchase\Controller;

use Doctrine\ORM\EntityManager;
use Purchase\Service\PurchaseManager;
use Sulde\Service\SuldeFrontController;
use Zend\View\Model\JsonModel;

class ApiController extends SuldeFrontController
{

    private $entityManager;
    private $purchaseManager;

    public function __construct(EntityManager $entityManager, PurchaseManager $purchaseManager)
    {
        $this->entityManager = $entityManager;
        $this->purchaseManager = $purchaseManager;
    }
}