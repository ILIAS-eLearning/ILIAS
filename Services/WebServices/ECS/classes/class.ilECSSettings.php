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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS
*/
class ilECSSettings
{
	const PROTOCOL_HTTP = 0;
	const PROTOCOL_HTTPS = 1;
	
	public $fisch = 5;
	
	protected static $instance = null;

	private $active = false;
	private $server;
	private $protocol;
	private $port;
	private $client_cert_path;
	private $ca_cert_path;
	private $polling;
	private $import_id;

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
	 		return true;
	 	}
		if(!$this->getServer() or !$this->getPort() or !$this->getClientCertPath() or !$this->getCACertPath())
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
	 	$this->storage->set('import_id',$this->getImportId());
	 	$this->storage->set('polling',$this->getPollingTime());
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
		$this->setPollingTime($this->storage->get('polling',128));
		$this->setImportId($this->storage->get('import_id'));
		$this->setEnabledStatus((int) $this->storage->get('active'));
	}
}
?>