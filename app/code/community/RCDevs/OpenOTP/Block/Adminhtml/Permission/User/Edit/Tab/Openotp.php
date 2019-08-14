<?php
/**
 * OpenOTP magento module
 *
 * LICENSE
 *
 * Copyright © 2013.
 * RCDevs OpenOTP. All rights reserved.
 *
 * The use and redistribution of this software, either compiled or uncompiled, with or without modifications are permitted provided that the following conditions are met:
 * *
 * @copyright Copyright (c) 201 RCDevs (http://www.rcdevs.com)
 * @author rcdevs <info@rcdevs.com>
 * @category RCDevs
 * @package RCDevs_OpenOTP
 */

/**
 * Additional tab for user permission configurartion
 */
class RCDevs_OpenOTP_Block_Adminhtml_Permission_User_Edit_Tab_Openotp
    extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('permissions_user');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('user_');

        $fieldset = $form->addFieldset('openotp_fieldset', array('legend' => Mage::helper('adminhtml')->__('Enable OpenOTP Two factors authentication for login')));		
		$fieldset->addField('openotp', 'select', array(
			  'label'       => Mage::helper('rcdevs_openotp')->__('Enable OpenOTP'),
			  'name'      => 'openotp',
			  'value'  => '0',
			  'values' => array('-1'=>Mage::helper('rcdevs_openotp')->__('Default...'),'1' => 'Yes','2' => 'No'),
			  'disabled' => false,
			  'readonly' => false,
			  'after_element_html' => '<div style="width:244px; background-position:8px 11px; padding:5px 0 5px 36px; margin-top: 3px;" class="notification-global notification-global-notice">Override [Enable OpenOTP] Plugin setting in System / Configuration</div>',			  
			));
		
        $data = $model->getData();		
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
