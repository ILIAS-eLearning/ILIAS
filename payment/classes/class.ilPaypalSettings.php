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
* @version $Id$
* 
* 
* @ingroup payment
*/
class ilPaypalSettings
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
			SELECT paypal FROM payment_settings WHERE settings_id = %s',
			array('integer'), array($this->getSettingsId()));

		$result = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
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
		$statement = $this->db->manipulateF('
			UPDATE payment_settings
			SET paypal = %s
			WHERE settings_id = %s',
			array('text', 'integer'), 
			array('', $this->getSettingsId()));

					
		$this->settings = array();
	}
		
	/** 
	 * Inserts or updates (if payment settings already exist) the paypal settings data
	 *
	 * @access	public
	 */
	public function save()
	{
		global $ilDB;
		
		$values = array(
			"server_host" => $this->getServerHost(),
			"server_path" => $this->getServerPath(),
			"vendor" => $this->getVendor(),
			"auth_token" => $this->getAuthToken(),
			"page_style" => $this->getPageStyle(),
			"ssl" => $this->getSsl()			
		);		

		if ($this->getSettingsId())
		{
			$statement = $ilDB->manipulateF('
				UPDATE payment_settings
				SET paypal = %s
				WHERE settings_id = %s',
				array('text', 'integer'),
				array(serialize($values), $this->getSettingsId()));

		}
		else
		{
			$statement = $ilDB->manipulateF('
				INSERT INTO payment_settings
				SET paypal = %s',
				array('text'), array(serialize($values)));
			
			$this->setSettingsId($this->db->getLastInsertId());
			
		}
	}	
}
?>