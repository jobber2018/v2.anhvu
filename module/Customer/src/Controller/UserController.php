<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Customer\Controller;


use Customer\Service\CustomerManager;
use Doctrine\ORM\EntityManager;
use Laminas\View\Model\ViewModel;
use Sulde\Service\SuldeUserController;

class UserController extends SuldeUserController
{

    private $entityManager;
    private $customerManager;

    public function __construct(EntityManager $entityManager, CustomerManager $customerManager)
    {
        $this->entityManager = $entityManager;
        $this->customerManager = $customerManager;
    }

    public function indexAction()
    {
        return new ViewModel();
    }
}