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
* Class ilObjChatServerGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id:class.ilObjChatServerGUI.php 12853 2006-12-15 13:36:31 +0000 (Fr, 15 Dez 2006) smeyer $
*
* @ilCtrl_Calls ilObjChatServerGUI: ilPermissionGUI
*
* @extends ilObjectGUI
*/

require_once "classes/class.ilObjectGUI.php";

class ilObjChatServerGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjChatServerGUI($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{

		#define("ILIAS_MODULE","chat");
		$this->type = "chac";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule("chat");
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}

	function editObject()
	{
		global $rbacsystem;
	
		$this->tabs_gui->setTabActive('edit_properties');

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.chac_edit.html",'Modules/Chat');

        $internal_ip = $_SESSION["error_post_vars"]["chat_internal_ip"] ? 
            $_SESSION["error_post_vars"]["chat_internal_ip"] :
            $this->object->server_conf->getInternalIp();

        $external_ip = $_SESSION["error_post_vars"]["chat_external_ip"] ? 
            $_SESSION["error_post_vars"]["chat_external_ip"] :
            $this->object->server_conf->getExternalIp();

		$port = $_SESSION["error_post_vars"]["chat_port"] ? 
			$_SESSION["error_post_vars"]["chat_port"] :
			$this->object->server_conf->getPort();
		
		$ssl_status = $_SESSION["error_post_vars"]["chat_ssl_status"] ? 
			$_SESSION["error_post_vars"]["chat_ssl_status"] :
			$this->object->server_conf->getSSLStatus();		
			
		$ssl_port = $_SESSION["error_post_vars"]["chat_ssl_port"] ? 
			$_SESSION["error_post_vars"]["chat_ssl_port"] :
			$this->object->server_conf->getSSLPort();

		$moderator = $_SESSION["error_post_vars"]["chat_moderator"] ? 
			$_SESSION["error_post_vars"]["chat_moderator"] :
			$this->object->server_conf->getModeratorPassword();

		$logfile = $_SESSION["error_post_vars"]["chat_logfile"] ? 
			$_SESSION["error_post_vars"]["chat_logfile"] :
			$this->object->server_conf->getLogfile();

		$loglevel = $_SESSION["error_post_vars"]["chat_loglevel"] ? 
			$_SESSION["error_post_vars"]["chat_loglevel"] :
			$this->object->server_conf->getLogLevel();

        $allowed = $_SESSION["error_post_vars"]["chat_allowed"] ? 
            $_SESSION["error_post_vars"]["chat_internal_ip"] :
            $this->object->server_conf->getAllowedHosts();

		$active = $_SESSION["error_post_vars"]["chat_active"] ?
			$_SESSION["error_post_vars"]["chat_active"] :
			$this->object->server_conf->getActiveStatus();

		
		if($this->object->server_conf->isAlive() or $this->object->server_conf->getActiveStatus())
		{
			$this->tpl->setCurrentBlock("chat_active");
			$this->tpl->setVariable("TXT_ACT_CHAT",$this->lng->txt("chat_ilias"));
			$this->tpl->setVariable("TXT_ACT_STATUS",$this->lng->txt("chat_status"));
			$this->tpl->setVariable("TXT_ACT_SUBMIT",$this->lng->txt("change"));
			$this->tpl->setVariable("SELECT_ACT_STATUS",$this->__getStatusSelect($active));
		}
			


		// SET TEXT VARIABLES
		$this->tpl->setVariable("FORMACTION",
								$this->ctrl->getFormAction($this));
								#$this->getFormAction("gateway","adm_object.php?ref_id=".$this->ref_id."&cmd=gateway"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_SETTINGS",$this->lng->txt("chat_server_settings"));
        $this->tpl->setVariable("TXT_CHAT_SERVER_INTERNAL_IP",$this->lng->txt("chat_server_internal_ip"));
        $this->tpl->setVariable("TXT_CHAT_SERVER_EXTERNAL_IP",$this->lng->txt("chat_server_external_ip"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_MODERATOR",$this->lng->txt("chat_moderator_password"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_PORT",$this->lng->txt("chat_server_port"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_SSL_SETTINGS",$this->lng->txt("chat_server_ssl_settings"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_SSL_ACTIVE",$this->lng->txt("chat_server_ssl_active"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_SSL_PORT",$this->lng->txt("chat_server_ssl_port"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_LOGFILE",$this->lng->txt("chat_server_logfile"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_LEVEL",$this->lng->txt("chat_server_loglevel"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_ALLOWED",$this->lng->txt("chat_server_allowed"));
		$this->tpl->setVariable("TXT_CHAT_SERVER_ALLOWED_B",$this->lng->txt("chat_server_allowed_b"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("save"));

		// SET SETTING VARS
        $this->tpl->setVariable("CHAT_SERVER_INTERNAL",$internal_ip);
        $this->tpl->setVariable("CHAT_SERVER_EXTERNAL",$external_ip);
		$this->tpl->setVariable("CHAT_PORT",$port);
		if ($ssl_status) $this->tpl->setVariable("CHAT_SSL_STATUS_CHECKED", "checked='checked'");
		$this->tpl->setVariable("CHAT_SSL_PORT",$ssl_port);
		$this->tpl->setVariable("CHAT_MODERATOR",$moderator);
		$this->tpl->setVariable("CHAT_LOGFILE",$logfile);
		$this->tpl->setVariable("CHAT_ALLOWED",$allowed);
		$this->tpl->setVariable("SELECT_LEVEL",$this->__getLogLevelSelect($loglevel));
		//$this->tpl->parseCurrentBlock();
		
		return true;
	}

	function updateObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
        $this->object->server_conf->setInternalIp(ilUtil::stripSlashes($_POST["chat_internal_ip"]));
        $this->object->server_conf->setExternalIp(ilUtil::stripSlashes($_POST["chat_external_ip"]));
		$this->object->server_conf->setPort(ilUtil::stripSlashes($_POST["chat_port"]));
		$this->object->server_conf->setSSLStatus($_POST["chat_ssl_status"] ? 1 : 0);
		$this->object->server_conf->setSSLPort(ilUtil::stripSlashes($_POST["chat_ssl_port"]));
		$this->object->server_conf->setModeratorPassword(ilUtil::stripSlashes($_POST["chat_moderator"]));
		$this->object->server_conf->setLogfile(ilUtil::stripSlashes($_POST["chat_logfile"]));
		$this->object->server_conf->setLogLevel(ilUtil::stripSlashes($_POST["chat_loglevel"]));
		$this->object->server_conf->setAllowedHosts(ilUtil::stripSlashes($_POST["chat_allowed"]));

		if(!$this->object->server_conf->validate())
		{
			$this->ilias->raiseError($this->object->server_conf->getErrorMessage(),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			if(!$this->object->server_conf->update())
			{
				$this->ilias->raiseError($this->object->server_conf->getErrorMessage(),$this->ilias->error_obj->MESSAGE);
			}
		}
		ilUtil::sendInfo($this->lng->txt("chat_settings_saved"),true);
		$this->editObject();
	}

	function activateObject()
	{
		$this->object->server_conf->setActiveStatus((bool) $_POST["chat_active"]);
		$this->object->server_conf->updateStatus();

		ilUtil::sendInfo($this->lng->txt("chat_status_saved"));
		$this->editObject();
	}

	// PRIVATE
	function __getLogLevelSelect($a_level)
	{
		$levels = array(1 => $this->lng->txt("chat_level_fatal"),
						2 => $this->lng->txt("chat_level_error"),
						3 => $this->lng->txt("chat_level_info"),
						5 => $this->lng->txt("chat_level_debug"),
						6 => $this->lng->txt("chat_level_all"));

		return ilUtil::formSelect($a_level,"chat_loglevel",$levels,false,true);
	}
	function __getStatusSelect($a_status)
	{
		$stati = array(1 => $this->lng->txt("chat_active"),
					   0 => $this->lng->txt("chat_inactive"));

		return ilUtil::formSelect($a_status,"chat_active",$stati,false,true);
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getAdminTabs(&$tabs_gui)
	{
		global $rbacsystem,$rbacreview;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

		if($rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$force_active = ($_GET["cmd"] == "" || $_GET["cmd"] == "view")
				? true
				: false;
			$tabs_gui->addTarget("chat_rooms",
				$this->ctrl->getLinkTarget($this, "view"), array("view", ""), get_class($this),
				"", $force_active);
		}
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this),
				"", $force_active);
		}
		if($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}


	


} // END class.ilObjChatServerGUI

?>