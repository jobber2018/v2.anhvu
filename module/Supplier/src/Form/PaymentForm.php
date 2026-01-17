<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Supplier\Form;


use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Sulde\Service\Common\ConfigManager;

class PaymentForm extends Form
{
    private $action;

    public function __construct($p_supplier)
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'payment-form',
            'class'=>'form-horizontal'
        ]);
        $this->addElements($p_supplier);
        $this->validator();
    }


    private function addElements($p_supplier)
    {
        //supplier
        $this->add([
            'type'=>'select',
            'name'=>'supplier',
            'attributes'=>[
                'class'=>'form-control select',
                'data-live-search'=>'true',
                'placeholder'=>'Nhập tên NCC.',
                'id'=>'supplier'
            ],
            'options'=>[
                'label'=>'Tên NCC',
                'label_attributes'=>[
                    'for' => 'supplier',
                    'class'=>'control-label'
                ],
                'value_options'=>$p_supplier
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'amount',
            'attributes'=>[
                'class'=>'form-control amount',
                'placeholder'=>'Số tiền thanh toán.',
                'autocomplete'=>'off',
                'id'=>'amount'
            ],
            'options'=>[
                'label'=>'Số tiền',
                'label_attributes'=>[
                    'for' => 'amount',
                    'class'=>'control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'date',
            'attributes'=>[
                'class'=>'form-control datetimepicker-input',
                'placeholder'=>'Ngày chứng từ.',
                'id'=>'date',
                'data-target'=>'#datetimepicker',
                'autocomplete'=>'off',
            ],
            'options'=>[
                'label'=>'Ngày chứng từ',
                'label_attributes'=>[
                    'for' => 'datetimepicker',
                    'class'=>'control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'select',
            'name'=>'method',
            'attributes'=>[
                'class'=>'form-control select',
                'placeholder'=>'Hình thức.',
                'id'=>'method'
            ],
            'options'=>[
                'label'=>'Hình thức',
                'label_attributes'=>[
                    'for' => 'method',
                    'class'=>'control-label'
                ],
                'value_options'=>ConfigManager::getPaymentMethod()
            ]
        ]);

        $this->add([
            'type'=>'textarea',
            'name'=>'note',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Ghi chú.',
                'id'=>'note'
            ],
            'options'=>[
                'label'=>'Ghi chú',
                'label_attributes'=>[
                    'for' => 'note',
                    'class'=>'control-label'
                ]
            ]
        ]);

        $this->add([
            'name'=>'file',
            'attributes'=>[
                'type'=>'file',
                'id'=>'file'
            ],
            'options'=>[
                'label'=>'Default image'
            ]
        ]);

        //btn
        $this->add([
            'type'=>'submit',
            'name'=>'btnSubmit',
            'attributes'=>[
                'class'=>'btn btn-success',
                'value'=>'Save',
                'id'=>'btnSubmit'
            ]
        ]);

    }

    private function validator()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
    }
}