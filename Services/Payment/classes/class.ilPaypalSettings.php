<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
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
	private $db;
	public $pSettings;

	private $settings;
	#private $settings_id;
	
	private $server_host;
	private $server_path;
	private $vendor;
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
	static public function getInstance()
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
	private function ilPaypalSettings()
	{
		global $ilDB;

		$this->db = $ilDB;
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
		$result->paypal = $this->pSettings->get('paypal');
		$data = array();
		if (is_object($result))
		{
			if ($result->paypal != "") $data = unserialize($result->paypal);
			else $data = array();
		}

		$this->setServerHost($data["server_host"]);
		$this->setServerPath($data["server_path"]);
		$this->setVendor($data["vendor"]);
		$this->setAuthToken($data["auth_token"]);
		$this->setPageStyle($data["page_style"]);
		$this->setSsl($data["ssl"]);
	}	
	
	/** 
	 * Fetches and sets the primary key of the payment settings
	 *
	 * @access	private
	 */
	private function fetchSettingsId()
	{
	
	}
	
	public function setSettingsId($a_settings_id = 0)
	{
	#	$this->settings_id = $a_settings_id;
	}
	
	public function getSettingsId()
	{
	#	return $this->settings_id;
	}
	
	public function setServerHost($a_server_host)
	{
		$this->server_host = $a_server_host;
	}
	
	public function getServerHost()
	{
		return $this->server_host;
	}
	
	public function setServerPath($a_server_path)
	{
		$this->server_path = $a_server_path;
	}
	
	public function getServerPath()
	{
		return $this->server_path;
	}
	
	public function setVendor($a_vendor)
	{
		$this->vendor = $a_vendor;
	}
	
	public function getVendor()
	{
		return $this->vendor;
	}
	
	public function setAuthToken($a_auth_token)
	{
		$this->auth_token = $a_auth_token;
	}
	
	public function getAuthToken()
	{
		return $this->auth_token;
	}
	
	public function setPageStyle($a_page_style)
	{
		$this->page_style = $a_page_style;
	}
	
	public function getPageStyle()
	{
		return $this->page_style;
	}
	
	public function setSsl($a_ssl)
	{
		$this->ssl = $a_ssl;
	}
	
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
	function getAll()
	{
		$values = array(
			"server_host" => $this->getServerHost(),
			"server_path" => $this->getServerPath(),
			"vendor" => $this->getVendor(),
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
	function clearAll()
	{
		$this->pSettings->set('paypal', NULL, 'paypal');
		$this->settings = array();
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
			"auth_token" => $this->getAuthToken(),
			"page_style" => $this->getPageStyle(),
			"ssl" => $this->getSsl()			
		);		

		$this->pSettings->set('paypal', serialize($values), 'paypal');
	}	
}
?>