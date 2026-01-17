<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-28
 * Time: 10:06
 */

namespace Sulde\Service\Common;


class Define
{
    const API_HOST ="http://xmartapi.local/";
    const API_URL_LOGIN ="login.html";

    const ITEM_PAGE_COUNT =10;
    const DEFAULT_USER_ROLE = '';
    const MESSAGE_WINDOW_REDIRECT = 'Đang chuyển hướng!';
    const DEFAULT_ACTIVE = 1;
    const DEFAULT_UN_ACTIVE = 0;


    const PRODUCT_TEXT_UN_ACTIVE = 'Dừng giao dịch';

    const PURCHASE_CODE = 'purchase';
    const PURCHASE = 'Nhập hàng';
    const PURCHASE_APPROVAL = 'approved';//don nhap da duoc nhap kho
    const PURCHASE_DRAFT = 1;//don nhap
//    const PURCHASE_WAIT_APPROVAL = 'progress';//cho phe duyet
    const UNIT = 'unit';
    const PACK = 'pack';
    const PERCENT = 'percent';//phan tram
    const MONEY = 'money';//tien
    const CASH_CODE = 'cash';//tien
    const DEFAULT_CUSTOMER = 1;//khách mặc định khi bán hàng
    const SELL_SHIPPING = 'ship';//Đang ship cho khách
    const SELL_PAID = 'paid';//Khách hàng đã thanh toán
    const SELL_DONE = 'done';//Hoàn thành (kế toán đã thu tiền)
    const SELL_TEXT = 'Bán hàng';
    const SELL_RETURN_TEXT = 'Trả lại';
    const PAYMENT_METHOD_BANK = 'bank';//thanh toán chuyển khoản
    const PAYMENT_METHOD_BANK_TEXT = 'Chuyển khoản';
    const PAYMENT_METHOD_CASH = 'cash';//thanh toán tiền mặt
    const PAYMENT_METHOD_CASH_TEXT = 'Tiền mặt';
    const DEBT_IN = 'in';//tang cong no
    const DEBT_OUT = 'out';//giam cong no
    const PURCHASE_RETURN = 'Trả NCC';
    const PURCHASE_RETURN_CODE = 'purchase_return';
    const PAYMENTS_CODE = 'payments';
    const PURCHASE_PAYMENTS = 'Thanh toán';
    const STATUS_PROCESS = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';//đã thanh toán

    //define image path
    const CUSTOMER_IMAGE_PATH='/assets/customer/images/';
    const NO_IMAGE_PATH = '/assets/images/no-image.jpg';
}