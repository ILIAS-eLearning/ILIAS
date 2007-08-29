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
* Class ilObjUserGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjUserGUI: ilLearningProgressGUI, ilObjiLincUserGUI
*
* @extends ilObjectGUI
*/

require_once "./classes/class.ilObjectGUI.php";

class ilObjUserGUI extends ilObjectGUI
{
	var $ilCtrl;

	/**
	* array of gender abbreviations
	* @var		array
	* @access	public
	*/
	var $gender;

	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* userfolder ref_id where user is assigned to
	* @var		string
	* @access	public
	*/
	var $user_ref_id;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjUserGUI($a_data,$a_id,$a_call_by_reference = false, $a_prepare_output = true)
	{
		global $ilCtrl;

		define('USER_FOLDER_ID',7);

		$this->type = "usr";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		$this->usrf_ref_id =& $this->ref_id;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,'obj_id');
		
		// for gender selection. don't change this
		// maybe deprecated
		$this->gender = array(
							  'm'    => "salutation_m",
							  'f'    => "salutation_f"
							  );
	}

	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		if ($cmd != "deliverVCard" && $cmd != "getPublicProfile")
		{
			$this->prepareOutput();
		}

		switch($next_class)
		{
			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui =& new ilLearningProgressGUI(LP_MODE_USER_FOLDER,USER_FOLDER_ID,$this->object->getId());
				$this->ctrl->forwardCommand($new_gui);
				break;
		
			case "ilobjilincusergui":
				include_once './ilinc/classes/class.ilObjiLincUserGUI.php';
				$new_gui =& new ilObjiLincUserGUI($this->object,$this->usrf_ref_id);
				$this->ctrl->forwardCommand($new_gui);
				break;


			default:
				if($cmd == "" || $cmd == "view")
				{
					$cmd = "edit";
				}
				$cmd .= "Object";
				$return = $this->$cmd();
					
				break;
		}
		return $return;
	}


	function cancelObject()
	{
		session_unregister("saved_post");

		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		if(strtolower($_GET["baseClass"]) == 'iladministrationgui')
		{
			$this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
			//$return_location = $_GET["cmd_return_location"];
			//ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
		}
		else
		{
			$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
		}
	}
	
	/**
	* admin and normal tabs are equal for roles
	*/
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	/**
	* get tabs 
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;
		
		$tabs_gui->clearTargets();
		
		if ($_GET["search"])
		{
			$tabs_gui->setBackTarget(
				$this->lng->txt("search_results"),$_SESSION["usr_search_link"]);

			$tabs_gui->addTarget("properties",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit","","view"), get_class($this),"",true);
		}
		else
		{
			$tabs_gui->addTarget("properties",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit","","view"), get_class($this));
		}
			
		$tabs_gui->addTarget("role_assignment",
			$this->ctrl->getLinkTarget($this, "roleassignment"), array("roleassignment"), get_class($this));

		// learning progress
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if($rbacsystem->checkAccess('read',$this->ref_id) and ilObjUserTracking::_enabledLearningProgress())
		{

			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass('illearningprogressgui',''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		
		if ($this->ilias->getSetting("ilinc_active"))
		{
			$tabs_gui->addTarget("extt_ilinc",
			$this->ctrl->getLinkTargetByClass('ilobjilincusergui',''),
			'',
			array('ilobjilincusergui'));
		}
	}

	/**
	* set back tab target
	*/
	function setBackTarget($a_text, $a_link)
	{
		$this->back_target = array("text" => $a_text,
			"link" => $a_link);
	}

	/**
	* display user create form
	*/
	function createObject()
	{
		global $ilias, $rbacsystem, $rbacreview, $styleDefinition, $ilSetting,$ilUser;
		
		//load ILIAS settings
		$settings = $ilias->getAllSettings();
		
		if (!$rbacsystem->checkAccess('create_user', $this->usrf_ref_id) and
			!$rbacsystem->checkAccess('cat_administrate_users',$this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		if($this->usrf_ref_id != USER_FOLDER_ID)
		{
			$this->tabs_gui->clearTargets();
		}

		// role selection
		$obj_list = $rbacreview->getRoleListByObject(ROLE_FOLDER_ID);
		$rol = array();
		foreach ($obj_list as $obj_data)
		{
			// allow only 'assign_users' marked roles if called from category
			if($this->object->getRefId() != USER_FOLDER_ID and !in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
			{
				include_once './classes/class.ilObjRole.php';
		
				if(!ilObjRole::_getAssignUsersStatus($obj_data['obj_id']))
				{
					continue;
				}
			}
			// exclude anonymous role from list
			if ($obj_data["obj_id"] != ANONYMOUS_ROLE_ID)
			{
				// do not allow to assign users to administrator role if current user does not has SYSTEM_ROLE_ID
				if ($obj_data["obj_id"] != SYSTEM_ROLE_ID or in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
				{
					$rol[$obj_data["obj_id"]] = $obj_data["title"];
				}
			}
		}
		
		// raise error if there is no global role user can be assigned to
		if(!count($rol))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_roles_users_can_be_assigned_to"),$this->ilias->error_obj->MESSAGE);
		}
		
		$keys = array_keys($rol);
		
		// set pre defined user role to default
		if (in_array(4,$keys))
		{
			$default_role = 4;
		}
		else
		{
			if (count($keys) > 1 and in_array(2,$keys))
			{
				// remove admin role as preselectable role
				foreach ($keys as $key => $val)
				{
					if ($val == 2)
					{
						unset($keys[$key]);
						break;
					}
				}
			}
		
			$default_role = array_shift($keys);
		}
		
		$pre_selected_role = (isset($_SESSION["error_post_vars"]["Fobject"]["default_role"])) ? $_SESSION["error_post_vars"]["Fobject"]["default_role"] : $default_role;
		
		$roles = ilUtil::formSelect($pre_selected_role,"Fobject[default_role]",$rol,false,true);
		
		$data = array();
		$data["fields"] = array();
		$data["fields"]["login"] = "";
		$data["fields"]["passwd"] = "";
		#$data["fields"]["passwd2"] = "";
		$data["fields"]["title"] = "";
		$data["fields"]["ext_account"] = "";
		$data["fields"]["gender"] = "";
		$data["fields"]["firstname"] = "";
		$data["fields"]["lastname"] = "";
		$data["fields"]["institution"] = "";
		$data["fields"]["department"] = "";
		$data["fields"]["street"] = "";
		$data["fields"]["city"] = "";
		$data["fields"]["zipcode"] = "";
		$data["fields"]["country"] = "";
		$data["fields"]["phone_office"] = "";
		$data["fields"]["phone_home"] = "";
		$data["fields"]["phone_mobile"] = "";
		$data["fields"]["fax"] = "";
		$data["fields"]["email"] = "";
		$data["fields"]["hobby"] = "";
		$data["fields"]["im_icq"] = "";
		$data["fields"]["im_yahoo"] = "";
		$data["fields"]["im_msn"] = "";
		$data["fields"]["im_aim"] = "";
		$data["fields"]["im_skype"] = "";
		$data["fields"]["matriculation"] = "";
		$data["fields"]["client_ip"] = "";
		$data["fields"]["referral_comment"] = "";
		$data["fields"]["create_date"] = "";
		$data["fields"]["approve_date"] = "";
		$data["fields"]["active"] = " checked=\"checked\"";
		$data["fields"]["default_role"] = $roles;
		$data["fields"]["auth_mode"] = "";
		
		$this->getTemplateFile("edit","usr");
		
		// fill presets
		foreach ($data["fields"] as $key => $val)
		{
			$str = $this->lng->txt($key);
			if ($key == "title")
			{
				$str = $this->lng->txt("person_title");
			}
			if ($key == "ext_account")
			{
				continue;
			}
			if($key == 'passwd')
			{
				$this->tpl->setCurrentBlock('passwords_visible');
				$this->tpl->setVariable('VISIBLE_TXT_PASSWD',$this->lng->txt('passwd'));
				$this->tpl->setVariable('VISIBLE_TXT_PASSWD2',$this->lng->txt('retype_password'));
				$this->tpl->setVariable('VISIBLE_PASSWD',$_SESSION['error_post_vars']['Fobject']['passwd']);
				$this->tpl->setVariable('VISIBLE_PASSWD2',$_SESSION['error_post_vars']['Fobject']['passwd2']);
				$this->tpl->parseCurrentBlock();
			}
		
			// check to see if dynamically required
			if (isset($settings["require_" . $key]) && $settings["require_" . $key])
			{
				$str = $str . '<span class="asterisk">*</span>';
			}
		
			$this->tpl->setVariable("TXT_".strtoupper($key), $str);
		
			if ($key == "default_role")
			{
				$this->tpl->setVariable(strtoupper($key), $val);
			}
			else
			{
				$this->tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val));
			}
		
			if ($this->prepare_output)
			{
				$this->tpl->parseCurrentBlock();
			}
		}
		
		// new account mail
		include_once './Services/User/classes/class.ilObjUserFolder.php';
		$amail = ilObjUserFolder::_lookupNewAccountMail($this->lng->getDefaultLanguage());
		if (trim($amail["body"]) != "" && trim($amail["subject"]) != "")
		{
			$this->tpl->setCurrentBlock("inform_user");
			if (true)
			{
				$this->tpl->setVariable("SEND_MAIL", " checked=\"checked\"");
			}
			$this->tpl->setVariable("TXT_INFORM_USER_MAIL",
				$this->lng->txt("user_send_new_account_mail"));
			$this->tpl->parseCurrentBlock();
		}

		$this->ctrl->setParameter($this,'new_type',$this->type);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));		
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($this->type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		$this->tpl->setVariable("TXT_LOGIN_DATA", $this->lng->txt("login_data"));
		$this->tpl->setVariable("TXT_SYSTEM_INFO", $this->lng->txt("system_information"));
		$this->tpl->setVariable("TXT_PERSONAL_DATA", $this->lng->txt("personal_data"));
		$this->tpl->setVariable("TXT_CONTACT_DATA", $this->lng->txt("contact_data"));
		$this->tpl->setVariable("TXT_SETTINGS", $this->lng->txt("settings"));
		$this->tpl->setVariable("TXT_PASSWD2", $this->lng->txt("retype_password"));
		$this->tpl->setVariable("TXT_LANGUAGE",$this->lng->txt("language"));
		$this->tpl->setVariable("TXT_SKIN_STYLE",$this->lng->txt("usr_skin_style"));
		$this->tpl->setVariable("TXT_HITS_PER_PAGE",$this->lng->txt("usr_hits_per_page"));
		$this->tpl->setVariable("TXT_SHOW_USERS_ONLINE",$this->lng->txt("show_users_online"));
		$this->tpl->setVariable("TXT_GENDER_F",$this->lng->txt("gender_f"));
		$this->tpl->setVariable("TXT_GENDER_M",$this->lng->txt("gender_m"));
		$this->tpl->setVariable("TXT_OTHER",$this->lng->txt("user_profile_other"));
		$this->tpl->setVariable("TXT_INSTANT_MESSENGERS",$this->lng->txt("user_profile_instant_messengers"));
		$this->tpl->setVariable("TXT_IM_ICQ",$this->lng->txt("im_icq"));
		$this->tpl->setVariable("TXT_IM_YAHOO",$this->lng->txt("im_yahoo"));
		$this->tpl->setVariable("TXT_IM_MSN",$this->lng->txt("im_msn"));
		$this->tpl->setVariable("TXT_IM_AIM",$this->lng->txt("im_aim"));
		$this->tpl->setVariable("TXT_IM_SKYPE",$this->lng->txt("im_skype"));

		include_once('classes/class.ilAuthUtils.php');
		if(ilAuthUtils::_isExternalAccountEnabled())
		{
			$this->tpl->setCurrentBlock("ext_account");
			$this->tpl->setVariable("TXT_EXT_ACCOUNT",$this->lng->txt("user_ext_account"));
			$this->tpl->setVariable("TXT_EXT_ACCOUNT_DESC",$this->lng->txt("user_ext_account_desc"));
			if (isset($_SESSION["error_post_vars"]["Fobject"]["ext_account"]))
			{
				$this->tpl->setVariable("EXT_ACCOUNT_VAL",
					$_SESSION["error_post_vars"]["Fobject"]["ext_account"]);
			}
			$this->tpl->parseCurrentBlock();
		}

		
		//$this->tpl->setVariable("TXT_CURRENT_IP",$this->lng->txt("current_ip").
		//	$_SERVER["REMOTE_ADDR"]);
		$this->tpl->setVariable("TXT_CURRENT_IP_ALERT",$this->lng->txt("current_ip_alert"));

		// FILL SAVED VALUES IN CASE OF ERROR
		if (isset($_SESSION["error_post_vars"]["Fobject"]))
		{
			if (!isset($_SESSION["error_post_vars"]["Fobject"]["active"]))
			{
				$_SESSION["error_post_vars"]["Fobject"]["active"] = 0;
			}
		
			foreach ($_SESSION["error_post_vars"]["Fobject"] as $key => $val)
			{
				if ($key != "default_role" and $key != "language" 
					and $key != "skin_style" and $key != "hits_per_page"
					and $key != "show_users_online" and $key != 'passwd' and $key != 'passwd2')
				{
					$this->tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val));
				}
			}
		
			// gender selection
			$gender = strtoupper($_SESSION["error_post_vars"]["Fobject"]["gender"]);
		
			if (!empty($gender))
			{
				$this->tpl->setVariable("BTN_GENDER_".$gender,"checked=\"checked\"");
			}

			$active = $_SESSION["error_post_vars"]["Fobject"]["active"];
			if ($active)
			{
				$this->tpl->setVariable("ACTIVE", "checked=\"checked\"");
			}
		}
		
		// auth mode selection
		include_once('classes/class.ilAuthUtils.php');
		$active_auth_modes = ilAuthUtils::_getActiveAuthModes();

		// preselect previous chosen auth mode otherwise default auth mode
		$selected_auth_mode = (isset($_SESSION["error_post_vars"]["Fobject"]["auth_mode"])) ? 
			$_SESSION["error_post_vars"]["Fobject"]["auth_mode"] : 'default';

		foreach ($active_auth_modes as $auth_name => $auth_key)
		{
			$this->tpl->setCurrentBlock("auth_mode_selection");

			if ($auth_name == 'default')
			{
				$name = $this->lng->txt('auth_'.$auth_name)." (".$this->lng->txt('auth_'.ilAuthUtils::_getAuthModeName($auth_key)).")";
			}
			else
			{
				$name = $this->lng->txt('auth_'.$auth_name);
			}
			
			$this->tpl->setVariable("AUTH_MODE_NAME", $name);

			$this->tpl->setVariable("AUTH_MODE", $auth_name);

			if ($selected_auth_mode == $auth_name)
			{
				$this->tpl->setVariable("SELECTED_AUTH_MODE", "selected=\"selected\"");
			}

			$this->tpl->parseCurrentBlock();
		} // END auth_mode selection
		
		// language selection
		$languages = $this->lng->getInstalledLanguages();
		
		// preselect previous chosen language otherwise default language
		$selected_lang = (isset($_SESSION["error_post_vars"]["Fobject"]["language"])) ? 
			$_SESSION["error_post_vars"]["Fobject"]["language"] : $this->ilias->getSetting("language");
		
		foreach ($languages as $lang_key)
		{
			$this->tpl->setCurrentBlock("language_selection");
			$this->tpl->setVariable("LANG", $this->lng->txt("lang_".$lang_key));
			$this->tpl->setVariable("LANGSHORT", $lang_key);
		
			if ($selected_lang == $lang_key)
			{
				$this->tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
			}
		
			$this->tpl->parseCurrentBlock();
		} // END language selection
		
		// skin & style selection
		$templates = $styleDefinition->getAllTemplates();
		//$this->ilias->getSkins();

		// preselect previous chosen skin/style otherwise default skin/style
		if (isset($_SESSION["error_post_vars"]["Fobject"]["skin_style"]))
		{
			$sknst = explode(":", $_SESSION["error_post_vars"]["Fobject"]["skin_style"]);
		
			$selected_style = $sknst[1];
			$selected_skin = $sknst[0];
		}
		else
		{
			$selected_style = $this->ilias->ini->readVariable("layout","style");;
			$selected_skin = $this->ilias->ini->readVariable("layout","skin");;
		}
		include_once("classes/class.ilObjStyleSettings.php");
		foreach ($templates as $template)
		{
			// get styles for skin
			//$this->ilias->getStyles($template["id"]);
			$styleDef =& new ilStyleDefinition($template["id"]);
			$styleDef->startParsing();
			$styles = $styleDef->getStyles();
		
			foreach($styles as $style)
			{
				if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
				{
					continue;
				}

				$this->tpl->setCurrentBlock("selectskin");
		
				if ($selected_skin == $template["id"] &&
					$selected_style == $style["id"])
				{
					$this->tpl->setVariable("SKINSELECTED", "selected=\"selected\"");
				}
		
				$this->tpl->setVariable("SKINVALUE", $template["id"].":".$style["id"]);
				$this->tpl->setVariable("SKINOPTION", $styleDef->getTemplateName()." / ".$style["name"]);
				$this->tpl->parseCurrentBlock();
			}
		} // END skin & style selection

		// BEGIN hits per page
		$hits_options = array(2,10,15,20,30,40,50,100,9999);
		// preselect previous chosen option otherwise default option
		if (isset($_SESSION["error_post_vars"]["Fobject"]["hits_per_page"]))
		{
			$selected_option = $_SESSION["error_post_vars"]["Fobject"]["hits_per_page"];
		}
		else
		{
			$selected_option = $this->ilias->getSetting("hits_per_page");
		}
		foreach($hits_options as $hits_option)
		{
			$this->tpl->setCurrentBlock("selecthits");

			if ($hits_option == $selected_option)
			{
				$this->tpl->setVariable("HITSSELECTED", "selected=\"selected\"");
			}

			$this->tpl->setVariable("HITSVALUE", $hits_option);

			if ($hits_option == 9999)
			{
				$hits_option = $this->lng->txt("no_limit");
			}

			$this->tpl->setVariable("HITSOPTION", $hits_option);
			$this->tpl->parseCurrentBlock();
		}
		// END hits per page

		// BEGIN show users online
		// preselect previous chosen option otherwise default option
		if (isset($_SESSION["error_post_vars"]["Fobject"]["show_users_online"]))
		{
			$selected_option = $_SESSION["error_post_vars"]["Fobject"]["show_users_online"];
		}
		else
		{
			$selected_option = $this->ilias->getSetting("show_users_online");
		}
		$users_online_options = array("y","associated","n");
		foreach($users_online_options as $an_option)
		{
			$this->tpl->setCurrentBlock("show_users_online");
			
			if ($selected_option == $an_option)
			{
				$this->tpl->setVariable("USERS_ONLINE_SELECTED", "selected=\"selected\"");
			}

			$this->tpl->setVariable("USERS_ONLINE_VALUE", $an_option);

			$this->tpl->setVariable("USERS_ONLINE_OPTION", $this->lng->txt("users_online_show_".$an_option));
			$this->tpl->parseCurrentBlock();
		}
		// END show users online
		
		// BEGIN hide_own_online_status
		
		if (isset($_SESSION["error_post_vars"]["Fobject"]["hide_own_online_status"]))
		{
			$hide_own_online_status = $_SESSION["error_post_vars"]["Fobject"]["hide_own_online_status"];
		}
		else
		{
			$hide_own_online_status = $this->ilias->getSetting("hide_own_online_status");
		}
		
		$this->tpl->setCurrentBlock("hide_own_online_status");		
		$this->tpl->setVariable("TXT_HIDE_OWN_ONLINE_STATUS", $this->lng->txt("hide_own_online_status"));
		if ($hide_own_online_status == "y") {
			$this->tpl->setVariable("CHK_HIDE_OWN_ONLINE_STATUS", "checked=\"checked\"");
		}
		else {
			$this->tpl->setVariable("CHK_HIDE_OWN_ONLINE_STATUS", "");
		} 
		$this->tpl->parseCurrentBlock();
		// END hide_own_online_status
		
		// time limit
		if (is_array($_SESSION["error_post_vars"]))
		{
			$time_limit_unlimited = $_SESSION["error_post_vars"]["time_limit"]["unlimited"];
		}
		else
		{
			$time_limit_unlimited = 1;
		}
		
		$time_limit_from = $_SESSION["error_post_vars"]["time_limit"]["from"] ?
			$this->__toUnix($_SESSION["error_post_vars"]["time_limit"]["from"]) :
			time();
		
		$time_limit_until = $_SESSION["error_post_vars"]["time_limit"]["until"] ?
			$this->__toUnix($_SESSION["error_post_vars"]["time_limit"]["until"]) :
			time();
		
		$this->lng->loadLanguageModule('crs');
		
		$this->tpl->setCurrentBlock("time_limit");
		$this->tpl->setVariable("TXT_TIME_LIMIT", $this->lng->txt("time_limit"));
		$this->tpl->setVariable("TXT_TIME_LIMIT_UNLIMITED", $this->lng->txt("crs_unlimited"));
		$this->tpl->setVariable("TXT_TIME_LIMIT_FROM", $this->lng->txt("crs_from"));
		$this->tpl->setVariable("TXT_TIME_LIMIT_UNTIL", $this->lng->txt("crs_to"));
		$this->tpl->setVariable("TXT_TIME_LIMIT_CLOCK", $this->lng->txt("clock"));
		$this->tpl->setVariable("TIME_LIMIT_UNLIMITED",ilUtil::formCheckbox($time_limit_unlimited,"time_limit[unlimited]",1));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_MINUTE",$this->__getDateSelect("minute","time_limit[from][minute]",
			date("i",$time_limit_from)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_HOUR",$this->__getDateSelect("hour","time_limit[from][hour]",
																					 date("G",$time_limit_from)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_DAY",$this->__getDateSelect("day","time_limit[from][day]",
																					date("d",$time_limit_from)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_MONTH",$this->__getDateSelect("month","time_limit[from][month]",
																					  date("m",$time_limit_from)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_YEAR",$this->__getDateSelect("year","time_limit[from][year]",
																					 date("Y",$time_limit_from)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_MINUTE",$this->__getDateSelect("minute","time_limit[until][minute]",
																						date("i",$time_limit_until)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_HOUR",$this->__getDateSelect("hour","time_limit[until][hour]",
																					  date("G",$time_limit_until)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_DAY",$this->__getDateSelect("day","time_limit[until][day]",
																					 date("d",$time_limit_until)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_MONTH",$this->__getDateSelect("month","time_limit[until][month]",
																					   date("m",$time_limit_until)));
		$this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_YEAR",$this->__getDateSelect("year","time_limit[until][year]",
																					  date("Y",$time_limit_until)));
		$this->tpl->parseCurrentBlock();


		$this->__showUserDefinedFields();

	}

	function __checkUserDefinedRequiredFields()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$this->user_defined_fields =& ilUserDefinedFields::_getInstance();

		foreach($this->user_defined_fields->getDefinitions() as $field_id => $definition)
		{
			if($definition['required'] and !strlen($_POST['udf'][$field_id]))
			{
				return false;
			}
		}
		return true;
	}


	function __showUserDefinedFields()
	{
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$this->user_defined_fields =& ilUserDefinedFields::_getInstance();
		
		if($this->object->getType() == 'usr')
		{
			$user_defined_data = $this->object->getUserDefinedData();
		}
		foreach($this->user_defined_fields->getDefinitions() as $field_id => $definition)
		{
			$old = isset($_SESSION["error_post_vars"]["udf"][$field_id]) ?
				$_SESSION["error_post_vars"]["udf"][$field_id] : $user_defined_data[$field_id];

			if($definition['field_type'] == UDF_TYPE_TEXT)
			{
				$this->tpl->setCurrentBlock("field_text");
				$this->tpl->setVariable("FIELD_NAME",'udf['.$definition['field_id'].']');
				$this->tpl->setVariable("FIELD_VALUE",ilUtil::prepareFormOutput($old));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("field_select");
				$this->tpl->setVariable("SELECT_BOX",ilUtil::formSelect($old,
																		'udf['.$definition['field_id'].']',
																		$this->user_defined_fields->fieldValuesToSelectArray(
																			$definition['field_values']),
																		false,
																		true));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("user_defined");

			if($definition['required'])
			{
				$name = $definition['field_name']."<span class=\"asterisk\">*</span>";
			}
			else
			{
				$name = $definition['field_name'];
			}
			$this->tpl->setVariable("TXT_FIELD_NAME",$name);
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}

	
	/**
	* set admin tabs
	* @access	public
	*
	function setAdminTabs()
	{
		global $rbacsystem;

		$tabs = array();
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
		
		if (isset($_POST["new_type"]) and $_POST["new_type"] == "usr")
		{
			$type = "usrf";
		}
		else
		{
			$type = $this->type;
		}
		$d = $this->objDefinition->getProperties($type);

		foreach ($d as $key => $row)
		{
			$tabs[] = array($row["lng"], $row["name"]);
		}

		// check for call_by_reference too to avoid hacking
		if (isset($_GET["obj_id"]) and $this->call_by_reference === false)
		{
			$object_link = "&obj_id=".$_GET["obj_id"];
		}

		foreach ($tabs as $row)
		{
			$i++;

			if ($row[1] == $_GET["cmd"])
			{
				$tabtype = "tabactive";
				$tab = $tabtype;
			}
			else
			{
				$tabtype = "tabinactive";
				$tab = "tab";
			}

			$show = true;

			// only check permissions for tabs if object is a permission object
			// TODO: automize checks by using objects.xml definitions!!
			if (true)
			//if ($this->call_by_reference)
			{
				// only show tab when the corresponding permission is granted
				switch ($row[1])
				{
					case 'view':
						if (!$rbacsystem->checkAccess('visible',$this->ref_id))
						{
							$show = false;
						}
						break;

					case 'edit':
						if (!$rbacsystem->checkAccess('write',$this->ref_id))
						{
							$show = false;
						}
						break;

					case 'perm':
						if (!$rbacsystem->checkAccess('edit_permission',$this->ref_id))
						{
							$show = false;
						}
						break;

					case 'trash':
						if (!$this->tree->getSavedNodeData($this->ref_id))
						{
							$show = false;
						}
						break;

					// user object only
					case 'roleassignment':
						if (!$rbacsystem->checkAccess('edit_roleassignment',$this->ref_id))
						{
							$show = false;
						}
						break;

					// role object only
					case 'userassignment':
						if (!$rbacsystem->checkAccess('edit_userassignment',$this->ref_id))
						{
							$show = false;
						}
						break;
				} //switch
			}

			if (!$show)
			{
				continue;
			}

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", $tabtype);
			$this->tpl->setVariable("TAB_TYPE2", $tab);
			$this->tpl->setVariable("IMG_LEFT", ilUtil::getImagePath("eck_l.gif"));
			$this->tpl->setVariable("IMG_RIGHT", ilUtil::getImagePath("eck_r.gif"));
			$this->tpl->setVariable("TAB_LINK", $this->tab_target_script."?ref_id=".$_GET["ref_id"].$object_link."&cmd=".$row[1]);
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($row[0]));
			$this->tpl->parseCurrentBlock();
		}
	}*/

	/**
	* display user edit form
	*
	* @access	public
	*/
    function editObject()
    {
        global $ilias, $rbacsystem, $rbacreview, $rbacadmin, $styleDefinition, $ilUser
			,$ilSetting;
			
		include_once('classes/class.ilAuthUtils.php');
			

        //load ILIAS settings
        $settings = $ilias->getAllSettings();

		// User folder
		if($this->usrf_ref_id == USER_FOLDER_ID and !$rbacsystem->checkAccess('visible,read',$this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"),$this->ilias->error_obj->MESSAGE);
		}
		// if called from local administration $this->usrf_ref_id is category id 
		// Todo: this has to be fixed. Do not mix user folder id and category id
		if($this->usrf_ref_id != USER_FOLDER_ID)
		{
			// check if user is assigned to category
			if(!$rbacsystem->checkAccess('cat_administrate_users',$this->object->getTimeLimitOwner()))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"),$this->ilias->error_obj->MESSAGE);
			}
		}

		if($this->usrf_ref_id != USER_FOLDER_ID)
		{
			$this->tabs_gui->clearTargets();
		}

		$data = array();
		$data["fields"] = array();
		$data["fields"]["login"] = $this->object->getLogin();
		$data["fields"]["passwd"] = "********";	// will not be saved
		#$data["fields"]["passwd2"] = "********";	// will not be saved
		$data["fields"]["ext_account"] = $this->object->getExternalAccount();
		$data["fields"]["title"] = $this->object->getUTitle();
		$data["fields"]["gender"] = $this->object->getGender();
		$data["fields"]["firstname"] = $this->object->getFirstname();
		$data["fields"]["lastname"] = $this->object->getLastname();
		$data["fields"]["institution"] = $this->object->getInstitution();
		$data["fields"]["department"] = $this->object->getDepartment();
		$data["fields"]["street"] = $this->object->getStreet();
		$data["fields"]["city"] = $this->object->getCity();
		$data["fields"]["zipcode"] = $this->object->getZipcode();
		$data["fields"]["country"] = $this->object->getCountry();
		$data["fields"]["phone_office"] = $this->object->getPhoneOffice();
		$data["fields"]["phone_home"] = $this->object->getPhoneHome();
		$data["fields"]["phone_mobile"] = $this->object->getPhoneMobile();
		$data["fields"]["fax"] = $this->object->getFax();
		$data["fields"]["email"] = $this->object->getEmail();
		$data["fields"]["hobby"] = $this->object->getHobby();
		$data["fields"]["im_icq"] = $this->object->getInstantMessengerId('icq');
		$data["fields"]["im_yahoo"] = $this->object->getInstantMessengerId('yahoo');
		$data["fields"]["im_msn"] = $this->object->getInstantMessengerId('msn');
		$data["fields"]["im_aim"] = $this->object->getInstantMessengerId('aim');
		$data["fields"]["im_skype"] = $this->object->getInstantMessengerId('skype');
		$data["fields"]["matriculation"] = $this->object->getMatriculation();
		$data["fields"]["client_ip"] = $this->object->getClientIP();
		$data["fields"]["referral_comment"] = $this->object->getComment();
		$data["fields"]["create_date"] = $this->object->getCreateDate();
		$data["fields"]["approve_date"] = $this->object->getApproveDate();
		$data["fields"]["active"] = $this->object->getActive();
		$data["fields"]["auth_mode"] = $this->object->getAuthMode();
		$data["fields"]["ext_account"] = $this->object->getExternalAccount();

		if (!count($user_online = ilUtil::getUsersOnline($this->object->getId())) == 1)
		{
			$user_is_online = false;
		}
		else
		{
			$user_is_online = true;

			// extract serialized role Ids from session data
			preg_match("/RoleId.*?;\}/",$user_online[$this->object->getId()]["data"],$matches);

			$active_roles = unserialize(substr($matches[0],7));

			// gather data for active roles
			$assigned_roles = $rbacreview->assignedRoles($this->object->getId());

			foreach ($assigned_roles as $key => $role)
			{
				$roleObj = $this->ilias->obj_factory->getInstanceByObjId($role);

				// fetch context path of role
				$rolf = $rbacreview->getFoldersAssignedToRole($role,true);

				// only list roles that are not set to status "deleted"
				if (count($rolf) > 0)
				{
					if (!$rbacreview->isDeleted($rolf[0]))
					{
						$path = "";

						if ($this->tree->isInTree($rolf[0]))
						{
							$tmpPath = $this->tree->getPathFull($rolf[0]);

							// count -1, to exclude the role folder itself
							for ($i = 0; $i < (count($tmpPath)-1); $i++)
							{
								if ($path != "")
								{
									$path .= " > ";
								}

								$path .= $tmpPath[$i]["title"];
							}
						}
						else
						{
							$path = "<b>Rolefolder ".$rolf[0]." not found in tree! (Role ".$role.")</b>";
						}
						$active_roles = $active_roles ? $active_roles : array();
						if (in_array($role,$active_roles))
						{
							$data["active_role"][$role]["active"] = true;
						}

						$data["active_role"][$role]["title"] = $roleObj->getTitle();
						$data["active_role"][$role]["context"] = $path;

						unset($roleObj);
					}
				}
				else
				{
					$path = "<b>No role folder found for role ".$role."!</b>";
				}
			}
		}

		$this->getTemplateFile("edit","usr");

		// FILL SAVED VALUES IN CASE OF ERROR
		if (isset($_SESSION["error_post_vars"]["Fobject"]))
		{
            if (!isset($_SESSION["error_post_vars"]["Fobject"]["active"]))
            {
                $_SESSION["error_post_vars"]["Fobject"]["active"] = 0;
            }

			foreach ($_SESSION["error_post_vars"]["Fobject"] as $key => $val)
			{
				$str = $this->lng->txt($key);
				if ($key == "title")
				{
					$str = $this->lng->txt("person_title");
				}
				if($key == 'passwd2')
				{
					continue;
				}
				if($key == 'passwd')
				{
					if(ilAuthUtils::_allowPasswordModificationByAuthMode(
						ilAuthUtils::_getAuthMode($_SESSION['error_post_vars']['Fobject']['auth_mode'])))
					{
						$this->tpl->setCurrentBlock('passwords_visible');
						$this->tpl->setVariable('VISIBLE_TXT_PASSWD',$this->lng->txt('passwd'));
						$this->tpl->setVariable('VISIBLE_TXT_PASSWD2',$this->lng->txt('retype_password'));
						$this->tpl->setVariable('VISIBLE_PASSWD',$_SESSION['error_post_vars']['Fobject']['passwd']);
						$this->tpl->setVariable('VISIBLE_PASSWD2',$_SESSION['error_post_vars']['Fobject']['passwd2']);
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock('passwords_invisible');
						$this->tpl->setVariable('INVISIBLE_TXT_PASSWD',$this->lng->txt('passwd'));
						$this->tpl->setVariable('INVISIBLE_TXT_PASSWD2',$this->lng->txt('retype_password'));
						$this->tpl->setVariable('INVISIBLE_PASSWD',strlen($this->object->getPasswd()) ? 
							"********" :
							"");
						$this->tpl->setVariable('INVISIBLE_PASSWD2',strlen($this->object->getPasswd()) ? 
							"********" :
							"");
						$this->tpl->setVariable('INVISIBLE_PASSWD_HIDDEN',"********");
						$this->tpl->parseCurrentBlock();
						
					}
					continue;
				}

                // check to see if dynamically required
                if (isset($settings["require_" . $key]) && $settings["require_" . $key])
                {
                    $str = $str . '<span class="asterisk">*</span>';
                }

				$this->tpl->setVariable("TXT_".strtoupper($key), $str);

				if ($key != "default_role" and $key != "language" 
					and $key != "skin_style" and $key != "hits_per_page"
					and $key != "show_users_online")
				{
					$this->tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val,true));
				}
			}

			// gender selection
			$gender = strtoupper($_SESSION["error_post_vars"]["Fobject"]["gender"]);
			

			if (!empty($gender))
			{
				$this->tpl->setVariable("BTN_GENDER_".$gender,"checked=\"checked\"");
			}

            $active = $_SESSION["error_post_vars"]["Fobject"]["active"];
            if ($active)
            {
                $this->tpl->setVariable("ACTIVE", "checked=\"checked\"");
            }
		}
		else
		{
            if (!isset($data["fields"]["active"]))
            {
                $data["fields"]["active"] = 0;
            }

			foreach ($data["fields"] as $key => $val)
			{
				$str = $this->lng->txt($key);
				if ($key == "title")
				{
					$str = $this->lng->txt("person_title");
				}
				if ($key == "ext_account")
				{
					continue;
				}
				if($key == 'passwd')
				{
					$auth_mode = $this->object->getAuthMode(true);
					if(ilAuthUtils::_allowPasswordModificationByAuthMode($auth_mode))
					{
						$this->tpl->setCurrentBlock('passwords_visible');
						$this->tpl->setVariable('VISIBLE_TXT_PASSWD',$this->lng->txt('passwd'));
						$this->tpl->setVariable('VISIBLE_TXT_PASSWD2',$this->lng->txt('retype_password'));
						$this->tpl->setVariable('VISIBLE_PASSWD',"********");
						$this->tpl->setVariable('VISIBLE_PASSWD2',"********");
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock('passwords_invisible');
						$this->tpl->setVariable('INVISIBLE_TXT_PASSWD',$this->lng->txt('passwd'));
						$this->tpl->setVariable('INVISIBLE_TXT_PASSWD2',$this->lng->txt('retype_password'));
						$this->tpl->setVariable('INVISIBLE_PASSWD',strlen($this->object->getPasswd()) ? 
							"********" :
							"");
						$this->tpl->setVariable('INVISIBLE_PASSWD2',strlen($this->object->getPasswd()) ? 
							"********" :
							"");
						$this->tpl->setVariable('INVISIBLE_PASSWD_HIDDEN',"********");
						$this->tpl->parseCurrentBlock();
					}
					continue;
				}

                // check to see if dynamically required
                if (isset($settings["require_" . $key]) && $settings["require_" . $key])
                {
                    $str = $str . '<span class="asterisk">*</span>';
                }

				$this->tpl->setVariable("TXT_".strtoupper($key), $str);

				$this->tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val));
				#$this->tpl->parseCurrentBlock();
			}
			
			// gender selection
			$gender = strtoupper($data["fields"]["gender"]);
		
			if (!empty($gender))
			{
				$this->tpl->setVariable("BTN_GENDER_".$gender,"checked=\"checked\"");
			}

			$active = $data["fields"]["active"];
			if ($active)
			{
				$this->tpl->setVariable("ACTIVE", "checked=\"checked\"");
			}
		}

		// external account
		include_once('classes/class.ilAuthUtils.php');
		if(ilAuthUtils::_isExternalAccountEnabled())
		{
			$this->tpl->setCurrentBlock("ext_account");
			$this->tpl->setVariable("TXT_EXT_ACCOUNT",$this->lng->txt("user_ext_account"));
			$this->tpl->setVariable("TXT_EXT_ACCOUNT_DESC",$this->lng->txt("user_ext_account_desc"));
			if (isset($_SESSION["error_post_vars"]["Fobject"]["ext_account"]))
			{
				$this->tpl->setVariable("EXT_ACCOUNT_VAL",
					$_SESSION["error_post_vars"]["Fobject"]["ext_account"]);
			}
			else
			{
				$this->tpl->setVariable("EXT_ACCOUNT_VAL",
					$data["fields"]["ext_account"]);
			}
			/* Disabled: external account names should be changeable by admins
			if ($this->object->getAuthMode(true) != AUTH_LOCAL &&
				$this->object->getAuthMode(true) != AUTH_CAS &&
				$this->object->getAuthMode(true) != AUTH_SHIBBOLETH &&
				$this->object->getAuthMode(true) != AUTH_SOAP)
			{
				$this->tpl->setVariable("OPTION_DISABLED_EXT", "\"disabled=disabled\"");
			}
			*/
			$this->tpl->parseCurrentBlock();
		}
		$auth_mode = $_SESSION['error_post_vars']['Fobject']['auth_mode'] ? 
			ilAuthUtils::_getAuthMode($_SESSION['error_post_vars']['Fobject']['auth_mode']) :
			$this->object->getAuthMode(true);
		if(!ilAuthUtils::_allowPasswordModificationByAuthMode($auth_mode))
		{
			$this->tpl->setVariable("OPTION_DISABLED", "\"disabled=disabled\"");
		}
		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

		$this->tpl->setVariable("TXT_LOGIN_DATA", $this->lng->txt("login_data"));
        $this->tpl->setVariable("TXT_SYSTEM_INFO", $this->lng->txt("system_information"));
		$this->tpl->setVariable("TXT_PERSONAL_DATA", $this->lng->txt("personal_data"));
		$this->tpl->setVariable("TXT_CONTACT_DATA", $this->lng->txt("contact_data"));
		$this->tpl->setVariable("TXT_SETTINGS", $this->lng->txt("settings"));
		$this->tpl->setVariable("TXT_LANGUAGE",$this->lng->txt("language"));
		$this->tpl->setVariable("TXT_SKIN_STYLE",$this->lng->txt("usr_skin_style"));
		$this->tpl->setVariable("TXT_HITS_PER_PAGE",$this->lng->txt("hits_per_page"));
		$this->tpl->setVariable("TXT_SHOW_USERS_ONLINE",$this->lng->txt("show_users_online"));
		$this->tpl->setVariable("TXT_GENDER_F",$this->lng->txt("gender_f"));
		$this->tpl->setVariable("TXT_GENDER_M",$this->lng->txt("gender_m"));
		$this->tpl->setVariable("TXT_INSTANT_MESSENGERS",$this->lng->txt("user_profile_instant_messengers"));
		$this->tpl->setVariable("TXT_OTHER",$this->lng->txt("user_profile_other"));
		if ($this->object->getId() == $ilUser->getId())
		{
			$this->tpl->setVariable("TXT_CURRENT_IP","(".$this->lng->txt("current_ip")." ".$_SERVER["REMOTE_ADDR"].")");
		}
		$this->tpl->setVariable("TXT_CURRENT_IP_ALERT",$this->lng->txt("current_ip_alert"));
		
		// auth mode selection
		include_once('classes/class.ilAuthUtils.php');
		$active_auth_modes = ilAuthUtils::_getActiveAuthModes();
