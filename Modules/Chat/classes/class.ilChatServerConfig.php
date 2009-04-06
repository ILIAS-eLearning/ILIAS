<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* @version $Id:class.ilChatServerConfig.php 12853 2006-12-15 13:36:31 +0000 (Fr, 15 Dez 2006) smeyer $
*
*/

class ilChatServerConfig
{
	var $ilias;
	var $lng;

    var $internal_ip;
    var $external_ip;
	var $port;
	var $ssl_status;
	var $ssl_port;
	var $moderator;
	var $logfile;
	var $loglevel;
	var $hosts;
	var $active;
	var $nic;

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


	function _isActive()
	{
		global $ilias;

		return (bool) $ilias->getSetting("chat_active");
	}


	// SET/GET
    function setInternalIp($ip)
    {
        $this->internal_ip = $ip;
    }
    function getInternalIp()
    {
        return $this->internal_ip;
    }
    function setExternalIp($ip)
    {
        $this->external_ip = $ip;
    }
    function getExternalIp()
    {
        return $this->external_ip ? $this->external_ip : $this->internal_ip;
    }
	function setPort($port)
	{
		$this->port = $port;
	}
	function getPort()
	{
		return $this->port;
	}
	function setSSLStatus($ssl_status = 0)
	{
		$this->ssl_status = $ssl_status;
	}
	function getSSLStatus()
	{
		return $this->ssl_status;
	}	
	function setSSLPort($ssl_port)
	{
		$this->ssl_port = $ssl_port;
	}
	function getSSLPort()
	{
		return $this->ssl_port;
	}
	function setModeratorPassword($a_passwd)
	{
		$this->moderator = $a_passwd;
	}
	function getModeratorPassword()
	{
		return $this->moderator;
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
	function getNic()
	{
		return substr($this->nic,0,6);
	}

	function validate()
	{
		$this->error_message = "";

        if(!$this->getInternalIp())
        {
            $this->error_message .= $this->lng->txt("chat_add_internal_ip");
        }
        if(!$this->getExternalIp())
        {
			if($this->error_message)
			{
				$this->error_message .= "<br />";
			}
            $this->error_message .= $this->lng->txt("chat_add_external_ip");
        }
		if(!$this->getPort())
		{
			if($this->error_message)
			{
				$this->error_message .= "<br />";
			}
			$this->error_message .= $this->lng->txt("chat_add_port");
		}
		if($this->getSSLStatus() && !$this->getSSLPort())
		{
			if($this->error_message)
			{
				$this->error_message .= "<br />";
			}
			$this->error_message .= $this->lng->txt("chat_add_ssl_port");
		}		
		if(!$this->getModeratorPassword())
		{
			if($this->error_message)
			{
				$this->error_message .= "<br />";
			}
			$this->error_message .= $this->lng->txt("chat_add_moderator_password");
		}
		if(!$this->getAllowedHosts())
		{
			if($this->error_message)
			{
				$this->error_message .= "<br />";
			}
			$this->error_message .= $this->lng->txt("chat_add_allowed_hosts");
		}

		if($this->getAllowedHosts())
		{
			$this->__parseAllowedHosts();
		}

		return $this->error_message ? false : true;
	}
	function update()
	{
        $this->ilias->setSetting("chat_internal_ip",$this->getInternalIp());
        $this->ilias->setSetting("chat_external_ip",$this->getExternalIp());
		$this->ilias->setSetting("chat_port",$this->getPort());
		$this->ilias->setSetting("chat_ssl_status",$this->getSSLStatus());
		$this->ilias->setSetting("chat_ssl_port",$this->getSSLPort());
		$this->ilias->setSetting("chat_logfile",$this->getLogfile());
		$this->ilias->setSetting("chat_loglevel",$this->getLogLevel());
		$this->ilias->setSetting("chat_hosts",$this->getAllowedHosts());
		$this->ilias->setSetting("chat_moderator_password",$this->getModeratorPassword());

		return $this->__writeConfigFile();
	}
	function updateStatus()
	{
		$this->ilias->setSetting("chat_active",$this->getActiveStatus());
	}

	function read()
	{
        $this->internal_ip = $this->ilias->getSetting("chat_internal_ip");
        $this->external_ip = $this->ilias->getSetting("chat_external_ip");
		$this->port = $this->ilias->getSetting("chat_port");
		$this->ssl_status = $this->ilias->getSetting("chat_ssl_status");
		$this->ssl_port = $this->ilias->getSetting("chat_ssl_port");
		$this->moderator = $this->ilias->getSetting("chat_moderator_password");
		$this->loglevel = $this->ilias->getSetting("chat_loglevel");
		$this->logfile = $this->ilias->getSetting("chat_logfile");
		$this->hosts = $this->ilias->getSetting("chat_hosts");
		$this->active = $this->ilias->getSetting("chat_active");
		$this->nic = $this->ilias->getSetting("nic_key");
	}

	function isAlive()
	{
        if($this->getInternalIp() and $this->getPort())
        {
            if( $sp = @fsockopen($this->getInternalIp(),$this->getPort(), $errno, $errstr, 100))
			{
				fclose($sp);
				return true;
			}
			return false;
		}
		return false;
	}

	//PRIVATE
	function __parseAllowedHosts()
	{
		$hosts_arr2 = array();
		$hosts_arr = explode(',',$this->getAllowedHosts());

		for($i = 0;$i < count($hosts_arr); ++$i)
		{
			if(trim($hosts_arr[$i]))
			{
				$hosts_arr2[] = trim($hosts_arr[$i]);
			}
		}
		$this->setAllowedHosts(implode(',',$hosts_arr2));

		return true;
	}
	function __writeConfigFile()
	{
		if(!@is_dir(ilUtil::getDataDir().'/chat'))
		{
			ilUtil::makeDir(ilUtil::getDataDir().'/chat');
		}		
		if(!($fp = @fopen(ilUtil::getDataDir().'/chat/server.ini',"w")))
		{
			$this->error_message = ilUtil::getDataDir().'/chat/server.ini ' .$this->lng->txt("chat_no_write_perm");
			return false;
		}
		$content =  "LogLevel = ".$this->getLogLevel()."\n";
		if($this->getLogfile())
		{
			$content .= "LogFile = ".$this->getLogfile()."\n";
		}
        $content .= "IpAddress = ".$this->getInternalIp()."\n";
        $content .= "ExternalIpAddress = ".$this->getExternalIp()."\n";
		$content .= "Port = ".$this->getPort()."\n";
		#$content .= "SSLStatus = ".($this->getSSLStatus() ? $this->getSSLStatus() : 0)."\n";
		#$content .= "SSLPort = ".$this->getSSLPort()."\n";
		$content .= "ModeratorPassword = ".$this->getModeratorPassword()."\n";
		$content .= "HeaderFileName = ".ILIAS_ABSOLUTE_PATH."/Modules/Chat/templates/default/header.html\n";
		$content .= "FooterFileName = ".ILIAS_ABSOLUTE_PATH."/Modules/Chat/templates/default/footer.html\n";
		$content .= "Authentication = 1\n";
		$content .= "ConnectionsFrom = ".$this->getAllowedHosts()."\n";

		if(!@fwrite($fp,$content))
		{
			$this->error_message = ilUtil::getDataDir().'/chat/server.ini '.$this->lng->txt("chat_no_write_perm");
			fclose($fp);
			
			return false;

		}
		fclose($fp);
		return true;
	}
	
} // END class.ilObjChatServer
?>
