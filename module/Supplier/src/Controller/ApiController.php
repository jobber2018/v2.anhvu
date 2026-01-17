<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 11:40
 */

namespace Supplier\Controller;

use Doctrine\ORM\EntityManager;
use Laminas\Json\Json;
use Laminas\View\Model\JsonModel;
use Purchase\Service\PurchaseManager;
use Sulde\Service\SuldeFrontController;
use Supplier\Service\SupplierManager;
use Supplier\Service\TaxLookupService;

class ApiController extends SuldeFrontController
{

    private $entityManager;
    private $taxLookupService;

    public function __construct(EntityManager $entityManager, TaxLookupService $taxLookupService)
    {
        $this->entityManager = $entityManager;
        $this->taxLookupService = $taxLookupService;
    }

    /**
     * truyen ma so thue, return info theo ma so thue. use API from misa
     * @return JsonModel
     */
    public function taxCodeLookupAction()
    {
        // Lấy tham số từ URL:  /tax/lookup?tax=0123456789
        $taxCode = $this->params()->fromQuery('tax', null);

        if (!$taxCode) {
            return new JsonModel([
                'success' => false,
                'message' => 'Missing tax code'
            ]);
        }

        $misaResult = $this->taxLookupService->getCompanyByTaxCode($taxCode);

        if (!$misaResult) {
            return new JsonModel([
                'success' => false,
                'message' => 'API error or not found'
            ]);
        }

        $companyInfo=array();

        $result['status']=1;
        $result['tax_code']=$taxCode;

        if($misaResult['Success'] && $misaResult['Data']['Success']){
            $info=Json::decode($misaResult['Data']['Data'])[0];
            $companyInfo['company_name']=$info->companyName;
            $companyInfo['short_name']=$this->_getShortName($info->companyName);
            $companyInfo['address']=$info->address;
            $result['data']=$companyInfo;
            //if($info->activeStatus)
        }else{
            $result['status']=0;
            $result['message']=Json::decode($misaResult['Data']['Message'])->message;
        }

        // Trả dữ liệu JSON
        return new JsonModel($result);
    }

    /**
     * Lấy tên định danh chính của công ty
     */
    private function _getShortName(string $companyName): string
    {
        // Chuẩn hóa khoảng trắng & chữ hoa
        $name = mb_strtoupper(trim($companyName), 'UTF-8');

        // Danh sách tiền tố cần loại bỏ (theo thực tế VN)
        $prefixes = [
            'CÔNG TY',
            'TNHH',
            'TRÁCH NHIỆM HỮU HẠN',
            'CỔ PHẦN',
            'CP',
            'TẬP ĐOÀN',
            'GROUP',
            'JSC',
            'LTD',
            'THƯƠNG MẠI',
            'DỊCH VỤ',
            'VÀ',
            'HỘ KINH DOANH'
        ];

        // Loại bỏ tiền tố
        foreach ($prefixes as $prefix) {
            $name = preg_replace(
                '/\b' . preg_quote($prefix, '/') . '\b/u',
                '',
                $name
            );
        }

        // Xóa khoảng trắng dư
        $name = preg_replace('/\s+/u', ' ', trim($name));

        return $name;
    }
}