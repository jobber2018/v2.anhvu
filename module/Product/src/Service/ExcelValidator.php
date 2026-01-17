<?php

namespace Product\Service;

use Sulde\Service\Common\Common;

class ExcelValidator
{
//    private $rules;

    public function __construct()
    {
//        $this->rules = $rules;
    }

    public function validateRow($p_value,$data)
    {

//        foreach ($this->rules as $col => $rule) {

        $value = $p_value ?? null;
        $rule=$data['rule'];
        $data['is_valid'] = 1;
        $data['message'] = '';
        $data['value']=trim($value);
        // Required
        if (($rule['required'] ?? false) && (is_null($value) || $value === '')) {
            $data['is_valid']=0;
            $data['message']="không được để trống";
            return $data;
        }

        // Skip if no value
        if ($value === null || $value === '')return $data;

        // Type
        if (($rule['type'] ?? null) === 'number' && !is_numeric($value)) {
            $data['is_valid']=0;
            $data['message']="Dữ liệu phải là số";
        }

        if (($rule['type'] ?? null) === 'digits' && !ctype_digit($value)) {
            $data['is_valid']=0;
            $data['message']="Dữ liệu phải là số nguyên";
        }

        if (($rule['type'] ?? null) === 'text' && !is_string($value)) {
            $data['is_valid']=0;
            $data['message']="Dữ liệu phải ký tự";
        }

        if (($rule['type'] ?? null) === 'select') {
            if (!array_key_exists(strtolower(Common::convertAlias($value)), $rule['options'])) {
                $ids = array_column($rule['options'], 'id');
                if (!in_array($value, $ids)) {
//                    echo "khong Tồn tại id = 2";
                    $data['is_valid'] = 0;
                    $data['message'] = "Giá trị không hợp lệ ($value)";
                    $data['selected'] = 0;
                }else{
                    //echo "Tồn tại id = 2";
                    $data['selected'] = $value;
                }
            }else{
                $data['selected']=$rule['options'][strtolower(Common::convertAlias($value))]['id'];
            }
        }

        // Min/Max
        if (isset($rule['min']) && $value < $rule['min']) {
            $data['is_valid'] = 0;
            $data['message']="Số phải >= {$rule['min']}";
        }
        if (isset($rule['max']) && $value > $rule['max']) {
            $data['is_valid'] = 0;
            $data['message']="Số phải <= {$rule['max']}";
        }

        // Length
        if (isset($rule['length']) && strlen($value) !== $rule['length']) {
            $data['is_valid'] = 0;
            $data['message']="Dữ liệu phải có độ dài {$rule['length']}";
        }
//            $row[$col]=array('value' => $value, 'error' => $error,'hidden'=>$rule['hidden'] ?? 0,'message'=>$errorMessage);
//        }

        return $data;
    }
}