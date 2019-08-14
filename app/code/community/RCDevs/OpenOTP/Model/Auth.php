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
 * openOTP service class
 */
class RCDevs_OpenOTP_Model_Auth extends Zend_Service_Abstract
{

	private $etcModuleDir;
	private $server_url;
	private $client_id;
	private $default_domain;
	private $client_settings;                                                                           
	private $proxy_host;                                                                              
	private $proxy_port;                                                                              
	private $proxy_username;
	private $proxy_password;
	private $soap_client;


	/**
	* Check if File exists
	*
	* @param string $file
	* @return bool
	*/
	public function checkFile($file)
	{
		if (!file_exists($this->etcModuleDir . '/'.$file)) {
			return false;
		}
		return true;
	}
	
	/**
	* Check if SOAP extension loaded
	*
	* @return bool
	*/
	public function checkSOAPext()
	{
		if (!extension_loaded('soap')) {
			return false;
		}
		return true;
	}

		
	public function getDomain($username)
	{
		$pos = strpos($username, "\\");
		if ($pos) {
			$ret['domain'] = substr($username, 0, $pos);
			$ret['username'] = substr($username, $pos+1);
		} else {                                                                                                                      
			$ret = $this->default_domain;
		}
		return $ret;
	}
	
	public function getOverlay($message, $username, $session, $timeout, $ldappw, $domain){
		$overlay = <<<EOT
		function addOpenOTPDivs(){
			var overlay_bg = document.createElement("div");
			overlay_bg.id = 'openotp_overlay_bg';
			overlay_bg.style.position = 'fixed'; 
			overlay_bg.style.top = '0'; 
			overlay_bg.style.left = '0'; 
			overlay_bg.style.width = '100%'; 
			overlay_bg.style.height = '100%'; 
			overlay_bg.style.background = 'grey';
			overlay_bg.style.zIndex = "9998"; 
			overlay_bg.style["filter"] = "0.9";
			overlay_bg.style["-moz-opacity"] = "0.9";
			overlay_bg.style["-khtml-opacity"] = "0.9";
			overlay_bg.style["opacity"] = "0.9";
		
			var overlay = document.createElement("div");
			overlay.id = 'openotp_overlay';
			overlay.style.position = 'absolute'; 
			overlay.style.top = '165px'; 
			overlay.style.left = '50%'; 
			overlay.style.width = '280px'; 
			overlay.style.marginLeft = '-180px';
			overlay.style.padding = '65px 40px 50px 40px';
			overlay.style.background = 'url('+path+'openotp_banner.png) no-repeat top left #E4E4E4';
			overlay.style.border = '5px solid #545454';
			overlay.style.borderRadius = '10px';
			overlay.style.MozBorderRadius = '10px';
			overlay.style.WebkitBorderRadius = '10px';
			overlay.style.boxShadow = '1px 1px 12px #555555';
			overlay.style.WebkitBoxShadow = '1px 1px 12px #555555';
			overlay.style.MozBoxShadow = '1px 1px 12px #555555';
			overlay.style.zIndex = "9999"; 
			overlay.innerHTML = '<a style="position:absolute; top:-12px; right:-12px;" href="$_SERVER[PHP_SELF]" title="close"><img src="'+path+'openotp_closebtn.png"/></a>'
			+ '<div style="background-color:red; margin:0 -40px 0; height:4px; width:360px; padding:0;" id="count_red"><div style="background-color:orange; margin:0; height:4px; width:360px; padding:0;" id="div_orange"></div></div>'
			+ '<form id="loginForm" autocomplete="off" style="margin-top:30px; display:block;" action="" method="POST">'
			+ '<input type="hidden" name="form_key" value="'+token+'">'
            + '<input type="hidden" id="username" name="login[username]" value="$username">'
            + '<input type="hidden" id="login" name="login[password]" class="required-entry input-text" value="$ldappw" />'			
			+ '<input type="hidden" name="openotp_state" value="$session">'
			+ '<input type="hidden" name="openotp_domain" value="$domain">'
			+ '<table width="100%">'
			+ '<tr><td style="text-align:center; font-weight:bold; font-size:14px;">$message</td></tr>'
			+ '<tr><td id="timout_cell" style="text-align:center; padding-top:4px; font-weight:bold; font-style:italic; font-size:11px;">Timeout: <span id="timeout">$timeout seconds</span></td></tr>'
			+ '<tr><td id="inputs_cell" style="text-align:center; padding-top:25px;"><input class="required-entry input-text"  type="text" size=15 name="openotp_password">&nbsp;'
			+ '<input style="padding:3px 10px;" type="submit" value="Ok" class="form-button"></td></tr>'
			+ '</table></form>';
			
			document.body.appendChild(overlay_bg);    
			document.body.appendChild(overlay); 
			document.forms.loginForm.openotp_password.focus();
		}
		
		addOpenOTPDivs();
		
		/* Compute Timeout */	
		var c = $timeout;
		var base = $timeout;
		function count()
		{
			plural = c <= 1 ? "" : "s";
			document.getElementById("timeout").innerHTML = c + " second" + plural;
			var div_width = 360;
			var new_width =  Math.round(c*div_width/base);
			document.getElementById('div_orange').style.width=new_width+'px';
			
			if(c == 0 || c < 0) {
				c = 0;
				clearInterval(timer);
				document.getElementById("timout_cell").innerHTML = " <b style='color:red;'>Login timedout!</b> ";
				document.getElementById("inputs_cell").innerHTML = "<input style='padding:3px 20px;' type='button' value='Retry' class='button mainaction' onclick='window.location.href=\"$_SERVER[PHP_SELF]\"'>";
			}
			c--;
		}
		count();
		var timer = setInterval(function() {count(); }, 1000);
EOT;

		return $overlay;
	}
	
