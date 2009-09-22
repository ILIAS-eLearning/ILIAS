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
* @defgroup ServicesWebServicesECS Services/WebServices/ECS
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSSettings
{
	const ERROR_EXTRACT_SERIAL = 'ecs_error_extract_serial';
	const ERROR_REQUIRED = 'fill_out_all_required_fields';
	const ERROR_INVALID_IMPORT_ID = 'ecs_check_import_id';
	
	const DEFAULT_DURATION = 6;
	
	
	const PROTOCOL_HTTP = 0;
	const PROTOCOL_HTTPS = 1;
	
	protected static $instance = null;

	private $active = false;
	private $server;
	private $protocol;
	private $port;
	private $client_cert_path;
	private $ca_cert_path;
	private $key_path;
	private $key_pathword;
	private $polling;
	private $import_id;
	private $cert_serial;
	private $global_role;
	private $duration;
	
	private $user_recipients = array();
	private $econtent_recipients = array();
	private $approval_recipients = array();

	/**
	 * Singleton contructor
	 *
	 * @access private
	 */
	private function __construct()
	{
	 	$this->initStorage();
	 	$this->read();
	}
	
	/**
	 * singleton getInstance
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilECSSettings();
	}
	
	/**
	 * en/disable ecs functionality
	 *
	 * @access public
	 * @param bool status
	 * 
	 */
	public function setEnabledStatus($a_status)
	{
	 	$this->active = $a_status;
	}
	
	/**
	 * is enabled
	 *
	 * @access public
	 * 
	 */
	public function isEnabled()
	{
	 	return $this->active;
	}
	
	/**
	 * set server 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setServer($a_server)
	{
	 	$this->server = $a_server;
	}
	
	/**
	 * get server
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getServer()
	{
	 	return $this->server;
	}
	
	/**
	 * get complete server uri
	 *
	 * @access public
	 * 
	 */
	public function getServerURI()
	{
	 	switch($this->getProtocol())
	 	{
	 		case self::PROTOCOL_HTTP:
	 			$uri = 'http://';
	 			break;
	 			
	 		case self::PROTOCOL_HTTPS:
	 			$uri = 'https://';
	 			break;
	 	}
	 	$uri .= $this->getServer().':'.$this->getPort();
	 	return $uri;
	}
	
	/**
	 * set protocol
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setProtocol($a_prot)
	{
	 	$this->protocol = $a_prot;
	}

	/**
	 * get protocol
	 *
	 * @access public
	 * 
	 */
	public function getProtocol()
	{
	 	return $this->protocol;
	}
	
	/**
	 * set port
	 *
	 * @access public
	 * @param int port
	 * 
	 */
	public function setPort($a_port)
	{
	 	$this->port = $a_port;
	}
	
	/**
	 * get port
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getPort()
	{
	 	return $this->port;
	}
	
	/**
	 * set polling time
	 *
	 * @access public
	 * @param int polling time
	 * 
	 */
	public function setPollingTime($a_time)
	{
	 	$this->polling = $a_time;
	}
	
	/**
	 * get polling time
	 *
	 * @access public
	 * 
	 */
	public function getPollingTime()
	{
	 	return $this->polling;
	}
	
	/**
	 * get polling time seconds (<60)
	 *
	 * @access public
	 * 
	 */
	public function getPollingTimeSeconds()
	{
	 	return (int) ($this->polling % 60);
	}
	
	/**
	 * get polling time minutes
	 *
	 * @access public
	 * 
	 */
	public function getPollingTimeMinutes()
	{
	 	return (int) ($this->polling / 60);
	}
	
	/**
	 * Set polling time
	 *
	 * @access public
	 *
	 * @param int minutes
	 * @param int seconds
	 */
	public function setPollingTimeMS($a_min,$a_sec)
	{
		$this->setPollingTime(60 * $a_min + $a_sec);
	}
	
	/**
	 * set 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setClientCertPath($a_path)
	{
	 	$this->client_cert_path = $a_path;
	}

	/**
	 * get certificate path
	 *
	 * @access public
	 */
	public function getClientCertPath()
	{
	 	return $this->client_cert_path;
	}
	
	/**
	 * set ca cert path
	 *
	 * @access public
	 * @param string ca cert path
	 * 
	 */
	public function setCACertPath($a_ca)
	{
	 	$this->ca_cert_path = $a_ca;
	}
	
	/**
	 * get ca cert path
	 *
	 * @access public
	 * 
	 */
	public function getCACertPath()
	{
	 	return $this->ca_cert_path;
	}
	
	/**
	 * get key path
	 *
	 * @access public
	 * 
	 */
	public function getKeyPath()
	{
	 	return $this->key_path;
	}
	
	/**
	 * set key path
	 *
	 * @access public
	 * @param string key path
	 * 
	 */
	public function setKeyPath($a_path)
	{
	 	$this->key_path = $a_path;
	}
	
	/**
	 * get key password
	 *
	 * @access public
	 * 
	 */
	public function getKeyPassword()
	{
	 	return $this->key_password;
	}
	
	/**
	 * set key password
	 *
	 * @access public
	 * @param string key password
	 * 
	 */
	public function setKeyPassword($a_pass)
	{
		$this->key_password = $a_pass;	
	}
	
	/**
	 * set import id
	 * Object of category, that store new remote courses
	 *
	 * @access public
	 * 
	 */
	public function setImportId($a_id)
	{
	 	$this->import_id = $a_id;
	}
	
	/**
	 * get import id
	 *
	 * @access public
	 */
	public function getImportId()
	{
	 	return $this->import_id;
	}
	
	/**
	 * set cert serial number
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setCertSerialNumber($a_cert_serial)
	{
	 	$this->cert_serial_number = $a_cert_serial;
	}
	
	/**
	 * get cert serial number
	 *
	 * @access public
	 * 
	 */
	public function getCertSerialNumber()
	{
	 	return $this->cert_serial_number;
	}
	
	/**
	 * get global role
	 *
	 * @access public
	 * 
	 */
	public function getGlobalRole()
	{
	 	return $this->global_role;
	}
	
	/**
	 * set default global role
	 *
	 * @access public
	 *
	 * @param int role_id
	 */
	public function setGlobalRole($a_role_id)
	{
		$this->global_role = $a_role_id;
	}
	
	/**
	 * set Duration
	 *
	 * @access public
	 * @param int duration
	 * 
	 */
	public function setDuration($a_duration)
	{
	 	$this->duration = $a_duration;
	}
	
	/**
	 * get duration
	 *
	 * @access public
	 * 
	 */
	public function getDuration()
	{
	 	return $this->duration ? $this->duration : self::DEFAULT_DURATION;
	}
	
	/** 
	 * Get new user recipients
	 *
	 * @access public
	 * 
	 */
	public function getUserRecipients()
	{
	 	return explode(',',$this->user_recipients);
	}
	
	/** 
	 * Get new user recipients
	 *
	 * @access public
	 * 
	 */
	public function getUserRecipientsAsString()
	{
	 	return $this->user_recipients;
	}
	
	/**
	 * set user recipients
	 *
	 * @access public
	 * @param array of recipients (array of user login names)
	 * 
	 */
	public function setUserRecipients($a_logins)
	{
	 	$this->user_recipients = $a_logins;
	}
	
	/**
	 * get Econtent recipients
	 *
	 * @access public
	 * 
	 */
	public function getEContentRecipients()
	{
	 	return explode(',',$this->econtent_recipients);
	}
	
	/** 
	 * get EContent recipients as string
	 *
	 * @access public
	 * 
	 */
	public function getEContentRecipientsAsString()
	{
	 	return $this->econtent_recipients;
	}
	
	/**
	 * set EContent recipients
	 *
	 * @access public
	 * @param array of user obj_ids
	 * 
	 */
	public function setEContentRecipients($a_logins)
	{
	 	$this->econtent_recipients = $a_logins;
	}
	
	/**
	 * get approval recipients
	 *
	 * @access public
	 * @return bool
	 */
	public function getApprovalRecipients()
	{
		return explode(',',$this->approval_recipients);
	}
	
	/**
	 * get approval recipients as string
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getApprovalRecipientsAsString()
	{
		return $this->approval_recipients;
	}
	
	/**
	 * set approval recipients
	 *
	 * @access public
	 * @param string recipients
	 */
	public function setApprovalRecipients($a_rcp)
	{
		$this->approval_recipients = $a_rcp;
	}
	
	/**
	 * Validate settings
	 *
	 * @access public
	 * @param void
	 * @return bool 
	 * 
	 */
	public function validate()
	{
	 	if(!$this->isEnabled())
	 	{
	 		return '';
	 	}
		if(!$this->getServer() or !$this->getPort() or !$this->getClientCertPath() or !$this->getCACertPath()
			or !$this->getKeyPath() or !$this->getKeyPassword() or !$this->getPollingTime() or !$this->getImportId()
			or !$this->getGlobalRole() or !$this->getDuration())
		{
			return self::ERROR_REQUIRED;
		}
		
		// Check import id
		if(!$this->fetchSerialID())
		{
			return self::ERROR_EXTRACT_SERIAL;
		}
		if(!$this->checkImportId())
		{
			return self::ERROR_INVALID_IMPORT_ID;			
		}
		return '';
	}
	
	/**
	 * check import id
	 *
	 * @access public
	 * 
	 */
	public function checkImportId()
	{
	 	global $ilObjDataCache,$tree;
	 	
	 	if(!$this->getImportId())
	 	{
	 		return false;
	 	}
		if($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($this->getImportId())) != 'cat')
		{
			return false;
		}
		if($tree->isDeleted($this->getImportId()))
		{
			return false;
		}
	 	return true;
	}
	
	/**
	 * save settings
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
	 	$this->storage->set('active',(int) $this->isEnabled());
	 	$this->storage->set('server',$this->getServer());
	 	$this->storage->set('port',$this->getPort());
	 	$this->storage->set('protocol',$this->getProtocol());
	 	$this->storage->set('client_cert_path',$this->getClientCertPath());
	 	$this->storage->set('ca_cert_path',$this->getCACertPath());
	 	$this->storage->set('key_path',$this->getKeyPath());
	 	$this->storage->set('key_password',$this->getKeyPassword());
	 	$this->storage->set('import_id',$this->getImportId());
	 	$this->storage->set('polling',$this->getPollingTime());
	 	$this->storage->set('cert_serial',$this->getCertSerialNumber());
	 	$this->storage->set('global_role',(int) $this->getGlobalRole());
	 	$this->storage->set('user_rcp',$this->getUserRecipientsAsString());
	 	$this->storage->set('econtent_rcp',$this->getEContentRecipientsAsString());
	 	$this->storage->set('approval_rcp',$this->getApprovalRecipientsAsString());
	 	$this->storage->set('duration',$this->getDuration());
	}
	
	/**
	 * Fetch serial ID from cert
	 *
	 * @access private
	 * 
	 */
	private function fetchSerialID()
	{
	 	global $ilLog;
	 	
	 	if(function_exists('openssl_x509_parse') and $cert = openssl_x509_parse('file://'.$this->getClientCertPath()))
	 	{
			if(isset($cert['serialNumber']) and $cert['serialNumber'])
	 		{
	 			$this->setCertSerialNumber($cert['serialNumber']);
	 			$ilLog->write(__METHOD__.': Serial number is '.$cert['serialNumber']);
	 			return true;
	 		}
	 	}
	 	
	 	if(!file_exists($this->getClientCertPath()) or !is_readable($this->getClientCertPath()))
	 	{
	 		return false;
	 	}
	 	$lines = file($this->getClientCertPath());
	 	$found = false;
	 	foreach($lines as $line)
	 	{
	 		if(strpos($line,'Serial Number:') !== false)
	 		{
	 			$found = true;
	 			$serial_line = explode(':',$line);
	 			$serial = (int) trim($serial_line[1]);
	 			break;
	 			
	 		}
	 	}
		if($found)
		{
			$this->setCertSerialNumber($serial);
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	 * Init storage class (ilSetting)
	 * @access private
	 * 
	 */
	private function initStorage()
	{
	 	include_once('./Services/Administration/classes/class.ilSetting.php');
	 	$this->storage = new ilSetting('ecs');
	}
	
	/**
	 * Read settings
	 *
	 * @access private
	 */
	private function read()
	{
		$this->setServer($this->storage->get('server'));
		$this->setProtocol($this->storage->get('protocol'));
		$this->setPort($this->storage->get('port'));
		$this->setClientCertPath($this->storage->get('client_cert_path'));
		$this->setCACertPath($this->storage->get('ca_cert_path'));
		$this->setKeyPath($this->storage->get('key_path'));
		$this->setKeyPassword($this->storage->get('key_password'));
		$this->setPollingTime($this->storage->get('polling',128));
		$this->setImportId($this->storage->get('import_id'));
		$this->setEnabledStatus((int) $this->storage->get('active'));
		$this->setCertSerialNumber($this->storage->get('cert_serial'));
		$this->setGlobalRole($this->storage->get('global_role'));
		$this->econtent_recipients = $this->storage->get('econtent_rcp');
		$this->approval_recipients = $this->storage->get('approval_rcp');
		$this->user_recipients = $this->storage->get('user_rcp');
		$this->setDuration($this->storage->get('duration'));
	}
}
?>