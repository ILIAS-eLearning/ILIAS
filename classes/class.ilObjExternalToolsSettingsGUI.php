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
* Class ilObjExternalToolsSettingsGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjExternalToolsSettingsGUI: ilPermissionGUI
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjExternalToolsSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjExternalToolsSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "extt";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		
		define ("ILINC_DEFAULT_HTTP_PORT",80);
		define ("ILINC_DEFAULT_SSL_PORT",443);
		define ("ILINC_DEFAULT_TIMEOUT",30);
	}
	
	/**
	* display settings menu
	* 
	* @access	public
	*/
	function viewObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->__initSubTabs("view");
		
		$this->getTemplateFile("general");
		
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_EXTT_TITLE", $this->lng->txt("extt_title_configure"));

		$this->tpl->setVariable("TXT_EXTT_NAME", $this->lng->txt("extt_name"));
		$this->tpl->setVariable("TXT_EXTT_ACTIVE", $this->lng->txt("active")."?");
		$this->tpl->setVariable("TXT_EXTT_DESC", $this->lng->txt("description"));

		$this->tpl->setVariable("TXT_CONFIGURE", $this->lng->txt("extt_configure"));
		$this->tpl->setVariable("TXT_EXTT_REMARK", $this->lng->txt("extt_remark"));

		// ilinc
		$this->tpl->setVariable("TXT_EXTT_ILINC_NAME", $this->lng->txt("extt_ilinc"));
		$this->tpl->setVariable("TXT_EXTT_ILINC_DESC", $this->lng->txt("extt_ilinc_desc"));

	
		// icon handlers
		$icon_ok = "<img src=\"".ilUtil::getImagePath("icon_ok.gif")."\" alt=\"".$this->lng->txt("enabled")."\" title=\"".$this->lng->txt("enabled")."\" border=\"0\" vspace=\"0\"/>";
		$icon_not_ok = "<img src=\"".ilUtil::getImagePath("icon_not_ok.gif")."\" alt=\"".$this->lng->txt("disabled")."\" title=\"".$this->lng->txt("disabled")."\" border=\"0\" vspace=\"0\"/>";

		$this->tpl->setVariable("EXTT_ILINC_ACTIVE", $this->ilias->getSetting('ilinc_active') ? $icon_ok : $icon_not_ok);
	}
	
	function cancelObject()
	{
		sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "view");
	}

	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}	
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "view"), array("view","editiLinc",""), "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	/**
	* Configure iLinc settings
	* 
	* @access	public
	*/
	function editiLincObject()
	{
		global $rbacsystem, $rbacreview;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->__initSubTabs("editiLinc");
		
		if ($_SESSION["error_post_vars"])
		{
			if ($_SESSION["error_post_vars"]["ilinc"]["active"] == "1")
			{
				$this->tpl->setVariable("CHK_ILINC_ACTIVE", "checked=\"checked\"");
			}
			
			$this->tpl->setVariable("ILINC_SERVER", $_SESSION["error_post_vars"]["ilinc"]["server"]);
			$this->tpl->setVariable("ILINC_REGISTRAR_LOGIN", $_SESSION["error_post_vars"]["ilinc"]["registrar_login"]);
			$this->tpl->setVariable("ILINC_REGISTRAR_PASSWD", $_SESSION["error_post_vars"]["ilinc"]["registrar_passwd"]);
			$this->tpl->setVariable("ILINC_CUSTOMER_ID", $_SESSION["error_post_vars"]["ilinc"]["customer_id"]);
		}
		else
		{
			// set already saved data or default value for port
			$settings = $this->ilias->getAllSettings();

			if ($settings["ilinc_active"] == "1")
			{
				$this->tpl->setVariable("CHK_ILINC_ACTIVE", "checked=\"checked\"");
			}

			$this->tpl->setVariable("ILINC_SERVER", $settings["ilinc_server"].$settings["ilinc_path"]);
			$this->tpl->setVariable("ILINC_REGISTRAR_LOGIN", $settings["ilinc_registrar_login"]);
			$this->tpl->setVariable("ILINC_REGISTRAR_PASSWD", $settings["ilinc_registrar_passwd"]);
			$this->tpl->setVariable("ILINC_CUSTOMER_ID", $settings["ilinc_customer_id"]);
			
			if (empty($settings["ilinc_port"]))
			{
				$this->tpl->setVariable("ILINC_PORT", ILINC_DEFAULT_HTTP_PORT);
			}
			else
			{
				$this->tpl->setVariable("ILINC_PORT", $settings["ilinc_port"]);			
			}

			if ($settings["ilinc_protocol"] == "https")
			{
				$this->tpl->setVariable("ILINC_PROTOCOL_SSL_SEL", "selected=\"selected\"");
			}
			else
			{
				$this->tpl->setVariable("ILINC_PROTOCOL_HTTP_SEL", "selected=\"selected\"");		
			}
			
			if (empty($settings["ilinc_timeout"]))
			{
				$this->tpl->setVariable("ILINC_TIMEOUT", ILINC_DEFAULT_TIMEOUT);
			}
			else
			{
				$this->tpl->setVariable("ILINC_TIMEOUT", $settings["ilinc_timeout"]);			
			}		
		}

		$this->getTemplateFile("ilinc");
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));//"adm_object.php?ref_id=".$this->ref_id."&cmd=gateway");
		$this->tpl->setVariable("TXT_ILINC_TITLE", $this->lng->txt("extt_ilinc_configure"));
		$this->tpl->setVariable("TXT_ILINC_ACTIVE", $this->lng->txt("extt_ilinc_enable"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_ILINC_SERVER", $this->lng->txt("extt_ilinc_server"));
		$this->tpl->setVariable("TXT_ILINC_PROTOCOL_PORT", $this->lng->txt("extt_ilinc_protocol_port"));
		$this->tpl->setVariable("TXT_ILINC_TIMEOUT", $this->lng->txt("extt_ilinc_timeout"));
		$this->tpl->setVariable("ILINC_DEFAULT_HTTP_PORT", ILINC_DEFAULT_HTTP_PORT);
		$this->tpl->setVariable("ILINC_DEFAULT_SSL_PORT", ILINC_DEFAULT_SSL_PORT);
		$this->tpl->setVariable("TXT_HTTP", $this->lng->txt('http'));
		$this->tpl->setVariable("TXT_SSL", $this->lng->txt('ssl'));
		
		$this->tpl->setVariable("TXT_SECONDS", $this->lng->txt("seconds"));
		$this->tpl->setVariable("TXT_ILINC_REGISTRAR_LOGIN", $this->lng->txt("extt_ilinc_registrar_login"));
		$this->tpl->setVariable("TXT_ILINC_REGISTRAR_PASSWD", $this->lng->txt("extt_ilinc_registrar_passwd"));
		$this->tpl->setVariable("TXT_ILINC_CUSTOMER_ID", $this->lng->txt("extt_ilinc_customer_id"));

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveiLinc");
	}

	/**
	* validates all input data, save them to database if correct and active chosen extt mode
	* 
	* @access	public
	*/
	function saveiLincObject()
	{
         global $ilUser;

        // validate required data 
		if (!$_POST["ilinc"]["server"] or !$_POST["ilinc"]["port"] or !$_POST["ilinc"]["registrar_login"] or !$_POST["ilinc"]["registrar_passwd"] or !$_POST["ilinc"]["customer_id"])
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate port
		if ((preg_match("/^[0-9]{0,5}$/",$_POST["ilinc"]["port"])) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_invalid_port"),$this->ilias->error_obj->MESSAGE);
		}
		
		if (substr($_POST["ilinc"]["server"],0,8) != "https://" and substr($_POST["ilinc"]["server"],0,7) != "http://")
		{
			$_POST["ilinc"]["server"] = $_POST["ilinc"]["protocol"]."://".$_POST["ilinc"]["server"];
		}
		
		$url = parse_url($_POST["ilinc"]["server"]);
		
		if (!ilUtil::isIPv4($url["host"]) and !ilUtil::isDN($url["host"]))
		{
			$this->ilias->raiseError($this->lng->txt("err_invalid_server"),$this->ilias->error_obj->MESSAGE);
		}
		
		if (is_numeric($_POST["ilinc"]["timeout"]))
		{
			$this->ilias->setSetting("ilinc_timeout", $_POST["ilinc"]["timeout"]);
		}

		// all ok. save settings
		$this->ilias->setSetting("ilinc_server", $url["host"]);
		$this->ilias->setSetting("ilinc_path", $url["path"]);
		$this->ilias->setSetting("ilinc_protocol", $_POST["ilinc"]["protocol"]);
		$this->ilias->setSetting("ilinc_port", $_POST["ilinc"]["port"]);
		$this->ilias->setSetting("ilinc_active", $_POST["ilinc"]["active"]);
		$this->ilias->setSetting("ilinc_registrar_login", $_POST["ilinc"]["registrar_login"]);
		$this->ilias->setSetting("ilinc_registrar_passwd", $_POST["ilinc"]["registrar_passwd"]);
		$this->ilias->setSetting("ilinc_customer_id", $_POST["ilinc"]["customer_id"]);

		sendInfo($this->lng->txt("extt_ilinc_settings_saved"),true);
		$this->ctrl->redirect($this,'editiLinc');
	}
	
	// init sub tabs
	function __initSubTabs($a_cmd)
	{
		$ilinc = ($a_cmd == 'editiLinc') ? true : false;
		$overview = ($a_cmd == 'view' or $a_cmd == '') ? true : false;

		$this->tabs_gui->addSubTabTarget("overview", $this->ctrl->getLinkTarget($this, "view"),
										 "", "", "", $overview);
		$this->tabs_gui->addSubTabTarget("extt_ilinc", $this->ctrl->getLinkTarget($this, "editiLinc"),
										 "", "", "", $ilinc);
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
	
} // END class.ilObjExternalToolsSettingsGUI
?>
