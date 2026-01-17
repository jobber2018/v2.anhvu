<?php
/**
 * Created by PhpStorm.
 * User: truonghm
 * Date: 2019-07-24
 * Time: 13:51
 */

namespace Customer\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Sulde\Service\Common\ConfigManager;

class CustomerForm extends Form
{
    private $group;
    private $route;

    public function __construct($p_groupData,$p_routeData)
    {
        parent::__construct();
        $this->setAttributes([
            'name'=>'customer-form',
            'class'=>'form-horizontal'
        ]);

        //format data to select group
        $groupData=array();
        foreach ($p_groupData as $groupItem){
            $groupTmp = array(
                'label'=>$groupItem->getName(),
                'value'=>$groupItem->getId(),
                'attributes'=>array(
                    'data-id'=>$groupItem->getId(),
                    'data-code'=>$groupItem->getCode(),
                    'data-name'=>htmlspecialchars($groupItem->getName(), ENT_QUOTES, 'UTF-8')
                )
            );
            $groupData[]=$groupTmp;
        }
        $this->group = $groupData;

        //format data to select route
        $routeData=array();
        foreach ($p_routeData as $routeItem){
            $label = '<label class="text">'.$routeItem->getName().'</label>';
            $label .= ' <small class="badge badge-info"><i class="far fa-user"></i> '.$routeItem->getUser()->getUsername().'</small>';
            $label.=' <small class="badge badge-success"><i class="far fa-clock"></i> '.ConfigManager::getDay($routeItem->getDay()).'</small>';
            $routeTmp = array(
                'label'=>$routeItem->getName(),
                'value'=>$routeItem->getId(),
                'attributes'=>array(
                    'data-id'=>$routeItem->getId(),
                    'data-html'=>$label,
                    'data-uid'=>$routeItem->getUser()->getId(),
                    'data-username'=>$routeItem->getUser()->getUsername(),
                    'data-day'=>ConfigManager::getDay($routeItem->getDay()),
                    'data-name'=>htmlspecialchars($routeItem->getName(), ENT_QUOTES, 'UTF-8')
                )
            );
            $routeData[]=$routeTmp;
        }
        $this->route=$routeData;

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
                'placeholder'=>'Nhập tên cửa hàng.',
                'id'=>'name'
            ],
            'options'=>[
                'label'=>'Tên cửa hàng',
                'label_attributes'=>[
                    'for' => 'name',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //customer_name
        $this->add([
            'type'=>'text',
            'name'=>'owner_name',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập tên chủ cửa hàng.',
                'id'=>'owner_name'
            ],
            'options'=>[
                'label'=>'Tên chủ cửa hàng',
                'label_attributes'=>[
                    'for' => 'owner_name',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //mobile
        $this->add([
            'type'=>'text',
            'name'=>'mobile',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Nhập số điện thoại chủ cửa hàng.',
                'id'=>'mobile'
            ],
            'options'=>[
                'label'=>'Điện thoại',
                'label_attributes'=>[
                    'for' => 'mobile',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //lat
        $this->add([
            'type'=>'hidden',
//            'type'=>'text',
            'name'=>'lat',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Input lat',
                'id'=>'lat'
            ],
            'options'=>[
                'label'=>'Lat (*):',
                'label_attributes'=>[
                    'for' => 'lat',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //lng
        $this->add([
            'type'=>'hidden',
//            'type'=>'text',
            'name'=>'lng',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Input lng',
                'id'=>'lng'
            ],
            'options'=>[
                'label'=>'Lng (*):',
                'label_attributes'=>[
                    'for' => 'lng',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //delivery_note
        $this->add([
            'type'=>'text',
            'name'=>'delivery_note',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Giờ giao hàng, vận chuyển bằng oto hay xe máy...',
                'id'=>'delivery_note'
            ],
            'options'=>[
                'label'=>'Ghi chú giao hàng:',
                'label_attributes'=>[
                    'for' => 'delivery_note',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //note
        $this->add([
            'type'=>'textarea',
            'name'=>'note',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Ghi chú khác về khách hàng...',
                'id'=>'note'
            ],
            'options'=>[
                'label'=>'Ghi chú:',
                'label_attributes'=>[
                    'for' => 'note',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //address
        $this->add([
            'type'=>'text',
            'name'=>'address',
            'attributes'=>[
                'class'=>'form-control',
                'placeholder'=>'Địa chỉ',
                'id'=>'address'
            ],
            'options'=>[
                'label'=>'Địa chỉ:',
                'label_attributes'=>[
                    'for' => 'address',
                    'class'=>'col-md-3 control-label'
                ]
            ]
        ]);

        //group
        $this->add([
            'type'=>'select',
            'name'=>'group',
            'attributes'=>[
                'class'=>'form-control select',
                'data-live-search'=>'true',
                'placeholder'=>'Nhóm khách hàng.',
                'id'=>'group'
            ],
            'options'=>[
                'label'=>'Nhóm',
                'label_attributes'=>[
                    'for' => 'group',
                    'class'=>'col-md-3 control-label'
                ],
                'value_options'=>$this->group
            ]
        ]);

        //route
        $this->add([
            'type'=>'select',
            'name'=>'route',
            'attributes'=>[
                'class'=>'form-control select',
                'data-live-search'=>'true',
                'placeholder'=>'Tuyến chắm sóc.',
                'id'=>'route'
            ],
            'options'=>[
                'label'=>'Tuyến',
                'label_attributes'=>[
                    'for' => 'route',
                    'class'=>'col-md-3 control-label'
                ],
                'value_options'=>$this->route
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

        //Name
        $inputFilter->add([
            'name'=>'customer_name',
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
                            NotEmpty::IS_EMPTY=>'Tên cửa hàng không được để trống'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>200,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Tên cửa hàng tối thiểu %min% ký tự',
                            StringLength::TOO_LONG=>'Tên cửa hàng dài không quá %max% ký tự'
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
                            NotEmpty::IS_EMPTY=>'Địa chỉ cửa hàng không được để trống.'
                        ]
                    ]
                ],
                [
                    'name'=>'StringLength',
                    'options'=>[
                        'min'=>8,
                        'max'=>100,
                        'messages'=>[
                            StringLength::TOO_SHORT=>'Address least %min% characters',
                            StringLength::TOO_LONG=>'Address no more than %max% characters'
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
                            NotEmpty::IS_EMPTY=>'Điện thoại chủ cửa hàng không được để trống.'
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

        //Owner customer
        $inputFilter->add([
            'name'=>'customer_name',
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
                            NotEmpty::IS_EMPTY=>'Tên chủ cửa hàng không được để trống.'
                        ]
                    ]
                ]
            ]
        ]);
    }
}