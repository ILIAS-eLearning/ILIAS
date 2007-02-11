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
* Class ilObjExternalToolsSettingsGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @ilCtrl_Calls ilObjExternalToolsSettingsGUI: ilPermissionGUI
* 
* @extends ilObjectGUI
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
		global $lng;
		
		$this->type = "extt";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		
		define ("ILINC_DEFAULT_HTTP_PORT",80);
		define ("ILINC_DEFAULT_SSL_PORT",443);
		define ("ILINC_DEFAULT_TIMEOUT",30);
		$lng->loadLanguageModule("delic");
		$lng->loadLanguageModule("gmaps");
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
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
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
				$this->ctrl->getLinkTarget($this, "view"), array("view","editiLinc","editDelicious", "editGoogleMaps",""), "", "");
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
			
			if ($_SESSION["error_post_vars"]["ilinc"]["akclassvalues_active"] == "1")
			{
				$this->tpl->setVariable("CHK_ILINC_AKCLASSVALUES_ACTIVE", "checked=\"checked\"");
			}
			
			if ($_SESSION["error_post_vars"]["ilinc"]["akclassvalues_required"] == "1")
			{
				$this->tpl->setVariable("CHK_ILINC_AKCLASSVALUES_REQUIRED", "checked=\"checked\"");
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

			if ($settings["ilinc_akclassvalues_active"] == "1")
			{
				$this->tpl->setVariable("CHK_ILINC_AKCLASSVALUES_ACTIVE", "checked=\"checked\"");
			}

			if ($settings["ilinc_akclassvalues_required"] == "1")
			{
				$this->tpl->setVariable("CHK_ILINC_AKCLASSVALUES_REQUIRED", "checked=\"checked\"");
			}	
		}

		$this->getTemplateFile("ilinc");
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_ILINC_TITLE", $this->lng->txt("extt_ilinc_configure"));
		$this->tpl->setVariable("TXT_ILINC_ACTIVE", $this->lng->txt("extt_ilinc_enable"));
		$this->tpl->setVariable("TXT_ILINC_CONNECTION_DATA", $this->lng->txt("extt_ilinc_connection_data"));
		$this->tpl->setVariable("TXT_ILINC_ADDITIONAL_OPTIONS", $this->lng->txt("extt_ilinc_additional_options"));
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
		
		$this->tpl->setVariable("TXT_ILINC_AKCLASSVALUES_ACTIVE", $this->lng->txt("extt_ilinc_akclassvalues_active"));
		$this->tpl->setVariable("TXT_ILINC_AKCLASSVALUES_ACTIVE_INFO", $this->lng->txt("extt_ilinc_akclassvalues_active_info"));
		$this->tpl->setVariable("TXT_ILINC_AKCLASSVALUES_REQUIRED", $this->lng->txt("extt_ilinc_akclassvalues_required"));
		$this->tpl->setVariable("TXT_ILINC_AKCLASSVALUES_REQUIRED_INFO", $this->lng->txt("extt_ilinc_akclassvalues_required_info"));

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
		
		$this->ilias->setSetting("ilinc_akclassvalues_active", $_POST["ilinc"]["akclassvalues_active"]);
		$this->ilias->setSetting("ilinc_akclassvalues_required", $_POST["ilinc"]["akclassvalues_required"]);

		ilUtil::sendInfo($this->lng->txt("extt_ilinc_settings_saved"),true);
		$this->ctrl->redirect($this,'editiLinc');
	}
	
	/**
	* Configure delicious settings
	* 
	* @access	public
	*/
	function editDeliciousObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl;
		
		$d_set = new ilSetting("delicious");
		
		$this->getTemplateFile("delicious");
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->__initSubTabs("editDelicious");

		if ($d_set->get("add_info_links") == "1")
		{
			$this->tpl->setVariable("CHK_ADD_LINKS_ACTIVE", "checked=\"checked\"");
		}
		
		if ($d_set->get("user_profile") == "1")
		{
			$this->tpl->setVariable("CHK_ADD_PROFILE_ACTIVE", "checked=\"checked\"");
		}
		
		$this->tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));
		$this->tpl->setVariable("TXT_DELICIOUS_SETTINGS", $lng->txt("delic_settings"));
		$this->tpl->setVariable("TXT_ADD_DELICIOUS_LINK", $lng->txt("delic_add_info_links"));
		$this->tpl->setVariable("TXT_ALLOW_PROFILE", $lng->txt("delic_user_profile"));
		$this->tpl->setVariable("TXT_CANCEL", $lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveDelicious");
	}


	/**
	* Save Delicious Setttings
	*/
	function saveDeliciousObject()
	{
		global $ilCtrl;

		$d_set = new ilSetting("delicious");
		
		$d_set->set("add_info_links", $_POST["add_info_links"]);
		$d_set->set("user_profile", $_POST["user_profile"]);
		$ilCtrl->redirect($this, "editDelicious");
	}
	

	/**
	* Configure google maps settings
	* 
	* @access	public
	*/
	function editGoogleMapsObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl, $tpl;
		
		$gm_set = new ilSetting("google_maps");
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->__initSubTabs("editGoogleMaps");

		$api_key = $gm_set->get("api_key");
		$std_latitude = $gm_set->get("std_latitude");
		$std_longitude = $gm_set->get("std_longitude");
		$api_url = "http://www.google.com/apis/maps/signup.html";
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("gmaps_settings"));
		$form->addTextProperty($lng->txt("gmaps_api_key"), "api_key", $api_key,
			$lng->txt("gmaps_api_key_desc").' <a href="'.$api_url.'">'.$api_url.'</a>',
			"", true, 200, 60);
			
		// test
		$form->addRadioProperty("Test", "test", array(
			array("value" => 1, "text" => "op1"),array("value" => 2, "text" => "op2")),
			"1");
		$form->addSectionHeader("Hello World");
		$form->addFileProperty("File", "file");
		$form->addSelectProperty("Select", "sel", array(
			array("value" => 1, "text" => "sel1"),array("value" => 2, "text" => "sel2")),
			2);
		$form->addDateTimeProperty("datetime", "datetime", "2007-12-24", true,
			"12:13:27", true, true);
		$form->addDateTimeProperty("End Date", "enddate", "", true,
			"11:22", true, false);
			
		$form->addLocationProperty($lng->txt("gmaps_std_location"), "std_location", $std_latitude,
			$std_longitude, $lng->txt("gmaps_std_location_desc"));
		$form->addCommandButton("saveGoogleMaps", $lng->txt("save"));
		$form->addCommandButton("view", $lng->txt("cancel"));
		
		$tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}


	/**
	* Save Google Maps Setttings
	*/
	function saveGoogleMapsObject()
	{
		global $ilCtrl;

		$gm_set = new ilSetting("google_maps");
		
		$gm_set->set("api_key", ilUtil::stripSlashes($_POST["api_key"]));
		$gm_set->set("std_latitude", ilUtil::stripSlashes($_POST["std_location"]["latitude"]));
		$gm_set->set("std_longitude", ilUtil::stripSlashes($_POST["std_location"]["longitude"]));
		
		$ilCtrl->redirect($this, "editGoogleMaps");
	}
	
	// init sub tabs
	function __initSubTabs($a_cmd)
	{
		$ilinc = ($a_cmd == 'editiLinc') ? true : false;
		$overview = ($a_cmd == 'view' or $a_cmd == '') ? true : false;
		$delicious = ($a_cmd == 'editDelicious') ? true : false;
		$gmaps = ($a_cmd == 'editGoogleMaps') ? true : false;

		$this->tabs_gui->addSubTabTarget("overview", $this->ctrl->getLinkTarget($this, "view"),
										 "", "", "", $overview);
		$this->tabs_gui->addSubTabTarget("delic_extt_delicious", $this->ctrl->getLinkTarget($this, "editDelicious"),
										 "", "", "", $delicious);
		$this->tabs_gui->addSubTabTarget("gmaps_extt_gmaps", $this->ctrl->getLinkTarget($this, "editGoogleMaps"),
										 "", "", "", $gmaps);
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
