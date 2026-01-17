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

class SupplierForm extends Form
{
    private $action;

    public function __construct($action = "add")
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'supplier-form',
            'class'=>'form-horizontal'
        ]);
        $this->action = $action;
        $this->addElements();
        $this->validator();
    }


    private function addElements()
    {
        //Name
        $this->add([
            'type'=>'text',
            'name'=>'name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập tên NCC.',
                'id'=>'name'
            ],
            'options'=>[
                'label'=>'Tên NCC',
                'label_attributes'=>[
                    'for' => 'name',
                    'class'=>'control-label'
                ]
            ]
        ]);
        //Short Name
        $this->add([
            'type'=>'text',
            'name'=>'short_name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Tên viết tắt NCC.',
                'id'=>'short_name'
            ],
            'options'=>[
                'label'=>'Tên viết tắt',
                'label_attributes'=>[
                    'for' => 'short_name',
                    'class'=>'control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'text',
            'name'=>'mobile',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập điện thoại NCC.',
                'id'=>'mobile'
            ],
            'options'=>[
                'label'=>'Điện thoại',
                'label_attributes'=>[
                    'for' => 'mobile',
                    'class'=>'control-label'
                ]
            ]
        ]);
        $this->add([
            'type'=>'text',
            'name'=>'email',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Email liên hệ.',
                'id'=>'email'
            ],
            'options'=>[
                'label'=>'Email',
                'label_attributes'=>[
                    'for' => 'email',
                    'class'=>'control-label'
                ]
            ]
        ]);
        $this->add([
            'type'=>'search',
            'name'=>'tax_code',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Mã số thuế.',
                'id'=>'tax_code'
            ],
            'options'=>[
                'label'=>'Mã số thuế',
                'label_attributes'=>[
                    'for' => 'tax_code',
                    'class'=>'control-label'
                ]
            ]
        ]);

        $this->add([
            'type'=>'textarea',
            'name'=>'notes',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Ghi chú.',
                'id'=>'notes'
            ],
            'options'=>[
                'label'=>'Ghi chú',
                'label_attributes'=>[
                    'for' => 'notes',
                    'class'=>'control-label'
                ]
            ]
        ]);
        $this->add([
            'type'=>'text',
            'name'=>'contact_person',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Người liên hệ.',
                'id'=>'contact_person'
            ],
            'options'=>[
                'label'=>'Người liên hệ',
                'label_attributes'=>[
                    'for' => 'contact_person',
                    'class'=>'control-label'
                ]
            ]
        ]);
        $this->add([
            'type'=>'text',
            'name'=>'address',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập địa chỉ NCC.',
                'id'=>'address'
            ],
            'options'=>[
                'label'=>'Địa chỉ',
                'label_attributes'=>[
                    'for' => 'address',
                    'class'=>'control-label'
                ]
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

        $inputFilter->add([
            'name'=>'name',
            'required'=>true,
            'filters'=>[
                ['name'=>'StringTrim'],
                ['name'=>'StringToLower'],
                ['name'=>'StripTags'],
                ['name'=>'StripNewlines']
            ],
            'validators'=>[
                [
                    'name'=>'NotEmpty',
                    'options'=>[
                        'break_chain_on_failure'=>true,
                        'messages'=>[
                            NotEmpty::IS_EMPTY=>'Tên NCC không được để trống'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>3,
                        'max'=>200,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Tên tối thiểu %min% ký tự',
                            StringLength::TOO_LONG=>'Tên dài không quá %max% ký tự'
                        ]
                    ]
                ]
            ]
        ]);

        //Address
        $inputFilter->add([
            'name'=>'address',
            'required'=>true,
            'filters'=>[
                ['name'=>'StringTrim'],
                ['name'=>'StringToLower'],
                ['name'=>'StripTags'],
                ['name'=>'StripNewlines']
            ],
            'validators'=>[
                [
                    'name'=>'NotEmpty',
                    'options'=>[
                        'break_chain_on_failure'=>true,
                        'messages'=>[
                            NotEmpty::IS_EMPTY=>'Địa chỉ không được để trống.'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>100,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Địa chỉ tối thiểu %min% ký tự',
                            StringLength::TOO_LONG=>'Địa chỉ tối đa %max% ký tự'
                        ]
                    ]
                ]
            ]
        ]);

        //Mobile
        $inputFilter->add([
            'name'=>'mobile',
            'required'=>true,
            'filters'=>[
                ['name'=>'StringTrim'],
                ['name'=>'StringToLower'],
                ['name'=>'StripTags'],
                ['name'=>'StripNewlines']
            ],
            'validators'=>[
                [
                    'name'=>'NotEmpty',
                    'options'=>[
                        'break_chain_on_failure'=>true,
                        'messages'=>[
                            NotEmpty::IS_EMPTY=>'Điện thoại không được để trống.'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>10,
                        'max'=>11,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Số điện thoại tối thiểu %min% số',
                            StringLength::TOO_LONG=>'Số điện thoại tối đa %max% số'
                        ]
                    ]
                ]
            ]
        ]);
    }
}