<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Purchase\Controller;

use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Exception;
use Doctrine\ORM\EntityManager;
use Purchase\Service\PurchaseManager;
use Sulde\Service\SuldeUserController;
use Zend\Paginator\Paginator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $purchaseManager;

    public function __construct(EntityManager $entityManager, PurchaseManager $purchaseManager)
    {
        $this->entityManager = $entityManager;
        $this->purchaseManager = $purchaseManager;
    }

    public function indexAction()
    {
        return new ViewModel();
    }
}