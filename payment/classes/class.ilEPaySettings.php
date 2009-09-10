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
* @author Jens Conze <jc@databay.de> 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id: class.ilPaypalSettings.php 19533 2009-04-03 10:23:37Z nkrzywon $
* 
* 
* @ingroup payment
*/
class ilEPaySettings
{
	private $db;

	private $settings;
	private $settings_id;
	
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
	* @access	private
	*/
	private function ilEPaySettings()
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->getSettings();
	}
	
	/** 
	 * Called from constructor to fetch settings from database
	 *
	 * @access	private
	 */
	private function getSettings()
	{
		$this->fetchSettingsId();
		
		$res = $this->db->queryf('
			SELECT epay FROM payment_settings WHERE settings_id = %s',
			array('integer'), array($this->getSettingsId()));

		$result = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		$data = array();
		if (is_object($result))
		{
			if ($result->epay != "") $data = unserialize($result->epay);
			else $data = array();
		}

		$this->setServerHost($data["server_host"]);
		$this->setServerPath($data["server_path"]);
		$this->setMerchantNumber($data["merchant_number"]);
		$this->setAuthToken($data["auth_token"]);
		$this->setAuthEmail($data["auth_email"]);
		$this->setInstantCapture($data['instant_capture']);
	}	
	
	/** 
	 * Fetches and sets the primary key of the payment settings
	 *
	 * @access	private
	 */
	private function fetchSettingsId()
	{

		$res = $this->db->query('SELECT * FROM payment_settings');

		$result = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		$this->setSettingsId($result->settings_id);
	}
	
	public function setSettingsId($a_settings_id = 0)
	{
		$this->settings_id = $a_settings_id;
	}
	
	public function getSettingsId()
	{
		return $this->settings_id;
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
	
	public function setMerchantNumber($a_merchant_number)
	{
		$this->merchant_number = $a_merchant_number;
	}
	
	public function getMerchantNumber()
	{
		return $this->merchant_number;
	}
	
	public function setAuthToken($a_auth_token)
	{
		$this->auth_token = $a_auth_token;
	}
	
	public function getAuthToken()
	{
		return $this->auth_token;
	}
	

	public function setAuthEmail($a_auth_email)
	{
		$this->auth_email = $a_auth_email;
	}
	
	public function getAuthEmail()
	{
		return $this->auth_email;
	}

	public function setInstantCapture($a_instant_capture)
	{
		$this->instant_capture = $a_instant_capture;
	}
	
	public function getInstantCapture()
	{
		return $this->instant_capture;
	}
	
	
	/** 
	 * Returns array of all epay settings
	 * 
	 * @access	public
	 * @return	array $values Array of all epay settings
	 */
	function getAll()
	{
		$values = array(
			"server_host" => $this->getServerHost(),
			"server_path" => $this->getServerPath(),
			"merchant_number" => $this->getMerchantNumber(),
			"auth_token" => $this->getAuthToken(),
			"auth_email" => $this->getAuthEmail(),
			"instant_capture" => $this->getInstantCapture()
		);
		
		return $values;
	}

	/** 
	 * Clears the payment settings for the epay payment method 
	 *
	 * @access	public
	 */
	function clearAll()
	{
		$statement = $this->db->manipulateF('
			UPDATE payment_settings
			SET epay = %s
			WHERE settings_id = %s',
			array('text', 'integer'), 
			array('NULL', $this->getSettingsId()));

					
		$this->settings = array();
	}
		
	/** 
	 * Inserts or updates (if payment settings already exist) the epay settings data
	 *
	 * @access	public
	 */
	public function save()
	{
		global $ilDB;
		
		$values = array(
			"server_host" => $this->getServerHost(),
			"server_path" => $this->getServerPath(),
			"merchant_number" => $this->getMerchantNumber(),
			"auth_token" => $this->getAuthToken(),
			"auth_email" => $this->getAuthEmail(),
			"instant_capture" => $this->getInstantCapture()
		);		

		if ($this->getSettingsId())
		{
			$statement = $ilDB->manipulateF('
				UPDATE payment_settings
				SET epay = %s
				WHERE settings_id = %s',
				array('text', 'integer'),
				array(serialize($values), $this->getSettingsId()));

		}
		else
		{
			$next_id = $ilDB->nextId('payment_settings');
			$statement = $ilDB->manipulateF('
				INSERT INTO payment_settings
				( 	settings_id,
					epay) 
				VALUES (%s, %s)',
				array('integer','text'), array($next_id, serialize($values)));
			
			//$this->setSettingsId($this->db->getLastInsertId());
			$this->setSettingsId($next_id);			
			
		}
	}	
}
?>