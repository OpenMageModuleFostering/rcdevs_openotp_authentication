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
 * Abstraction for store config to fetch global openotp settings
 */
class RCDevs_OpenOTP_Model_Config extends Mage_Core_Model_Abstract
{

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_ENABLED = 'admin/openotp/enabled';

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_SERVER_URL = 'admin/openotp/openotp_server_url';

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_CLIENT_ID = 'admin/openotp/openotp_client_id';
	
    /**
     * @var string
     */
    const XML_PATH_OPENOTP_CREATE_ACCOUNT = 'admin/openotp/openotp_create_account';

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_DEFAULT_DOMAIN = 'admin/openotp/openotp_default_domain';

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_CLIENT_SETTINGS = 'admin/openotp/openotp_client_settings';

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_PROXY_HOST = 'admin/openotp/openotp_proxy_host';

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_PROXY_PORT = 'admin/openotp/openotp_proxy_port';

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_PROXY_LOGIN = 'admin/openotp/openotp_proxy_login';

    /**
     * @var string
     */
    const XML_PATH_OPENOTP_PROXY_PASSWORD = 'admin/openotp/openotp_proxy_password';
	
    /**
     * @var string
     */
    const XML_PATH_OPENOTP_LOG_ENABLED = 'admin/openotp/log_enabled';


    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_ENABLED) == 1;
    }

    /**
     * @return string
     */
    public function getServerUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_SERVER_URL);
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_CLIENT_ID);
    }

    /**
     * @return string
     */
    public function getCreateAccount()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_CREATE_ACCOUNT) == 1;
    }	

    /**
     * @return string
     */
    public function getDefaultDomain()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_DEFAULT_DOMAIN);
    }

    /**
     * @return string
     */
    public function getClientSettings()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_CLIENT_SETTINGS);
    }

    /**
     * @return string
     */
    public function getProxyHost()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_PROXY_HOST);
    }

    /**
     * @return string
     */
    public function getProxyPort()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_PROXY_PORT);
    }

    /**
     * @return string
     */
    public function getProxyLogin()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_PROXY_LOGIN);
    }

    /**
     * @return string
     */
    public function getProxyPassword()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_PROXY_PASSWORD);
    }

    /**
     * @return bool
     */
    public function isLogEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_OPENOTP_LOG_ENABLED) == 1;
    }
	

}
