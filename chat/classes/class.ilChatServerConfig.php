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
* Class ilChatServerConfig
* 
* @author Stefan Meyer 
* @version $Id$
*
* @package chat
*/

class ilChatServerConfig
{
	var $ilias;
	var $lng;

	var $ip;
	var $port;
	var $logfile;
	var $loglevel;
	var $hosts;
	var $active;

	var $error_message;

	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilChatServerConfig()
	{
		global $ilias,$lng;

		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("chat");

		$this->read();
	}

	// SET/GET
	function setIp($ip)
	{
		$this->ip = $ip;
	}
	function getIp()
	{
		return $this->ip;
	}
	function setPort($port)
	{
		$this->port = $port;
	}
	function getPort()
	{
		return $this->port;
	}
	function setLogfile($logfile)
	{
		$this->logfile = $logfile;
	}
	function getLogfile()
	{
		return $this->logfile;
	}
	function setLogLevel($loglevel)
	{
		$this->loglevel = $loglevel;
	}
	function getLogLevel()
	{
		return $this->loglevel;
	}
	function setAllowedHosts($hosts)
	{
		$this->hosts = $hosts;
	}
	function getAllowedHosts()
	{
		return $this->hosts;
	}

	function getErrorMessage()
	{
		return $this->error_message;
	}
	function setActiveStatus($status)
	{
		$this->active = $status;
	}
	function getActiveStatus()
	{
		return $this->active;
	}


	function validate()
	{
		$this->error_message = "";

		if(!$this->getIp())
		{
			$this->error_message .= $this->lng->txt("chat_add_ip");
		}
		if(!$this->getPort())
		{
			$this->error_message .= $this->lng->txt("chat_add_port");
		}

		return $this->error_message ? false : true;
	}
	function update()
	{
		$this->ilias->setSetting("chat_ip",$this->getIp());
		$this->ilias->setSetting("chat_port",$this->getPort());
		$this->ilias->setSetting("chat_logfile",$this->getLogfile());
		$this->ilias->setSetting("chat_loglevel",$this->getLogLevel());
		$this->ilias->setSetting("chat_hosts",$this->getAllowedHosts());
	}
	function updateStatus()
	{
		$this->ilias->setSetting("chat_active",$this->getActiveStatus());
	}

	function read()
	{
		$this->ip = $this->ilias->getSetting("chat_ip");
		$this->port = $this->ilias->getSetting("chat_port");
		$this->loglevel = $this->ilias->getSetting("chat_loglevel");
		$this->logfile = $this->ilias->getSetting("chat_logfile");
		$this->hosts = $this->ilias->getSetting("chat_hosts");
		$this->active = $this->ilias->getSetting("chat_active");
	}

	function isAlive()
	{
		if($this->getIp() and $this->getPort())
		{
			if( $sp = @fsockopen($this->getIp(),$this->getPort(), $errno, $errstr, 100))
			{
				fclose($sp);
				return true;
			}
			return false;
		}
		return false;
	}
	
} // END class.ilObjChatServer
?>
