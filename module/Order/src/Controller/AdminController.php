<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Order\Controller;

use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\UploadedFile;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Order\Service\OrderManager;
use Product\Entity\History;
use Product\Form\ProductForm;
use Product\Service\ProductManager;
use Product\Service\VariantManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Sulde\Service\FileUploader;
use Sulde\Service\SuldeAdminController;
use Supplier\Entity\SupplierDebtLedger;
use Supplier\Form\SupplierForm;
use Supplier\Service\SupplierManager;

class AdminController extends SuldeAdminController
{
    private $entityManager;
    private $orderManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManager $entityManager, OrderManager $orderManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->orderManager = $orderManager;
        $this->translator     = $translator;
    }

    public function dashboardAction()
    {
        $privilegeId=95;
        return new ViewModel([
            'privilegeId'=>$privilegeId,
            'privileges'=>$this->userLogin->getPrivileges()
        ]);
    }
}