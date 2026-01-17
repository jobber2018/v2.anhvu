<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Supplier\Controller;


use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Supplier\Entity\Supplier;
use Supplier\Form\SupplierForm;
use Supplier\Service\DebtManager;
use Supplier\Service\SupplierManager;
use Doctrine\ORM\EntityManager;
use Sulde\Service\SuldeAdminController;

class DebtController extends SuldeAdminController
{
    private $entityManager;
    private $debtManager;
    public function __construct(EntityManager $entityManager, DebtManager $debtManager)
    {
        $this->entityManager = $entityManager;
        $this->debtManager = $debtManager;
    }

    public function dashboardAction(){
        $request = $this->getRequest();
        if($request->isPost()) {
            $accountsPayableSummary = $this->debtManager->getAccountsPayableSummary();
            $data=array();
            foreach ($accountsPayableSummary as $accountPayableSummary){
                $supplierId=$accountPayableSummary['supplier_id'];
                $name=$accountPayableSummary['name'];
                $referenceType=$accountPayableSummary['reference_type'];
                $amount=$accountPayableSummary['amount'];

                $data[$supplierId]['supplier_id']=$supplierId;
                $data[$supplierId]['public_id']=$accountPayableSummary['public_id'];
                $data[$supplierId]['supplier_name']=$name;
                $data[$supplierId][$referenceType]=@$data[$referenceType]+abs($amount);
            }

            $result['data']=$data;
            return new JsonModel($result);
        }
        return new ViewModel();
    }

    public function detailAction()
    {
        $supplierId = $this->params()->fromRoute('id',0);
        $supplierManager = new SupplierManager($this->entityManager);
        $supplier = $supplierManager->getByPublicId($supplierId);

        return new ViewModel(['supplier'=>$supplier]);
    }
}