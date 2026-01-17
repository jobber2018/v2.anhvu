<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-08-15
 * Time: 15:55
 */

namespace Admin\Controller;


use Admin\Service\AdminManager;
use Doctrine\ORM\EntityManager;
use Product\Service\ProductManager;
use Sell\Entity\SellOrder;
use Sell\Service\SellManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\SuldeAdminController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class StaffController extends SuldeAdminController
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        $sellManager = new SellManager($this->entityManager);
        $dateNow=date("Y-m-d");
        $username=$this->userLogin->getUsername();
//        $dateNow='2024-11-01';
//        $username='truonghm3';
        $sells = $sellManager->getSellOrderByUserAndByDateNow($dateNow,$username);
        $totalRevenue=0;
        $totalCash=0;
        $totalBank=0;
        foreach ($sells as $sell) {
            $sellMoney = $sell->getSellMoney();
            $payableOrder=Common::roundNumber($sellMoney['payable']);
            $totalRevenue+=$payableOrder;
            if($sell->getPaymentMethod()==Define::PAYMENT_METHOD_BANK)
                $totalBank+=$payableOrder;
            else
                $totalCash+=$payableOrder;
        }
        return new ViewModel([
            'privileges'=>$this->userLogin->getPrivileges()
            ,'totalRevenue'=>$totalRevenue
            ,'totalCash'=>$totalCash
            ,'totalBank'=>$totalBank
        ]);
    }
}