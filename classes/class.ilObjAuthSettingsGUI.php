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
* Class ilObjAuthSettingsGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjAuthSettingsGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjAuthSettingsGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "auth";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
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
		
		$this->getTemplateFile("general");
		
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&cmd=gateway");
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_AUTH_TITLE", $this->lng->txt("auth_select"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_LOCAL", $this->lng->txt("auth_local"));
		$this->tpl->setVariable("TXT_LOCAL_DESC", $this->lng->txt("auth_local_desc"));
		$this->tpl->setVariable("TXT_LDAP", $this->lng->txt("auth_ldap"));
		$this->tpl->setVariable("TXT_LDAP_DESC", $this->lng->txt("auth_ldap_desc"));
		$this->tpl->setVariable("TXT_SHIB", $this->lng->txt("auth_shib"));
		$this->tpl->setVariable("TXT_SHIB_DESC", $this->lng->txt("auth_shib_desc"));

		$this->tpl->setVariable("TXT_RADIUS", $this->lng->txt("auth_radius"));
		$this->tpl->setVariable("TXT_RADIUS_DESC", $this->lng->txt("auth_radius_desc"));
		$this->tpl->setVariable("TXT_SCRIPT", $this->lng->txt("auth_script"));
		$this->tpl->setVariable("TXT_SCRIPT_DESC", $this->lng->txt("auth_script_desc"));

		$this->tpl->setVariable("TXT_CONFIGURE", $this->lng->txt("auth_configure"));
		$this->tpl->setVariable("TXT_AUTH_REMARK", $this->lng->txt("auth_remark_non_local_auth"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "setAuthMode");
				
		// local vars
		$checked = "checked=\"checked\"";
		$disabled = "disabled=\"disabled\"";
		$style_disabled = "_disabled";
		
		// alter style and disable buttons depending on current selection
		switch (AUTH_CURRENT)
		{
			case AUTH_LOCAL: // default
				$this->tpl->setVariable("CHK_LOCAL", $checked);
				$this->tpl->setVariable("SUB_LDAP", $style_disabled);
				$this->tpl->setVariable("BTN_LDAP", $disabled);
				//$this->tpl->setVariable("SUB_SHIB", $style_disabled);
				//$this->tpl->setVariable("BTN_SHIB", $disabled);	
				$this->tpl->setVariable("SUB_RADIUS", $style_disabled);
				$this->tpl->setVariable("BTN_RADIUS", $disabled);
				$this->tpl->setVariable("SUB_SCRIPT", $style_disabled);
				$this->tpl->setVariable("BTN_SCRIPT", $disabled);				
				break;
				
			case AUTH_LDAP: // LDAP
				$this->tpl->setVariable("CHK_LDAP", $checked);
				//$this->tpl->setVariable("SUB_SHIB", $style_disabled);
				//$this->tpl->setVariable("BTN_SHIB", $disabled);	
				$this->tpl->setVariable("SUB_RADIUS", $style_disabled);
				$this->tpl->setVariable("BTN_RADIUS", $disabled);
				$this->tpl->setVariable("SUB_SCRIPT", $style_disabled);
				$this->tpl->setVariable("BTN_SCRIPT", $disabled);	
				break;
				
			case AUTH_SHIBBOLETH: // SHIB
				$this->tpl->setVariable("BTN_LDAP", $disabled);
				$this->tpl->setVariable("SUB_LDAP", $style_disabled);
				$this->tpl->setVariable("SUB_SCRIPT", $style_disabled);
				$this->tpl->setVariable("CHK_SHIB", $checked);
				$this->tpl->setVariable("SUB_RADIUS", $style_disabled);
				$this->tpl->setVariable("BTN_RADIUS", $disabled);
				$this->tpl->setVariable("SUB_SCRIPT", $style_disabled);
				$this->tpl->setVariable("BTN_SCRIPT", $disabled);	
				break;
				
			case AUTH_RADIUS: // RADIUS
				//$this->tpl->setVariable("SUB_SHIB", $style_disabled);
				//$this->tpl->setVariable("BTN_SHIB", $disabled);	
				$this->tpl->setVariable("CHK_RADIUS", $checked);
				$this->tpl->setVariable("SUB_LDAP", $style_disabled);
				$this->tpl->setVariable("BTN_LDAP", $disabled);
				$this->tpl->setVariable("SUB_SCRIPT", $style_disabled);
				$this->tpl->setVariable("BTN_SCRIPT", $disabled);	
				break;
			
			case AUTH_SCRIPT: // script
				//$this->tpl->setVariable("SUB_SHIB", $style_disabled);
				//$this->tpl->setVariable("BTN_SHIB", $disabled);	
				$this->tpl->setVariable("CHK_SCRIPT", $checked);
				$this->tpl->setVariable("SUB_LDAP", $style_disabled);
				$this->tpl->setVariable("BTN_LDAP", $disabled);
				$this->tpl->setVariable("SUB_RADIUS", $style_disabled);
				$this->tpl->setVariable("BTN_RADIUS", $disabled);
				break;
		}
	}
	
	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff
			
		// always send a message
		sendInfo($this->lng->txt("object_added"),true);
		
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}
	
	function setAuthModeObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		if (empty($_POST["auth_mode"]))
		{
			$this->ilias->raiseError($this->lng->txt("auth_err_no_mode_selected"),$this->ilias->error_obj->MESSAGE);
		}
		
		if ($_POST["auth_mode"] == AUTH_CURRENT)
		{
			sendInfo($this->lng->txt("auth_mode").": ".$this->getAuthModeTitle()." ".$this->lng->txt("auth_mode_not_changed"),true);
			ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"")));
		}

		switch ($_POST["auth_mode"])
		{
			case AUTH_LDAP:
				if ($this->object->checkAuthLDAP() !== true)
				{
					sendInfo($this->lng->txt("auth_ldap_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"editLDAP")));
				}
				break;
				
				case AUTH_SHIB:
				if ($this->object->checkAuthSHIB() !== true)
				{
					sendInfo($this->lng->txt("auth_shib_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"editSHIB")));
				}
				break;

			case AUTH_RADIUS:
				if ($this->object->checkAuthRADIUS() !== true)
				{
					sendInfo($this->lng->txt("auth_radius_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"editRADIUS")));
				}
				break;

			case AUTH_SCRIPT:
				if ($this->object->checkAuthScript() !== true)
				{
					sendInfo($this->lng->txt("auth_script_not_configured"),true);
					ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"editScript")));
				}
				break;
		}
		
		$this->ilias->setSetting("auth_mode",$_POST["auth_mode"]);
		
		sendInfo($this->lng->txt("auth_mode_changed_to")." ".$this->getAuthModeTitle(),true);
		ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"")));
	}
	
	/**
	* Configure LDAP settings
	* 
	* @access	public
	*/
	function editLDAPObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		if ($_SESSION["error_post_vars"])
		{
			if ($_SESSION["error_post_vars"]["ldap"]["tls"] == "1")
			{
				$this->tpl->setVariable("LDAP_TLS_CHK", "checked=\"checked\"");
			}
			
			if ($_SESSION["error_post_vars"]["ldap"]["version"] == "3")
			{
				$this->tpl->setVariable("LDAP_VERSION3_CHK", "checked=\"checked\"");
			}
			else
			{
				$this->tpl->setVariable("LDAP_VERSION2_CHK", "checked=\"checked\"");
			}
			
			$this->tpl->setVariable("LDAP_SERVER", $_SESSION["error_post_vars"]["ldap"]["server"]);
			$this->tpl->setVariable("LDAP_BASEDN", $_SESSION["error_post_vars"]["ldap"]["basedn"]);
			$this->tpl->setVariable("LDAP_SEARCH_BASE", $_SESSION["error_post_vars"]["ldap"]["search_base"]);
			$this->tpl->setVariable("LDAP_PORT", $_SESSION["error_post_vars"]["ldap"]["port"]);
			$this->tpl->setVariable("LDAP_LOGIN_KEY", $_SESSION["error_post_vars"]["ldap"]["login_key"]);
			$this->tpl->setVariable("LDAP_OBJECTCLASS", $_SESSION["error_post_vars"]["ldap"]["objectclass"]);
		}
		else
		{
			// set already saved data or default value for port
			$settings = $this->ilias->getAllSettings();

			if ($settings["ldap_tls"] == "1")
			{
				$this->tpl->setVariable("LDAP_TLS_CHK", "checked=\"checked\"");
			}

			$this->tpl->setVariable("LDAP_SERVER", $settings["ldap_server"]);
			$this->tpl->setVariable("LDAP_BASEDN", $settings["ldap_basedn"]);
			$this->tpl->setVariable("LDAP_SEARCH_BASE", $settings["ldap_search_base"]);
			
			if (empty($settings["ldap_port"]))
			{
				$this->tpl->setVariable("LDAP_PORT", "389");
			}
			else
			{
				$this->tpl->setVariable("LDAP_PORT", $settings["ldap_port"]);			
			}

			if (empty($settings["ldap_login_key"]))
			{
				$this->tpl->setVariable("LDAP_LOGIN_KEY", "uid");
			}
			else
			{
				$this->tpl->setVariable("LDAP_LOGIN_KEY", $settings["ldap_login_key"]);			
			}
			
			if (empty($settings["ldap_objectclass"]))
			{
				$this->tpl->setVariable("LDAP_OBJECTCLASS", "posixAccount");
			}
			else
			{
				$this->tpl->setVariable("LDAP_OBJECTCLASS", $settings["ldap_objectclass"]);
			}

			if (empty($settings["ldap_version"]) or $settings["ldap_version"] == "2")
			{
				$this->tpl->setVariable("LDAP_VERSION2_CHK", "checked=\"checked\"");
			}
			else
			{
				$this->tpl->setVariable("LDAP_VERSION3_CHK", "checked=\"checked\"");			
			}
		}

		$this->getTemplateFile("ldap");
		
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&cmd=gateway");
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_LDAP_TITLE", $this->lng->txt("ldap_configure"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_LDAP_TLS", $this->lng->txt("ldap_tls"));
		$this->tpl->setVariable("TXT_LDAP_SERVER", $this->lng->txt("ldap_server"));
		$this->tpl->setVariable("TXT_LDAP_BASEDN", $this->lng->txt("ldap_basedn"));
		$this->tpl->setVariable("TXT_LDAP_SEARCH_BASE", $this->lng->txt("ldap_search_base"));
		$this->tpl->setVariable("TXT_LDAP_PORT", $this->lng->txt("ldap_port"));
		$this->tpl->setVariable("TXT_LDAP_TLS", $this->lng->txt("ldap_tls"));

		$this->tpl->setVariable("TXT_LDAP_VERSION", $this->lng->txt("ldap_version"));
		$this->tpl->setVariable("TXT_LDAP_VERSION2", $this->lng->txt("ldap_v2"));
		$this->tpl->setVariable("TXT_LDAP_VERSION3", $this->lng->txt("ldap_v3"));

		$this->tpl->setVariable("TXT_LDAP_LOGIN_KEY", $this->lng->txt("ldap_login_key"));
		$this->tpl->setVariable("TXT_LDAP_OBJECTCLASS", $this->lng->txt("ldap_objectclass"));
				
		$this->tpl->setVariable("TXT_LDAP_PASSWD", $this->lng->txt("ldap_passwd"));

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveLDAP");
	}


	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveLDAPObject()
	{
        global $ilUser;

        // validate required data 
		if (!$_POST["ldap"]["server"] or !$_POST["ldap"]["basedn"] or !$_POST["ldap"]["port"] or !$_POST["ldap"]["login_key"] or !$_POST["ldap"]["objectclass"])
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate password 
		if (!$_POST["ldap"]["passwd"])
		{
			$this->ilias->raiseError($this->lng->txt("err_enter_current_passwd"),$this->ilias->error_obj->MESSAGE);
		}

		// validate port
		if ((preg_match("/^[0-9]{0,5}$/",$_POST["ldap"]["port"])) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_invalid_port"),$this->ilias->error_obj->MESSAGE);
		}
		
		// TODO: implement TLS support
		if ($_POST["ldap"]["tls"] != "1")
		{
			$_POST["ldap"]["tls"] = "0";
		}	
		
		// check connection to ldap server
		
		
		$ldap_host	= $_POST["ldap"]["server"];
		$ldap_port	= $_POST["ldap"]["port"];
		$ldap_pass	= $_POST["ldap"]["passwd"];
		
		$ldap_userattr = $_POST["ldap"]["login_key"];
		$ldap_useroc = $_POST["ldap"]["objectclass"];

		$ldap_dn	= $ldap_userattr."=".$this->ilias->account->getLogin().",";

        // create base_dn
        if ($_POST["ldap"]["search_base"])
		{
			$ldap_searchbase .= $_POST["ldap"]["search_base"].",";
		}
		
		$ldap_searchbase 	.= $_POST["ldap"]["basedn"];
		
		$ldap_dn .= $ldap_searchbase;
		
		// test connection
		$ldap_conn = ldap_connect($ldap_host,$ldap_port);

		@ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, $_POST["ldap"]["version"]);
		
		// bind anonymously
		if (($ldap_bind = ldap_bind($ldap_conn)) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_ldap_connect_failed"),$this->ilias->error_obj->MESSAGE);
		}

        // make user search
        $filter = sprintf('(&(objectClass=%s)(%s=%s))', $ldap_useroc, $ldap_userattr, $ilUser->getLogin());

        // make functions params array
        $func_params = array($ldap_conn, $ldap_searchbase, $filter, array($ldap_userattr));

        // search
        if (($result_id = @call_user_func_array('ldap_search', $func_params)) == false)
        {
   			$this->ilias->raiseError($this->lng->txt("err_ldap_search_failed"),$this->ilias->error_obj->MESSAGE);
        }

        if (ldap_count_entries($ldap_conn, $result_id) != 1)
        {
   			$this->ilias->raiseError($this->lng->txt("err_ldap_user_not_found"),$this->ilias->error_obj->MESSAGE);
        }

        // then get the user dn
        $entry_id = ldap_first_entry($ldap_conn, $result_id);
        $user_dn  = ldap_get_dn($ldap_conn, $entry_id);

        ldap_free_result($result_id);

        // bind with password
        if (@ldap_bind($ldap_conn, $user_dn, $ldap_pass) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_ldap_auth_failed"),$this->ilias->error_obj->MESSAGE);
		}

		// close connection
		@ldap_unbind($ldap_conn);

		// all ok. save settings and activate LDAP
		$this->ilias->setSetting("ldap_tls", $_POST["ldap"]["tls"]);
		$this->ilias->setSetting("ldap_server", $_POST["ldap"]["server"]);
		$this->ilias->setSetting("ldap_basedn", $_POST["ldap"]["basedn"]);
		$this->ilias->setSetting("ldap_search_base", $_POST["ldap"]["search_base"]);
		$this->ilias->setSetting("ldap_port", $_POST["ldap"]["port"]);
		$this->ilias->setSetting("ldap_version", $_POST["ldap"]["version"]);
		$this->ilias->setSetting("ldap_login_key", $_POST["ldap"]["login_key"]);
		$this->ilias->setSetting("ldap_objectclass", $_POST["ldap"]["objectclass"]);
		$this->ilias->setSetting("auth_mode", AUTH_LDAP);

		sendInfo($this->lng->txt("auth_mode_changed_to")." ".$this->getAuthModeTitle(),true);
		ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"")));
	}

	/**
	* Configure SHIB settings
	* 
	* @access	public
	*/
	function editSHIBObject()
	{
		global $rbacsystem, $rbacreview;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
			// set already saved data or default value for port
		$settings = $this->ilias->getAllSettings();
		
		// Compose role list
		$role_list = $rbacreview->getRolesByFilter(2,$this->object->getId());
		$selectElement = '<select name="shib[user_default_role]">';
		
		if (!$settings["shib_user_default_role"])
		{
			$settings["shib_user_default_role"] = 4;
		}
			
		foreach ($role_list as $role){
			$selectElement .= '<option value="'.$role['obj_id'].'"';
			if ($settings["shib_user_default_role"] == $role['obj_id'])
				$selectElement .= 'selected="selected"';
			
			$selectElement .= '>'.$role['title'].'</option>';
		}
		$selectElement .= '</select>';
		
		
		// Set text field content
		$shib_settings = array(
								'shib_login',
								'shib_title',
								'shib_firstname',
								'shib_lastname',
								'shib_email',
								'shib_gender',
								'shib_institution',
								'shib_department',
								'shib_zipcode',
								'shib_city',
								'shib_country',
								'shib_street',
								'shib_phone_office',
								'shib_phone_home',
								'shib_phone_mobile',
								'shib_language'
								);
		
		$this->getTemplateFile("shib");
		
		foreach ($shib_settings as $setting)
		{
			$field = ereg_replace('shib_','',$setting);
			$this->tpl->setVariable(strtoupper($setting), $settings[$setting]);
			$this->tpl->setVariable('SHIB_UPDATE_'.strtoupper($field), $settings["shib_update_".$field]);
			
			if ($settings["shib_update_".$field])
				$this->tpl->setVariable('CHK_SHIB_UPDATE_'.strtoupper($field), 'checked="checked"');
		}
		if ($settings["shib_active"])
		{
			$this->tpl->setVariable("CHK_SHIB_ACTIVE", 'checked="checked"');
		}
		
		$this->tpl->setVariable("SHIB_USER_DEFAULT_ROLE", $selectElement);
		$this->tpl->setVariable("SHIB_LOGIN_BUTTON", $settings["shib_login_button"]);
		$this->tpl->setVariable("SHIB_LOGIN_INSTRUCTIONS", $settings["shib_login_instructions"]);
		$this->tpl->setVariable("SHIB_DATA_CONV", $settings["shib_data_conv"]);
		
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&cmd=gateway");
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_SHIB_INSTRUCTIONS", $this->lng->txt("shib_instructions"));
		$this->tpl->setVariable("TXT_SHIB", $this->lng->txt("shib"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_SHIB_UPDATE", $this->lng->txt("shib_update"));
		$this->tpl->setVariable("TXT_SHIB_ACTIVE", $this->lng->txt("shib_active"));
		$this->tpl->setVariable("TXT_SHIB_USER_DEFAULT_ROLE", $this->lng->txt("shib_user_default_role"));
		$this->tpl->setVariable("TXT_SHIB_LOGIN_BUTTON", $this->lng->txt("shib_login_button"));
		$this->tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS", $this->lng->txt("shib_login_instructions"));
		$this->tpl->setVariable("TXT_SHIB_DATA_CONV", $this->lng->txt("shib_data_conv"));
		foreach ($shib_settings as $setting)
		{
			$this->tpl->setVariable("TXT_".strtoupper($setting), $this->lng->txt($setting));
		}
		
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveSHIB");
		
		// Set some default values
		if (!$settings["shib_login_instructions"] || $settings["shib_login_instructions"] == '')
		{
			$this->tpl->setVariable("SHIB_LOGIN_INSTRUCTIONS", "Login for Shibboleth users");
		}
		
		if (!$settings["shib_login_button"] || $settings["shib_login_button"] == '')
		{
			$this->tpl->setVariable("SHIB_LOGIN_BUTTON", "images/shib_login_button.gif");
		}
	}

	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveSHIBObject()
	{
        global $ilUser;

        // validate required data 
		if (
			!$_POST["shib"]["login"] 
			or !$_POST["shib"]["firstname"] 
			or !$_POST["shib"]["lastname"] 
			or !$_POST["shib"]["email"] 
			or !$_POST["shib"]["user_default_role"]
			)
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		
		// validate port
		if (!is_readable($_POST["shib"]["data_conv"]) )
		{
			$this->ilias->raiseError($this->lng->txt("shib_data_conv_warning"),$this->ilias->error_obj->MESSAGE);
		}
		
		// all ok. save settings
		$shib_settings = array(
								'shib_login',
								'shib_title',
								'shib_firstname',
								'shib_lastname',
								'shib_email',
								'shib_gender',
								'shib_institution',
								'shib_department',
								'shib_zipcode',
								'shib_city',
								'shib_country',
								'shib_street',
								'shib_phone_office',
								'shib_phone_home',
								'shib_phone_mobile',
								'shib_language'
								);
		
		foreach ($shib_settings as $setting)
		{
			$field = ereg_replace('shib_','',$setting);
			if ($_POST["shib"]["update_".$field] != "1")
				$_POST["shib"]["update_".$field] = "0";
			$this->ilias->setSetting($setting, $_POST["shib"][$field]);
			$this->ilias->setSetting("shib_update_".$field, $_POST["shib"]["update_".$field]);
		}
		
		if ($_POST["shib"]["active"] != "1")
		{
		$this->ilias->setSetting("shib_active", "0");
		}
		else
		{
			$this->ilias->setSetting("shib_active", "1");
		}
		
		$this->ilias->setSetting("shib_user_default_role", $_POST["shib"]["user_default_role"]);
		$this->ilias->setSetting("shib_login_instructions", $_POST["shib"]["login_instructions"]);
		$this->ilias->setSetting("shib_login_button", $_POST["shib"]["login_button"]);
		$this->ilias->setSetting("shib_data_conv", $_POST["shib"]["data_conv"]);
	
		sendInfo($this->lng->txt("shib_settings_saved"),true);
		ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"")));
	}

	/**
	* Configure Custom settings
	* 
	* @access	public
	*/
	function editScriptObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		if ($_SESSION["error_post_vars"])
		{
			$this->tpl->setVariable("AUTH_SCRIPT_NAME", $_SESSION["error_post_vars"]["auth_script"]["name"]);
		}
		else
		{
			// set already saved data
			$settings = $this->ilias->getAllSettings();

			$this->tpl->setVariable("AUTH_SCRIPT_NAME", $settings["auth_script_name"]);
		}

		$this->getTemplateFile("script");
		
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&cmd=gateway");
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_AUTH_SCRIPT_TITLE", $this->lng->txt("auth_script_configure"));
		$this->tpl->setVariable("TXT_OPTIONS", $this->lng->txt("options"));
		$this->tpl->setVariable("TXT_AUTH_SCRIPT_NAME", $this->lng->txt("auth_script_name"));
		
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveScript");
	}

	/**
	* validates all input data, save them to database if correct and active chosen auth mode
	* 
	* @access	public
	*/
	function saveScriptObject()
	{
		// validate required data 
		if (!$_POST["auth_script"]["name"])
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}

		// validate script url
		/*
		if (( TODO ,$_POST["ldap"]["server"])) == false)
		{
			$this->ilias->raiseError($this->lng->txt("err_invalid_server"),$this->ilias->error_obj->MESSAGE);
		}*/
		
		// TODO: check connection to server
		
		// all ok. save settings and activate auth by external script
		$this->ilias->setSetting("auth_script_name", $_POST["auth_script"]["name"]);
		$this->ilias->setSetting("auth_mode", AUTH_SCRIPT);

		sendInfo($this->lng->txt("auth_mode_changed_to")." ".$this->getAuthModeTitle(),true);
		ilUtil::redirect($this->getReturnLocation("view",$this->ctrl->getLinkTarget($this,"")));
	}
	
	/**
	* get the title of auth mode
	* 
	* @access	public
	* @return	string	language dependent title of auth mode
	*/
	function getAuthModeTitle()
	{
		switch ($this->ilias->getSetting("auth_mode"))
		{
			case AUTH_LOCAL:
				return $this->lng->txt("auth_local");
				break;
			
			case AUTH_LDAP:
				return $this->lng->txt("auth_ldap");
				break;
			
			case AUTH_SHIBBOLETH:
				return $this->lng->txt("auth_shib");
				break;

			case AUTH_RADIUS:
				return $this->lng->txt("auth_radius");
				break;
		
			case AUTH_SCRIPT:
				return $this->lng->txt("auth_script");
				break;

			default:
				return $this->lng->txt("unknown");
				break;
		}
	}
} // END class.ilObjAuthSettingsGUI
?>
