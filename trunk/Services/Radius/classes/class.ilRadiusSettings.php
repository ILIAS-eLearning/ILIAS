<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesRadius 
*/
class ilRadiusSettings
{
	const RADIUS_CHARSET_UTF8 = 0;
	const RADIUS_CHARSET_LATIN1 = 1;

	const SYNC_DISABLED = 0;
	const SYNC_RADIUS = 1;
	const SYNC_LDAP = 2;
	
	
	private $settings;
	private $db;
	private static $instance = null;
	
	private $account_migration = false;
	
	private $servers = array();
	var $active = false;
	
	/**
	 * singleton constructor
	 *
	 * @access private
	 * 
	 */
	private function __construct()
	{
	 	global $ilSetting,$ilDB;
	 	
	 	$this->settings = $ilSetting;
	 	$this->db = $ilDB;
	 	
	 	$this->read();
	}
	
	/**
	 * singleton get instance
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstance()
	{
		if(isset(self::$instance) and self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilRadiusSettings();
	}
	
	public function isActive()
	{
	 	return $this->active ? true : false;
	}
	public function setActive($a_status)
	{
		$this->active = $a_status;
	}
	public function setPort($a_port)
	{
		$this->port = $a_port;
	}
	public function getPort()
	{
		return $this->port;
	}
	public function setSecret($a_secret)
	{
		$this->secret = $a_secret;
	}
	public function getSecret()
	{
		return $this->secret;
	}
	public function setServerString($a_server_string)
	{
		$this->server_string = $a_server_string;
		$this->servers = explode(',',$this->server_string);
	}
	public function getServersAsString()
	{
		return implode(',',$this->servers);
	}
	public function getServers()
	{
		return $this->servers ? $this->servers : array();
	}
	public function setName($a_name)
	{
		$this->name = $a_name;
	}
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Create options array for PEAR Auth constructor
	 *
	 * @access public
	 * 
	 */
	public function toPearAuthArray()
	{
	 	foreach($this->getServers() as $server)
	 	{
	 		$auth_params['servers'][] = array($server,$this->getPort(),$this->getSecret());
	 	}
	 	return $auth_params ? $auth_params : array();
	}
	
	/**
	 * Get default role for new radius users
	 *
	 * @access public
	 * @return int role_id
	 * 
	 */
	public function getDefaultRole()
	{
	 	return $this->default_role;
	}
	
	public function setDefaultRole($a_role)
	{
		$this->default_role = $a_role;
	}
	
	/**
	 * Enable creation of users
	 *
	 * @access public
	 * 
	 */
	public function enabledCreation()
	{
	 	return $this->creation;
	}
	
	/**
	 * Enable creation
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function enableCreation($a_status)
	{
	 	$this->creation = $a_status;
	}
	
	/**
	 * Enable account migration
	 *
	 * @access public
	 * @param bool status
	 * 
	 */
	public function enableAccountMigration($a_status)
	{
	 	$this->account_migration = $a_status;
	}
	
	/**
	 * enabled account migration
	 *
	 * @access public
	 * 
	 */
	public function isAccountMigrationEnabled()
	{
	 	return $this->account_migration ? true : false;
	}
	
	/**
	 * get charset
	 *
	 * @access public
	 * 
	 */
	public function getCharset()
	{
	 	return $this->charset ? 1 : 0;
	}
	
	/**
	 * set charset
	 *
	 * @access public
	 * @param int charset
	 * 
	 */
	public function setCharset($a_charset)
	{
	 	$this->charset = $a_charset;
	}
	
	/**
	 * Save settings
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
	 	// first delete old servers
		$this->settings->deleteLike('radius_server%');
	 	
		$this->settings->set('radius_active',$this->isActive() ? 1 : 0);
		$this->settings->set('radius_port',$this->getPort());
		$this->settings->set('radius_shared_secret',$this->getSecret());
		$this->settings->set('radius_name',$this->getName());
		$this->settings->set('radius_creation',$this->enabledCreation() ? 1 : 0);
		$this->settings->set('radius_migration',$this->isAccountMigrationEnabled() ? 1 : 0);
		$this->settings->set('radius_charset',$this->getCharset() ? 1 : 0);
		
		$counter = 0;
		foreach($this->getServers() as $server)
		{
			if(++$counter == 1)
			{
				$this->settings->set('radius_server',trim($server));
			}
			else
			{
				$this->settings->set('radius_server'.$counter,trim($server));
			}
		}
		
		include_once('./Services/AccessControl/classes/class.ilObjRole.php');
		ilObjRole::_resetAuthMode('radius');
		
		if($this->getDefaultRole())
		{
			ilObjRole::_updateAuthMode(array($this->getDefaultRole() => 'radius'));
		}
		return true;
	}
	
	/**
	 * Validate required
	 *
	 * @access public
	 * 
	 */
	public function validateRequired()
	{
	 	$ok = strlen($this->getServersAsString()) and strlen($this->getPort()) and strlen($this->getSecret()) and strlen($this->getName());
	 	
	 	$role_ok = true;
	 	if($this->enabledCreation() and !$this->getDefaultRole())
	 	{
	 		$role_ok = false;
	 	}
	 	return $ok and $role_ok;
	}
	
	/**
	 * Validate port
	 *
	 * @access public
	 * 
	 */
	public function validatePort()
	{
		return preg_match("/^[0-9]{0,5}$/",$this->getPort()) == 1;
	}
	
	/**
	 * Validate servers
	 *
	 * @access public
	 * 
	 */
	public function validateServers()
	{
		$servers = explode(",",$this->server_string);
		
		foreach ($servers as $server)
		{
			$server = trim($server);

			if (!ilUtil::isIPv4($server) and !ilUtil::isDN($server))
			{
				return false;
			}
		}
		return true;
	}
	
	
	/**
	 * Read settings
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	$all_settings = $this->settings->getAll();

	 	$sets = array("radius_active" => "setActive",
	 		"radius_port" => "setPort",
		 	"radius_shared_secret" => "setSecret",
		 	"radius_name" => "setName",
		 	"radius_creation" => "enableCreation",
		 	"radius_migration" => "enableAccountMigration",
		 	"radius_charset" => "setCharset"
		 	);
		foreach ($sets as $s => $m)
		{
		 	if (isset($all_settings[$s]))
		 	{
		 		$this->$m($all_settings[$s]);
		 	}
		}
	 	
		reset($all_settings);
		foreach($all_settings as $k => $v)
		{
			if (substr($k, 0, 13) == "radius_server")
			{
				$this->servers[] = $v;
			}
		}
		
		include_once('./Services/AccessControl/classes/class.ilObjRole.php');
		$roles = ilObjRole::_getRolesByAuthMode('radius');
		$this->default_role = 0;
		if (isset($roles[0]) && $roles[0])
		{
			$this->default_role = $roles[0];
		}
	}
}


?>