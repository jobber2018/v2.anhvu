<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Product\Controller;

use Product\Service\ProductManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\Common\Common;
use Sulde\Service\SuldeFrontController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends SuldeFrontController
{

    private $entityManager;
    private $productManager;

    public function __construct(EntityManager $entityManager, ProductManager $productManager)
    {
        $this->entityManager = $entityManager;
        $this->productManager = $productManager;
    }

    public function indexAction()
    {
//        $product = $this->productManager->getAll();
        $viewModel = new ViewModel();
//        $viewModel->setVariable('product',$product);
        return $viewModel;
    }
}