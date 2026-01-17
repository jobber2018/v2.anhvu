<?php
/**
 * Created by PhpStorm.
 * User: Truonghm
 * Date: 2019-07-24
 * Time: 11:18
 */

namespace Product\Service;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Product\Entity\Variants;
use Product\Entity\Image;
use Product\Entity\Product;
use Product\Entity\Categories;
use Product\Entity\Unit;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sulde\Service\Common\SessionManager;

class ProductManager
{
    private $entityManager;
    private $cache;

    public function __construct($entityManager)
    {
        $this->entityManager=$entityManager;
//        $this->cache=$cache;
    }
    /**
     * @param $p_id
     * @return Product
     */
    public function getById($p_id){
        return $this->entityManager->getRepository(Product::class)->find($p_id);
    }

    /**
     * @param $p_publicId
     * @return Product
     */
    public function getPublicById($p_publicId): Product
    {
        return $this->entityManager->getRepository(Product::class)->findOneBy(array('public_id' => $p_publicId));
    }

    /**
     * @return Categories
     */
    public function getAllCategories()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('pc')
            ->from(Categories::class, 'pc')
            ->andWhere('pc.active=1')
            ->orderBy('pc.sort', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Unit
     */
    public function getAllUnit()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('u')
            ->from(Unit::class, 'u')
            ->orderBy('u.name', 'ASC');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_categoriesId
     * @return Categories
     */
    public function getCategoriesById($p_categoriesId)
    {
        return $this->entityManager->getRepository(Categories::class)->find($p_categoriesId);
    }

    /**
     * @param $p_unitId
     * @return Unit
     */
    public function getUnitById($p_unitId)
    {
        return $this->entityManager->getRepository(Unit::class)->find($p_unitId);
    }

    /**
     * @param $p_imageId
     * @return Image
     */
    public function getImageById($p_imageId)
    {
        return $this->entityManager->getRepository(Image::class)->find($p_imageId);
    }

    /**
     * @param $p_productId
     * @return Image
     */
    public function getImageByProductId($p_productId)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('i')
            ->from(Image::class, 'i')
            ->where('i.product = :productId')
            ->setParameter('productId', $p_productId);
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $p_keyword
     * @param $length
     * @param $start
     * @param $columnOrder
     * @param $dir
     * @return Paginator
     */
    public function searchProducts($p_keyword, $length, $start, $columnOrder='', $dir='')
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->InnerJoin('p.variants', 'v')
            ->InnerJoin('p.categories', 'c')
            ->setFirstResult($start)
            ->setMaxResults($length);

        if(!empty($p_keyword)){
            $queryBuilder->where('LOWER(p.name) LIKE :keyword')
                ->orWhere('LOWER(p.keyword) LIKE :keyword')
                ->orWhere('LOWER(v.barcode) LIKE :keyword')
                ->orWhere('LOWER(c.name) LIKE :keyword')
                ->setParameter('keyword', '%'.$p_keyword.'%');
            $queryBuilder->orderBy('p.name', 'ASC');
        }else{
            $queryBuilder->orderBy('v.created_date', 'DESC');
        }
        return new Paginator($queryBuilder->getQuery());
    }

    /**
     * @param $p_keyword
     * @param $length
     * @param $start
     * @param $columnOrder
     * @param $dir
     * @return Paginator
     */
    public function searchVariants($p_keyword, $length, $start, $columnOrder='', $dir='')
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('v')
            ->from(Variants::class, 'v')
            ->InnerJoin('v.product', 'p')
            ->setFirstResult($start)
            ->setMaxResults($length);

        if(!empty($p_keyword)){
            $queryBuilder->where('LOWER(p.name) LIKE :keyword')
                        ->orWhere('LOWER(p.keyword) LIKE :keyword')
                        ->orWhere('LOWER(v.barcode) LIKE :keyword')
                ->setParameter('keyword', '%'.$p_keyword.'%');
        }
//        $queryBuilder->orderBy('a.'.$columnOrder, $dir);
        $queryBuilder->orderBy('v.created_date', 'DESC');
        return new Paginator($queryBuilder->getQuery());
    }

    public function searchByKeywords(array $p_keywords, int $length, int $start)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Product::class, 'p')
            ->setFirstResult($start)
            ->setMaxResults($length);

        if(!empty($p_keywords)){
            foreach ($p_keywords as $i => $kw) {
//                $param = ':kw' . $i;
//                $orX->add($qb->expr()->like('LOWER(p.keywords)', $param));
//                $qb->setParameter($param, '%' . strtolower($kw) . '%');

                $queryBuilder->orWhere('LOWER(p.keyword) LIKE :keyword')
                    ->setParameter('keyword', '%'.strtolower($kw).'%');
            }
            /*$queryBuilder->where('LOWER(p.name) LIKE :keyword')
                ->orWhere('LOWER(p.keyword) LIKE :keyword')
                ->orWhere('LOWER(v.barcode) LIKE :keyword')
                ->setParameter('keyword', '%'.$p_keyword.'%');*/
        }
//        $queryBuilder->orderBy('a.'.$columnOrder, $dir);
//        $queryBuilder->orderBy('v.created_date', 'DESC');
        return $queryBuilder->getQuery()->getResult();
    }
}