<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2024-07-21
 * Time: 23:49
 */

namespace Order\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as orm;
use Product\Entity\Product;
use Product\Entity\Variants;
use Ramsey\Uuid\Uuid;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Sulde\Service\HasPublicId;

/**
 * @orm\Entity
 * @orm\Table(name="order")
 * @orm\HasLifecycleCallbacks
 */

class Order
{
    use HasPublicId;

    public function __construct() {

    }

    /**
     * @orm\Id
     * @orm\Column(type="integer")
     * @orm\GeneratedValue (strategy="AUTO")
     */
    private $id;

}