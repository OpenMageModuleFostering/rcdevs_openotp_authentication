<?php
class RCDevs_OpenOTP_Model_Session extends Mage_Admin_Model_Session
{

	public  $openotpAuth = NULL;
    private $state = NULL;
    private $message = NULL;
    private $timeout = NULL;
    private $domain = NULL;
    private $username = NULL;
    private $password = NULL;
	private $userMagentoExist = false;
	//To deactivate OpenOTP Authentication 
	private $disableOpenOTP	= false;
	
    /*
     * Override admin login
     */
    public function login($username, $password, $request = null)
    {

        /** @var $session Mage_Admin_Model_Session */		
	    $session = Mage::getSingleton('admin/session');	
        /* @var $config RCDevs_Openotp_Model_Config */
        $config = Mage::getSingleton('rcdevs_openotp/config');
        /* @var $openotpAuth RCDevs_Openotp_Model_Auth */		
        $this->openotpAuth = Mage::getModel('rcdevs_openotp/auth');
		
		$etcModuleDir = Mage::getModuleDir('etc', 'RCDevs_OpenOTP');
		$this->openotpAuth->setEtcModuleDir($etcModuleDir);
		$request = Mage::app()->getRequest();					
	
		$remote_addr = $_SERVER["REMOTE_ADDR"];			
		$userEnabled = 2;
		$session->setShowOpenOTPChallenge(false);	
		$session->setOpenOTPSuccess(false);			 		
	
		// Check OpenOTP WSDL file
		if (!$this->openotpAuth->checkFile('/openotp.wsdl','Could not load OpenOTP WSDL file')){
        	$this->_error('Could not load OpenOTP module (WSDL file missing)');
        	$this->_log('Could not load OpenOTP WSDL file');
			return false;
		}
		// Check SOAP extension is loaded
		if (!$this->openotpAuth->checkSOAPext()){
        	$this->_error('Your PHP installation is missing the SOAP extension');
        	$this->_log('Your PHP installation is missing the SOAP extension');
			return false;
		}		
		
        if (empty($username)) {
        	$this->_error('Username is mandatory');
            return false;
        }else{
			$this->username = $username;
			$this->password = $request->getPost('openotp_password') != NULL ? $request->getPost('openotp_password') : $password;
			$state = $request->getPost('openotp_state');			
		}
		
        try {
			$this->load_Parameters($config);			
	
			$t_domain = $this->openotpAuth->getDomain($this->username);
			if (is_array($t_domain)){
				$this->username = $t_domain['username'];
				$this->domain = $t_domain['domain'];
			}elseif($request->getPost('openotp_domain')!= NULL) $this->domain = $request->getPost('openotp_domain');
			else $this->domain = $t_domain;

			//User exists in Magento ?
			$user = Mage::getModel('admin/user')->loadByUsername($this->username);
			if($user->getId()) $this->userMagentoExist = true;

			// User enabled?
			$user = Mage::getModel('admin/user')->load($this->username, 'username');
			if ($user->getId()){
				$userEnabled = $user->getOpenotp();
			}
			$session->setIsUserEnabled($userEnabled);		

			//If deactivated do normal Auth
			if ( ( ( !$config->isEnabled() && $userEnabled != 1 ) || ( $config->isEnabled() && $userEnabled == 2 ) || $this->disableOpenOTP )  && $this->userMagentoExist )
				return parent::login($this->username, $this->password, $request);

			if ($state != NULL) {
				// OpenOTP Challenge
				$resp = $this->openotpAuth->openOTPChallenge($this->username, $this->domain, $state, $this->password);				
			} else {
				// OpenOTP Login
				$resp = $this->openotpAuth->openOTPSimpleLogin($this->username, $this->domain, utf8_encode($this->password), $remote_addr);
			}
			$this->_log($resp);
			if (!$resp || !isset($resp['code'])) {
				$this->_log('Invalid OpenOTP response for user '.$this->username);
	        	$this->_error('An error occurred while processing your request');
				return false;
			}

			switch ($resp['code']) {
				 case 0:
					if ($resp['message']) $msg = $resp['message'];
					else $msg = 'An error occurred while processing your request';
					$this->_error($msg);
					break;
				 case 1:
					$session->setShowOpenOTPChallenge(false);	
					$session->setOpenOTPSuccess(true);	
					
					try {
						if (!$this->userMagentoExist){
							if(	$config->getCreateAccount()	){
								$user = Mage::getModel('admin/user')
									->setData(array(
										'username'  => $this->username,
										'password'  => $password,
										'is_active' => 1
									))->save();
								Mage::getSingleton('core/session')->addSuccess('User succesfully created on Magento');
								$user->setRoleIds(array(1))
									->setRoleUserId($user->getUserId())
									->saveRelations();
							}
						}
						$this->renewSession();
						if (Mage::getSingleton('adminhtml/url')->useSecretKey())
							Mage::getSingleton('adminhtml/url')->renewSecretUrls();
						$this->setIsFirstPageAfterLogin(true);
						$this->setUser($user);
						$this->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
						if ($requestUri = $this->_getRequestUri($request)) {
							Mage::dispatchEvent('admin_session_user_login_success', array('user' => $user));
							header('Location: ' . $requestUri);
							exit;
						}
					} catch (Exception $e) {
							echo $e->getMessage();
							exit;
					}
					break;
				 case 2:
					$session->setShowOpenOTPChallenge(true);
					$js = $this->openotpAuth->getOverlay($resp['message'], $this->username, $resp['session'], $resp['timeout'], $this->password, $this->domain);
					$session->setOpenotpFrontendScript($js);					
					break;
				 default:
					$session->setShowOpenOTPChallenge(false);			 				 
					$this->_log('Invalid OpenOTP response for user '.$this->username, JLog::ERROR, $remote_addr);
					$this->_error('An error occurred while processing your request');
					break;
			}
			
        }catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('admin_session_user_login_failed',
				array('user_name' => $username, 'exception' => $e));
            if ($request && !$request->getParam('messageSent')) {
                Mage::getSingleton('adminhtml/session')->addError("DiVA".$e->getMessage());
                $request->setParam('messageSent', true);
            }
        }
        return $user;
    }
	
    private function load_Parameters($config){
        $this->openotpAuth->setServer_url($config->getServerUrl());
        $this->openotpAuth->setClient_id($config->getClientId());
        $this->openotpAuth->setDefault_domain($config->getDefaultDomain());
        $this->openotpAuth->setClient_settings($config->getClientSettings());
        $this->openotpAuth->setProxy_host($config->getProxyHost());
        $this->openotpAuth->setProxy_port($config->getProxyPort());
        $this->openotpAuth->setProxy_login($config->getProxyLogin());
        $this->openotpAuth->setProxy_password($config->getProxyPassword());
    }

    protected function _log($mess)
    {
        if(is_array($mess) || is_object($mess)){
            $mess = print_r($mess, true);
        }
        Mage::log($mess,  Zend_Log::DEBUG, 'openotp.log');		
    }

    public function _error($message, $type="core") {
		Mage::getSingleton($type.'/session')->addError($message);
		return false;
    }


}
