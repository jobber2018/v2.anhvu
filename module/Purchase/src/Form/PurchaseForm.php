<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Purchase\Form;


use Laminas\Form\Form;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\Size;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

class PurchaseForm extends Form
{
    private $action;

    public function __construct()
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'purchaseForm',
            'class'=>'form-horizontal'
        ]);
//        $this->action = $action;
        $this->addElements();
        $this->validator();
    }


    private function addElements()
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
                'id'=>'name'
            ],
            'options'=>[
                'label'=>'Tên sản phẩm:',
                'label_attributes'=>[
                    'for' => 'name',
                    'class'=>'col-md-3 control-label'
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
                        'min'=>2,
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