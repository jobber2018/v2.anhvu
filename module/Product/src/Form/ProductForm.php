<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Product\Form;


use Sulde\Service\Common\Common;
use Sulde\Service\Common\ConfigManager;
use Sulde\Service\Common\Define;
use Zend\Form\Form;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\Size;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;

class ProductForm extends Form
{
    public function __construct($p_cat,$p_unit)
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'product-form',
            'class'=>'form-horizontal'
        ]);
        $this->addElements($p_cat,$p_unit);
        $this->validator();
    }


    private function addElements($p_cat,$p_unit)
    {
        $this->add([
            'name'=>'imageFile',
            'attributes'=>[
                'type'=>'file',
                'id'=>'imageFile'
            ],
            'options'=>[
                'label'=>'Default image'
            ]
        ]);
        //Name
        $this->add([
            'type'=>'text',
            'name'=>'name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập tên sản phẩm',
                'autocomplete'=>'off',
                'id'=>'name'
            ],
            'options'=>[
                'label'=>'Tên sản phẩm (*)',
                'label_attributes'=>[
                    'for' => 'name',
                    'class'=>'control-label'
                ]
            ]
        ]);

        //Sku
        $this->add([
            'type'=>'text',
            'name'=>'sku',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Sku',
                'id'=>'sku'
            ],
            'options'=>[
                'label'=>'Sku',
                'label_attributes'=>[
                    'for' => 'sku',
                    'class'=>'control-label'
                ]
            ]
        ]);

        //norm min
        $this->add([
            'type'=>'number',
            'name'=>'norm_min',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'norm_min'
            ],
            'options'=>[
                'label'=>'Tôn kho tối thiểu',
                'label_attributes'=>[
                    'for' => 'norm_min',
                    'class'=>'control-label'
                ]
            ]
        ]);
        //norm max
        $this->add([
            'type'=>'number',
            'name'=>'norm_max',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'norm_max'
            ],
            'options'=>[
                'label'=>'Tôn kho tối đa',
                'label_attributes'=>[
                    'for' => 'norm_max',
                    'class'=>'control-label'
                ]
            ]
        ]);

        //norm input
        $this->add([
            'type'=>'number',
            'name'=>'norm_input',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'norm_input'
            ],
            'options'=>[
                'label'=>'Hạn mức nhập',
                'label_attributes'=>[
                    'for' => 'norm_input',
                    'class'=>'control-label'
                ]
            ]
        ]);

        //Route
        $this->add([
            'type'=>'select',
            'name'=>'categories',
            'attributes'=>[
                'class'=>'form-control select',
                'data-live-search'=>'true',
                'id'=>'categories'
            ],
            'options'=>[
                'label'=>'Phân loại:',
                'label_attributes'=>[
                    'for' => 'categories',
                    'class'=>'control-label'
                ],
                'value_options'=>$p_cat
            ]
        ]);

        //vat
        $this->add([
            'type'=>'select',
            'name'=>'vat_option',
            'attributes'=>[
                'class'=>'form-control select',
                'id'=>'vat_option'
            ],
            'options'=>[
                'label'=>'Thuế GTGT (%):',
                'label_attributes'=>[
                    'for' => 'vat_option',
                    'class'=>'control-label'
                ],
                'value_options'=>Common::getVatOptions()
            ]
        ]);
        $this->add([
            'type'=>'text',
            'name'=>'vat_value',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'vat_value',
                'value'=>'0',
                'style'=>'display:none'
            ],
            'options'=>[
                'label'=>'',
                'label_attributes'=>[
                    'for' => 'vat_value',
                    'class'=>'control-label'
                ]
            ]
        ]);
        $this->add([
            'type'=>'text',
            'name'=>'import_tax',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'import_tax'
            ],
            'options'=>[
                'label'=>'Thuế nhập khẩu (%)',
                'label_attributes'=>[
                    'for' => 'import_tax',
                    'class'=>'control-label'
                ]
            ]
        ]);
        $this->add([
            'type'=>'text',
            'name'=>'export_tax',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
                'id'=>'export_tax'
            ],
            'options'=>[
                'label'=>'Thuế xuất khẩu (%)',
                'label_attributes'=>[
                    'for' => 'export_tax',
                    'class'=>'control-label'
                ]
            ]
        ]);

        //base unit
        $this->add([
            'type'=>'select',
            'name'=>'unit',
            'attributes'=>[
                'class'=>'form-control select',
                'data-live-search'=>'true',
                'id'=>'unit'
            ],
            'options'=>[
                'label'=>'Đơn vị cơ bản:',
                'label_attributes'=>[
                    'for' => 'unit',
                    'class'=>'control-label'
                ],
                'value_options'=>$p_unit
            ]
        ]);

        //note
        $this->add([
            'type'=>'textarea',
            'name'=>'note',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'',
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
        //Keyword
        $this->add([
            'type'=>'text',
            'name'=>'keyword',
            'attributes'=>[
                'class'=>'form-control',
                'data-role'=>'tagsinput',
                'placeholder'=>'Từ khoá tìm kiếm nhanh sản phẩm.',
                'autocomplete'=>'off',
                'id'=>'keyword'
            ],
            'options'=>[
                'label'=>'Từ khoá tìm kiếm',
                'label_attributes'=>[
                    'for' => 'keyword',
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

        $this->validator();
    }

    private function validator()
    {
        $inputFilter = new InputFilter();

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
                            NotEmpty::IS_EMPTY=>'Tên sản phẩm không được để trống!'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>100,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Tên sản phẩm tối thiểu %min% ký tự',
                            StringLength::TOO_LONG=>'Tên sản phẩm không dài quá %max% ký tự'
                        ]
                    ]
                ]
            ]
        ]);

        $inputFilter->add([
            'name'=>'norm',
            'required'=>false
        ]);
        $inputFilter->add([
            'name'=>'norm_input',
            'required'=>false
        ]);
        $this->uploadInputFilter();
        $this->setInputFilter($inputFilter);
    }

    public function uploadInputFilter(){
        $fileUpload = new FileInput('imageFile');
        $fileUpload->setRequired(false);
        //fileSize
        $size = new Size(['max'=>20000*1024]); //200kB
        $size->setMessages([
            Size::TOO_BIG=>'File bạn chọn quá lớn, vui lòng chọn file có kích thước bé hơn %max%'
        ]);

        //MimeType
        //image/png, image/jpeg, image/jpg
        $mimeType = new MimeType('image/png, image/jpeg, image/jpg');
        $mimeType->setMessages([
            MimeType::FALSE_TYPE=>'Kiểu file %type% không được phép chọn',
            MimeType::NOT_DETECTED=>'MimeType không xác định',
            MimeType::NOT_READABLE => 'MineType không thể đọc'
        ]);

        $fileUpload->getValidatorChain()
            ->attach($size, true, 2)
            ->attach($mimeType,true,1);

        $inputFilter = new InputFilter();
        $inputFilter->add($fileUpload);
        $this->setInputFilter($inputFilter);
    }
}