<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjUserGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjUserGUI: ilLearningProgressGUI, ilObjiLincUserGUI
*
* @ingroup ServicesUser
*/
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
		global $ilCtrl, $lng;

		define('USER_FOLDER_ID',7);

		$this->type = "usr";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		$this->usrf_ref_id =& $this->ref_id;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,'obj_id');
		
		$lng->loadLanguageModule('user');

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

		$this->prepareOutput();

		switch($next_class)
		{
			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui =& new ilLearningProgressGUI(LP_MODE_USER_FOLDER,USER_FOLDER_ID,$this->object->getId());
				$this->ctrl->forwardCommand($new_gui);
				break;

			case "ilobjilincusergui":
				include_once './Modules/ILinc/classes/class.ilObjiLincUserGUI.php';
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

	/* Overwritten from base class
	*/
	function setTitleAndDescription()
	{
		if(strtolower(get_class($this->object)) == 'ilobjuser')
		{
			$this->tpl->setTitle('['.$this->object->getLogin().'] '.$this->object->getTitle());
			$this->tpl->setDescription($this->object->getLongDescription());
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"), $this->lng->txt("obj_" . $this->object->getType()));
		}
		else
		{
			parent::setTitleAndDescription();
		}
	}



	function cancelObject()
	{
		session_unregister("saved_post");

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
/*
	function createOldObject()
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
				include_once './Services/AccessControl/classes/class.ilObjRole.php';

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

			// BEGIN DiskQuota Remember the state of the "send info mail" checkbox
			$sendInfoMail = $ilUser->getPref('send_info_mails') == 'y';
			if ($sendInfoMail)
			// END DiskQuota Remember the state of the "send info mail" checkbox
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

		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
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
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
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
		include_once("./Services/Style/classes/class.ilObjStyleSettings.php");
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
*/

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

	function initCreate()
	{
		global $tpl, $rbacsystem, $rbacreview, $ilUser;

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
				include_once './Services/AccessControl/classes/class.ilObjRole.php';

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
			$this->default_role = 4;
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

			$this->default_role = array_shift($keys);
		}
		$this->selectable_roles = $rol;
	}

	/**
	* Display user create form
	*/
	function createObject()
	{
		global $tpl, $rbacsystem, $rbacreview, $ilUser;

		if (!$rbacsystem->checkAccess('create_user', $this->usrf_ref_id) and
			!$rbacsystem->checkAccess('cat_administrate_users',$this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCreate();
		$this->initForm("create");
		return $tpl->setContent($this->form_gui->getHtml());
	}

	/**
	* save user data
	* @access	public
	*/
	function saveObject()
	{
        global $ilAccess, $ilSetting, $tpl, $ilUser, $rbacadmin;

        include_once('./Services/Authentication/classes/class.ilAuthUtils.php');

		// User folder
		if (!$ilAccess->checkAccess('create_user', "", $this->usrf_ref_id) &&
			!$ilAccess->checkAccess('cat_administrate_users', "", $this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
	
		$this->initCreate();
		$this->initForm("create");

		if ($this->form_gui->checkInput())
		{
// @todo: external account; time limit check and savings

			// set password type manually
			$_POST["passwd_type"] = IL_PASSWD_PLAIN;
			// checks passed. save user
			$userObj = new ilObjUser();
			
			$from = new ilDateTime($_POST['time_limit_from']['date'].' '.$_POST['time_limit_from']['time'],IL_CAL_DATETIME,$ilUser->getTimeZone());
			$_POST['time_limit_from'] = $from->get(IL_CAL_UNIX);
			
			$until = new ilDateTime($_POST['time_limit_until']['date'].' '.$_POST['time_limit_until']['time'],IL_CAL_DATETIME,$ilUser->getTimeZone());
			$_POST['time_limit_until'] = $until->get(IL_CAL_UNIX);
			if (is_array($_POST['birthday']))
			{
				if (strlen($_POST['birthday']['date']))
				{
					$_POST['birthday'] = $_POST['birthday']['date'];
				}
				else
				{
					$_POST['birthday'] = null;
				}
			}
			$userObj->assignData($_POST);
			$userObj->setTitle($userObj->getFullname());
			$userObj->setDescription($userObj->getEmail());

			$userObj->setTimeLimitOwner($this->object->getRefId());
			$userObj->setTimeLimitUnlimited($_POST["time_limit_unlimited"]);
// @todo
//			$userObj->setTimeLimitFrom($this->__toUnix($_POST["time_limit"]["from"]));
//			$userObj->setTimeLimitUntil($this->__toUnix($_POST["time_limit"]["until"]));

			$udf = array();
			foreach($_POST as $k => $v)
			{
				if (substr($k, 0, 4) == "udf_")
				{
					$udf[substr($k, 4)] = $v;
				}
			}
			$userObj->setUserDefinedData($udf);

			$userObj->create();
			
			include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
			if(ilAuthUtils::_isExternalAccountEnabled())
			{
				$userObj->setExternalAccount($_POST["ext_account"]);
			}

			// set a timestamp for last_password_change
			// this ts is needed by the ACCOUNT_SECURITY_MODE_CUSTOMIZED
			// in ilSecuritySettings
			$userObj->setLastPasswordChangeTS( time() );

			//insert user data in table user_data
			$userObj->saveAsNew();

			// setup user preferences
			$userObj->setLanguage($_POST["language"]);

			// Set disk quota
			require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				// The disk quota is entered in megabytes but stored in bytes
				$userObj->setPref("disk_quota", trim($_POST["disk_quota"]) * ilFormat::_getSizeMagnitude() * ilFormat::_getSizeMagnitude());
			}

			//set user skin and style
			$sknst = explode(":", $_POST["skin_style"]);

			if ($userObj->getPref("style") != $sknst[1] ||
				$userObj->getPref("skin") != $sknst[0])
			{
				$userObj->setPref("skin", $sknst[0]);
				$userObj->setPref("style", $sknst[1]);
			}

			$userObj->setPref("hits_per_page", $_POST["hits_per_page"]);
			$userObj->setPref("show_users_online", $_POST["show_users_online"]);
			$userObj->setPref("hide_own_online_status", $_POST["hide_own_online_status"] ? 'y' : 'n');
			if((int)$ilSetting->get('session_reminder_enabled'))
			{
				$userObj->setPref('session_reminder_enabled', (int)$_POST['session_reminder_enabled']);
			}
			$userObj->writePrefs();

			//set role entries
			$rbacadmin->assignUser($_POST["default_role"],$userObj->getId(),true);

			$msg = $this->lng->txt("user_added");			

			$ilUser->setPref('send_info_mails', ($_POST['send_mail'] == 'y') ? 'y' : 'n');
			$ilUser->writePrefs();                        

			$this->object = $userObj;
			$this->uploadUserPictureObject();

			// send new account mail
			if($_POST['send_mail'] == 'y')
			{
				include_once('Services/Mail/classes/class.ilAccountMail.php');
				$acc_mail = new ilAccountMail();
				$acc_mail->useLangVariablesAsFallback(true);
				$acc_mail->setUserPassword($_POST['passwd']);
				$acc_mail->setUser($userObj);

				if ($acc_mail->send())
				{
					$msg = $msg.'<br />'.$this->lng->txt('mail_sent');
					ilUtil::sendSuccess($msg, true);
				}
				else
				{
					$msg = $msg.'<br />'.$this->lng->txt('mail_not_sent');
					ilUtil::sendInfo($msg, true);
				}
			}
			else
			{
				ilUtil::sendSuccess($msg, true);
			}


			if(strtolower($_GET["baseClass"]) == 'iladministrationgui')
			{
				$this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
			}
			else
			{
				$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
			}
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$tpl->setContent($this->form_gui->getHtml());
		}
	}

	/**
	* Display user edit form
	*
	* @access	public
	*/
    function editObject()
    {
        global $ilias, $rbacsystem, $rbacreview, $rbacadmin, $styleDefinition, $ilUser
			,$ilSetting, $ilCtrl;

		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');

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

		// get form
		$this->initForm("edit");
		$this->getValues();
		$this->tpl->setContent($this->form_gui->getHTML());
	}

	/**
	* Update user
	*/
	public function updateObject()
	{
		global $tpl, $rbacsystem, $ilias, $ilUser, $ilSetting;
		
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
		$this->initForm("edit");
		
		// we do not want to store this dates, they are only printed out
		unset($_POST['approve_date']);
		$_POST['agree_date'] = $this->object->getAgreeDate();
		unset($_POST['last_login']);
		
		if ($this->form_gui->checkInput())
		{
			// @todo: external account; time limit
			// if not allowed or empty -> do no change password
			if (!ilAuthUtils::_allowPasswordModificationByAuthMode(ilAuthUtils::_getAuthMode($_POST['auth_mode']))
				|| trim($_POST['passwd']) == "")
			{
				$_POST['passwd'] = "********";
			}
			$_POST["passwd_type"] = IL_PASSWD_PLAIN;

			// differentiate account security mode
			require_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
			$security_settings = ilSecuritySettings::_getInstance();
			if( $security_settings->getAccountSecurityMode() ==
				ilSecuritySettings::ACCOUNT_SECURITY_MODE_CUSTOMIZED )
			{
				/*
				 * reset counter for failed logins
				 * if $_POST['active'] is set to 1
				 */
				if( $_POST['active'] == 1 )
				{
					ilObjUser::_resetLoginAttempts( $this->object->getId() );
				}
			}

			$from = new ilDateTime($_POST['time_limit_from']['date'].' '.$_POST['time_limit_from']['time'],IL_CAL_DATETIME,$ilUser->getTimeZone());
			$_POST['time_limit_from'] = $from->get(IL_CAL_UNIX);
			
			$until = new ilDateTime($_POST['time_limit_until']['date'].' '.$_POST['time_limit_until']['time'],IL_CAL_DATETIME,$ilUser->getTimeZone());
			$_POST['time_limit_until'] = $until->get(IL_CAL_UNIX);
			$_POST['time_limit_owner'] = $this->usrf_ref_id;
			if (is_array($_POST['birthday']))
			{
				if (strlen($_POST['birthday']['date']))
				{
					$_POST['birthday'] = $_POST['birthday']['date'];
				}
				else
				{
					$_POST['birthday'] = null;
				}
			}
			$this->object->assignData($_POST);

			$udf = array();
			foreach($_POST as $k => $v)
			{
				if (substr($k, 0, 4) == "udf_")
				{
					$udf[substr($k, 4)] = $v;
				}
			}
			$this->object->setUserDefinedData($udf);
			
			try 
			{
				$this->object->updateLogin($_POST['login']);
			}
			catch (ilUserException $e)
			{
				ilUtil::sendFailure($e->getMessage());
				$this->form_gui->setValuesByPost();
				return $tpl->setContent($this->form_gui->getHtml());				
			}			
			
			$this->object->setTitle($this->object->getFullname());
			$this->object->setDescription($this->object->getEmail());
			$this->object->setLanguage($_POST["language"]);

			require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				// set disk quota
				$this->object->setPref("disk_quota", $_POST["disk_quota"] * ilFormat::_getSizeMagnitude() * ilFormat::_getSizeMagnitude());
			}

			//set user skin and style
			$sknst = explode(":", $_POST["skin_style"]);

			if ($this->object->getPref("style") != $sknst[1] ||
				$this->object->getPref("skin") != $sknst[0])
			{
				$this->object->setPref("skin", $sknst[0]);
				$this->object->setPref("style", $sknst[1]);
			}

			$this->object->setPref("hits_per_page", $_POST["hits_per_page"]);
			$this->object->setPref("show_users_online", $_POST["show_users_online"]);
			$this->object->setPref("hide_own_online_status", $_POST["hide_own_online_status"] ? 'y' : 'n');

			// set a timestamp for last_password_change
			// this ts is needed by the ACCOUNT_SECURITY_MODE_CUSTOMIZED
			// in ilSecuritySettings
			$this->object->setLastPasswordChangeTS( time() );
			
			global $ilSetting;
			if((int)$ilSetting->get('session_reminder_enabled'))
			{
				$this->object->setPref('session_reminder_enabled', (int)$_POST['session_reminder_enabled']);
			}


			$this->update = $this->object->update();
                        
                
			// If the current user is editing its own user account,
			// we update his preferences.
			if ($ilUser->getId() == $this->object->getId()) 
			{
				$ilUser->readPrefs();    
			}
			$ilUser->setPref('send_info_mails', ($_POST['send_mail'] == 'y') ? 'y' : 'n');
			$ilUser->writePrefs();

			$mail_message = $this->__sendProfileMail();
			$msg = $this->lng->txt('saved_successfully').$mail_message;
			
			// same personal image
//var_dump($_POST);
//var_dump($_FILES);
			$this->uploadUserPictureObject();

			// feedback
			ilUtil::sendSuccess($msg,true);

			if (strtolower($_GET["baseClass"]) == 'iladministrationgui')
			{
				$this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
			}
			else
			{
				$this->ctrl->redirectByClass('ilobjcategorygui','listUsers');
			}
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$tpl->setContent($this->form_gui->getHtml());
		}
	}

	/**
	* Get values from user object and put them into form
	*/
	function getValues()
	{
		global $ilUser, $ilSetting;

		$data = array();

		// login data
		$data["auth_mode"] = $this->object->getAuthMode();
		$data["login"] = $this->object->getLogin();
		//$data["passwd"] = "********";
		//$data["passwd2"] = "********";
		$data["ext_account"] = $this->object->getExternalAccount();

		// system information
		require_once 'classes/class.ilFormat.php';
		$data["create_date"] = ilFormat::formatDate($this->object->getCreateDate(),'datetime',true);
		$data["owner"] = ilObjUser::_lookupLogin($this->object->getOwner());
		$data["approve_date"] = ($this->object->getApproveDate() != "")
			? ilFormat::formatDate($this->object->getApproveDate(),'datetime',true)
			: null;
		$data["agree_date"] = ($this->object->getAgreeDate() != "")
			? ilFormat::formatDate($this->object->getAgreeDate(),'datetime',true)
			: null;
		$data["last_login"] =  ($this->object->getLastLogin() != "")
			 ? ilFormat::formatDate($this->object->getLastLogin(),'datetime',true)
			 : null;
		$data["active"] = $this->object->getActive();
		$data["time_limit_unlimited"] = $this->object->getTimeLimitUnlimited();
		
		$from = new ilDateTime($this->object->getTimeLimitFrom() ? $this->object->getTimeLimitFrom() : time(),IL_CAL_UNIX);
		$data["time_limit_from"]["date"] = $from->get(IL_CAL_FKT_DATE,'Y-m-d',$ilUser->getTimeZone());
		$data["time_limit_from"]["time"] = $from->get(IL_CAL_FKT_DATE,'H:i:s',$ilUser->getTimeZone());

		$until = new ilDateTime($this->object->getTimeLimitUntil() ? $this->object->getTimeLimitUntil() : time(),IL_CAL_UNIX);
		$data['time_limit_until']['date'] = $until->get(IL_CAL_FKT_DATE,'Y-m-d',$ilUser->getTimeZone());
		$data['time_limit_until']['time'] = $until->get(IL_CAL_FKT_DATE,'H:i:s',$ilUser->getTimeZone());

		
		// BEGIN DiskQuota, Show disk space used
		require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		if (ilDiskQuotaActivationChecker::_isActive())
		{
			$data["disk_quota"] = $this->object->getDiskQuota() / ilFormat::_getSizeMagnitude() / ilFormat::_getSizeMagnitude();
		}
		// W. Randelshofer 2008-09-09: Deactivated display of disk space usage,
		// because determining the disk space usage may take several minutes.
                /*
		require_once "Modules/File/classes/class.ilObjFileAccess.php";
		require_once "Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php";
		require_once "Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php";
		require_once "Services/Mail/classes/class.ilObjMailAccess.php";
		require_once "Modules/Forum/classes/class.ilObjForumAccess.php";
		require_once "Modules/MediaCast/classes/class.ilObjMediaCastAccess.php";
		$data["disk_space_used"] =
			ilObjFileAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjFileBasedLMAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjSAHSLearningModuleAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjMailAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjForumAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjMediaCastAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>';
		*/
		// END DiskQuota, Show disk space used

		// personal data
		$data["gender"] = $this->object->getGender();
		$data["firstname"] = $this->object->getFirstname();
		$data["lastname"] = $this->object->getLastname();
		$data["title"] = $this->object->getUTitle();
		$data['birthday'] = $this->object->getBirthday();
		$data["institution"] = $this->object->getInstitution();
		$data["department"] = $this->object->getDepartment();
		$data["street"] = $this->object->getStreet();
		$data["city"] = $this->object->getCity();
		$data["zipcode"] = $this->object->getZipcode();
		$data["country"] = $this->object->getCountry();
		$data["phone_office"] = $this->object->getPhoneOffice();
		$data["phone_home"] = $this->object->getPhoneHome();
		$data["phone_mobile"] = $this->object->getPhoneMobile();
		$data["fax"] = $this->object->getFax();
		$data["email"] = $this->object->getEmail();
		$data["hobby"] = $this->object->getHobby();
		$data["referral_comment"] = $this->object->getComment();

		// instant messengers
		$data["im_icq"] = $this->object->getInstantMessengerId('icq');
		$data["im_yahoo"] = $this->object->getInstantMessengerId('yahoo');
		$data["im_msn"] = $this->object->getInstantMessengerId('msn');
		$data["im_aim"] = $this->object->getInstantMessengerId('aim');
		$data["im_skype"] = $this->object->getInstantMessengerId('skype');
		$data["im_jabber"] = $this->object->getInstantMessengerId('jabber');
		$data["im_voip"] = $this->object->getInstantMessengerId('voip');

		// other data
		$data["matriculation"] = $this->object->getMatriculation();
		$data["client_ip"] = $this->object->getClientIP();

		// user defined fields
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$this->user_defined_fields = ilUserDefinedFields::_getInstance();
		$user_defined_data = $this->object->getUserDefinedData();
		foreach($this->user_defined_fields->getDefinitions() as $field_id => $definition)
		{
			$data["udf_".$field_id] = $user_defined_data[$field_id];
		}

		// settings
		$data["language"] = $this->object->getLanguage();
		$data["skin_style"] = $this->object->skin.":".$this->object->prefs["style"];
		$data["hits_per_page"] = $this->object->prefs["hits_per_page"];
		$data["show_users_online"] = $this->object->prefs["show_users_online"];
		$data["hide_own_online_status"] = $this->object->prefs["hide_own_online_status"] == 'y';
		$data["session_reminder_enabled"] = (int)$this->object->prefs["session_reminder_enabled"];

		$this->form_gui->setValuesByArray($data);
	}

	/**
	* Init user form
	*/
	function initForm($a_mode)
	{
		global $lng, $ilCtrl, $styleDefinition, $ilSetting, $ilClientIniFile, $ilUser;

		$settings = $ilSetting->getAll();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
		if ($a_mode == "create")
		{
			$this->form_gui->setTitle($lng->txt("usr_new"));
		}
		else
		{
			$this->form_gui->setTitle($lng->txt("usr_edit"));
		}

		// login data
		$sec_l = new ilFormSectionHeaderGUI();
		$sec_l->setTitle($lng->txt("login_data"));
		$this->form_gui->addItem($sec_l);

		// authentication mode
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
		$active_auth_modes = ilAuthUtils::_getActiveAuthModes();
		$am = new ilSelectInputGUI($lng->txt("auth_mode"), "auth_mode");
		$option = array();
		foreach ($active_auth_modes as $auth_name => $auth_key)
		{
			if ($auth_name == 'default')
			{
				$name = $this->lng->txt('auth_'.$auth_name)." (".$this->lng->txt('auth_'.ilAuthUtils::_getAuthModeName($auth_key)).")";
			}
			else
			{
				$name = $this->lng->txt('auth_'.$auth_name);
			}
			$option[$auth_name] = $name;
		}
		$am->setOptions($option);
		$this->form_gui->addItem($am);

		// login
		$lo = new ilUserLoginInputGUI($lng->txt("login"), "login");
		$lo->setRequired(true);
		if ($a_mode == "edit")
		{
			$lo->setCurrentUserId($this->object->getId());
			try
			{
				include_once 'Services/Calendar/classes/class.ilDate.php';				
 
				$last_history_entry = ilObjUser::_getLastHistoryDataByUserId($this->object->getId());				
				$lo->setInfo(
					sprintf(
						$this->lng->txt('usr_loginname_history_info'),
						ilDatePresentation::formatDate(new ilDateTime($last_history_entry[1],IL_CAL_UNIX)),
						$last_history_entry[0]
					)
				);		
			}
			catch(ilUserException $e) { }
		}
		
		$this->form_gui->addItem($lo);

		// passwords
// @todo: do not show passwords, if there is not a single auth, that
// allows password setting
		{
			$pw = new ilPasswordInputGUI($lng->txt("passwd"), "passwd");
			$pw->setSize(32);
			$pw->setMaxLength(32);
			$pw->setValidateAuthPost("auth_mode");
			if ($a_mode == "create")
			{
				$pw->setRequiredOnAuth(true);
			}
			$this->form_gui->addItem($pw);
		}
		// @todo: invisible/hidden passwords

		// external account
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
		if(ilAuthUtils::_isExternalAccountEnabled())
		{
			$ext = new ilTextInputGUI($lng->txt("user_ext_account"), "ext_account");
			$ext->setSize(40);
			$ext->setMaxLength(50);
			$ext->setInfo($lng->txt("user_ext_account_desc"));
			$this->form_gui->addItem($ext);
		}

		// login data
		$sec_si = new ilFormSectionHeaderGUI();
		$sec_si->setTitle($this->lng->txt("system_information"));
		$this->form_gui->addItem($sec_si);

		// create date, approve date, agreement date, last login
		if ($a_mode == "edit")
		{
			$sia = array("create_date", "approve_date", "agree_date", "last_login", "owner");
			foreach($sia as $a)
			{
				$siai = new ilNonEditableValueGUI($lng->txt($a), $a);
				$this->form_gui->addItem($siai);
			}
		}

		// active
		$ac = new ilCheckboxInputGUI($lng->txt("active"), "active");
		$ac->setChecked(true);
		$this->form_gui->addItem($ac);

		// access	@todo: get fields right (names change)
		$lng->loadLanguageModule('crs');
		
		// access
		$radg = new ilRadioGroupInputGUI($lng->txt("time_limit"), "time_limit_unlimited");
		$radg->setValue(1);
			$op1 = new ilRadioOption($lng->txt("user_access_unlimited"), 1);
			$radg->addOption($op1);
			$op2 = new ilRadioOption($lng->txt("user_access_limited"), 0);
			$radg->addOption($op2);
		
//		$ac = new ilCheckboxInputGUI($lng->txt("time_limit"), "time_limit_unlimited");
//		$ac->setChecked(true);
//		$ac->setOptionTitle($lng->txt("crs_unlimited"));

		// access.from
		$acfrom = new ilDateTimeInputGUI($this->lng->txt("crs_from"), "time_limit_from");
		$acfrom->setShowTime(true);
//		$ac->addSubItem($acfrom);
		$op2->addSubItem($acfrom);

		// access.to
		$acto = new ilDateTimeInputGUI($this->lng->txt("crs_to"), "time_limit_until");
		$acto->setShowTime(true);
//		$ac->addSubItem($acto);
		$op2->addSubItem($acto);

//		$this->form_gui->addItem($ac);
		$this->form_gui->addItem($radg);

		require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		if (ilDiskQuotaActivationChecker::_isActive())
		{
			// disk quota
			$disk_quota = new ilTextInputGUI($lng->txt("disk_quota"), "disk_quota");
			$disk_quota->setSize(10);
			$disk_quota->setMaxLength(11);
			$this->form_gui->addItem($disk_quota);

			if ($a_mode == "edit")
			{
				// show which disk quota is in effect, and explain why
				require_once 'Services/WebDAV/classes/class.ilDiskQuotaChecker.php';
				$dq_info = ilDiskQuotaChecker::_lookupDiskQuota($this->object->getId());
				if ($dq_info['user_disk_quota'] > $dq_info['role_disk_quota'])
				{
					$info_text = sprintf($lng->txt('disk_quota_is_1_instead_of_2_by_3'),
						ilFormat::formatSize($dq_info['user_disk_quota'],'short'),
						ilFormat::formatSize($dq_info['role_disk_quota'],'short'),
						$dq_info['role_title']);
				}
				else if (is_infinite($dq_info['role_disk_quota']))
				{
					$info_text = sprintf($lng->txt('disk_quota_is_unlimited_by_1'), $dq_info['role_title']);
				}
				else
				{
					$info_text = sprintf($lng->txt('disk_quota_is_1_by_2'),
						ilFormat::formatSize($dq_info['role_disk_quota'],'short'),
						$dq_info['role_title']);
				}
				$disk_quota->setInfo($this->lng->txt("enter_in_mb_desc").'<br>'.$info_text);


				// disk usage
				$du_info = ilDiskQuotaChecker::_lookupDiskUsage($this->object->getId());
				$disk_usage = new ilNonEditableValueGUI($lng->txt("disk_usage"), "disk_usage");
				if ($du_info['last_update'] === null)
				{
					$disk_usage->setValue($lng->txt('unknown'));
				}
				else
				{
			        require_once 'classes/class.ilFormat.php';
					$disk_usage->setValue(ilFormat::formatSize($du_info['disk_usage'],'short'));
					$info = '<table>';
					// write the count and size of each object type
					foreach ($du_info['details'] as $detail_data)
					{
						$info .= '<tr>'.
							'<td>'.$detail_data['count'].'</td>'.
							'<td>'.$lng->txt($detail_data['type']).'</td>'.
							'<td>'.ilFormat::formatSize($detail_data['size'], 'short').'</td>'.
							'</tr>'
							;
					}
					$info .= '</table>';
					$info .= '<br>'.$this->lng->txt('last_update').': '.ilFormat::formatDate($du_info['last_update'], 'datetime', true);
					$disk_usage->setInfo($info);

				}
				$this->form_gui->addItem($disk_usage);

				// date when the last disk quota reminder was sent to the user
				if (true || $dq_info['last_reminder'])
				{
					$reminder = new ilNonEditableValueGUI($lng->txt("disk_quota_last_reminder_sent"), "last_reminder");
					$reminder->setValue(ilFormat::formatDate($dq_info['last_reminder'], 'datetime', true));
					$reminder->setInfo($this->lng->txt("disk_quota_last_reminder_sent_desc"));
					$this->form_gui->addItem($reminder);
				}
			}
		}

         
		// personal data
		$sec_pd = new ilFormSectionHeaderGUI();
		$sec_pd->setTitle($this->lng->txt("personal_data"));
		$this->form_gui->addItem($sec_pd);

		// gender
		$gndr = new ilRadioGroupInputGUI($lng->txt("gender"), "gender");
		$gndr->setRequired(isset($settings["require_gender"]) && $settings["require_gender"]);
		$female = new ilRadioOption($lng->txt("gender_f"), "f");
		$gndr->addOption($female);
		$male = new ilRadioOption($lng->txt("gender_m"), "m");
		$gndr->addOption($male);
		$this->form_gui->addItem($gndr);

		// firstname, lastname, title
		$fields = array("firstname" => true, "lastname" => true,
			"title" => isset($settings["require_title"]) && $settings["require_title"]);
		foreach($fields as $field => $req)
		{
			$inp = new ilTextInputGUI($lng->txt($field), $field);
			$inp->setSize(32);
			$inp->setMaxLength(32);
			$inp->setRequired($req);
			$this->form_gui->addItem($inp);
		}

		// personal image
		$pi = new ilImageFileInputGUI($lng->txt("personal_picture"), "userfile");
		if ($a_mode == "edit" || $a_mode == "upload")
		{
			$pi->setImage(ilObjUser::_getPersonalPicturePath($this->object->getId(), "small", true,
				true));
		}
		$this->form_gui->addItem($pi);

		$birthday = new ilBirthdayInputGUI($lng->txt('birthday'), 'birthday');
		$birthday->setRequired(isset($settings["require_birthday"]) && $settings["require_birthday"]);
		$birthday->setShowEmpty(true);
		$birthday->setStartYear(1900);
		$this->form_gui->addItem($birthday);

		// contact data
		$sec_cd = new ilFormSectionHeaderGUI();
		$sec_cd->setTitle($this->lng->txt("contact_data"));
		$this->form_gui->addItem($sec_cd);

		// institution, department, street, city, zip code, country, phone office
		// phone home, phone mobile, fax, e-mail
		$fields = array(
			array("institution", 40, 80),
			array("department", 40, 80),
			array("street", 40, 40),
			array("city", 40, 40),
			array("zipcode", 10, 10),
			array("country", 40, 40),
			array("phone_office", 30, 30),
			array("phone_home", 30, 30),
			array("phone_mobile", 30, 30),
			array("fax", 30, 30));
		foreach ($fields as $field)
		{
			$inp = new ilTextInputGUI($lng->txt($field[0]), $field[0]);
			$inp->setSize($field[1]);
			$inp->setMaxLength($field[2]);
			$inp->setRequired(isset($settings["require_".$field[0]]) &&
				$settings["require_".$field[0]]);
			$this->form_gui->addItem($inp);
		}

		// email
		$em = new ilEMailInputGUI($lng->txt("email"), "email");
		$em->setRequired(isset($settings["require_email"]) &&
			$settings["require_email"]);
		$this->form_gui->addItem($em);

		// interests/hobbies
		$hob = new ilTextAreaInputGUI($lng->txt("hobby"), "hobby");
		$hob->setRows(3);
		$hob->setCols(40);
		$hob->setRequired(isset($settings["require_hobby"]) &&
			$settings["require_hobby"]);
		$this->form_gui->addItem($hob);

		// referral comment
		$rc = new ilTextAreaInputGUI($lng->txt("referral_comment"), "referral_comment");
		$rc->setRows(3);
		$rc->setCols(40);
		$rc->setRequired(isset($settings["require_referral_comment"]) &&
			$settings["require_referral_comment"]);
		$this->form_gui->addItem($rc);

		// instant messengers
		$sec_im = new ilFormSectionHeaderGUI();
		$sec_im->setTitle($this->lng->txt("instant_messengers"));
		$this->form_gui->addItem($sec_im);

		// icq, yahoo, msn, aim, skype
		$fields = array("icq", "yahoo", "msn", "aim", "skype", "jabber", "voip");
		foreach ($fields as $field)
		{
			$im = new ilTextInputGUI($lng->txt("im_".$field), "im_".$field);
			$im->setSize(40);
			$im->setMaxLength(40);
			$this->form_gui->addItem($im);
		}

		// other information
		$sec_oi = new ilFormSectionHeaderGUI();
		$sec_oi->setTitle($this->lng->txt("user_profile_other"));
		$this->form_gui->addItem($sec_oi);

		// matriculation number
		$mr = new ilTextInputGUI($lng->txt("matriculation"), "matriculation");
		$mr->setSize(40);
		$mr->setMaxLength(40);
		$mr->setRequired(isset($settings["require_matriculation"]) &&
			$settings["require_matriculation"]);
		$this->form_gui->addItem($mr);

		// client IP
		$ip = new ilTextInputGUI($lng->txt("client_ip"), "client_ip");
		$ip->setSize(40);
		$ip->setMaxLength(255);
		$ip->setInfo($this->lng->txt("current_ip")." ".$_SERVER["REMOTE_ADDR"]." <br />".
			'<small class="warning">'.$this->lng->txt("current_ip_alert")."</span>");
		$this->form_gui->addItem($ip);

		// additional user defined fields
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$user_defined_fields = ilUserDefinedFields::_getInstance();
		foreach($user_defined_fields->getDefinitions() as $field_id => $definition)
		{
			if($definition['field_type'] == UDF_TYPE_TEXT)	// text input
			{
				$udf = new ilTextInputGUI($definition['field_name'],
					"udf_".$definition['field_id']);
				$udf->setSize(40);
				$udf->setMaxLength(255);
			}
			else if($definition['field_type'] == UDF_TYPE_WYSIWYG)	// text area input
			{
				$udf = new ilTextAreaInputGUI($definition['field_name'],
					"udf_".$definition['field_id']);
				$udf->setUseRte(true);
			}
			else			// selection input
			{
				$udf = new ilSelectInputGUI($definition['field_name'],
					"udf_".$definition['field_id']);
				$udf->setOptions($user_defined_fields->fieldValuesToSelectArray(
							$definition['field_values']));
			}
			$udf->setRequired($definition['required']);
			$this->form_gui->addItem($udf);
		}

		// settings
		$sec_st = new ilFormSectionHeaderGUI();
		$sec_st->setTitle($this->lng->txt("settings"));
		$this->form_gui->addItem($sec_st);

		// role
		if ($a_mode == "create")
		{
			$role = new ilSelectInputGUI($lng->txt("default_role"),
				'default_role');
			$role->setRequired(true);
			$role->setValue($this->default_role);
			$role->setOptions($this->selectable_roles);
			$this->form_gui->addItem($role);
		}

		// language
		$lang = new ilSelectInputGUI($lng->txt("language"),
			'language');
		$languages = $this->lng->getInstalledLanguages();
		$options = array();
		foreach($languages as $l)
		{
			$options[$l] = $lng->txt("lang_".$l);
		}
		$lang->setOptions($options);
		$lang->setValue($ilSetting->get("language"));
		$this->form_gui->addItem($lang);

		// skin/style
		$sk = new ilSelectInputGUI($lng->txt("skin_style"),
			'skin_style');
		$templates = $styleDefinition->getAllTemplates();
		include("./Services/Style/classes/class.ilObjStyleSettings.php");
		$options = array();
		if (count($templates) > 0 && is_array ($templates))
		{
			foreach ($templates as $template)
			{
				$styleDef =& new ilStyleDefinition($template["id"]);
				$styleDef->startParsing();
				$styles = $styleDef->getStyles();
				foreach ($styles as $style)
				{
					if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
					{
						continue;
					}
					$options[$template["id"].":".$style["id"]] =
						$styleDef->getTemplateName()." / ".$style["name"];
				}
			}
		}
		$sk->setOptions($options);
		$sk->setValue($ilClientIniFile->readVariable("layout","skin").
			":".$ilClientIniFile->readVariable("layout","style"));

		$this->form_gui->addItem($sk);

		// hits per page
		$hpp = new ilSelectInputGUI($lng->txt("hits_per_page"),
			'hits_per_page');
		$options = array(10 => 10, 15 => 15, 20 => 20, 30 => 30, 40 => 40,
			50 => 50, 100 => 100, 9999 => $this->lng->txt("no_limit"));
		$hpp->setOptions($options);
		$hpp->setValue($ilSetting->get("hits_per_page"));
		$this->form_gui->addItem($hpp);

		// users online
		$uo = new ilSelectInputGUI($lng->txt("users_online"),
			'show_users_online');
		$options = array(
			"y" => $lng->txt("users_online_show_y"),
			"associated" => $lng->txt("users_online_show_associated"),
			"n" => $lng->txt("users_online_show_n"));
		$uo->setOptions($options);
		$uo->setValue($ilSetting->get("show_users_online"));
		$this->form_gui->addItem($uo);

		// hide online status
		$os = new ilCheckboxInputGUI($lng->txt("hide_own_online_status"), "hide_own_online_status");
		$this->form_gui->addItem($os);

		// Options
		$sec_op = new ilFormSectionHeaderGUI();
		$sec_op->setTitle($this->lng->txt("options"));
		$this->form_gui->addItem($sec_op);

		// send email
		$se = new ilCheckboxInputGUI($lng->txt('inform_user_mail'), 'send_mail');
		$se->setValue('y');
		$se->setChecked(($ilUser->getPref('send_info_mails') == 'y'));
		$this->form_gui->addItem($se);
		
		if((int)$ilSetting->get('session_reminder_enabled'))
		{
			$cb = new ilCheckboxInputGUI($this->lng->txt('session_reminder'), 'session_reminder_enabled');
			$cb->setValue(1);
			$this->form_gui->addItem($cb);
		}

		// @todo: handle all required fields

		// command buttons
		if ($a_mode == "create" || $a_mode == "save")
		{
			$this->form_gui->addCommandButton("save", $lng->txt("save"));
		}
		if ($a_mode == "edit" || $a_mode == "update")
		{
			$this->form_gui->addCommandButton("update", $lng->txt("save"));
		}
		$this->form_gui->addCommandButton("cancel", $lng->txt("cancel"));
	}

	/**
	* display user edit form
	*
	* @access	public
	*/
    function editOldObject()
    {
        global $ilias, $rbacsystem, $rbacreview, $rbacadmin, $styleDefinition, $ilUser
			,$ilSetting;

		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');


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
		$data["fields"]["im_jabber"] = $this->object->getInstantMessengerId('jabber');
		$data["fields"]["im_voip"] = $this->object->getInstantMessengerId('voip');
		$data["fields"]["matriculation"] = $this->object->getMatriculation();
		$data["fields"]["client_ip"] = $this->object->getClientIP();
		$data["fields"]["referral_comment"] = $this->object->getComment();
		$data["fields"]["owner"] = ilObjUser::_lookupLogin($this->object->getOwner());
		$data["fields"]["create_date"] = $this->object->getCreateDate();
		$data["fields"]["approve_date"] = $this->object->getApproveDate();
		$data["fields"]["agree_date"] = $this->object->getAgreeDate();
		$data["fields"]["last_login"] = $this->object->getLastLogin();
		$data["fields"]["active"] = $this->object->getActive();
		$data["fields"]["auth_mode"] = $this->object->getAuthMode();
		$data["fields"]["ext_account"] = $this->object->getExternalAccount();

		// BEGIN DiskQuota Get Picture, Owner, Last login, Approve Date and AgreeDate
		$this->tpl->setVariable("TXT_UPLOAD",$this->lng->txt("personal_picture"));
		$webspace_dir = ilUtil::getWebspaceDir("output");
		$full_img = $this->object->getPref("profile_image");
		$last_dot = strrpos($full_img, ".");
		$small_img = substr($full_img, 0, $last_dot).
				"_small".substr($full_img, $last_dot, strlen($full_img) - $last_dot);
		$image_file = $webspace_dir."/usr_images/".$small_img;
		if (@is_file($image_file))
		{
			$this->tpl->setVariable("IMG_PERSONAL", $image_file."?dummy=".rand(1,99999));
			$this->tpl->setVariable("ALT_IMG_PERSONAL",$this->lng->txt("personal_picture"));
			$this->tpl->setVariable("TXT_REMOVE_PIC", $this->lng->txt("remove_personal_picture"));
		}

		$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_FILE", $this->lng->txt("userfile"));
		$this->tpl->setVariable("USER_FILE", $this->lng->txt("user_file"));
		// END DiskQuota Get Picture, Owner, Last login, Approve Date and AgreeDate

		// BEGIN DiskQuota, Show disk space used
		// W. Randelshofer 2008-07-07: Deactivated display of disk space usage,
        // because determining the disk space usage may take several minutes.
		/*
		require_once "Modules/File/classes/class.ilObjFileAccess.php";
		require_once "Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php";
		require_once "Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php";
		require_once "Services/Mail/classes/class.ilObjMailAccess.php";
		require_once "Modules/Forum/classes/class.ilObjForumAccess.php";
		$this->tpl->setVariable('TXT_DISK_SPACE_USED',$this->lng->txt('disk_space_used'));
		$this->tpl->setVariable('DISK_SPACE_USED',
			ilObjFileAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjFileBasedLMAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjSAHSLearningModuleAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjMailAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'.
			ilObjForumAccess::_getDiskSpaceUsedBy($this->object->getId(), true).'<br>'
		);
        */
		// END DiskQuota, Show disk space used

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
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
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
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
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

		include("./Services/Style/classes/class.ilObjStyleSettings.php");
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

		// BEGIN DiskQuota Remember the state of the "send info mail" checkbox
		$sendInfoMail = $ilUser->getPref('send_info_mails') == 'y';
		if ($sendInfoMail)
		// END DiskQuota Remember the state of the "send info mail" checkbox
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

		$this->__showUserDefinedFields();
	}

// BEGIN DiskQuota: Allow administrators to edit user picture
	/**
	* upload user image
	*
	* (original method by ratana ty)
	*/
	function uploadUserPictureObject()
	{
		global $ilUser, $rbacsystem;

		// User folder
		if($this->usrf_ref_id == USER_FOLDER_ID and
			!$rbacsystem->checkAccess('visible,read',$this->usrf_ref_id))
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

		$userfile_input = $this->form_gui->getItemByPostVar("userfile");

		if ($_FILES["userfile"]["tmp_name"] == "")
		{
			if ($userfile_input->getDeletionFlag())
			{
				$this->object->removeUserPicture();
			}
			return;
		}
		if ($_FILES["userfile"]["size"] == 0)
		{
			ilUtil::sendFailure($this->lng->txt("msg_no_file"));
		}
		else
		{
			$webspace_dir = ilUtil::getWebspaceDir();
			$image_dir = $webspace_dir."/usr_images";
			$store_file = "usr_".$this->object->getId()."."."jpg";

			// store filename
			$this->object->setPref("profile_image", $store_file);
			$this->object->update();

			// move uploaded file
			$uploaded_file = $image_dir."/upload_".$this->object->getId()."pic";
			if (!ilUtil::moveUploadedFile($_FILES["userfile"]["tmp_name"], $_FILES["userfile"]["name"],
				$uploaded_file, false))
			{
				ilUtil::sendFailure($this->lng->txt("upload_error", true));
				$this->ctrl->redirect($this, "showProfile");
			}
			chmod($uploaded_file, 0770);

			// take quality 100 to avoid jpeg artefacts when uploading jpeg files
			// taking only frame [0] to avoid problems with animated gifs
			$show_file  = "$image_dir/usr_".$this->object->getId().".jpg";
			$thumb_file = "$image_dir/usr_".$this->object->getId()."_small.jpg";
			$xthumb_file = "$image_dir/usr_".$this->object->getId()."_xsmall.jpg";
			$xxthumb_file = "$image_dir/usr_".$this->object->getId()."_xxsmall.jpg";
			$uploaded_file = ilUtil::escapeShellArg($uploaded_file);
			$show_file = ilUtil::escapeShellArg($show_file);
			$thumb_file = ilUtil::escapeShellArg($thumb_file);
			$xthumb_file = ilUtil::escapeShellArg($xthumb_file);
			$xxthumb_file = ilUtil::escapeShellArg($xxthumb_file);
			system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 200x200 -quality 100 JPEG:$show_file");
			system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 100x100 -quality 100 JPEG:$thumb_file");
			system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 75x75 -quality 100 JPEG:$xthumb_file");
			system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 30x30 -quality 100 JPEG:$xxthumb_file");
		}
	}

	/**
	* remove user image
	*/
	function removeUserPictureObject()
	{
		$webspace_dir = ilUtil::getWebspaceDir();
		$image_dir = $webspace_dir."/usr_images";
		$file = $image_dir."/usr_".$this->object->getID()."."."jpg";
		$thumb_file = $image_dir."/usr_".$this->object->getID()."_small.jpg";
		$xthumb_file = $image_dir."/usr_".$this->object->getID()."_xsmall.jpg";
		$xxthumb_file = $image_dir."/usr_".$this->object->getID()."_xxsmall.jpg";
		$upload_file = $image_dir."/upload_".$this->object->getID();

		// remove user pref file name
		$this->object->setPref("profile_image", "");
		$this->object->update();
		ilUtil::sendSuccess($this->lng->txt("user_image_removed"));

		if (@is_file($file))
		{
			unlink($file);
		}
		if (@is_file($thumb_file))
		{
			unlink($thumb_file);
		}
		if (@is_file($xthumb_file))
		{
			unlink($xthumb_file);
		}
		if (@is_file($xxthumb_file))
		{
			unlink($xxthumb_file);
		}
		if (@is_file($upload_file))
		{
			unlink($upload_file);
		}

		$this->editObject();
	}
// END DiskQuota: Allow administrators to edit user picture

	/**
	* save user data
	* @access	public
	*/
/*
	function saveObjectOld()
	{
        global $ilias, $rbacsystem, $rbacadmin, $ilSetting;

        include_once('./Services/Authentication/classes/class.ilAuthUtils.php');

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
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
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

		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
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

		// BEGIN DiskQuota: Remember the state of the "send info mail" checkbox
		global $ilUser;
		$ilUser->setPref('send_info_mails', ($_POST["send_mail"] != "") ? 'y' : 'n');
		$ilUser->writePrefs();
		// END DiskQuota: Remember the state of the "send info mail" checkbox

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
*/
	/**
	* Does input checks and updates a user account if everything is fine.
	* @access	public
	*/
	function updateObjectOld()
	{
        global $ilias, $rbacsystem, $rbacadmin,$ilUser;

		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');

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

				ilUtil::sendFailure($this->lng->txt('time_limit_not_within_owners'));
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

		try 
		{
			$this->object->updateLogin($_POST['Fobject']['login']);
		}
		catch (ilUserException $e)
		{
			ilUtil::sendFailure($e->getMessage());
			$this->form_gui->setValuesByPost();
			return $tpl->setContent($this->form_gui->getHtml());				
		}
		
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

		// BEGIN DiskQuota: Remember the state of the "send info mail" checkbox
		global $ilUser;
		$ilUser->setPref('send_info_mails', ($_POST['send_mail'] == 'y') ? 'y' : 'n');
		$ilUser->writePrefs();
		// END DiskQuota: Remember the state of the "send info mail" checkbox

		$mail_message = $this->__sendProfileMail();
		$msg = $this->lng->txt('saved_successfully').$mail_message;

		// feedback
		ilUtil::sendSuccess($msg,true);

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
            ilUtil::sendFailure($this->lng->txt("msg_min_one_role")."<br/>".$this->lng->txt("action_aborted"),true);
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

        include_once "./Services/AccessControl/classes/class.ilObjRole.php";

		// update object data entry (to update last modification date)
		$this->object->update();

		ilUtil::sendSuccess($this->lng->txt("msg_roleassignment_changed"),true);

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
		global $rbacreview,$rbacsystem,$ilUser, $ilTabs;
		
		$ilTabs->activateTab("role_assignment");

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

		if(false)
		{
			$this->tpl->setCurrentBlock("filter");
			$this->tpl->setVariable("FILTER_TXT_FILTER",$this->lng->txt('filter'));
			$this->tpl->setVariable("SELECT_FILTER",$this->__buildFilterSelect());
			$this->tpl->setVariable("FILTER_ACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("FILTER_NAME",'roleassignment');
			$this->tpl->setVariable("FILTER_VALUE",$this->lng->txt('apply_filter'));
			$this->tpl->parseCurrentBlock();
		}

		// init table
		include_once("./Services/User/classes/class.ilRoleAssignmentTableGUI.php");
		$tab = new ilRoleAssignmentTableGUI($this, "roleassignment");

		// now get roles depending on filter settings
		$role_list = $rbacreview->getRolesByFilter($tab->filter["role_filter"],$this->object->getId());
		$assigned_roles = $rbacreview->assignedRoles($this->object->getId());

        $counter = 0;

        include_once ('./Services/AccessControl/classes/class.ilObjRole.php');
		
		$records = array();
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

            $result_set[$counter][] = $checkbox = ilUtil::formCheckBox(in_array($role["obj_id"],$assigned_roles),"role_id[]",$role["obj_id"],$disabled)."<input type=\"hidden\" name=\"role_id_ctrl[]\" value=\"".$role["obj_id"]."\"/>";
			$this->ctrl->setParameterByClass("ilobjrolegui", "ref_id", $rolf[0]);
			$this->ctrl->setParameterByClass("ilobjrolegui", "obj_id", $role["obj_id"]);
			$result_set[$counter][] = $link = "<a href=\"".$this->ctrl->getLinkTargetByClass("ilobjrolegui", "perm")."\">".ilObjRole::_getTranslation($role["title"])."</a>";
			$title = ilObjRole::_getTranslation($role["title"]);
            $result_set[$counter][] = $role["description"];

		// Add link to objector local Rores
	        if ($role["role_type"] == "local") {
        	        // Get Object to the role
                	$obj_id = ilRbacReview::getObjectOfRole($role["rol_id"]);

	                $obj_type = ilObject::_lookupType($obj_id);

        	        $ref_ids = ilObject::_getAllReferences($obj_id);

                	foreach ($ref_ids as $ref_id) {}

	                require_once("./classes/class.ilLink.php");
	
        	        $result_set[$counter][] = $context = "<a href='".ilLink::_getLink($ref_id, ilObject::_lookupType($obj_id))."' target='_top'>".$path."</a>";
	        }
        	else
			{
				$result_set[$counter][] = $path;
				$context = $path;
			}

			$records[] = array("path" => $path, "description" => $role["description"],
				"context" => $context, "checkbox" => $checkbox,
				"role" => $link, "title" => $title);
   			++$counter;
        }

		if (true)
		{
			$tab->setData($records);
			$this->tpl->setVariable("ROLES_TABLE",$tab->getHTML());
			return;
		}
		
		return $this->__showRolesTable($result_set,$role_ids);
    }

	/**
	* Apply filter
	*/
	function applyFilterObject()
	{
		include_once("./Services/User/classes/class.ilRoleAssignmentTableGUI.php");
		$table_gui = new ilRoleAssignmentTableGUI($this, "roleassignment");
		$table_gui->writeFilterToSession();        // writes filter to session
		$table_gui->resetOffset();                // sets record offest to 0 (first page)
		$this->roleassignmentObject();
	}
	
	/**
	* Reset filter
	*/
	function resetFilterObject()
	{
		include_once("./Services/User/classes/class.ilRoleAssignmentTableGUI.php");
		$table_gui = new ilRoleAssignmentTableGUI($this, "roleassignment");
		$table_gui->resetOffset();                // sets record offest to 0 (first page)
		$table_gui->resetFilter();                // clears filter
		$this->roleassignmentObject();
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
		if($_POST['passwd'] != '********')
		{
			$body .= $usr_lang->txt("reg_mail_body_text2")."\n".
				ILIAS_HTTP_PATH."/login.php?client_id=".$ilias->client_id."\n".
				$usr_lang->txt("login").": ".$this->object->getLogin()."\n".
				$usr_lang->txt("passwd").": ".$_POST['passwd']."\n\n";
		}
		$body .= ($usr_lang->txt("reg_mail_body_text3")."\n");
		$body .= $this->object->getProfileAsString($usr_lang);

		$mmail->Subject($subject);
		$mmail->Body($body);
		$mmail->Send();


		return "<br/>".$this->lng->txt("mail_sent");
	}
	
	/**
	 * Goto user profile screen
	 */
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng, $ilNavigationHistory;
		$_GET["cmd"] = "view";
		$_GET["user_id"] = (int) $a_target;
		$_GET["baseClass"] = "ilPublicUserProfileGUI";
		$_GET["cmdClass"] = "ilpublicuserprofilegui";
		include("ilias.php");
		exit;
	}

} // END class.ilObjUserGUI
?>
