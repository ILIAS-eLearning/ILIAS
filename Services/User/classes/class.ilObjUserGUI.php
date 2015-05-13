<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";
include_once('./Services/Calendar/classes/class.ilDatePresentation.php');

/**
* Class ilObjUserGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjUserGUI: ilLearningProgressGUI, ilObjectOwnershipManagementGUI
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
		$this->ctrl->saveParameter($this, array('obj_id', 'letter'));
		$this->ctrl->setParameterByClass("ilobjuserfoldergui", "letter", $_GET["letter"]);
		
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
				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_USER_FOLDER,USER_FOLDER_ID,$this->object->getId());
				$this->ctrl->forwardCommand($new_gui);
				break;

			case "ilobjectownershipmanagementgui":
				include_once("Services/Object/classes/class.ilObjectOwnershipManagementGUI.php");
				$gui = new ilObjectOwnershipManagementGUI($this->object->getId());
				$this->ctrl->forwardCommand($gui);
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
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_".$this->object->getType().".svg"), $this->lng->txt("obj_" . $this->object->getType()));
		}
		else
		{
			parent::setTitleAndDescription();
		}
	}



	function cancelObject()
	{
		ilSession::clear("saved_post");

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
		global $rbacsystem, $ilHelp;

		$tabs_gui->clearTargets();
		
		$ilHelp->setScreenIdComponent("usr");

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
		if($rbacsystem->checkAccess('read',$this->ref_id) and 
			ilObjUserTracking::_enabledLearningProgress() and
			ilObjUserTracking::_enabledUserRelatedData())
		{

			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass('illearningprogressgui',''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}

		$tabs_gui->addTarget('user_ownership',
			$this->ctrl->getLinkTargetByClass('ilobjectownershipmanagementgui',''),
			'',
			'ilobjectownershipmanagementgui');
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

		if (!$rbacsystem->checkAccess('create_usr', $this->usrf_ref_id) and
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
		if (!$ilAccess->checkAccess('create_usr', "", $this->usrf_ref_id) &&
			!$ilAccess->checkAccess('cat_administrate_users', "", $this->usrf_ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
	
		$this->initCreate();
		$this->initForm("create");
		
		// Manipulate form so ignore required fields are no more required. This has to be done before ilPropertyFormGUI::checkInput() is called.
		$profileMaybeIncomplete = false;
		if($this->form_gui->getInput('ignore_rf', false))
		{			
			$profileMaybeIncomplete = $this->handleIgnoredRequiredFields();
		}

		if ($this->form_gui->checkInput())
		{
// @todo: external account; time limit check and savings

			// checks passed. save user
			$userObj = $this->loadValuesFromForm();
	
			$userObj->setPasswd($this->form_gui->getInput('passwd'),IL_PASSWD_PLAIN);
			$userObj->setTitle($userObj->getFullname());
			$userObj->setDescription($userObj->getEmail());

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
			// this ts is needed by ilSecuritySettings
			$userObj->setLastPasswordChangeTS( time() );

			//insert user data in table user_data
			$userObj->saveAsNew();

			// setup user preferences
			if($this->isSettingChangeable('language'))
			{
				$userObj->setLanguage($_POST["language"]);
			}

			// Set disk quota
			require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				// The disk quota is entered in megabytes but stored in bytes
				$userObj->setPref("disk_quota", trim($_POST["disk_quota"]) * ilFormat::_getSizeMagnitude() * ilFormat::_getSizeMagnitude());
			}
			
			if($this->isSettingChangeable('skin_style'))
			{
				//set user skin and style
				$sknst = explode(":", $_POST["skin_style"]);
	
				if ($userObj->getPref("style") != $sknst[1] ||
					$userObj->getPref("skin") != $sknst[0])
				{
					$userObj->setPref("skin", $sknst[0]);
					$userObj->setPref("style", $sknst[1]);
				}
			}
			if($this->isSettingChangeable('hits_per_page'))
			{
				$userObj->setPref("hits_per_page", $_POST["hits_per_page"]);
			}
			if($this->isSettingChangeable('show_users_online'))
			{
				$userObj->setPref("show_users_online", $_POST["show_users_online"]);
			}
			if($this->isSettingChangeable('hide_own_online_status'))
			{
				$userObj->setPref("hide_own_online_status", $_POST["hide_own_online_status"] ? 'y' : 'n');
			}
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
			
			if($this->isSettingChangeable('upload'))
			{
				$this->uploadUserPictureObject();
			}
			
			if( $profileMaybeIncomplete )
			{
				include_once 'Services/User/classes/class.ilUserProfile.php';
				if( ilUserProfile::isProfileIncomplete($this->object) )
				{
					$this->object->setProfileIncomplete( true );
					$this->object->update();
				}
			}

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
		$this->showAcceptedTermsOfService();
		$this->tpl->setContent($this->form_gui->getHTML());
	}
	
	/**
	 * @param object $a_mode [optional]
	 * @return object ilObjUser
	 */
	protected function loadValuesFromForm($a_mode = 'create')
	{
		global $ilSetting,$ilUser;
		
		switch($a_mode)
		{
			case 'create':
				$user = new ilObjUser();
				break;
			
			case 'update':
				$user = $this->object;
				break;
		}		
		
		$from = new ilDateTime($_POST['time_limit_from']['date'].' '.$_POST['time_limit_from']['time'],IL_CAL_DATETIME);
		$user->setTimeLimitFrom($from->get(IL_CAL_UNIX));
		
		$until = new ilDateTime($_POST['time_limit_until']['date'].' '.$_POST['time_limit_until']['time'],IL_CAL_DATETIME);
		$user->setTimeLimitUntil($until->get(IL_CAL_UNIX));
		
		$user->setTimeLimitUnlimited($this->form_gui->getInput('time_limit_unlimited'));
		
		if($a_mode == 'create')
		{
			$user->setTimeLimitOwner($this->usrf_ref_id);
		}
		
		// Birthday
		if($this->isSettingChangeable('birthday'))
		{
			$bd = $this->form_gui->getInput('birthday');
			if($bd['date'])
			{
				$user->setBirthday($bd['date']);
			}
			else
			{
				$user->setBirthday(null);
			}
		}
		
		// Login
		$user->setLogin($this->form_gui->getInput('login'));
		
		
		// Gender
		if($this->isSettingChangeable('gender'))
		{
			$user->setGender($this->form_gui->getInput('gender'));
		}
		
		// Title
		if($this->isSettingChangeable('title'))
		{
			$user->setUTitle($this->form_gui->getInput('title'));
		}

		// Firstname
		if($this->isSettingChangeable('firstname'))
		{
			$user->setFirstname($this->form_gui->getInput('firstname'));
		}
		// Lastname
		if($this->isSettingChangeable('lastname'))
		{
			$user->setLastname($this->form_gui->getInput('lastname'));
		}
		$user->setFullname();
		
		// Institution
		if($this->isSettingChangeable('institution'))
		{
			$user->setInstitution($this->form_gui->getInput('institution'));
		}
		
		// Department
		if($this->isSettingChangeable('department'))
		{
			$user->setDepartment($this->form_gui->getInput('department'));
		}
		// Street
		if($this->isSettingChangeable('street'))
		{
			$user->setStreet($this->form_gui->getInput('street'));
		}
		// City
		if($this->isSettingChangeable('city'))
		{
			$user->setCity($this->form_gui->getInput('city'));
		}
		// Zipcode
		if($this->isSettingChangeable('zipcode'))
		{
			$user->setZipcode($this->form_gui->getInput('zipcode'));
		}
		// Country
		if($this->isSettingChangeable('country'))
		{
			$user->setCountry($this->form_gui->getInput('country'));
		}
		// Selected Country
		if($this->isSettingChangeable('sel_country'))
		{
			$user->setSelectedCountry($this->form_gui->getInput('sel_country'));
		}
		// Phone Office
		if($this->isSettingChangeable('phone_office'))
		{
			$user->setPhoneOffice($this->form_gui->getInput('phone_office'));
		}
		// Phone Home
		if($this->isSettingChangeable('phone_home'))
		{
			$user->setPhoneHome($this->form_gui->getInput('phone_home'));
		}
		// Phone Mobile
		if($this->isSettingChangeable('phone_mobile'))
		{
			$user->setPhoneMobile($this->form_gui->getInput('phone_mobile'));
		}
		// Fax
		if($this->isSettingChangeable('fax'))
		{
			$user->setFax($this->form_gui->getInput('fax'));
		}
		// Matriculation
		if($this->isSettingChangeable('matriculation'))
		{
			$user->setMatriculation($this->form_gui->getInput('matriculation'));
		}
		// Email
		if($this->isSettingChangeable('email'))
		{
			$user->setEmail($this->form_gui->getInput('email'));
		}
		// Hobby
		if($this->isSettingChangeable('hobby'))
		{
			$user->setHobby($this->form_gui->getInput('hobby'));
		}
		// Referral Comment
		if($this->isSettingChangeable('referral_comment'))
		{
			$user->setComment($this->form_gui->getInput('referral_comment'));
		}
		
		// interests
		$user->setGeneralInterests($this->form_gui->getInput('interests_general'));
		$user->setOfferingHelp($this->form_gui->getInput('interests_help_offered'));
		$user->setLookingForHelp($this->form_gui->getInput('interests_help_looking'));			
		
		// ClientIP
		$user->setClientIP($this->form_gui->getInput('client_ip'));
		
		if($this->isSettingChangeable('instant_messengers'))
		{
			$user->setInstantMessengerId('icq', $this->form_gui->getInput('im_icq'));
			$user->setInstantMessengerId('yahoo', $this->form_gui->getInput('im_yahoo'));
			$user->setInstantMessengerId('msn', $this->form_gui->getInput('im_msn'));
			$user->setInstantMessengerId('aim', $this->form_gui->getInput('im_aim'));
			$user->setInstantMessengerId('skype', $this->form_gui->getInput('im_skype'));
			$user->setInstantMessengerId('jabber', $this->form_gui->getInput('im_jabber'));
			$user->setInstantMessengerId('voip', $this->form_gui->getInput('im_voip'));
		}
		// Delicious
		if($this->isSettingChangeable('delicious'))
		{
			$user->setDelicious($this->form_gui->getInput('delicious'));
		}
		// Google maps
		$user->setLatitude($this->form_gui->getInput('latitude'));
		$user->setLongitude($this->form_gui->getInput('longitude'));
		$user->setLocationZoom($this->form_gui->getInput('loc_zoom'));
		
		// External account
		$user->setAuthMode($this->form_gui->getInput('auth_mode'));
		$user->setExternalAccount($this->form_gui->getInput('ext_account'));

		if((int) $user->getActive() != (int) $this->form_gui->getInput('active'))
		{
			$user->setActive($this->form_gui->getInput('active'), $ilUser->getId());
		}
		
		return $user;
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
		
		// Manipulate form so ignore required fields are no more required. This has to be done before ilPropertyFormGUI::checkInput() is called.		
		$profileMaybeIncomplete = false;
		if($this->form_gui->getInput('ignore_rf', false))
		{			
			$profileMaybeIncomplete = $this->handleIgnoredRequiredFields();
		}
		
		if ($this->form_gui->checkInput())
		{
			// @todo: external account; time limit
			// if not allowed or empty -> do no change password
			if (ilAuthUtils::_allowPasswordModificationByAuthMode(ilAuthUtils::_getAuthMode($_POST['auth_mode']))
				&& trim($_POST['passwd']) != "")
			{
				$this->object->setPasswd($_POST['passwd'], IL_PASSWD_PLAIN);
			}
						
			/*
			 * reset counter for failed logins
			 * if $_POST['active'] is set to 1
			 */
			if( $_POST['active'] == 1 )
			{
				ilObjUser::_resetLoginAttempts( $this->object->getId() );
			}
			
			#$this->object->assignData($_POST);
			$this->loadValuesFromForm('update');

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
			
			if($this->isSettingChangeable('language'))
			{
				$this->object->setLanguage($this->form_gui->getInput('language'));
			}

			require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				// set disk quota
				$this->object->setPref("disk_quota", $_POST["disk_quota"] * ilFormat::_getSizeMagnitude() * ilFormat::_getSizeMagnitude());
			}
			if (ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
			{
				// set personal workspace disk quota
				$this->object->setPref("wsp_disk_quota", $_POST["wsp_disk_quota"] * ilFormat::_getSizeMagnitude() * ilFormat::_getSizeMagnitude());
			}

			if($this->isSettingChangeable('skin_style'))
			{
				//set user skin and style
				$sknst = explode(":", $_POST["skin_style"]);
	
				if ($this->object->getPref("style") != $sknst[1] ||
					$this->object->getPref("skin") != $sknst[0])
				{
					$this->object->setPref("skin", $sknst[0]);
					$this->object->setPref("style", $sknst[1]);
				}
			}
			if($this->isSettingChangeable('hits_per_page'))
			{
				$this->object->setPref("hits_per_page", $_POST["hits_per_page"]);
			}
			if($this->isSettingChangeable('show_users_online'))
			{
				$this->object->setPref("show_users_online", $_POST["show_users_online"]);
			}
			if($this->isSettingChangeable('hide_own_online_status'))
			{
				$this->object->setPref("hide_own_online_status", $_POST["hide_own_online_status"] ? 'y' : 'n');
			}

			// set a timestamp for last_password_change
			// this ts is needed by ilSecuritySettings
			$this->object->setLastPasswordChangeTS( time() );
			
			global $ilSetting;
			if((int)$ilSetting->get('session_reminder_enabled'))
			{
				$this->object->setPref('session_reminder_enabled', (int)$_POST['session_reminder_enabled']);
			}

			// #10054 - profile may have been completed, check below is only for incomplete
			$this->object->setProfileIncomplete( false );
			
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
			if($this->isSettingChangeable('upload'))
			{
				$this->uploadUserPictureObject();
			}
						
			if( $profileMaybeIncomplete )
			{
				include_once 'Services/User/classes/class.ilUserProfile.php';
				if( ilUserProfile::isProfileIncomplete($this->object) )
				{
					$this->object->setProfileIncomplete( true );
					$this->object->update();
				}
			}
						
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
		require_once './Services/Utilities/classes/class.ilFormat.php';
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
		if (ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
		{
			$data["wsp_disk_quota"] = $this->object->getPersonalWorkspaceDiskQuota() / ilFormat::_getSizeMagnitude() / ilFormat::_getSizeMagnitude();
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
		$data["sel_country"] = $this->object->getSelectedCountry();
		$data["phone_office"] = $this->object->getPhoneOffice();
		$data["phone_home"] = $this->object->getPhoneHome();
		$data["phone_mobile"] = $this->object->getPhoneMobile();
		$data["fax"] = $this->object->getFax();
		$data["email"] = $this->object->getEmail();
		$data["hobby"] = $this->object->getHobby();
		$data["referral_comment"] = $this->object->getComment();
		
		// interests
		$data["interests_general"] = $this->object->getGeneralInterests();
		$data["interests_help_offered"] = $this->object->getOfferingHelp();
		$data["interests_help_looking"] = $this->object->getLookingForHelp();

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
		$data["delicious"] = $this->object->getDelicious();
		$data["client_ip"] = $this->object->getClientIP();

		// user defined fields
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$this->user_defined_fields = ilUserDefinedFields::_getInstance();
		$user_defined_data = $this->object->getUserDefinedData();
		foreach($this->user_defined_fields->getDefinitions() as $field_id => $definition)
		{
			$data["udf_".$field_id] = $user_defined_data["f_".$field_id];
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

		if($a_mode == "edit")
		{
			$id = new ilNonEditableValueGUI($lng->txt("usr_id"), "id");
			$id->setValue($this->object->getId());
			$this->form_gui->addItem($id);
		}
		
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
						ilDatePresentation::formatDate(new ilDateTime($last_history_entry[1], IL_CAL_UNIX)),
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
			$pw->setInfo(ilUtil::getPasswordRequirementsInfo());
			$this->form_gui->addItem($pw);
		}
		// @todo: invisible/hidden passwords

		// external account
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
		if(ilAuthUtils::_isExternalAccountEnabled())
		{
			$ext = new ilTextInputGUI($lng->txt("user_ext_account"), "ext_account");
			$ext->setSize(40);
			$ext->setMaxLength(250);
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
			$lng->loadLanguageModule("file");
			
			$quota_head = new ilFormSectionHeaderGUI();
			$quota_head->setTitle($lng->txt("repository_disk_quota"));
			$this->form_gui->addItem($quota_head);
			
			// disk quota
			$disk_quota = new ilTextInputGUI($lng->txt("disk_quota"), "disk_quota");
			$disk_quota->setSize(10);
			$disk_quota->setMaxLength(11);
			$disk_quota->setInfo($this->lng->txt("enter_in_mb_desc"));
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
			        require_once './Services/Utilities/classes/class.ilFormat.php';
					$disk_usage->setValue(ilFormat::formatSize($du_info['disk_usage'],'short'));
				$info = '<table class="il_user_quota_disk_usage_overview">';
					// write the count and size of each object type
					foreach ($du_info['details'] as $detail_data)
					{
						$info .= '<tr>'.
							'<td class="std">'.$detail_data['count'].'</td>'.
							'<td class="std">'.$lng->txt($detail_data['type']).'</td>'.
							'<td class="std">'.ilFormat::formatSize($detail_data['size'], 'short').'</td>'.
							'</tr>'
							;
					}
					$info .= '</table>';
					$info .= '<br>'.$this->lng->txt('last_update').': '.
						ilDatePresentation::formatDate(new ilDateTime($du_info['last_update'], IL_CAL_DATETIME));
					$disk_usage->setInfo($info);

				}
				$this->form_gui->addItem($disk_usage);

				// date when the last disk quota reminder was sent to the user
				if (true || $dq_info['last_reminder'])
				{
					$reminder = new ilNonEditableValueGUI($lng->txt("disk_quota_last_reminder_sent"), "last_reminder");
					$reminder->setValue(
						ilDatePresentation::formatDate(new ilDateTime($dq_info['last_reminder'], IL_CAL_DATETIME))
					);
					$reminder->setInfo($this->lng->txt("disk_quota_last_reminder_sent_desc"));
					$this->form_gui->addItem($reminder);
				}
			}
		}
		
		if (ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
		{
			$lng->loadLanguageModule("file");
		
			$quota_head = new ilFormSectionHeaderGUI();
			$quota_head->setTitle($lng->txt("personal_workspace_disk_quota"));
			$this->form_gui->addItem($quota_head);
			
			// personal workspace disk quota
			$wsp_disk_quota = new ilTextInputGUI($lng->txt("disk_quota"), "wsp_disk_quota");
			$wsp_disk_quota->setSize(10);
			$wsp_disk_quota->setMaxLength(11);
			$wsp_disk_quota->setInfo($this->lng->txt("enter_in_mb_desc"));
			$this->form_gui->addItem($wsp_disk_quota);
			
			if ($a_mode == "edit")
			{
				// show which disk quota is in effect, and explain why
				require_once 'Services/WebDAV/classes/class.ilDiskQuotaChecker.php';
				$dq_info = ilDiskQuotaChecker::_lookupPersonalWorkspaceDiskQuota($this->object->getId());
				if ($dq_info['user_wsp_disk_quota'] > $dq_info['role_wsp_disk_quota'])
				{
					$info_text = sprintf($lng->txt('disk_quota_is_1_instead_of_2_by_3'),
						ilFormat::formatSize($dq_info['user_wsp_disk_quota'],'short'),
						ilFormat::formatSize($dq_info['role_wsp_disk_quota'],'short'),
						$dq_info['role_title']);
				}
				else if (is_infinite($dq_info['role_wsp_disk_quota']))
				{
					$info_text = sprintf($lng->txt('disk_quota_is_unlimited_by_1'), $dq_info['role_title']);
				}
				else
				{
					$info_text = sprintf($lng->txt('disk_quota_is_1_by_2'),
						ilFormat::formatSize($dq_info['role_wsp_disk_quota'],'short'),
						$dq_info['role_title']);
				}
				$wsp_disk_quota->setInfo($this->lng->txt("enter_in_mb_desc").'<br>'.$info_text);
			}
			
			// disk usage
			include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
			$du_info = ilDiskQuotaHandler::getFilesizeByTypeAndOwner($this->object->getId());
			$disk_usage = new ilNonEditableValueGUI($lng->txt("disk_usage"), "disk_usage");
			if (!sizeof($du_info))
			{
				$disk_usage->setValue($lng->txt('unknown'));
			}
			else
			{
				require_once './Services/Utilities/classes/class.ilFormat.php';
				$disk_usage->setValue(ilFormat::formatSize(ilDiskQuotaHandler::getFilesizeByOwner($this->object->getId())));
				$info = '<table class="il_user_quota_disk_usage_overview">';
				// write the count and size of each object type
				foreach ($du_info as $detail_data)
				{
					$info .= '<tr>'.
						'<td class="std">'.$detail_data['count'].'</td>'.
						'<td class="std">'.$lng->txt("obj_".$detail_data["src_type"]).'</td>'.
						'<td class="std">'.ilFormat::formatSize($detail_data['filesize'], 'short').'</td>'.
						'</tr>'
						;
				}
				$info .= '</table>';
				$disk_usage->setInfo($info);

			}
			$this->form_gui->addItem($disk_usage);
		}
         
		// personal data
		if(
			$this->isSettingChangeable('gender') or
			$this->isSettingChangeable('firstname') or
			$this->isSettingChangeable('lastname') or
			$this->isSettingChangeable('title') or
			$this->isSettingChangeable('personal_image') or
			$this->isSettingChangeable('birhtday')
		)
		{
			$sec_pd = new ilFormSectionHeaderGUI();
			$sec_pd->setTitle($this->lng->txt("personal_data"));
			$this->form_gui->addItem($sec_pd);
		}

		// gender
		if($this->isSettingChangeable('gender'))
		{
			$gndr = new ilRadioGroupInputGUI($lng->txt("gender"), "gender");
			$gndr->setRequired(isset($settings["require_gender"]) && $settings["require_gender"]);
			$female = new ilRadioOption($lng->txt("gender_f"), "f");
			$gndr->addOption($female);
			$male = new ilRadioOption($lng->txt("gender_m"), "m");
			$gndr->addOption($male);
			$this->form_gui->addItem($gndr);
		}

		// firstname, lastname, title
		$fields = array("firstname" => true, "lastname" => true,
			"title" => isset($settings["require_title"]) && $settings["require_title"]);
		foreach($fields as $field => $req)
		{
			if($this->isSettingChangeable($field))
			{
				$inp = new ilTextInputGUI($lng->txt($field), $field);
				$inp->setSize(32);
				$inp->setMaxLength(32);
				$inp->setRequired($req);
				$this->form_gui->addItem($inp);
			}
		}

		// personal image
		if($this->isSettingChangeable('upload'))
		{
			$pi = new ilImageFileInputGUI($lng->txt("personal_picture"), "userfile");
			if ($a_mode == "edit" || $a_mode == "upload")
			{
				$pi->setImage(ilObjUser::_getPersonalPicturePath($this->object->getId(), "small", true,
					true));
			}
			$this->form_gui->addItem($pi);
		}

		if($this->isSettingChangeable('birthday'))
		{
			$birthday = new ilBirthdayInputGUI($lng->txt('birthday'), 'birthday');
			$birthday->setRequired(isset($settings["require_birthday"]) && $settings["require_birthday"]);
			$birthday->setShowEmpty(true);
			$birthday->setStartYear(1900);
			$this->form_gui->addItem($birthday);
		}


		// institution, department, street, city, zip code, country, phone office
		// phone home, phone mobile, fax, e-mail
		$fields = array(
			array("institution", 40, 80),
			array("department", 40, 80),
			array("street", 40, 40),
			array("city", 40, 40),
			array("zipcode", 10, 10),
			array("country", 40, 40),
			array("sel_country"),
			array("phone_office", 30, 30),
			array("phone_home", 30, 30),
			array("phone_mobile", 30, 30),
			array("fax", 30, 30));
			
		$counter = 0;
		foreach ($fields as $field)
		{
			if(!$counter++ and $this->isSettingChangeable($field[0]))
			{
				// contact data
				$sec_cd = new ilFormSectionHeaderGUI();
				$sec_cd->setTitle($this->lng->txt("contact_data"));
				$this->form_gui->addItem($sec_cd);
			}
			if($this->isSettingChangeable($field[0]))
			{
				if ($field[0] != "sel_country")
				{
					$inp = new ilTextInputGUI($lng->txt($field[0]), $field[0]);
					$inp->setSize($field[1]);
					$inp->setMaxLength($field[2]);
					$inp->setRequired(isset($settings["require_".$field[0]]) &&
						$settings["require_".$field[0]]);
					$this->form_gui->addItem($inp);
				}
				else
				{
					// country selection
					include_once("./Services/Form/classes/class.ilCountrySelectInputGUI.php");
					$cs = new ilCountrySelectInputGUI($lng->txt($field[0]), $field[0]);
					$cs->setRequired(isset($settings["require_".$field[0]]) &&
						$settings["require_".$field[0]]);
					$this->form_gui->addItem($cs);
				}
			}
		}

		// email
		if($this->isSettingChangeable('email'))
		{
			$em = new ilEMailInputGUI($lng->txt("email"), "email");
			$em->setRequired(isset($settings["require_email"]) &&
				$settings["require_email"]);
			$this->form_gui->addItem($em);
		}

		// interests/hobbies
		if($this->isSettingChangeable('hobby'))
		{
			$hob = new ilTextAreaInputGUI($lng->txt("hobby"), "hobby");
			$hob->setRows(3);
			$hob->setCols(40);
			$hob->setRequired(isset($settings["require_hobby"]) &&
				$settings["require_hobby"]);
			$this->form_gui->addItem($hob);
		}

		// referral comment
		if($this->isSettingChangeable('referral_comment'))
		{
			$rc = new ilTextAreaInputGUI($lng->txt("referral_comment"), "referral_comment");
			$rc->setRows(3);
			$rc->setCols(40);
			$rc->setRequired(isset($settings["require_referral_comment"]) &&
				$settings["require_referral_comment"]);
			$this->form_gui->addItem($rc);
		}
		
		
		// interests 
		
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($lng->txt("interests"));
		$this->form_gui->addItem($sh);
		
		$multi_fields = array("interests_general", "interests_help_offered", "interests_help_looking");
		foreach($multi_fields as $multi_field)
		{
			if($this->isSettingChangeable($multi_field))
			{
				// see ilUserProfile
				$ti = new ilTextInputGUI($lng->txt($multi_field), $multi_field);
				$ti->setMulti(true);				
				$ti->setMaxLength(40);
				$ti->setSize(40);
				$ti->setRequired(isset($settings["require_".$multi_field]) &&
					$settings["require_".$multi_field]);												 					
				$this->form_gui->addItem($ti);
			}
		}		
		
		
		// instant messengers
		if($this->isSettingChangeable('instant_messengers'))
		{
			$sec_im = new ilFormSectionHeaderGUI();
			$sec_im->setTitle($this->lng->txt("instant_messengers"));
			$this->form_gui->addItem($sec_im);
		}

		// icq, yahoo, msn, aim, skype
		$fields = array("icq", "yahoo", "msn", "aim", "skype", "jabber", "voip");
		foreach ($fields as $field)
		{
			if($this->isSettingChangeable('instant_messengers'))
			{
				$im = new ilTextInputGUI($lng->txt("im_".$field), "im_".$field);
				$im->setSize(40);
				$im->setMaxLength(40);
				$this->form_gui->addItem($im);
			}
		}

		// other information
		if($this->isSettingChangeable('user_profile_other'))
		{
			$sec_oi = new ilFormSectionHeaderGUI();
			$sec_oi->setTitle($this->lng->txt("user_profile_other"));
			$this->form_gui->addItem($sec_oi);
		}

		// matriculation number
		if($this->isSettingChangeable('matriculation'))
		{
			$mr = new ilTextInputGUI($lng->txt("matriculation"), "matriculation");
			$mr->setSize(40);
			$mr->setMaxLength(40);
			$mr->setRequired(isset($settings["require_matriculation"]) &&
				$settings["require_matriculation"]);
			$this->form_gui->addItem($mr);
		}

		// delicious
		if($this->isSettingChangeable('delicious'))
		{
			$mr = new ilTextInputGUI($lng->txt("delicious"), "delicious");
			$mr->setSize(40);
			$mr->setMaxLength(40);
			$mr->setRequired(isset($settings["require_delicious"]) &&
				$settings["require_delicious"]);
			$this->form_gui->addItem($mr);
		}

		// client IP
		$ip = new ilTextInputGUI($lng->txt("client_ip"), "client_ip");
		$ip->setSize(40);
		$ip->setMaxLength(255);
		$ip->setInfo($this->lng->txt("current_ip")." ".$_SERVER["REMOTE_ADDR"]." <br />".
			'<span class="warning">'.$this->lng->txt("current_ip_alert")."</span>");
		$this->form_gui->addItem($ip);

		// additional user defined fields
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$user_defined_fields = ilUserDefinedFields::_getInstance();
		
		if($this->usrf_ref_id == USER_FOLDER_ID)
		{
			$all_defs = $user_defined_fields->getDefinitions();
		}
		else
		{
			$all_defs = $user_defined_fields->getChangeableLocalUserAdministrationDefinitions();
		}
	
		foreach($all_defs as $field_id => $definition)
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
		if(
			$a_mode == 'create' or
			$this->isSettingChangeable( 'language') or
			$this->isSettingChangeable( 'skin_style') or
			$this->isSettingChangeable( 'hits_per_page') or
			$this->isSettingChangeable( 'hide_own_online_status')
		)
		{
			$sec_st = new ilFormSectionHeaderGUI();
			$sec_st->setTitle($this->lng->txt("settings"));
			$this->form_gui->addItem($sec_st);
		}

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
		if($this->isSettingChangeable('language'))
		{
			$lang = new ilSelectInputGUI($lng->txt("language"),
				'language');
			$languages = $lng->getInstalledLanguages();
			$lng->loadLanguageModule("meta");
			$options = array();
			foreach($languages as $l)
			{
				$options[$l] = $lng->txt("meta_l_".$l);
			}
			$lang->setOptions($options);
			$lang->setValue($ilSetting->get("language"));
			$this->form_gui->addItem($lang);
		}

		// skin/style
		if($this->isSettingChangeable('skin_style'))
		{
			$sk = new ilSelectInputGUI($lng->txt("skin_style"),
				'skin_style');
			$templates = $styleDefinition->getAllTemplates();

			include_once("./Services/Style/classes/class.ilObjStyleSettings.php");

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
		}

		// hits per page
		if($this->isSettingChangeable('hits_per_page'))
		{
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
		}

		// hide online status
		if($this->isSettingChangeable('hide_own_online_status'))
		{
			$os = new ilCheckboxInputGUI($lng->txt("hide_own_online_status"), "hide_own_online_status");
			$this->form_gui->addItem($os);
		}

		if((int)$ilSetting->get('session_reminder_enabled'))
		{
			$cb = new ilCheckboxInputGUI($this->lng->txt('session_reminder'), 'session_reminder_enabled');
			$cb->setValue(1);
			$this->form_gui->addItem($cb);
		}
		
		// Options
		if($this->isSettingChangeable('send_mail'))
		{
			$sec_op = new ilFormSectionHeaderGUI();
			$sec_op->setTitle($this->lng->txt("options"));
			$this->form_gui->addItem($sec_op);
		}

		// send email
		$se = new ilCheckboxInputGUI($lng->txt('inform_user_mail'), 'send_mail');
		$se->setInfo($lng->txt('inform_user_mail_info'));
		$se->setValue('y');
		$se->setChecked(($ilUser->getPref('send_info_mails') == 'y'));
		$this->form_gui->addItem($se);
		
		// ignore required fields
		$irf = new ilCheckboxInputGUI($lng->txt('ignore_required_fields'), 'ignore_rf');
		$irf->setInfo($lng->txt('ignore_required_fields_info'));
		$irf->setValue(1);
		$this->form_gui->addItem($irf);

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
	 * Check if setting is visible
	 * This is the case when called from user folder.
	 * Otherwise (category local user account depend on a setting)
	 * @param array $settings
	 * @param string $a_field
	 * @return 
	 */
	protected function isSettingChangeable($a_field)
	{
		// TODO: Allow mixed field parameter to support checks against an array of field names.
		
		global $ilSetting;
		static $settings = null;
		
		
		
		if($this->usrf_ref_id == USER_FOLDER_ID)
		{
			return true;
		}
		
		if($settings == NULL)
		{
			$settings = $ilSetting->getAll();
		}
		return (bool) $settings['usr_settings_changeable_lua_'.$a_field];
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
			
			if(ilUtil::isConvertVersionAtLeast("6.3.8-3"))
			{
				ilUtil::execConvert($uploaded_file . "[0] -geometry 200x200^ -gravity center -extent 200x200 -quality 100 JPEG:".$show_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 100x100^ -gravity center -extent 100x100 -quality 100 JPEG:".$thumb_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 75x75^ -gravity center -extent 75x75 -quality 100 JPEG:".$xthumb_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 30x30^ -gravity center -extent 30x30 -quality 100 JPEG:".$xxthumb_file);
			}
			else
			{
				ilUtil::execConvert($uploaded_file . "[0] -geometry 200x200 -quality 100 JPEG:".$show_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 100x100 -quality 100 JPEG:".$thumb_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 75x75 -quality 100 JPEG:".$xthumb_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 30x30 -quality 100 JPEG:".$xxthumb_file);
			}
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

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.usr_role_assignment.html','Services/User');

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

				    $path = $tmpPath[count($tmpPath)-1]["title"];
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
			
			// protected admin role
			if($role['obj_id'] == SYSTEM_ROLE_ID && !$rbacreview->isAssigned($ilUser->getId(),SYSTEM_ROLE_ID))
			{
				include_once './Services/PrivacySecurity/classes/class.ilSecuritySettings.php';
				if(ilSecuritySettings::_getInstance()->isAdminRoleProtected())
				{
					$disabled = true;
				}
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

				$parent_node = $this->tree->getNodeData($rolf2);

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

	                require_once("./Services/Link/classes/class.ilLink.php");
	
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
			$this->ctrl->setParameterByClass("ilobjsystemfoldergui",
				"ref_id", SYSTEM_FOLDER_ID);
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass(array("iladministrationgui", "ilobjsystemfoldergui"), ""),
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
	public static function _goto($a_target)
	{
		global $ilUser, $ilCtrl;
		
		// #10888
		if($a_target == md5("usrdelown"))
		{						
			if($ilUser->getId() != ANONYMOUS_USER_ID &&
				$ilUser->hasDeletionFlag())
			{
				$ilCtrl->setTargetScript("ilias.php");
				$ilCtrl->initBaseClass("ilpersonaldesktopgui");
				$ilCtrl->redirectByClass(array("ilpersonaldesktopgui", "ilpersonalsettingsgui"), "deleteOwnAccount3");						
			}
			exit("This account is not flagged for deletion."); // #12160
		}

		if (substr($a_target, 0, 1) == "n")
		{
			$a_target = ilObjUser::_lookupId(ilUtil::stripSlashes(substr($a_target, 1)));
		}

		$_GET["cmd"] = "view";
		$_GET["user_id"] = (int) $a_target;
		$_GET["baseClass"] = "ilPublicUserProfileGUI";
		$_GET["cmdClass"] = "ilpublicuserprofilegui";
		include("ilias.php");
		exit;
	}

	/**
	 * 
	 * Handles ignored required fields by changing the required flag of form elements
	 * 
	 * @access	protected
	 * @return	boolean	A flag whether the user profile is maybe incomplete after saving the form data 
	 * 
	 */
	protected function handleIgnoredRequiredFields()
	{        
		$profileMaybeIncomplete = false;
		
		require_once 'Services/User/classes/class.ilUserProfile.php';
		
		foreach( ilUserProfile::getIgnorableRequiredSettings() as $fieldName )
		{
			$elm = $this->form_gui->getItemByPostVar($fieldName);
			
			if( !$elm ) continue;            
			
			if( $elm->getRequired() )
			{
				$profileMaybeIncomplete = true;
				
				// Flag as optional
				$elm->setRequired( false );
			}
		}
		
		include_once 'Services/User/classes/class.ilUserDefinedFields.php';
		$user_defined_fields = ilUserDefinedFields::_getInstance();
		foreach($user_defined_fields->getDefinitions() as $field_id => $definition)
		{
			$elm = $this->form_gui->getItemByPostVar('udf_'.$definition['field_id']);
			
			if( !$elm ) continue;            
			
			if( $elm->getRequired() && $definition['changeable'] && $definition['required'] && $definition['visible'] )
			{
				$profileMaybeIncomplete = true;
				
				// Flag as optional
				$elm->setRequired( false );
			}
		}
		
		return $profileMaybeIncomplete;
	}

	/**
	 * 
	 */
	protected function showAcceptedTermsOfService()
	{
		/**
		 * @var $agree_date ilNonEditableValueGUI
		 */
		$agree_date = $this->form_gui->getItemByPostVar('agree_date');
		if($agree_date && $agree_date->getValue())
		{
			$this->lng->loadLanguageModule('tos');
			require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';
			/**
			 * @var $entity ilTermsOfServiceAcceptanceEntity
			 */
			$entity = ilTermsOfServiceHelper::getCurrentAcceptanceForUser($this->object);
			if($entity->getId())
			{
				$show_agreement_text = new ilCheckboxInputGUI($this->lng->txt('tos_show_signed_text'), 'tos_show_signed_text');

				$agreement_lang = new ilNonEditableValueGUI($this->lng->txt('language'), '');
				$agreement_lang->setValue($this->lng->txt('meta_l_' . $entity->getIso2LanguageCode()));
				$show_agreement_text->addSubItem($agreement_lang);

				require_once 'Services/TermsOfService/classes/form/class.ilTermsOfServiceSignedDocumentFormElementGUI.php';
				$agreement_document = new ilTermsOfServiceSignedDocumentFormElementGUI($this->lng->txt('tos_agreement_document'), '', $entity);
				$show_agreement_text->addSubItem($agreement_document);
				$agree_date->addSubItem($show_agreement_text);
			}
		}
		else if($agree_date)
		{
			$agree_date->setValue($this->lng->txt('tos_not_accepted_yet'));
		}
	}
} // END class.ilObjUserGUI
?>
