<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Nadia Ahmad <nahmad@databay.de> 
* @author Jens Conze <jc@databay.de> 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id: class.ilPaypalSettings.php 22133 2009-10-16 08:09:11Z nkrzywon $
* 
* 
* @ingroup payment
*/
include_once './Services/Payment/classes/class.ilPaymentSettings.php';

class ilPaypalSettings
{
	public $pSettings;

//	private $settings;
	
	private $server_host;
	private $server_path;
	private $vendor;
//	private $vender_password;
	private $auth_token;
	private $page_style;
	private $ssl;
	
	static private $instance = null;
	
	/**
	* Static method to get the singleton instance
	* 
	* @access	public
	* @return	object $instance Singular ilPaypalSettings instance
	*/
	public static function getInstance()
	{
		if (!self::$instance)
		{
	    	self::$instance = new ilPaypalSettings();
	    }
	    
	    return self::$instance;	    	    
	}

	/**
	* Constructor
	* 
	* @access	private
	*/
	private function __construct()
	{
		$this->pSettings = ilPaymentSettings::_getInstance();
		$this->getSettings();
	}
	
	/** 
	 * Called from constructor to fetch settings from database
	 *
	 * @access	private
	 */
	private function getSettings()
	{
		$paypal = null;
		$paypal = $this->pSettings->get('paypal');
		$data = array();

		if ($paypal != "" && $paypal != NULL ) 
		{
			$data = unserialize($paypal);
		}

		$this->setServerHost($data["server_host"]);
		$this->setServerPath($data["server_path"]);
		$this->setVendor($data["vendor"]);
//		$this->setVendorPassword($data['vendor_password']);
		$this->setAuthToken($data["auth_token"]);
		$this->setPageStyle($data["page_style"]);
		$this->setSsl($data["ssl"]);
	}

	/**
	 * @param string $a_server_host
	 */
	public function setServerHost($a_server_host)
	{
		$this->server_host = $a_server_host;
	}

	/**
	 * @return mixed
	 */
	public function getServerHost()
	{
		return $this->server_host;
	}

	/**
	 * @param string $a_server_path
	 */
	public function setServerPath($a_server_path)
	{
		$this->server_path = $a_server_path;
	}

	/**
	 * @return mixed
	 */
	public function getServerPath()
	{
		return $this->server_path;
	}

	/**
	 * @param string $a_vendor
	 */
	public function setVendor($a_vendor)
	{
		$this->vendor = $a_vendor;
	}

	/**
	 * @return mixed
	 */
	public function getVendor()
	{
		return $this->vendor;
	}

	/**
	 * @param string $a_vendor_password
	 */
	public function setVendorPassword($a_vendor_password)
	{
		$this->vender_password = $a_vendor_password;
	}

	/**
	 * @return mixed
	 */
	public function getVendorPassword()
	{
		return $this->vender_password;
	}
	/**
	 * @param string $a_auth_token
	 */
	public function setAuthToken($a_auth_token)
	{
		$this->auth_token = $a_auth_token;
	}

	/**
	 * @return mixed
	 */
	public function getAuthToken()
	{
		return $this->auth_token;
	}

	/**
	 * @param string$a_page_style
	 */
	public function setPageStyle($a_page_style)
	{
		$this->page_style = $a_page_style;
	}

	/**
	 * @return mixed
	 */
	public function getPageStyle()
	{
		return $this->page_style;
	}

	/**
	 * @param string $a_ssl
	 */
	public function setSsl($a_ssl)
	{
		$this->ssl = $a_ssl;
	}

	/**
	 * @return mixed
	 */
	public function getSsl()
	{
		return $this->ssl;
	}
	
	/** 
	 * Returns array of all paypal settings
	 * 
	 * @access	public
	 * @return	array $values Array of all paypal settings
	 */
	public function getAll()
	{
		$values = array(
			"server_host" => $this->getServerHost(),
			"server_path" => $this->getServerPath(),
			"vendor" => $this->getVendor(),
//			"vendor_password" => $this->getVendorPassword(),
			"auth_token" => $this->getAuthToken(),
			"page_style" => $this->getPageStyle(),
			"ssl" => $this->getSsl()
		);
		
		return $values;
	}

	/** 
	 * Clears the payment settings for the paypal payment method 
	 *
	 * @access	public
	 */
	public function clearAll()
	{
		$this->pSettings->set('paypal', NULL, 'paypal');
//		$this->settings = array();
	}
		
	/** 
	 * Inserts or updates (if payment settings already exist) the paypal settings data
	 *
	 * @access	public
	 */
	public function save()
	{
		$values = array(
			"server_host" => $this->getServerHost(),
			"server_path" => $this->getServerPath(),
			"vendor" => $this->getVendor(),
//			"vendor_password" => $this->getVendorPassword(),
			"auth_token" => $this->getAuthToken(),
			"page_style" => $this->getPageStyle(),
			"ssl" => $this->getSsl()			
		);		

		$this->pSettings->set('paypal', serialize($values), 'paypal');
	}	
}