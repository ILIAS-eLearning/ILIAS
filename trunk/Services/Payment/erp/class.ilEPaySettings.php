<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* @author Nicolai Lundgaard <nicolai@ilias.dk>
* @author Jesper Godvad <jesper@ilias.dk>
* @version $Id: class.ilPaypalSettings.php 19533 2009-04-03 10:23:37Z nkrzywon $
* 
* 
* @ingroup payment
*/

include_once './Services/Payment/classes/class.ilPaymentSettings.php';

class ilEPaySettings
{
	private $db;
	public $pSettings;

//	private $settings;
//	private $settings_id;
	
	private $server_host;
	private $server_path;
	private $vendor;
	private $auth_token;
	private $page_style;
	private $merchant_number;
	private $ssl;
	private $instant_capture;
	
	static private $instance = null;
	
	/**
	* Static method to get the singleton instance
	* 
	* @access	public
	* @return	object $instance Singular ilBMFSettings instance
	*/
	static public function getInstance()
	{
	   if (!self::$instance)
		{
		  self::$instance = new ilEPaySettings();
	    }
	    
	    return self::$instance;	    	    
	}

	/**
	* Constructor
	* 
	*/
	private function __construct()
	{
		$this->pSettings = ilPaymentSettings::_getInstance();
		$this->getSettings();
	}

	
	/**
	* This function validates data received from ePay, and verifies the md5key is valid
	* @return bool
	*/
  public function validateEPayData($amount, $orderid, $transactionid, $md5Key)
  {
    $this->getSettings();
    $password = $this->getAuthToken();
    $strForValidate = "";
    $strForValidate = $amount . $orderid . $transactionid . $password;
    if (md5($strForValidate) == $md5Key) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
  * This function generates a valid MD5 key on the data about to be transmitted
  * to ePay. ePay will again verify the data and if the data is not valid and
  * error will be thrown (redirected to decline)
  */
  public function generatekeyForEpay($cur, $amount, $orderid) 
  {
    $this->getSettings();
    $password = $this->getAuthToken();
    return md5($cur . $amount . $orderid . $password);
  }
	
	
	/** 
	 * Called from constructor to fetch settings from database
	 *
	 * @access	private
	 */
	private function getSettings()
	{
		
		$result->epay = $this->pSettings->get('epay');

//    global $ilDB;
//
//	$this->fetchSettingsId();
//
//		$res = $ilDB->queryf('
//			SELECT epay FROM payment_settings WHERE settings_id = %s',
//			array('integer'), array($this->getSettingsId()));
//
//		$result = $res->fetchRow(DB_FETCHMODE_OBJECT);
	
		$data = array();
		if (is_object($result))
		{
			if ($result->epay != "") $data = unserialize($result->epay);
			else $data = array();
		}
		
		$this->setAll($data);
	}	
	
	/** 
	 * Fetches and sets the primary key of the payment settings
	 *
	 * @access	private
	 */
	private function fetchSettingsId()
	{
//		global $ilDB;
//
//		$res = $ilDB->query('SELECT * FROM payment_settings');
//
//		$result = $res->fetchRow(DB_FETCHMODE_OBJECT);
//
//		$this->setSettingsId($result->settings_id);
	}
	
	public function setSettingsId($a_settings_id = 0)
	{
//		$this->settings_id = $a_settings_id;
	}
	
	public function getSettingsId()
	{
//		return $this->settings_id;
	}
	
	public function setServerHost($a_server_host)
	{
		$this->server_host = $a_server_host;
	}
	
	private function getServerHost()
	{
		return $this->server_host;
	}
	
	public function setServerPath($a_server_path)
	{
		$this->server_path = $a_server_path;
	}
	
	private function getServerPath()
	{
		return $this->server_path;
	}
	
	public function setMerchantNumber($a_merchant_number)
	{
		$this->merchant_number = (int) $a_merchant_number;
	}
	
	private function getMerchantNumber()
	{
		return $this->merchant_number;
	}
	
	public function setAuthToken($a_auth_token)
	{
		$this->auth_token = $a_auth_token;
	}
	
	private function getAuthToken()
	{
		return $this->auth_token;
	}
	

	public function setAuthEmail($a_auth_email)
	{
		$this->auth_email = $a_auth_email;
	}
	
	private function getAuthEmail()
	{
		return $this->auth_email;
	}

	public function setInstantCapture($a_instant_capture)
	{
    if ((!$a_instant_capture == 1) || (!$a_instant_capture== '1')) $a_instant_capture=0; else $a_instant_capture=1;
		$this->instant_capture = $a_instant_capture;
	}
	
	private function getInstantCapture()
	{
		return $this->instant_capture;
	}
	
	
	/** 
	 * Returns array of all epay settings
	 * 
	 * @access	public
	 * @return	array $values Array of all epay settings
	 */
	public function getAll()
	{
		$values = array(
			"server_host" => $this->getServerHost(),
			"server_path" => $this->getServerPath(),
			"merchant_number" => (int) $this->getMerchantNumber(),
			"auth_token" => $this->getAuthToken(),
			"auth_email" => $this->getAuthEmail(),
			"instant_capture" => $this->getInstantCapture()
		);		
		return $values;
	}
	
	/**
	* Set all ePay settings using an array
	*/	
	public function setAll($a)
	{
    if (isset($a['server_host'])) $this->setServerHost($a['server_host']);
    if (isset($a['server_path'])) $this->setServerPath($a['server_path']);
    if (isset($a['merchant_number'])) $this->setMerchantNumber($a['merchant_number']);
    if (isset($a['auth_token'])) $this->setAuthToken($a['auth_token']);
    if (isset($a['auth_email'])) $this->setAuthEmail($a['auth_email']);
    if (isset($a['instant_capture'])) $this->setInstantCapture($a['instant_capture']);    
	}
	
	/**
	* Check if the current settings looks valid
	*/	
	public function valid()
	{
    $r = true;
    $a = $this->getAll();
    if ( ($a['server_host'] == '') || ($a['server_path'] == '') ) $r = false;
    if ( (int) $a['merchant_number'] <= 0 ) $r = false;
    if ( ((int) $a['instant_capture'] != 0 ) && ((int) $a['instant_capture'] != 1)) $r = false;
    return $r;
	}
	
	

	/** 
	 * Clears the payment settings for the epay payment method 
	 *
	 * @access	public
	 */
	function clearAll()
	{
		$this->pSettings->set('epay', NULL, 'epay');
		$this->settings = array();
//		$statement = $this->db->manipulateF('
//			UPDATE payment_settings
//			SET epay = %s
//			WHERE settings_id = %s',
//			array('text', 'integer'),
//			array('NULL', $this->getSettingsId()));
//
//
//		$this->settings = array();

	}
		
	/** 
	 * Inserts or updates (if payment settings already exist) the epay settings data
	 *
	 * @access	public
	 */
	public function save()
	{		
		$values = $this->getAll();
		$this->pSettings->set('epay', serialize($values), 'epay');
	
//	global $ilDB;
//		if ($this->getSettingsId())
//		{
//			$statement = $ilDB->manipulateF('
//				UPDATE payment_settings
//				SET epay = %s
//				WHERE settings_id = %s',
//				array('text', 'integer'),
//				array(serialize($values), $this->getSettingsId()));
//
//		}
//		else
//		{
//			$next_id = $ilDB->nextId('payment_settings');
//			$statement = $ilDB->manipulateF('
//				INSERT INTO payment_settings
//				( 	settings_id,
//					epay)
//				VALUES (%s, %s)',
//				array('integer','text'), array($next_id, serialize($values)));
//
//			$this->setSettingsId($next_id);			
//		}
	}	
}
?>