	private function soapRequest(){

		$options = array('location' => $this->server_url);
		if ($this->proxy_host != NULL && $this->proxy_port != NULL) {
			$options['proxy_host'] = $this->proxy_host;
			$options['proxy_port'] = $this->proxy_port;
			if ($this->proxy_username != NULL && $this->proxy_password != NULL) {
				$options['proxy_login'] = $this->proxy_username;
				$options['proxy_password'] = $this->proxy_password;
			}
		}
			
		$soap_client = new SoapClient($this->etcModuleDir.'/openotp.wsdl', $options);
		if (!$soap_client) {
			return false;
		}
		$this->soap_client = $soap_client;	
		return true;
	}
		
	public function openOTPSimpleLogin($username, $domain, $password, $remote_add){
		if (!$this->soapRequest()) return false;
		$resp = $this->soap_client->openotpSimpleLogin($username, $domain, $password, $this->client_id, $remote_add, $this->client_settings);

		return $resp;
	}
	
	public function openOTPChallenge($username, $domain, $state, $password){
		if (!$this->soapRequest()) return false;
		$resp = $this->soap_client->openotpChallenge($username, $domain, $state, $password);
		
		return $resp;
	}
	
	public function setEtcModuleDir($dir)
	{
		$this->etcModuleDir = $dir;
	}
	
	public function setServer_url($server_url)
	{
		$this->server_url = $server_url;
	}

	public function getServer_url()
	{
		return $this->server_url;
	}

	public function setClient_id($client_id)
	{
		$this->client_id = $client_id;
	}

	public function getClient_id()
	{
		return $this->client_id;
	}

	public function setDefault_domain($default_domain)
	{
		$this->default_domain = $default_domain;
	}

	public function getDefault_domain()
	{
		return $this->default_domain;
	}

	public function setClient_settings($client_settings)
	{
		$this->client_settings = $client_settings;
	}

	public function getClient_settings()
	{
		return $this->client_settings;
	}
	
	public function setProxy_host($proxy_host)
	{
		$this->proxy_host = $proxy_host;
	}

	public function getProxy_host()
	{
		return $this->proxy_host;
	}

	public function setProxy_port($proxy_port)
	{
		$this->proxy_port = $proxy_port;
	}

	public function getProxy_port()
	{
		return $this->proxy_port;
	}
	
	public function setProxy_login($proxy_login)
	{
		$this->proxy_login = $proxy_login;
	}

	public function getProxy_login()
	{
		return $this->proxy_login;
	}
	
	public function setProxy_password($proxy_password)
	{
		$this->proxy_password = $proxy_password;
	}

	public function getProxy_password()
	{
		return $this->proxy_password;
	}

}