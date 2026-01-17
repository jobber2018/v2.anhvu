<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-25
 * Time: 12:48
 */

namespace Sulde\Service\Common;


class ConfigManager
{
    private $geoKey = 'AIzaSyDbp7O0Nqb5AJyOIwlqQqw2Ctqnue1feWI';//jobber.vn@gmail.com
    static function getRoleAdmin(){
        return[
            'admin'=> 'Admin',
            'staff' => 'Nhân viên',
            'editor'=> 'Biên tập',
            'customer'=>'Khách hàng (Admin homestay)',
            'user' => 'Người dùng'
        ];
    }
    static function getPurchaseStatus(){
        return [
            Define::PURCHASE_APPROVAL=>'Đã nhập kho',
            Define::PURCHASE_DRAFT=>'Nháp',
//            Define::PURCHASE_WAIT_APPROVAL=>'Chờ duyệt'
        ];
    }

    static function getSellStatus(){
        return [
            Define::SELL_PAID=>'Đã thanh toán',
            Define::SELL_DONE=>'Đã thu tiền',
            Define::SELL_SHIPPING=>'Đang ship',
            Define::SELL_TEXT=>'Bán hàng'
        ];
    }

    static function getPaymentMethod(){
        return [
            Define::PAYMENT_METHOD_BANK=>Define::PAYMENT_METHOD_BANK_TEXT,
            Define::PAYMENT_METHOD_CASH=>Define::PAYMENT_METHOD_CASH_TEXT
        ];
    }
    public function getGeoKey(){
        return $this->geoKey;
    }

    /**
     * return ngay trong tuan
     * @param $p_number
     * @return string
     */
    static function getDay($p_number){
        $days=[
            2=>'Monday',
            3=>'Tuesday',
            4=>'Wednesday',
            5=>'Thursday',
            6=>'Friday',
            7=>'Saturday',
            8=>'Sunday'
        ];
        return ($p_number>1 && $p_number<9)?$days[$p_number]:'N/A';
    }
}