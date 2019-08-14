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
 * Hooks into every adminhtml controller and checks if yubikey is enabled.
 * Forwards not authorized yubikey enabled users to yubikey login form.
 */
class RCDevs_OpenOTP_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function controllerActionPredispatch(Varien_Event_Observer $observer)
    {	
		$request = Mage::app()->getRequest();
        /** @var $session Mage_Admin_Model_Session */				
		$session = Mage::getSingleton('admin/session');
	
		/* @var $request Mage_Core_Controller_Request_Http */
		if (  $request->getRequestedControllerName() == 'index' && $request->getRequestedActionName() == 'login' ){
			$request->setControllerName('openotp')
					->setActionName('login')
					->setDispatched(false);		
		}
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addOpenOTPTabToUserPermissionForm(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
		
        /* @var $block Mage_Adminhtml_Block_Permissions_User_Edit_Tabs */
        if ($block instanceof Mage_Adminhtml_Block_Permissions_User_Edit_Tabs) {
            $tabData = array(
                'label'     => Mage::helper('rcdevs_openotp')->__('OpenOTP setup'),
                'title'     => Mage::helper('rcdevs_openotp')->__('OpenOTP setup'),
                'content'   => $block->getLayout()->createBlock('rcdevs_openotp/adminhtml_permission_user_edit_tab_openotp')->toHtml(),
                'active'    => true
            );
            if (method_exists($block, 'addTabAfter')) {
                // >= CE 1.6
                $block->addTabAfter('openotp_section', $tabData, 'roles_section');
            } else {
                $block->addTab('openotp_section', $tabData);
            }
        }
    }
}
