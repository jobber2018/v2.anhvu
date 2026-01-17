<?php
/**
 * Copyright (c) 2019.  Sulde JSC
 * Created by   : TruongHM
 * Created date: 7/19/19 10:53 AM
 *
 */


namespace Product\Controller;

use DateTime;
use Exception;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Product\Entity\History;
use Product\Entity\Price;
use Product\Entity\Product;
use Product\Entity\Variants;
use Product\Service\ExcelValidator;
use Product\Service\ProductManager;
use Doctrine\ORM\EntityManager;
use Product\Service\VariantManager;
use Sulde\Service\Common\Common;
use Sulde\Service\Common\Define;
use Sulde\Service\SuldeAdminController;

class ExcelController extends SuldeAdminController
{
    private $entityManager;
    private $productManager;

    public function __construct(EntityManager $entityManager, ProductManager $productManager)
    {
        $this->entityManager = $entityManager;
        $this->productManager = $productManager;
    }

    // ----------------------------------
    // 1. Form Upload + Đọc Excel
    // ----------------------------------
    public function uploadAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return new ViewModel();
        }

        $file = $request->getFiles()->toArray()['file'];
//        $file = $request->getFiles()->toArray();

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $result['status']=0;
            $result['message'] = 'File không hợp lệ';
            return new JsonModel($result);
        }

        // Load Excel
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Lưu tạm vào session để chuyển sang bước preview
        $session = new \Laminas\Session\Container('excel_import');
        $session->excelImportData = $data;

        $result['status']=1;
        return new JsonModel($result);
    }


    // ----------------------------------
    // 2. Hiển thị preview để chỉnh sửa
    // ----------------------------------
    public function previewAction()
    {
        $dataImportProducts = new \Laminas\Session\Container('excel_import');

        if (!isset($dataImportProducts->excelImportData) && !isset($dataImportProducts->excelReimportData)) {
            return $this->redirect()->toRoute('product-excel-admin',['action'=>'upload']);
        }

        $request = $this->getRequest();

        // Nếu người dùng submit để import DB
        if ($request->isPost()) {

            $rows = $request->getPost()->toArray();

            $columnFields=$this->_getColumnField();
            $validator=new ExcelValidator();
            $failedRows=array();
            foreach ($rows as $index => $data) {
                foreach ($data as $col => $value) {
                    $columnField[$col]=$validator->validateRow($value,$columnFields[$col]);
                }
//                $dataValidate[] = $columnField;
                $saveItem = $this->_saveItem($columnField);
                if($saveItem['status']==0){
//                    $data=$saveItem['data'];
                    $data=array_replace_recursive($columnFields, $saveItem['data']);
//                    $failedRows[]=$saveItem['data'];
                    $failedRows[]=$data;
                }
            }

            if(!empty($failedRows)){
                $dataImportProducts->excelReimportData = $failedRows;
            }else{
                unset($dataImportProducts->excelReimportData);
            }

            unset($dataImportProducts->excelImportData);

            return new JsonModel(array(
                    'status'=>1,
                    'total_record'=>count($rows),
                    'total_record_fail'=>count($failedRows),
                    'total_record_success'=>count($rows)-count($failedRows),
                )
            );
        }

        //dinh nghia du lieu excel file
        $columnField=$this->_getColumnField();

        $header=$this->_getHeader($columnField);

        $validator = new ExcelValidator();

        $rows=$dataImportProducts->excelImportData;

        //Du lieu trong file excel
        if($rows){
            foreach ($rows as $index =>$row) {
                if ($index < 4) continue;
                /**
                 * validate data theo rule defile, return data array(data define,'value'=>value,'is_valid'=>0|1,'message'=>'neu co')
                 */
                foreach ($row as $col => $value){
                    if (array_key_exists($col,$columnField)){
                        $columnField[$col]=$validator->validateRow($value,$columnField[$col]);
                    }
                }
                $dataValidate[] = $columnField;
            }
            $message='Tìm thấy '.count($dataValidate).' sản phẩm yêu cầu.';
        }else if($dataImportProducts->excelReimportData){
            //du lieu reimport
            $dataValidate=$dataImportProducts->excelReimportData;
            $message=count($dataValidate).' sản phẩm cần kiểm tra lại dữ liệu.';
        }
        /*foreach ($dataValidate[0] as $key => $value) {
            $$header[]
        }*/
        return new ViewModel([
            'dataValidate' => $dataValidate,
            'header'=>$header,
            'message'=>$message
        ]);
    }

    private function _getColumnField(){
        $units = $this->productManager->getAllUnit();

        $dataUnit['']=array('id'=>'','name'=>'...','code'=>'');
        foreach ($units as $unit) {
            $dataUnit[strtolower($unit->getCode())]=$unit->serialize();
        }
        $categories = $this->productManager->getAllCategories();
        $dataCategory=array();
        foreach ($categories as $category) {
            $dataCategory[strtolower($category->getCode())]=$category->serialize();
        }

        return [
            'A'=>array(
                'field_name'=>'product_id',
                'table'=>'product',
                'label'=>'ID sản phẩm',
                'rule'=>array(
                    'required'=>false
                )
            ),
            'B'=>array(
                'field_name'=>'name',
                'table'=>'product',
                'label'=>'Tên sản phẩm',
                'rule'=>array(
                    'required'=>true,
                    'type' => 'text',
                    'max_length' => 100
                )
            ),
            'C'=>array(
                'field_name'=>'unit_id',
                'table'=>'product',
                'label'=>'ĐV cơ bản',
                'rule'=>array(
                    'required'=>true,
                    'type' => 'select',
                    'options' => $dataUnit
                )
            ),
            'D'=>array(
                'field_name'=>'category_id',
                'table'=>'product',
                'label'=>'Phân loại',
                'rule'=>array(
                    'required'=>true,
                    'type' => 'select',
                    'options' => $dataCategory
                )
            ),
            'E'=>array(
                'field_name'=>'vat_value',
                'table'=>'product',
                'label'=>'Thuế GTGT(%)',
                'rule'=>array(
                    'required' => true,
                    'type' => 'text',
                    'min' => 0,
                    'max' => 20
                )
            ),
            'F'=>array(
                'field_name'=>'cost',
                'table'=>'product',
                'label'=>'Giá vốn',
                'class'=>'amount',
                'rule'=>array(
                    'required' => true,
                    'type' => 'number',
                    'min' => 0
                )
            ),
            'G'=>array(
                'field_name'=>'inventory',
                'table'=>'product',
                'label'=>'Tồn kho',
                'rule'=>array(
                    'required' => true,
                    'type' => 'number',
                    'min' => 0
                )
            ),
            'H'=>array(
                'field_name'=>'norm_min',
                'table'=>'product',
                'label'=>'Tồn tối thiểu',
                'rule'=>array(
                    'required' => true,
                    'type' => 'number',
                    'min' => 0
                )
            ),
            'I'=>array(
                'field_name'=>'norm_max',
                'table'=>'product',
                'label'=>'Tồn tối đa',
                'rule'=>array(
                    'required' => true,
                    'type' => 'number',
                    'min' => 0
                )
            ),
            'J'=>array(
                'field_name'=>'norm_input',
                'table'=>'product',
                'label'=>'Gợi ý nhập',
                'rule'=>array(
                    'required' => true,
                    'type' => 'number',
                    'min' => 0
                )
            ),
            'K'=>array(
                'field_name'=>'barcode',
                'table'=>'variant_1',
                'label'=>'Mã vạch',
                'rule'=>array(
                    'required'=>false,
                    'type' => 'text'
                )
            ),
            'L'=>array(
                'field_name'=>'name',
                'table'=>'variant_1',
                'label'=>'Quy cách',
                'rule'=>array(
                    'required'=>true,
                    'type' => 'text',
                    'max_length' => 255
                )
            ),
            'M'=>array(
                'field_name'=>'unit_id',
                'table'=>'variant_1',
                'label'=>'Đơn vị',
                'rule'=>array(
                    'required'=>true,
                    'type' => 'select',
                    'options' => $dataUnit
                )
            ),
            'N'=>array(
                'field_name'=>'conversion_rate',
                'table'=>'variant_1',
                'label'=>'Quy đổi',
                'rule'=>array(
                    'required' => true,
                    'type' => 'number',
                    'min' => 1
                )
            ),
            'O'=>array(
                'field_name'=>'retail_price',
                'table'=>'variant_1',
                'label'=>'Giá bán',
                'class'=>'amount',
                'rule'=>array(
                    'required' => false,
                    'type' => 'number',
                    'min' => 0
                )
            ),
            'P'=>array(
                'field_name'=>'barcode',
                'table'=>'variant_2',
                'label'=>'Mã vạch',
                'rule'=>array(
                    'required'=>false,
                    'type' => 'text'
                )
            ),
            'Q'=>array(
                'field_name'=>'name',
                'table'=>'variant_2',
                'label'=>'Quy cách',
                'rule'=>array(
                    'required'=>true,
                    'type' => 'text',
                    'max_length' => 255
                )
            ),
            'R'=>array(
                'field_name'=>'unit_id',
                'table'=>'variant_2',
                'label'=>'Đơn vị',
                'rule'=>array(
                    'required'=>true,
                    'type' => 'select',
                    'options' => $dataUnit
                )
            ),
            'S'=>array(
                'field_name'=>'conversion_rate',
                'table'=>'variant_2',
                'label'=>'Quy đổi',
                'rule'=>array(
                    'required' => true,
                    'type' => 'number',
                    'min' => 1
                )
            ),
            'T'=>array(
                'field_name'=>'retail_price',
                'table'=>'variant_2',
                'label'=>'Giá bán',
                'class'=>'amount',
                'rule'=>array(
                    'required' => false,
                    'type' => 'number',
                    'min' => 0
                )
            ),
        ];
    }

    /**
     * @param array $columnFields
     * @return array
     */
    private function _getHeader(array $columnFields)
    {
        $header=array();
        foreach ($columnFields as $col=>$field) {
            $header[$col] = $field['label'];
        }
        return $header;
    }

    private function _saveItem($data)
    {
        $variantManager = new VariantManager($this->entityManager);
        $valid=1;
        foreach ($data as $col=>$value){
            //neu field la barcode thi kiem tra su ton tai cua barcode?
            if($value['field_name']=='barcode' && !empty($value['value'])){
                $barcode = $variantManager->getByBarcode(trim($value['value']));
                if($barcode){
                    $data[$col]['is_valid'] = 0;
                    $data[$col]['message'] = 'Mã vạch đang sử dụng bởi: '.$barcode->getProduct()->getName().' | '.$barcode->getName();
                    $valid=0;
                }
            }
            $table[$value['table']][]=array($value['field_name']=>$value['value']);
        }

        if(!$valid) return ['status'=>0,'data'=>$data];

        $product = $this->_buildProduct($table['product']);

        $variant = $this->_buildVariant($table['variant_1']);
        $variant->setProduct($product);
        $product->addVariants($variant);

        $variant = $this->_buildVariant($table['variant_2']);
        $variant->setProduct($product);
        $product->addVariants($variant);

        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return ['status'=>1,'data'=>$product];
    }

    private function _buildProduct($data)
    {
        $vData = array_merge(...$data);

        $product = new Product();
        $name=$this->_getData('name',$vData);
        $unitId=$this->_getData('unit_id',$vData);

        $unitItem = $this->productManager->getUnitById($unitId);

        $categoryId=$this->_getData('category_id',$vData);
        $categoriesItem = $this->productManager->getCategoriesById($categoryId);

        $vatValue=$this->_getData('vat_value',$vData);
        $cost=$this->_getData('cost',$vData);
        $inventory=$this->_getData('inventory',$vData);
        $normMin=$this->_getData('norm_min',$vData);
        $normMax=$this->_getData('norm_max',$vData);
        $normInput=$this->_getData('norm_input',$vData);

        $product->setNormMin($normMin);
        $product->setNormMax($normMax);
        $product->setNormInput($normInput);
        $product->setName($name);
        $product->setUnit($unitItem);
        $product->setCategories($categoriesItem);

        //vat value and option
        $vatOptions = Common::getVatOptions();
        if (array_key_exists($vatValue, $vatOptions)) {
            $product->setVatValue(is_numeric($vatValue)?$vatValue:0);
            $product->setVatOption($vatOptions[$vatValue]);
        }else{
            $product->setVatValue($vatValue);
            $product->setVatOption($vatValue?$vatValue:'other');
        }

        $product->setCost($cost);
        $product->setCreatedBy($this->userLogin->getUsername());
        $product->setCreatedDate(new \DateTime());

        if($inventory>0){
            $history = new History();
            $history->setNote('import');
            $history->setChange($inventory);
            $history->setInventory(0);
            $history->setType('import');
            $history->setUrl('#');
            $history->setCreatedBy($this->userLogin->getUsername());
            $history->setCreatedDate(new \DateTime());
            $history->setProduct($product);
            $product->addHistory($history);
        }
        return $product;
    }

    private function _buildVariant($data)
    {
        $vData = array_merge(...$data);
        $variant = new Variants();
        $name=$this->_getData('name',$vData);
        $barcode=$this->_getData('barcode',$vData);

        $unitId=$this->_getData('unit_id',$vData);
        $unitItem = $this->productManager->getUnitById($unitId);

        $conversionRate=$this->_getData('conversion_rate',$vData);
        $retailPrice=$this->_getData('retail_price',$vData);

        $variant->setName($name);
        $variant->setUnit($unitItem);
        $variant->setConversionRate($conversionRate);
        $variant->setBarcode($barcode);
        $variant->setStatus(Define::DEFAULT_UN_ACTIVE);
        $variant->setCreatedBy($this->userLogin->getUsername());
        $variant->setCreatedDate(new \DateTime());

        if($retailPrice>0){
            $price = new Price();
            $price->setRetailPrice($retailPrice);
            $price->setActive(Define::DEFAULT_ACTIVE);
            $price->setCreatedBy($this->userLogin->getUsername());
            $price->setCreatedDate(new \DateTime());
            $price->setVariants($variant);
            $variant->addPrice($price);
        }
        return $variant;
    }
    private function _getData($key,$data){
       return trim($data[$key]);
    }
}