//var_dump($active_auth_modes);
		// preselect previous chosen auth mode otherwise default auth mode
		$selected_auth_mode = (isset($_SESSION["error_post_vars"]["Fobject"]["auth_mode"])) ? $_SESSION["error_post_vars"]["Fobject"]["auth_mode"] : $this->object->getAuthMode();

		foreach ($active_auth_modes as $auth_name => $auth_key)
		{
			$this->tpl->setCurrentBlock("auth_mode_selection");

			if ($auth_name == 'default')
			{
				$name = $this->lng->txt('auth_'.$auth_name)." (".$this->lng->txt('auth_'.ilAuthUtils::_getAuthModeName($auth_key)).")";
			}
			else
			{
				$name = $this->lng->txt('auth_'.$auth_name);
			}
			
			$this->tpl->setVariable("AUTH_MODE_NAME", $name);

			$this->tpl->setVariable("AUTH_MODE", $auth_name);

			if ($selected_auth_mode == $auth_name)
			{
				$this->tpl->setVariable("SELECTED_AUTH_MODE", "selected=\"selected\"");
			}

			$this->tpl->parseCurrentBlock();
		} // END auth_mode selection
		
		
		// language selection
		$languages = $this->lng->getInstalledLanguages();

		// preselect previous chosen language otherwise default language
		$selected_lang = (isset($_SESSION["error_post_vars"]["Fobject"]["language"])) ? $_SESSION["error_post_vars"]["Fobject"]["language"] : $this->object->getLanguage();

		foreach ($languages as $lang_key)
		{
			$this->tpl->setCurrentBlock("language_selection");
			$this->tpl->setVariable("LANG", $this->lng->txt("lang_".$lang_key));
			$this->tpl->setVariable("LANGSHORT", $lang_key);

			if ($selected_lang == $lang_key)
			{
				$this->tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
			}

			$this->tpl->parseCurrentBlock();
		} // END language selection
		
		// BEGIN skin & style selection
		//$this->ilias->getSkins();
		$templates = $styleDefinition->getAllTemplates();
		
		// preselect previous chosen skin/style otherwise default skin/style
		if (isset($_SESSION["error_post_vars"]["Fobject"]["skin_style"]))
		{
			$sknst = explode(":", $_SESSION["error_post_vars"]["Fobject"]["skin_style"]);
			
			$selected_style = $sknst[1];
			$selected_skin = $sknst[0];	
		}
		else
		{
			$selected_style = $this->object->prefs["style"];
			$selected_skin = $this->object->skin;	
		}
		
		include("classes/class.ilObjStyleSettings.php");
		if (count($templates) > 0 && is_array ($templates))
		{
			foreach ($templates as $template)
			{
				// get styles for skin
				//$this->ilias->getStyles($skin["name"]);
				$styleDef =& new ilStyleDefinition($template["id"]);
				$styleDef->startParsing();
				$styles = $styleDef->getStyles();
				foreach ($styles as $style)
				{
					if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
					{
						continue;
					}
	
					$this->tpl->setCurrentBlock("selectskin");
	
					if ($selected_skin == $template["id"] &&
						$selected_style == $style["id"])
					{
						$this->tpl->setVariable("SKINSELECTED", "selected=\"selected\"");
					}
	
					$this->tpl->setVariable("SKINVALUE", $template["id"].":".$style["id"]);
					$this->tpl->setVariable("SKINOPTION", $styleDef->getTemplateName()." / ".$style["name"]);
					$this->tpl->parseCurrentBlock();
				}
			} // END skin & style selection
		}
		// BEGIN hits per page
		$hits_options = array(2,10,15,20,30,40,50,100,9999);
		// preselect previous chosen option otherwise default option
		if (isset($_SESSION["error_post_vars"]["Fobject"]["hits_per_page"]))
		{
			$selected_option = $_SESSION["error_post_vars"]["Fobject"]["hits_per_page"];
		}
		else
		{
			$selected_option = $this->object->prefs["hits_per_page"];
		}
		foreach($hits_options as $hits_option)
		{
			$this->tpl->setCurrentBlock("selecthits");

			if ($selected_option == $hits_option)
			{
				$this->tpl->setVariable("HITSSELECTED", "selected=\"selected\"");
			}

			$this->tpl->setVariable("HITSVALUE", $hits_option);

			if ($hits_option == 9999)
			{
				$hits_option = $this->lng->txt("no_limit");
			}

			$this->tpl->setVariable("HITSOPTION", $hits_option);
			$this->tpl->parseCurrentBlock();
		}
		// END hits per page

		// BEGIN show users online
		$users_online_options = array("y","associated","n");
		// preselect previous chosen option otherwise default option
		if (isset($_SESSION["error_post_vars"]["Fobject"]["show_users_online"]))
		{
			$selected_option = $_SESSION["error_post_vars"]["Fobject"]["show_users_online"];
		}
		else
		{
			$selected_option = $this->object->prefs["show_users_online"];
		}
		foreach($users_online_options as $an_option)
		{
			$this->tpl->setCurrentBlock("show_users_online");

			if ($selected_option == $an_option)
			{
				$this->tpl->setVariable("USERS_ONLINE_SELECTED", "selected=\"selected\"");
			}

			$this->tpl->setVariable("USERS_ONLINE_VALUE", $an_option);

			$this->tpl->setVariable("USERS_ONLINE_OPTION", $this->lng->txt("users_online_show_".$an_option));
			$this->tpl->parseCurrentBlock();
		}
		// END show users online
			
		// BEGIN hide_own_online_status
		if (isset($_SESSION["error_post_vars"]["Fobject"]["hide_own_online_status"]))
		{
			$hide_own_online_status = $_SESSION["error_post_vars"]["Fobject"]["hide_own_online_status"];
		}
		else
		{			
			$hide_own_online_status = ($this->object->prefs["hide_own_online_status"] != '') ? $this->object->prefs["hide_own_online_status"] : "n";
		}
		$this->tpl->setCurrentBlock("hide_own_online_status");				
		$this->tpl->setVariable("TXT_HIDE_OWN_ONLINE_STATUS", $this->lng->txt("hide_own_online_status"));
		if ($hide_own_online_status == "y") {
			$this->tpl->setVariable("CHK_HIDE_OWN_ONLINE_STATUS", "checked=\"checked\"");
		}
		else {
			$this->tpl->setVariable("CHK_HIDE_OWN_ONLINE_STATUS", "");
		} 
		$this->tpl->parseCurrentBlock();		
		//END hide_own_online_status
		
		// inform user about changes option
		$this->tpl->setCurrentBlock("inform_user");


		if (true)
		{
			$this->tpl->setVariable("SEND_MAIL", " checked=\"checked\"");
		}

		$this->tpl->setVariable("TXT_INFORM_USER_MAIL", $this->lng->txt("inform_user_mail"));
		$this->tpl->parseCurrentBlock();

		$this->lng->loadLanguageModule('crs');

		$time_limit_unlimited = $_SESSION["error_post_vars"]["time_limit"]["unlimited"] ?
            $_SESSION["error_post_vars"]["time_limit"]["unlimited"] :
            $this->object->getTimeLimitUnlimited();
        $time_limit_from = $_SESSION["error_post_vars"]["time_limit"]["from"] ?
            $this->__toUnix($_SESSION["error_post_vars"]["time_limit"]["from"]) :
            $this->object->getTimeLimitFrom();

        $time_limit_until = $_SESSION["error_post_vars"]["time_limit"]["until"] ?
            $this->__toUnix($_SESSION["error_post_vars"]["time_limit"]["until"]) :
            $this->object->getTimeLimitUntil();

		$this->tpl->setCurrentBlock("time_limit");
        $this->tpl->setVariable("TXT_TIME_LIMIT", $this->lng->txt("time_limit"));
        $this->tpl->setVariable("TXT_TIME_LIMIT_UNLIMITED", $this->lng->txt("crs_unlimited"));
        $this->tpl->setVariable("TXT_TIME_LIMIT_FROM", $this->lng->txt("crs_from"));
        $this->tpl->setVariable("TXT_TIME_LIMIT_UNTIL", $this->lng->txt("crs_to"));

        $this->tpl->setVariable("TIME_LIMIT_UNLIMITED",ilUtil::formCheckbox($time_limit_unlimited,"time_limit[unlimited]",1));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_MINUTE",$this->__getDateSelect("minute","time_limit[from][minute]",
                                                                                     date("i",$time_limit_from)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_HOUR",$this->__getDateSelect("hour","time_limit[from][hour]",
                                                                                     date("G",$time_limit_from)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_DAY",$this->__getDateSelect("day","time_limit[from][day]",
                                                                                     date("d",$time_limit_from)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_MONTH",$this->__getDateSelect("month","time_limit[from][month]",
                                                                                       date("m",$time_limit_from)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_FROM_YEAR",$this->__getDateSelect("year","time_limit[from][year]",
                                                                                      date("Y",$time_limit_from)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_MINUTE",$this->__getDateSelect("minute","time_limit[until][minute]",
                                                                                     date("i",$time_limit_until)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_HOUR",$this->__getDateSelect("hour","time_limit[until][hour]",
                                                                                     date("G",$time_limit_until)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_DAY",$this->__getDateSelect("day","time_limit[until][day]",
                                                                                   date("d",$time_limit_until)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_MONTH",$this->__getDateSelect("month","time_limit[until][month]",
                                                                                     date("m",$time_limit_until)));
        $this->tpl->setVariable("SELECT_TIME_LIMIT_UNTIL_YEAR",$this->__getDateSelect("year","time_limit[until][year]",
                                                                                    date("Y",$time_limit_until)));
		$this->tpl->parseCurrentBlock();


		if ($user_is_online)
		{
			// BEGIN TABLE ROLES
			$this->tpl->setCurrentBlock("TABLE_ROLES");

			$counter = 0;

			foreach ($data["active_role"] as $role_id => $role)
			{
				++$counter;
				$css_row = ilUtil::switchColor($counter,"tblrow2","tblrow1");
				($role["active"]) ? $checked = "checked=\"checked\"" : $checked = "";

				$this->tpl->setVariable("ACTIVE_ROLE_CSS_ROW",$css_row);
				$this->tpl->setVariable("ROLECONTEXT",$role["context"]);
				$this->tpl->setVariable("ROLENAME",$role["title"]);
				$this->tpl->setVariable("CHECKBOX_ID", $role_id);
				$this->tpl->setVariable("CHECKED", $checked);
				$this->tpl->parseCurrentBlock();
			}
			// END TABLE ROLES

			// BEGIN ACTIVE ROLES
			$this->tpl->setCurrentBlock("ACTIVE_ROLE");
			$this->tpl->setVariable("ACTIVE_ROLE_FORMACTION",
				$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_ACTIVE_ROLES",$this->lng->txt("active_roles"));
			$this->tpl->setVariable("TXT_ASSIGN",$this->lng->txt("change_active_assignment"));
			$this->tpl->parseCurrentBlock();
			// END ACTIVE ROLES
		}
		$this->__showUserDefinedFields();
	}

	/**
	* save user data
	* @access	public
	*/
	function saveObject()
	{
        global $ilias, $rbacsystem, $rbacadmin, $ilSetting;
        
        include_once('classes/class.ilAuthUtils.php');

        //load ILIAS settings
        $settings = $ilias->getAllSettings();

		// User folder
		if (!$rbacsystem->checkAccess('create_user', $this->usrf_ref_id) and
			!$rbacsystem->checkAccess('cat_administrate_users',$this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

        // check dynamically required fields
        foreach ($settings as $key => $val)
        {
            if (substr($key,0,8) == "require_")
            {
                $field = substr($key,8);
				
                switch($field)
                {
                	case 'passwd':
                	case 'passwd2':
                		if(ilAuthUtils::_allowPasswordModificationByAuthMode(ilAuthUtils::_getAuthMode($_POST['Fobject']['auth_mode'])))
                		{
			                $require_keys[] = $field;
                		}
			            break;
                	default:
		                $require_keys[] = $field;
		                break;
                }
            }
        }

        foreach ($require_keys as $key => $val)
        {
            if (isset($settings["require_" . $val]) && $settings["require_" . $val])
            {
                if (empty($_POST["Fobject"][$val]))
                {
                    $this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields") . ": " .
											 $this->lng->txt($val),$this->ilias->error_obj->MESSAGE);					
                }
            }
        }

		if(!$this->__checkUserDefinedRequiredFields())
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}			

		// validate login
		if (!ilUtil::isLogin($_POST["Fobject"]["login"]))
		{
			$this->ilias->raiseError($this->lng->txt("login_invalid"),$this->ilias->error_obj->MESSAGE);
		}

		// check loginname
		if (ilObjUser::_loginExists($_POST["Fobject"]["login"]))
		{
			$this->ilias->raiseError($this->lng->txt("login_exists"),$this->ilias->error_obj->MESSAGE);
		}

		// Do password checks only if auth mode allows password modifications
		include_once('classes/class.ilAuthUtils.php');
		if(ilAuthUtils::_allowPasswordModificationByAuthMode(ilAuthUtils::_getAuthMode($_POST['Fobject']['auth_mode'])))
		{
			// check passwords
			if ($_POST["Fobject"]["passwd"] != $_POST["Fobject"]["passwd2"])
			{
				$this->ilias->raiseError($this->lng->txt("passwd_not_match"),$this->ilias->error_obj->MESSAGE);
			}
	
			// validate password
			if (!ilUtil::isPassword($_POST["Fobject"]["passwd"]))
			{
				$this->ilias->raiseError($this->lng->txt("passwd_invalid"),$this->ilias->error_obj->MESSAGE);
			}
		}
		if(ilAuthUtils::_needsExternalAccountByAuthMode(ilAuthUtils::_getAuthMode($_POST['Fobject']['auth_mode'])))
		{
			if(!strlen($_POST['Fobject']['ext_account']))
			{
				$this->ilias->raiseError($this->lng->txt('ext_acccount_required'),$this->ilias->error_obj->MESSAGE);
			}
		}
		
		if($_POST['Fobject']['ext_account'] && 
			($elogin = ilObjUser::_checkExternalAuthAccount($_POST['Fobject']['auth_mode'],$_POST['Fobject']['ext_account'])))
		{
			if($elogin != '')
			{
				$this->ilias->raiseError(
						sprintf($this->lng->txt("err_auth_ext_user_exists"),
							$_POST["Fobject"]["ext_account"],
							$_POST['Fobject']['auth_mode'],
						    $elogin),
						$this->ilias->error_obj->MESSAGE);
			}
		}
		
		
		// The password type is not passed in the post data.  Therefore we
		// append it here manually.
		include_once ('./Services/User/classes/class.ilObjUser.php');
	    $_POST["Fobject"]["passwd_type"] = IL_PASSWD_PLAIN;

		// validate email
		if (strlen($_POST['Fobject']['email']) and !ilUtil::is_email($_POST["Fobject"]["email"]))
		{
			$this->ilias->raiseError($this->lng->txt("email_not_valid"),$this->ilias->error_obj->MESSAGE);
		}

		// validate time limit
        if ($_POST["time_limit"]["unlimited"] != 1 and
            ($this->__toUnix($_POST["time_limit"]["until"]) < $this->__toUnix($_POST["time_limit"]["from"])))
        {
            $this->ilias->raiseError($this->lng->txt("time_limit_not_valid"),$this->ilias->error_obj->MESSAGE);
        }
		if(!$this->ilias->account->getTimeLimitUnlimited())
		{
			if($this->__toUnix($_POST["time_limit"]["from"]) < $this->ilias->account->getTimeLimitFrom() or
			   $this->__toUnix($_POST["time_limit"]["until"])> $this->ilias->account->getTimeLimitUntil() or
			   $_POST['time_limit']['unlimited'])
			{
				$this->ilias->raiseError($this->lng->txt("time_limit_not_within_owners"),$this->ilias->error_obj->MESSAGE);
			}
		}

		// TODO: check if login or passwd already exists
		// TODO: check length of login and passwd

		// checks passed. save user
		$userObj = new ilObjUser();
		$userObj->assignData($_POST["Fobject"]);
		$userObj->setTitle($userObj->getFullname());
		$userObj->setDescription($userObj->getEmail());

		$userObj->setTimeLimitOwner($this->object->getRefId());
        $userObj->setTimeLimitUnlimited($_POST["time_limit"]["unlimited"]);
        $userObj->setTimeLimitFrom($this->__toUnix($_POST["time_limit"]["from"]));
        $userObj->setTimeLimitUntil($this->__toUnix($_POST["time_limit"]["until"]));

		$userObj->setUserDefinedData($_POST['udf']);

		$userObj->create();
		
		include_once('classes/class.ilAuthUtils.php');
		if(ilAuthUtils::_isExternalAccountEnabled())
		{
			$userObj->setExternalAccount($_POST["Fobject"]["ext_account"]);
		}

		//$user->setId($userObj->getId());

		//insert user data in table user_data
		$userObj->saveAsNew();

		// setup user preferences
		$userObj->setLanguage($_POST["Fobject"]["language"]);

		//set user skin and style
		$sknst = explode(":", $_POST["Fobject"]["skin_style"]);

		if ($userObj->getPref("style") != $sknst[1] ||
			$userObj->getPref("skin") != $sknst[0])
		{
			$userObj->setPref("skin", $sknst[0]);
			$userObj->setPref("style", $sknst[1]);
		}

		// set hits per pages
		$userObj->setPref("hits_per_page", $_POST["Fobject"]["hits_per_page"]);
		// set show users online
		$userObj->setPref("show_users_online", $_POST["Fobject"]["show_users_online"]);			
		// set hide_own_online_status				
		$userObj->setPref("hide_own_online_status", $_POST["Fobject"]["hide_own_online_status"]);
		
		$userObj->writePrefs();

		//set role entries
		$rbacadmin->assignUser($_POST["Fobject"]["default_role"],$userObj->getId(),true);

		$msg = $this->lng->txt("user_added");
		
		// send new account mail
		if ($_POST["send_mail"] != "")
		{
			include_once("Services/Mail/classes/class.ilAccountMail.php");
			$acc_mail = new ilAccountMail();		
			$acc_mail->setUserPassword($_POST["Fobject"]["passwd"]);
			$acc_mail->setUser($userObj);
			
			if ($acc_mail->send())
			{
				$msg = $msg."<br />".$this->lng->txt("mail_sent");
			}
			else
			{
				$msg = $msg."<br />".$this->lng->txt("mail_not_sent");
			}
		}

		ilUtil::sendInfo($msg, true);

		if(strtolower($_GET["baseClass"]) == 'iladministrationgui')
		{
			$this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
		}
		else
		{
			$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
		}
	}

	/**
	* Does input checks and updates a user account if everything is fine.
	* @access	public
	*/
	function updateObject()
	{
        global $ilias, $rbacsystem, $rbacadmin,$ilUser;

		include_once('classes/class.ilAuthUtils.php');

        //load ILIAS settings
        $settings = $ilias->getAllSettings();

		// User folder
		if($this->usrf_ref_id == USER_FOLDER_ID and !$rbacsystem->checkAccess('visible,read,write',$this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"),$this->ilias->error_obj->MESSAGE);
		}
		// if called from local administration $this->usrf_ref_id is category id 
		// Todo: this has to be fixed. Do not mix user folder id and category id
		if($this->usrf_ref_id != USER_FOLDER_ID)
		{
			// check if user is assigned to category
			if(!$rbacsystem->checkAccess('cat_administrate_users',$this->object->getTimeLimitOwner()))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"),$this->ilias->error_obj->MESSAGE);
			}
		}

		foreach ($_POST["Fobject"] as $key => $val)
		{
			$_POST["Fobject"][$key] = ilUtil::stripSlashes($val);
		}

        // check dynamically required fields
        foreach ($settings as $key => $val)
        {
            $field = substr($key,8);
            switch($field)
            {
            	case 'passwd':
            	case 'passwd2':
            		if(ilAuthUtils::_allowPasswordModificationByAuthMode(ilAuthUtils::_getAuthMode($_POST['Fobject']['auth_mode'])))
            		{
		               $require_keys[] = $field;
            		}
		            break;
            	default:
	                $require_keys[] = $field;
	                break;
        	
        	}
        }
		
        foreach ($require_keys as $key => $val)
        {
            // exclude required system and registration-only fields
            $system_fields = array("default_role");
            if (!in_array($val, $system_fields))
            {
                if (isset($settings["require_" . $val]) && $settings["require_" . $val])
                {
                    if (empty($_POST["Fobject"][$val]))
                    {
                        $this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields") . ": " . 
												 $this->lng->txt($val),$this->ilias->error_obj->MESSAGE);
                    }
                }
            }
        }

		if(!$this->__checkUserDefinedRequiredFields())
		{
			$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
		}
		// validate login
		if ($this->object->getLogin() != $_POST["Fobject"]["login"] &&
			!ilUtil::isLogin($_POST["Fobject"]["login"]))
		{
			$this->ilias->raiseError($this->lng->txt("login_invalid"),$this->ilias->error_obj->MESSAGE);
		}

		// check loginname
		if (ilObjUser::_loginExists($_POST["Fobject"]["login"],$this->id))
		{
			$this->ilias->raiseError($this->lng->txt("login_exists"),$this->ilias->error_obj->MESSAGE);
		}

		if(ilAuthUtils::_allowPasswordModificationByAuthMode(ilAuthUtils::_getAuthMode($_POST['Fobject']['auth_mode'])))
		{
			if($_POST['Fobject']['passwd'] == "********" and
				!strlen($this->object->getPasswd()))
			{
                $this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields") . ": " . 
					$this->lng->txt('password'),$this->ilias->error_obj->MESSAGE);
			}				
			// check passwords
			if ($_POST["Fobject"]["passwd"] != $_POST["Fobject"]["passwd2"])
			{
				$this->ilias->raiseError($this->lng->txt("passwd_not_match"),$this->ilias->error_obj->MESSAGE);
			}

			// validate password
			if (!ilUtil::isPassword($_POST["Fobject"]["passwd"]))
			{
				$this->ilias->raiseError($this->lng->txt("passwd_invalid"),$this->ilias->error_obj->MESSAGE);
			}
		}
		else
		{
			// Password will not be changed...
			$_POST['Fobject']['passwd'] = "********";
		}
		if(ilAuthUtils::_needsExternalAccountByAuthMode(ilAuthUtils::_getAuthMode($_POST['Fobject']['auth_mode'])))
		{
			if(!strlen($_POST['Fobject']['ext_account']))
			{
				$this->ilias->raiseError($this->lng->txt('ext_acccount_required'),$this->ilias->error_obj->MESSAGE);
			}
		}
		if($_POST['Fobject']['ext_account'] && 
			($elogin = ilObjUser::_checkExternalAuthAccount($_POST['Fobject']['auth_mode'],$_POST['Fobject']['ext_account'])))
		{
			if($elogin != $this->object->getLogin())
			{
				$this->ilias->raiseError(
						sprintf($this->lng->txt("err_auth_ext_user_exists"),
							$_POST["Fobject"]["ext_account"],
							$_POST['Fobject']['auth_mode'],
						    $elogin),
						$this->ilias->error_obj->MESSAGE);
			}
		}

		// The password type is not passed with the post data.  Therefore we
		// append it here manually.
		include_once ('./Services/User/classes/class.ilObjUser.php');
	    $_POST["Fobject"]["passwd_type"] = IL_PASSWD_PLAIN;

		// validate email
		if (strlen($_POST['Fobject']['email']) and !ilUtil::is_email($_POST["Fobject"]["email"]))
		{
			$this->ilias->raiseError($this->lng->txt("email_not_valid"),$this->ilias->error_obj->MESSAGE);
		}

		$start = $this->__toUnix($_POST["time_limit"]["from"]);
		$end = $this->__toUnix($_POST["time_limit"]["until"]);

		// validate time limit
		if (!$_POST["time_limit"]["unlimited"] and 
			( $start > $end))
        {
            $this->ilias->raiseError($this->lng->txt("time_limit_not_valid"),$this->ilias->error_obj->MESSAGE);
        }

		if(!$this->ilias->account->getTimeLimitUnlimited())
		{
			if($start < $this->ilias->account->getTimeLimitFrom() or
			   $end > $this->ilias->account->getTimeLimitUntil() or
			   $_POST['time_limit']['unlimited'])
			{
				$_SESSION['error_post_vars'] = $_POST;

				ilUtil::sendInfo($this->lng->txt('time_limit_not_within_owners'));
				$this->editObject();

				return false;
			}
		}

		// TODO: check length of login and passwd

		// checks passed. save user
		$_POST['Fobject']['time_limit_owner'] = $this->object->getTimeLimitOwner();

		$_POST['Fobject']['time_limit_unlimited'] = (int) $_POST['time_limit']['unlimited'];
		$_POST['Fobject']['time_limit_from'] = $this->__toUnix($_POST['time_limit']['from']);
		$_POST['Fobject']['time_limit_until'] = $this->__toUnix($_POST['time_limit']['until']);

		if($_POST['Fobject']['time_limit_unlimited'] != $this->object->getTimeLimitUnlimited() or
		   $_POST['Fobject']['time_limit_from'] != $this->object->getTimeLimitFrom() or
		   $_POST['Fobject']['time_limit_until'] != $this->object->getTimeLimitUntil())
		{
			$_POST['Fobject']['time_limit_message'] = 0;
		}
		else
		{
			$_POST['Fobject']['time_limit_message'] = $this->object->getTimeLimitMessage();
		}

		$this->object->assignData($_POST["Fobject"]);
		$this->object->setUserDefinedData($_POST['udf']);
		
		$this->object->updateLogin($_POST["Fobject"]["login"]);
		$this->object->setTitle($this->object->getFullname());
		$this->object->setDescription($this->object->getEmail());
		$this->object->setLanguage($_POST["Fobject"]["language"]);

		//set user skin and style
		$sknst = explode(":", $_POST["Fobject"]["skin_style"]);

		if ($this->object->getPref("style") != $sknst[1] ||
			$this->object->getPref("skin") != $sknst[0])
		{
			$this->object->setPref("skin", $sknst[0]);
			$this->object->setPref("style", $sknst[1]);
		}

		// set hits per pages
		$this->object->setPref("hits_per_page", $_POST["Fobject"]["hits_per_page"]);
		// set show users online
		$this->object->setPref("show_users_online", $_POST["Fobject"]["show_users_online"]);	
		// set hide_own_online_status				
		if ($_POST["Fobject"]["hide_own_online_status"]) {
			$this->object->setPref("hide_own_online_status", $_POST["Fobject"]["hide_own_online_status"]);	
		}
		else {
			$this->object->setPref("hide_own_online_status", "n");
		}
		
		$this->update = $this->object->update();
		//$rbacadmin->updateDefaultRole($_POST["Fobject"]["default_role"], $this->object->getId());

		$mail_message = $this->__sendProfileMail();
		$msg = $this->lng->txt('saved_successfully').$mail_message;

		// feedback
		ilUtil::sendInfo($msg,true);

		if (strtolower($_GET["baseClass"]) == 'iladministrationgui')
		{
			$this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
		}
		else
		{
			$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
		}
	}



	/**
	* assign users to role
	*
	* @access	public
	*/
	function assignSaveObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		if (!$rbacsystem->checkAccess("edit_roleassignment", $this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_role_to_user"),$this->ilias->error_obj->MESSAGE);
		}

		$selected_roles = $_POST["role_id"] ? $_POST["role_id"] : array();
		$posted_roles = $_POST["role_id_ctrl"] ? $_POST["role_id_ctrl"] : array();
		
		// prevent unassignment of system role from system user
		if ($this->object->getId() == SYSTEM_USER_ID and in_array(SYSTEM_ROLE_ID, $posted_roles))
		{
			array_push($selected_roles,SYSTEM_ROLE_ID);
		}

		$global_roles_all = $rbacreview->getGlobalRoles();
		$assigned_roles_all = $rbacreview->assignedRoles($this->object->getId());
		$assigned_roles = array_intersect($assigned_roles_all,$posted_roles);
		$assigned_global_roles_all = array_intersect($assigned_roles_all,$global_roles_all);
		$assigned_global_roles = array_intersect($assigned_global_roles_all,$posted_roles);
		$posted_global_roles = array_intersect($selected_roles,$global_roles_all);
		
		if ((empty($selected_roles) and count($assigned_roles_all) == count($assigned_roles))
			 or (empty($posted_global_roles) and count($assigned_global_roles_all) == count($assigned_global_roles)))
		{
            //$this->ilias->raiseError($this->lng->txt("msg_min_one_role")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
            // workaround. sometimes jumps back to wrong page
            ilUtil::sendInfo($this->lng->txt("msg_min_one_role")."<br/>".$this->lng->txt("action_aborted"),true);
            $this->ctrl->redirect($this,'roleassignment');
		}

		foreach (array_diff($assigned_roles,$selected_roles) as $role)
		{
			$rbacadmin->deassignUser($role,$this->object->getId());
		}

		foreach (array_diff($selected_roles,$assigned_roles) as $role)
		{
			$rbacadmin->assignUser($role,$this->object->getId(),false);
		}
		
        include_once "./classes/class.ilObjRole.php";

		// update object data entry (to update last modification date)
		$this->object->update();

		ilUtil::sendInfo($this->lng->txt("msg_roleassignment_changed"),true);

		if(strtolower($_GET["baseClass"]) == 'iladministrationgui')
		{
			$this->ctrl->redirect($this,'roleassignment');
		}
		else
		{
			$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
		}

	}
	
	/**
	* display roleassignment panel
	*
	* @access	public
	*/
	function roleassignmentObject ()
	{
		global $rbacreview,$rbacsystem,$ilUser;

		if (!$rbacsystem->checkAccess("edit_roleassignment", $this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_role_to_user"),$this->ilias->error_obj->MESSAGE);
		}
		
		$_SESSION['filtered_roles'] = isset($_POST['filter']) ? $_POST['filter'] : $_SESSION['filtered_roles'];

        if ($_SESSION['filtered_roles'] > 5)
        {
            $_SESSION['filtered_roles'] = 0;
        }
        
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.usr_role_assignment.html');

		if(true)
		{
			$this->tpl->setCurrentBlock("filter");
			$this->tpl->setVariable("FILTER_TXT_FILTER",$this->lng->txt('filter'));
			$this->tpl->setVariable("SELECT_FILTER",$this->__buildFilterSelect());
			$this->tpl->setVariable("FILTER_ACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("FILTER_NAME",'roleassignment');
			$this->tpl->setVariable("FILTER_VALUE",$this->lng->txt('apply_filter'));
			$this->tpl->parseCurrentBlock();
		}
		
		// now get roles depending on filter settings
		$role_list = $rbacreview->getRolesByFilter($_SESSION["filtered_roles"],$this->object->getId());
		$assigned_roles = $rbacreview->assignedRoles($this->object->getId());

        $counter = 0;
        
        include_once ('./classes/class.ilObjRole.php');

		foreach ($role_list as $role)
		{
			// fetch context path of role
			$rolf = $rbacreview->getFoldersAssignedToRole($role["obj_id"],true);

			// only list roles that are not set to status "deleted"
			if ($rbacreview->isDeleted($rolf[0]))
			{
                continue;
            }
            
            // build context path
            $path = "";

			if ($this->tree->isInTree($rolf[0]))
			{
                if ($rolf[0] == ROLE_FOLDER_ID)
                {
                    $path = $this->lng->txt("global");
                }
                else
                {
				    $tmpPath = $this->tree->getPathFull($rolf[0]);

				    // count -1, to exclude the role folder itself
				    /*for ($i = 1; $i < (count($tmpPath)-1); $i++)
				    {
					    if ($path != "")
					    {
						    $path .= " > ";
					    }

					    $path .= $tmpPath[$i]["title"];
				    }*/
				
				    $path = $tmpPath[count($tmpPath)-2]["title"];
				}
			}
			else
			{
				$path = "<b>Rolefolder ".$rolf[0]." not found in tree! (Role ".$role["obj_id"].")</b>";
			}
			
			$disabled = false;
			
			// disable checkbox for system role for the system user
			if (($this->object->getId() == SYSTEM_USER_ID and $role["obj_id"] == SYSTEM_ROLE_ID)
				or (!in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())) and $role["obj_id"] == SYSTEM_ROLE_ID))
			{
				$disabled = true;
			}
			
            if (substr($role["title"],0,3) == "il_")
            {
            	if (!$assignable)
            	{
            		$rolf_arr = $rbacreview->getFoldersAssignedToRole($role["obj_id"],true);
            		$rolf2 = $rolf_arr[0];
            	}
            	else
            	{
            		$rolf2 = $rolf;
            	}
            		
				$parent_node = $this->tree->getParentNodeData($rolf2);
				
				$role["description"] = $this->lng->txt("obj_".$parent_node["type"])."&nbsp;(#".$parent_node["obj_id"].")";
            }
			
			$role_ids[$counter] = $role["obj_id"];
			
            $result_set[$counter][] = ilUtil::formCheckBox(in_array($role["obj_id"],$assigned_roles),"role_id[]",$role["obj_id"],$disabled)."<input type=\"hidden\" name=\"role_id_ctrl[]\" value=\"".$role["obj_id"]."\"/>";
			$this->ctrl->setParameterByClass("ilobjrolegui", "ref_id", $rolf[0]);
			$this->ctrl->setParameterByClass("ilobjrolegui", "obj_id", $role["obj_id"]);
			$result_set[$counter][] = "<a href=\"".$this->ctrl->getLinkTargetByClass("ilobjrolegui", "perm")."\">".ilObjRole::_getTranslation($role["title"])."</a>";
            $result_set[$counter][] = $role["description"];
		    $result_set[$counter][] = $path;

   			++$counter;
        }

		return $this->__showRolesTable($result_set,$role_ids);
    }
		
	/**
	* display public profile
	*
	* DEPRECATED! Use getPublicProfile and deliverVCard via ilCtrl instead!
	*
	* @param	string	$a_template_var			template variable where profile
	*											should be inserted
	* @param	string	$a_template_block_name	name of profile template block
	* @access	public
	*/
	function insertPublicProfile($a_template_var, $a_template_block_name, $a_additional = "")
	{
		if ($_GET["vcard"] == 1)
		{
			$this->deliverVCardObject();
		}
		else
		{
			$this->tpl->setVariable($a_template_var,
				$this->getPublicProfile($a_additional, true));
		}
	}

	// stupid....
	// I agree. yes, really stupid....
	function getPublicProfileObject($a_additional = "", $no_ilctrl = false)
	{
		return $this->getPublicProfile($a_additional, $no_ilctrl);
	}
	
	/**
	* get public profile html code
	*
	* @param	array	$a_additional		additional name/value pairs for profile
	* @param	boolean	$no_ctrl			workaround for old insert public profile
	*										implementation
	*/
	function getPublicProfile($a_additional = "", $no_ilctrl = false,
		$a_raw_rows = false)
	{
		global $ilSetting, $lng;
		
		$tpl = new ilTemplate("tpl.usr_public_profile.html", true, true);
		
		if (!$a_raw_rows)
		{
			$tpl->touchBlock("table_end");
			$tpl->setCurrentBlock("table_start");
			$tpl->setVariable("USR_PROFILE", $this->lng->txt("profile_of")." ".$this->object->getLogin());
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("ROWCOL1", "tblrow1");
		$tpl->setVariable("ROWCOL2", "tblrow2");

		// Check from Database if value
		// of public_profile = "y" show user infomation
		if ($this->object->getPref("public_profile") != "y")
		{
			return;
		}
		
		$tpl->setVariable("TXT_MAIL", $lng->txt("send_mail"));
		$mail_to = ilMail::_getUserInternalMailboxAddress(
			$this->object->getId(),
			$this->object->getLogin(), 
			$this->object->getFirstname(), 
			$this->object->getLastname()
		);
		$tpl->setVariable("MAIL_USR_LOGIN", urlencode(
			$mail_to)
		);

		$tpl->setVariable("TXT_NAME", $this->lng->txt("name"));
		$tpl->setVariable("FIRSTNAME", $this->object->getFirstName());
		$tpl->setVariable("LASTNAME", $this->object->getLastName());
		
		$tpl->setCurrentBlock("vcard");
		$tpl->setVariable("TXT_VCARD", $this->lng->txt("vcard"));
		$tpl->setVariable("TXT_DOWNLOAD_VCARD", $this->lng->txt("vcard_download"));
		if ($no_ilctrl)
		{
			// to do: get rid of this, use ilctrl
			$tpl->setVariable("HREF_VCARD", basename($_SERVER["PHP_SELF"]) . "?ref_id=".
				$_GET["ref_id"]."&amp;user=" . $_GET["user"] . "&vcard=1");
		}
		else
		{
			$this->ctrl->setParameter($this, "user", $this->object->getId());
			$tpl->setVariable("HREF_VCARD", $this->ctrl->getLinkTarget($this, "deliverVCard"));
		}
		$tpl->setVariable("IMG_VCARD", ilUtil::getImagePath("vcard.png"));
		
		$webspace_dir = ilUtil::getWebspaceDir("user");
		$check_dir = ilUtil::getWebspaceDir();
		$imagefile = $webspace_dir."/usr_images/".$this->object->getPref("profile_image");
		$check_file = $check_dir."/usr_images/".$this->object->getPref("profile_image");

		if ($this->object->getPref("public_upload")=="y" && @is_file($check_file))
		{
			//Getting the flexible path of image form ini file
			//$webspace_dir = ilUtil::getWebspaceDir("output");
			$tpl->setCurrentBlock("image");
			$tpl->setVariable("TXT_IMAGE",$this->lng->txt("image"));
			$tpl->setVariable("IMAGE_PATH", $webspace_dir."/usr_images/".$this->object->getPref("profile_image")."?dummy=".rand(1,999999));
			$tpl->parseCurrentBlock();
		}

		$val_arr = array("getInstitution" => "institution", "getDepartment" => "department",
			"getStreet" => "street",
			"getZipcode" => "zip", "getCity" => "city", "getCountry" => "country",
			"getPhoneOffice" => "phone_office", "getPhoneHome" => "phone_home",
			"getPhoneMobile" => "phone_mobile", "getFax" => "fax", "getEmail" => "email",
			"getHobby" => "hobby", "getMatriculation" => "matriculation", "getClientIP" => "client_ip");
			
		foreach ($val_arr as $key => $value)
		{
			// if value "y" show information
			if ($this->object->getPref("public_".$value) == "y")
			{
				$tpl->setCurrentBlock("profile_data");
				$tpl->setVariable("TXT_DATA", $this->lng->txt($value));
				$tpl->setVariable("DATA", $this->object->$key());
				$tpl->parseCurrentBlock();
			}
		}
		
		// delicious row
		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile") == "1" && $this->object->getPref("public_delicious") == "y")
		{
			$tpl->setCurrentBlock("delicious_row");
			$tpl->setVariable("TXT_DELICIOUS", $lng->txt("delicious"));
			$tpl->setVariable("TXT_DEL_ICON", $lng->txt("delicious"));
			$tpl->setVariable("SRC_DEL_ICON", ilUtil::getImagePath("icon_delicious.gif"));
			$tpl->setVariable("DEL_ACCOUNT", $this->object->getDelicious());
			$tpl->parseCurrentBlock();
		}
		
		// map
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
		if (ilGoogleMapUtil::isActivated() && $this->object->getPref("public_location"))
		{
			$tpl->setVariable("TXT_LOCATION", $lng->txt("location"));

			include_once("./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php");
			$map_gui = new ilGoogleMapGUI();
			
			$map_gui->setMapId("user_map");
			$map_gui->setWidth("350px");
			$map_gui->setHeight("230px");
			$map_gui->setLatitude($this->object->getLatitude());
			$map_gui->setLongitude($this->object->getLongitude());
			$map_gui->setZoom($this->object->getLocationZoom());
			$map_gui->setEnableNavigationControl(true);
			$map_gui->addUserMarker($this->object->getId());
			
			$tpl->setVariable("MAP_CONTENT", $map_gui->getHTML());
		}
		
		// display available IM contacts
		if ($ilSetting->get("usr_settings_hide_instant_messengers") != 1)
		{
			$im_arr = array("icq","yahoo","msn","aim","skype");
			
			foreach ($im_arr as $im_name)
			{
				if ($im_id = $this->object->getInstantMessengerId($im_name))
				{
					$tpl->setCurrentBlock("profile_data");
					$tpl->setVariable("TXT_DATA", $this->lng->txt('im_'.$im_name));
					$tpl->setVariable("IMG_ICON", ilUtil::getImagePath($im_name.'online.gif'));
					$tpl->setVariable("TXT_ICON", $this->lng->txt("im_".$im_name."_icon"));
					$tpl->setVariable("DATA", $im_id);
					$tpl->parseCurrentBlock();
				}
			}
		}
		
		if (is_array($a_additional))
		{
			foreach($a_additional as $key => $val)
			{
				$tpl->setCurrentBlock("profile_data");
				$tpl->setVariable("TXT_DATA", $key);
				$tpl->setVariable("DATA", $val);
				$tpl->parseCurrentBlock();
			}
		}
		return $tpl->get();
	}
	
	/**
	* deliver vcard information
	*/
	function deliverVCardObject()
	{
		require_once "./Services/User/classes/class.ilvCard.php";
		$vcard = new ilvCard();
		
		if ($this->object->getPref("public_profile")!="y")
		{
			return;
		}
		
		$vcard->setName($this->object->getLastName(), $this->object->getFirstName(), "", $this->object->getUTitle());
		$vcard->setNickname($this->object->getLogin());
		
		$webspace_dir = ilUtil::getWebspaceDir("output");
		$imagefile = $webspace_dir."/usr_images/".$this->object->getPref("profile_image");
		if ($this->object->getPref("public_upload")=="y" && @is_file($imagefile))
		{
			$fh = fopen($imagefile, "r");
			if ($fh)
			{
				$image = fread($fh, filesize($imagefile));
				fclose($fh);
				require_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
				$mimetype = ilObjMediaObject::getMimeType($imagefile);
				if (preg_match("/^image/", $mimetype))
				{
					$type = $mimetype;
				}
				$vcard->setPhoto($image, $type);
			}
		}

		$val_arr = array("getInstitution" => "institution", "getDepartment" => "department",
			"getStreet" => "street",
			"getZipcode" => "zip", "getCity" => "city", "getCountry" => "country",
			"getPhoneOffice" => "phone_office", "getPhoneHome" => "phone_home",
			"getPhoneMobile" => "phone_mobile", "getFax" => "fax", "getEmail" => "email",
			"getHobby" => "hobby", "getMatriculation" => "matriculation", "getClientIP" => "client_ip");

		$org = array();
		$adr = array();
		foreach ($val_arr as $key => $value)
		{
			// if value "y" show information
			if ($this->object->getPref("public_".$value) == "y")
			{
				switch ($value)
				{
					case "institution":
						$org[0] = $this->object->$key();
						break;
					case "department":
						$org[1] = $this->object->$key();
						break;
					case "street":
						$adr[2] = $this->object->$key();
						break;
					case "zip":
						$adr[5] = $this->object->$key();
						break;
					case "city":
						$adr[3] = $this->object->$key();
						break;
					case "country":
						$adr[6] = $this->object->$key();
						break;
					case "phone_office":
						$vcard->setPhone($this->object->$key(), TEL_TYPE_WORK);
						break;
					case "phone_home":
						$vcard->setPhone($this->object->$key(), TEL_TYPE_HOME);
						break;
					case "phone_mobile":
						$vcard->setPhone($this->object->$key(), TEL_TYPE_CELL);
						break;
					case "fax":
						$vcard->setPhone($this->object->$key(), TEL_TYPE_FAX);
						break;
					case "email":
						$vcard->setEmail($this->object->$key());
						break;
					case "hobby":
						$vcard->setNote($this->object->$key());
						break;
				}
			}
		}

		if (count($org))
		{
			$vcard->setOrganization(join(";", $org));
		}
		if (count($adr))
		{
			$vcard->setAddress($adr[0], $adr[1], $adr[2], $adr[3], $adr[4], $adr[5], $adr[6]);
		}
		
		ilUtil::deliverData(utf8_decode($vcard->buildVCard()), $vcard->getFilename(), $vcard->getMimetype());
	}


	function __getDateSelect($a_type,$a_varname,$a_selected)
    {
        switch($a_type)
        {
            case "minute":
                for($i=0;$i<=60;$i++)
                {
                    $days[$i] = $i < 10 ? "0".$i : $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

            case "hour":
                for($i=0;$i<24;$i++)
                {
                    $days[$i] = $i < 10 ? "0".$i : $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

            case "day":
                for($i=1;$i<32;$i++)
                {
                    $days[$i] = $i < 10 ? "0".$i : $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

            case "month":
                for($i=1;$i<13;$i++)
                {
                    $month[$i] = $i < 10 ? "0".$i : $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$month,false,true);

            case "year":
				if($a_selected < date('Y',time()))
				{
					$start = $a_selected;
				}
				else
				{
					$start = date('Y',time());
				}
					
                for($i = $start;$i < date("Y",time()) + 11;++$i)
                {
                    $year[$i] = $i;
                }
                return ilUtil::formSelect($a_selected,$a_varname,$year,false,true);
        }
    }

	function __toUnix($a_time_arr)
    {
        return mktime($a_time_arr["hour"],
                      $a_time_arr["minute"],
                      $a_time_arr["second"],
                      $a_time_arr["month"],
                      $a_time_arr["day"],
                      $a_time_arr["year"]);
    }

	function __showRolesTable($a_result_set,$a_role_ids = NULL)
	{
        global $rbacsystem;

		$actions = array("assignSave"  => $this->lng->txt("change_assignment"));

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

			$tpl->setVariable("COLUMN_COUNTS",4);
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

            foreach ($actions as $name => $value)
			{
				$tpl->setCurrentBlock("tbl_action_btn");
				$tpl->setVariable("BTN_NAME",$name);
				$tpl->setVariable("BTN_VALUE",$value);
				$tpl->parseCurrentBlock();
			}
			
			if (!empty($a_role_ids))
			{
				// set checkbox toggles
				$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
				$tpl->setVariable("JS_VARNAME","role_id");			
				$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_role_ids));
				$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
				$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
				$tpl->parseCurrentBlock();
			}

            $tpl->setVariable("TPLPATH",$this->tpl->tplPath);


		$this->ctrl->setParameter($this,"cmd","roleassignment");

		// title & header columns
		$tbl->setTitle($this->lng->txt("edit_roleassignment"),"icon_role.gif",$this->lng->txt("roles"));

		//user must be administrator
		$tbl->setHeaderNames(array("",$this->lng->txt("role"),$this->lng->txt("description"),$this->lng->txt("context")));
		$tbl->setHeaderVars(array("","title","description","context"),$this->ctrl->getParameterArray($this,"",false));
		$tbl->setColumnWidth(array("","30%","40%","30%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set,"roleassignment");
		$tbl->render();
		$this->tpl->setVariable("ROLES_TABLE",$tbl->tpl->get());

		return true;
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			default:
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				break;
		}

        //$tbl->enable("hits");
		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function __unsetSessionVariables()
	{
		unset($_SESSION["filtered_roles"]);
	}

	function __buildFilterSelect()
	{
		$action[0] = $this->lng->txt('assigned_roles');
		$action[1] = $this->lng->txt('all_roles');
		$action[2] = $this->lng->txt('all_global_roles');
		$action[3] = $this->lng->txt('all_local_roles');
		$action[4] = $this->lng->txt('internal_local_roles_only');
		$action[5] = $this->lng->txt('non_internal_local_roles_only');

		return ilUtil::formSelect($_SESSION['filtered_roles'],"filter",$action,false,true);
	}
	
	function hitsperpageObject()
	{
		parent::hitsperpageObject();
		$this->roleassignmentObject();
	}
	
	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator;
		
		$ilLocator->clearItems();

		if ($_GET["admin_mode"] == "settings")	// system settings
		{		
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));
				
			if ($_GET['ref_id'] == USER_FOLDER_ID)
			{					
				$ilLocator->addItem($this->lng->txt("obj_".ilObject::_lookupType(
					ilObject::_lookupObjId($_GET["ref_id"]))),
					$this->ctrl->getLinkTargetByClass("ilobjuserfoldergui", "view"));
			}
			elseif ($_GET['ref_id'] == ROLE_FOLDER_ID)
			{
				$ilLocator->addItem($this->lng->txt("obj_".ilObject::_lookupType(
					ilObject::_lookupObjId($_GET["ref_id"]))),
					$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));
			}
			
			if ($_GET["obj_id"] > 0)
			{
				$ilLocator->addItem($this->object->getTitle(),
					$this->ctrl->getLinkTarget($this, "view"));
			}
		}
		else							// repository administration
		{
			// ?
		}
	}

	function showUpperIcon()
	{
		global $tree, $tpl, $objDefinition;

		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			$tpl->setUpperIcon(
				$this->ctrl->getLinkTargetByClass("ilobjuserfoldergui", "view"));
		}
		else
		{		
			if ($this->object->getRefId() != ROOT_FOLDER_ID &&
				$this->object->getRefId() != SYSTEM_FOLDER_ID)
			{
				$par_id = $tree->getParentId($this->usrf_ref_id);
				$tpl->setUpperIcon("repository.php?ref_id=".$par_id);
			}
		}
	}

	function __sendProfileMail()
	{
		global $ilUser,$ilias;

		if($_POST['send_mail'] != 'y')
		{
			return '';
		}
		if(!strlen($this->object->getEmail()))
		{
			return '';
		}

		// Choose language of user
		$usr_lang = new ilLanguage($this->object->getLanguage());
		$usr_lang->loadLanguageModule('crs');
		$usr_lang->loadLanguageModule('registration');

		include_once "Services/Mail/classes/class.ilMimeMail.php";

		$mmail = new ilMimeMail();
		$mmail->autoCheck(false);
		$mmail->From($ilUser->getEmail());
		$mmail->To($this->object->getEmail());

		// mail subject
		$subject = $usr_lang->txt("profile_changed");

			
		// mail body
		$body = ($usr_lang->txt("reg_mail_body_salutation")." ".$this->object->getFullname().",\n\n");

		$date = $this->object->getApproveDate();
		// Approve
		if((time() - strtotime($date)) < 10)
		{
			$body .= ($usr_lang->txt('reg_mail_body_approve')."\n\n");
		}
		else
		{
			$body .= ($usr_lang->txt('reg_mail_body_profile_changed')."\n\n");
		}

		// Append login info only if password has been chacnged
		if($_POST['Fobject']['passwd'] != '********')
		{
			$body .= $usr_lang->txt("reg_mail_body_text2")."\n".
				ILIAS_HTTP_PATH."/login.php?client_id=".$ilias->client_id."\n".
				$usr_lang->txt("login").": ".$this->object->getLogin()."\n".
				$usr_lang->txt("passwd").": ".$_POST["Fobject"]["passwd"]."\n\n";
		}
		$body .= ($usr_lang->txt("reg_mail_body_text3")."\n");
		$body .= $this->object->getProfileAsString($usr_lang);

		$mmail->Subject($subject);
		$mmail->Body($body);
		$mmail->Send();

			
		return "<br/>".$this->lng->txt("mail_sent");
	}

} // END class.ilObjUserGUI
?>
