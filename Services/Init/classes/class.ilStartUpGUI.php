<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* StartUp GUI class. Handles Login and Registration.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
* @ilCtrl_Calls ilStartUpGUI: ilRegistrationGUI, ilPasswordAssistanceGUI
*
* @ingroup ServicesInit
*/
class ilStartUpGUI
{

	/**
	* constructor
	*/
	function ilStartUpGUI()
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;

		$ilCtrl->saveParameter($this, array("rep_ref_id", "lang", "target", "client_id"));
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd("processIndexPHP");
		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class)
		{
			case "ilregistrationgui":
				require_once("Services/Registration/classes/class.ilRegistrationGUI.php");
				return $this->ctrl->forwardCommand(new ilRegistrationGUI());
				break;

			case "ilpasswordassistancegui":
				require_once("Services/Init/classes/class.ilPasswordAssistanceGUI.php");
				return $this->ctrl->forwardCommand(new ilPasswordAssistanceGUI());
				break;

			default:
				return $this->$cmd();
				break;
		}
	}

	/**
	* jump to registration gui
	*/
	function jumpToRegistration()
	{
		$this->ctrl->setCmdClass("ilregistrationgui");
		$this->ctrl->setCmd("");
		$this->executeCommand();
	}

	/**
	* jump to password assistance
	*/
	function jumpToPasswordAssistance()
	{
		$this->ctrl->setCmdClass("ilpasswordassistancegui");
		$this->ctrl->setCmd("");
		$this->executeCommand();
	}

	/**
	* show login
	*/
	function showLogin()
	{
		global $ilSetting, $ilAuth, $ilUser, $tpl, $ilIliasIniFile, $ilias;

		// if authentication of soap user failed, but email address is
		// known, show users and ask for password
		$status = $ilAuth->getStatus();
		if ($status == "")
		{
			$status = $_GET["auth_stat"];
		}
		if ($status == AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL)
		{
			$this->showUserMappingSelection();
			return;
		}

		// login language selection is post type
		if ($_POST["lang"] != "")
		{
			$_GET["lang"] = ilUtil::stripSlashes($_POST["lang"]);
		}

		// check for session cookies enabled
		if (!isset($_COOKIE['iltest']))
		{
			if (empty($_GET['cookies']))
			{
				setcookie("iltest","cookie");
				//header('Location: '.$_SERVER['PHP_SELF']."?target=".$_GET["target"]."&soap_pw=".$_GET["soap_pw"]."&ext_uid=".$_GET["ext_uid"]."&cookies=nocookies&client_id=".$_GET['client_id']."&lang=".$_GET['lang']);
				header("Location: login.php?target=".$_GET["target"]."&soap_pw=".$_GET["soap_pw"]."&ext_uid=".$_GET["ext_uid"]."&cookies=nocookies&client_id=".rawurlencode(CLIENT_ID)."&lang=".$_GET['lang']);
			}
			else
			{
				$_COOKIE['iltest'] = "";
			}
		}
		else
		{
			unset($_GET['cookies']);
		}

		// check correct setup
		if (!$ilSetting->get("setup_ok"))
		{
			die("Setup is not completed. Please run setup routine again. (Login)");
		}

		if ($ilSetting->get("shib_active") && $ilSetting->get("shib_hos_type"))
		{
			require_once "./Services/AuthShibboleth/classes/class.ilShibbolethWAYF.php";
			// Check if we user selects Home Organization
			$WAYF = new ShibWAYF();
		}

		if (isset($WAYF) && $WAYF->isSelection())
		{
			if ($WAYF->isValidSelection())
			{
				// Set cookie
				$WAYF->setSAMLCookie();

				// Redirect
				$WAYF->redirect();
			}
		}
		elseif ($ilAuth->getAuth())
		{
			// Or we do authentication here
			// To do: check whether some $ilInit method could be used here.

			if(!$ilUser->checkTimeLimit())
			{
				$ilAuth->logout();
				session_destroy();

				// to do: get rid of this
				ilUtil::redirect('login.php?time_limit=true');
			}

			include_once './Services/Tracking/classes/class.ilOnlineTracking.php';
			ilOnlineTracking::_addUser($ilUser->getId());

			// handle chat kicking
			if ($ilSetting->get("chat_active"))
			{
				include_once "./Modules/Chat/classes/class.ilChatServerCommunicator.php";
				include_once "./Modules/Chat/classes/class.ilChatRoom.php";

				ilChatServerCommunicator::_login();
				ilChatRoom::_unkick($ilUser->getId());
			}

			// update last forum visit
			include_once './Modules/Forum/classes/class.ilObjForum.php';
			ilObjForum::_updateOldAccess($ilUser->getId());

			if ($_GET["rep_ref_id"] != "")
			{
				$_GET["ref_id"] = $_GET["rep_ref_id"];
			}
			$this->processStartingPage();
			exit;
		}

		// Instantiate login template
		// Use Shibboleth-only authentication if auth_mode is set to Shibboleth
		$tpl->addBlockFile("CONTENT", "content", "tpl.login.html");

		//language handling
		if ($_GET["lang"] == "")
		{
			$_GET["lang"] = $ilIliasIniFile->readVariable("language","default");
		}

		//instantiate language
		$lng = new ilLanguage($_GET["lang"]);

		$tpl->setVariable("TXT_OK", $lng->txt("ok"));

		$languages = $lng->getInstalledLanguages();

		foreach ($languages as $lang_key)
		{
			$tpl->setCurrentBlock("languages");
			$tpl->setVariable("LANG_KEY", $lang_key);
			$tpl->setVariable("LANG_NAME",
				ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_".$lang_key));
			$tpl->setVariable("BORDER", 0);
			$tpl->setVariable("VSPACE", 0);
			$tpl->parseCurrentBlock();
		}

		// allow new registrations?
		include_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
		if (ilRegistrationSettings::_lookupRegistrationType() != IL_REG_DISABLED)
		{
			$tpl->setCurrentBlock("new_registration");
			$tpl->setVariable("REGISTER", $lng->txt("registration"));
			$tpl->setVariable("CMD_REGISTER",
				$this->ctrl->getLinkTargetByClass("ilregistrationgui", ""));
			$tpl->parseCurrentBlock();
		}
		// allow password assistance? Surpress option if Authmode is not local database
		if ($ilSetting->get("password_assistance"))
		{
			$tpl->setCurrentBlock("password_assistance");
			$tpl->setVariable("FORGOT_PASSWORD", $lng->txt("forgot_password"));
			$tpl->setVariable("FORGOT_USERNAME", $lng->txt("forgot_username"));
			$tpl->setVariable("CMD_FORGOT_PASSWORD",
				$this->ctrl->getLinkTargetByClass("ilpasswordassistancegui", ""));
			$tpl->setVariable("CMD_FORGOT_USERNAME",
				$this->ctrl->getLinkTargetByClass("ilpasswordassistancegui", "showUsernameAssistanceForm"));
			$tpl->setVariable("LANG_ID", $_GET["lang"]);
			$tpl->parseCurrentBlock();
		}

		if ($ilSetting->get("pub_section"))
		{
			$tpl->setCurrentBlock("homelink");
			$tpl->setVariable("CLIENT_ID","?client_id=".$_COOKIE["ilClientId"]."&lang=".$_GET["lang"]);
			$tpl->setVariable("TXT_HOME",$lng->txt("home"));
			$tpl->parseCurrentBlock();
		}

		if ($ilIliasIniFile->readVariable("clients","list"))
		{
			$tpl->setCurrentBlock("client_list");
			$tpl->setVariable("TXT_CLIENT_LIST", $lng->txt("to_client_list"));
			$tpl->setVariable("CMD_CLIENT_LIST",
				$this->ctrl->getLinkTarget($this, "showClientList"));
			$tpl->parseCurrentBlock();
		}

		// shibboleth login link
		if ($ilSetting->get("shib_active"))
		{
			if($ilSetting->get("shib_hos_type") != 'external_wayf'){
				$tpl->setCurrentBlock("shibboleth_wayf_login");
				$tpl->setVariable("TXT_SHIB_LOGIN", $lng->txt("login_to_ilias_via_shibboleth"));
				$tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $ilSetting->get("shib_federation_name"));
				$tpl->setVariable("TXT_SELECT_HOME_ORGANIZATION", sprintf($lng->txt("shib_select_home_organization"), $ilSetting->get("shib_federation_name")));
				$tpl->setVariable("TXT_CONTINUE", $lng->txt("btn_next"));
				$tpl->setVariable("TXT_SHIB_HOME_ORGANIZATION", $lng->txt("shib_home_organization"));
				$tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS", $lng->txt("shib_general_wayf_login_instructions").' <a href="mailto:'.$ilias->getSetting("admin_email").'">ILIAS '. $lng->txt("administrator").'</a>.');
				$tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $ilSetting->get("shib_login_instructions"));
				$tpl->setVariable("TXT_SHIB_INVALID_SELECTION", $WAYF->showNotice());
				$tpl->setVariable("SHIB_IDP_LIST", $WAYF->generateSelection());
				$tpl->setVariable("ILW_TARGET", $_GET["target"]);

				$tpl->parseCurrentBlock();
			} else {
				$tpl->setCurrentBlock("shibboleth_login");
				$tpl->setVariable("TXT_SHIB_LOGIN", $lng->txt("login_to_ilias_via_shibboleth"));
				$tpl->setVariable("IL_TARGET", $_GET["target"]);
				$tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $ilSetting->get("shib_federation_name"));
				$tpl->setVariable("TXT_SHIB_LOGIN_BUTTON", $ilSetting->get("shib_login_button"));
					$tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS", sprintf($lng->txt("shib_general_login_instructions"),$ilSetting->get("shib_federation_name")).' <a href="mailto:'.$ilias->getSetting("admin_email").'">ILIAS '. $lng->txt("administrator").'</a>.');
				$tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $ilSetting->get("shib_login_instructions"));
				$tpl->parseCurrentBlock();
			}
		}

		// cas login link
		if ($ilSetting->get("cas_active"))
		{
			$tpl->setCurrentBlock("cas_login");
			$tpl->setVariable("TXT_CAS_LOGIN", $lng->txt("login_to_ilias_via_cas"));
			$tpl->setVariable("TXT_CAS_LOGIN_BUTTON", ilUtil::getImagePath("cas_login_button.gif"));
			$tpl->setVariable("TXT_CAS_LOGIN_INSTRUCTIONS", $ilSetting->get("cas_login_instructions"));
			$this->ctrl->setParameter($this, "forceCASLogin", "1");
			$tpl->setVariable("TARGET_CAS_LOGIN",
				$this->ctrl->getLinkTarget($this, "showLogin"));
			$this->ctrl->setParameter($this, "forceCASLogin", "");
			$tpl->parseCurrentBlock();
		}

		// Show selection of auth modes
		include_once('./Services/Authentication/classes/class.ilAuthModeDetermination.php');
		$det = ilAuthModeDetermination::_getInstance();
		if(ilAuthUtils::_hasMultipleAuthenticationMethods() and $det->isManualSelection())
		{
			foreach(ilAuthUtils::_getMultipleAuthModeOptions($lng) as $key => $option)
			{
				$tpl->setCurrentBlock('auth_mode_row');
				$tpl->setVariable('VAL_AUTH_MODE',$key);
				$tpl->setVariable('AUTH_CHECKED',isset($option['checked']) ? 'checked=checked' : '');
				$tpl->setVariable('TXT_AUTH_MODE',$option['txt']);
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setCurrentBlock('auth_selection');
			$tpl->setVariable('TXT_AUTH_MODE',$lng->txt('auth_selection'));
			$tpl->parseCurrentBlock();
		}		
		// login via ILIAS (this also includes radius and ldap)
		if ($ilSetting->get("auth_mode") != AUTH_SHIBBOLETH &&
			$ilSetting->get("auth_mode") != AUTH_CAS)
		{
			$loginSettings = new ilSetting("login_settings");
			if ($_GET["lang"] == false)
			{				
				$information = $loginSettings->get("login_message_".$lng->getDefaultLanguage());							
			}
			else
			{				
				$information = $loginSettings->get("login_message_".$_GET["lang"]);	
			}
						
			if(strlen(trim($information)))
			{
				$tpl->setVariable("TXT_LOGIN_INFORMATION", $information);
			}
			$tpl->setVariable("TXT_ILIAS_LOGIN", $lng->txt("login_to_ilias"));
			$tpl->setVariable("TXT_USERNAME", $lng->txt("username"));
			$tpl->setVariable("TXT_PASSWORD", $lng->txt("password"));
			$tpl->setVariable("USERNAME", ilUtil::prepareFormOutput($_POST["username"], true));
			$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("ILIAS_RELEASE", $ilSetting->get("ilias_version"));
		
		$this->ctrl->setTargetScript("login.php");
		$tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
//echo "-".htmlentities($this->ctrl->getFormAction($this, "showLogin"))."-";
		$tpl->setVariable("LANG_FORM_ACTION",
			$this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));
		$tpl->setVariable("LANG_ID", $_GET["lang"]);

		if ($_GET["inactive"])
		{
			$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_inactive"));
		}
		elseif ($_GET["expired"])
		{
			$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_session_expired"));
		}

		// TODO: Move this to header.inc since an expired session could not detected in login script
		$status = $ilAuth->getStatus();
		
		if ($status == "")
		{
			$status = $_GET["auth_stat"];
		}
		$auth_error = $ilias->getAuthError();

		if (!empty($status))
		{
			switch ($status)
			{
				case AUTH_EXPIRED:
					$tpl->setVariable('TXT_MSG_LOGIN_FAILED', $lng->txt("err_session_expired"));
					break;
				case AUTH_IDLED:
					// lang variable err_idled not existing
					//$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_idled"));
					break;

				case AUTH_CAS_NO_ILIAS_USER:
					$tpl->setVariable('TXT_MSG_LOGIN_FAILED',
						$lng->txt("err_auth_cas_no_ilias_user"));
					break;

				case AUTH_SOAP_NO_ILIAS_USER:
					$tpl->setVariable('TXT_MSG_LOGIN_FAILED',
					$lng->txt("err_auth_soap_no_ilias_user"));
					break;

				case AUTH_LDAP_NO_ILIAS_USER:
					$tpl->setVariable('TXT_MSG_LOGIN_FAILED',
						$lng->txt('err_auth_ldap_no_ilias_user'));
					break;
				
				case AUTH_RADIUS_NO_ILIAS_USER:
					$tpl->setVariable('TXT_MSG_LOGIN_FAILED',
						$lng->txt('err_auth_radius_no_ilias_user'));
					break;
							
					
				case AUTH_WRONG_LOGIN:
				default:
					$add = "";
					if (is_object($auth_error))
					{
						$add = "<br>".$auth_error->getMessage();
					}
					$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_wrong_login").$add);
					break;
			}
		}


		if ($_GET['time_limit'])
		{
			$tpl->setVariable("TXT_MSG_LOGIN_FAILED", $lng->txt('time_limit_reached'));
		}

		// output wrong IP message
		if($_GET['wrong_ip'])
		{
			$tpl->setVariable("TXT_MSG_LOGIN_FAILED", $lng->txt('wrong_ip_detected')." (".$_SERVER["REMOTE_ADDR"].")");
		}

		$this->ctrl->setTargetScript("ilias.php");
		$tpl->setVariable("PHP_SELF", $_SERVER['PHP_SELF']);
		$tpl->setVariable("USER_AGREEMENT", $lng->txt("usr_agreement"));
		$tpl->setVariable("LINK_USER_AGREEMENT",
			$this->ctrl->getLinkTarget($this, "showUserAgreement"));

		// browser does not accept cookies
		if ($_GET['cookies'] == 'nocookies')
		{
			$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_no_cookies"));
			$tpl->setVariable("COOKIES_HOWTO", $lng->txt("cookies_howto"));
			$tpl->setVariable("LINK_NO_COOKIES",
				$this->ctrl->getLinkTarget($this, "showNoCookiesScreen"));
		}

		$tpl->show("DEFAULT", false);
	}
	
	/**
	 * Show account migration screen
	 *
	 * @access public
	 * @param 
	 * 
	 */
	public function showAccountMigration($a_message = '')
	{
	 	global $tpl,$lng;
	 	
		$lng->loadLanguageModule('auth');
	 	$tpl->addBlockFile("CONTENT", "content", "tpl.login_account_migration.html");
	 	
	 	if(strlen($a_message))
	 	{
	 		ilUtil::sendInfo($a_message);
	 	}
		$tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this,'migrateAccount'));
		$tpl->setVariable('TXT_ACCOUNT_MIGRATION',$lng->txt('auth_account_migration'));
		$tpl->setVariable('INFO_MIGRATE',$lng->txt('auth_info_migrate'));
		$tpl->setVariable('INFO_ADD',$lng->txt('auth_info_add'));
		
		$tpl->setVariable('MIG_USER',$_POST['username']);
		$tpl->setVariable('TXT_USER',$lng->txt('login'));
		$tpl->setVariable('TXT_PASS',$lng->txt('password'));
		
		$tpl->setVariable('TXT_SUBMIT',$lng->txt('save'));
		$tpl->setVariable('TXT_CANCEL',$lng->txt('cancel'));
		
		$tpl->show('DEFAULT',false);		
	}
	
	/**
	 * migrate account
	 *
	 * @access public
	 * 
	 */
	public function migrateAccount()
	{
	 	global $lng,$ilClientIniFile,$ilLog,$rbacadmin;
	 	
	 	$lng->loadLanguageModule('auth');
	 	
	 	if($_POST['account_migration'] == 1 and (!strlen($_POST['mig_username']) or !strlen($_POST['mig_password'])))
	 	{
	 		$this->showAccountMigration($lng->txt('err_wrong_login'));
	 		return false;
	 	}
	 	
	 	if($_POST['account_migration'] == 1)
	 	{
			if(!$user_id = ilObjUser::_lookupId(ilUtil::stripSlashes($_POST['mig_username'])))
			{
		 		$this->showAccountMigration($lng->txt('err_wrong_login'));
		 		return false;
			}
			$_POST['username'] = $_POST['mig_username'];
			$_POST['password'] = $_POST['mig_password'];
			$auth_params = array(
				'dsn'		  => IL_DSN,
				'table'       => $ilClientIniFile->readVariable("auth", "table"),
				'usernamecol' => $ilClientIniFile->readVariable("auth", "usercol"),
				'passwordcol' => $ilClientIniFile->readVariable("auth", "passcol")
				);
			$ilAuth = new Auth("DB", $auth_params,"",false);
			$ilAuth->start();
			if(!$ilAuth->getAuth())
			{
		 		$ilAuth->logout();
		 		$this->showAccountMigration($lng->txt('err_wrong_login'));
 				return false;
			}

			$user = new ilObjUser($user_id);
			$user->setAuthMode($_SESSION['tmp_auth_mode']);
			$user->setExternalAccount($_SESSION['tmp_external_account']);
			$user->update();
			
			// Assign to default role
			if($_SESSION['tmp_role'])
			{
				$rbacadmin->assignUser((int) $_SESSION['tmp_role'],$user->getId());
			}

			// Log migration
			$ilLog->write(__METHOD__.': Migrated '.$_SESSION['tmp_external_account'].' to ILIAS account '.$user->getLogin().'.');
	 	}
	 	elseif($_POST['account_migration'] == 2)
	 	{
			switch($_SESSION['tmp_auth_mode'])
			{
				case 'ldap':
					$_POST['username'] = $_SESSION['tmp_external_account'];
					$_POST['password'] = $_SESSION['tmp_pass'];
					
					include_once('Services/LDAP/classes/class.ilAuthLDAP.php');
					$ilAuth = new ilAuthLDAP();
					$ilAuth->forceCreation(true);
					$ilAuth->setIdle($ilClientIniFile->readVariable("session","expire"), false);
					$ilAuth->setExpire(0);
					$ilAuth->start();
					break;
				
				case 'radius':
					$_POST['username'] = $_SESSION['tmp_external_account'];
					$_POST['password'] = $_SESSION['tmp_pass'];
					
					include_once('Services/Radius/classes/class.ilAuthRadius.php');
					$ilAuth = new ilAuthRadius();
					$ilAuth->forceCreation(true);
					$ilAuth->setIdle($ilClientIniFile->readVariable("session","expire"), false);
					$ilAuth->setExpire(0);
					$ilAuth->start();
					break;
			}
	 	}
		// show personal desktop
		ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
	}

	/**
	* show logout screen
	*/
	function showLogout()
	{
		global $tpl, $ilSetting, $ilAuth, $lng, $ilIliasIniFile;

		// LOGOUT CHAT USER
		if($ilSetting->get("chat_active"))
		{
			include_once "./Modules/Chat/classes/class.ilChatServerCommunicator.php";
			ilChatServerCommunicator::_logout();
		}

		$ilAuth->logout();
		session_destroy();

		// reset cookie
		$client_id = $_COOKIE["ilClientId"];
		setcookie("ilClientId","");
		$_COOKIE["ilClientId"] = "";

		//instantiate logout template
		$tpl->addBlockFile("CONTENT", "content", "tpl.logout.html");

		if ($ilSetting->get("pub_section"))
		{
			$tpl->setCurrentBlock("homelink");
			$tpl->setVariable("CLIENT_ID","?client_id=".$client_id."&lang=".$_GET['lang']);
			$tpl->setVariable("TXT_HOME",$lng->txt("home"));
			$tpl->parseCurrentBlock();
		}

		if ($ilIliasIniFile->readVariable("clients","list"))
		{
			$tpl->setCurrentBlock("client_list");
			$tpl->setVariable("TXT_CLIENT_LIST", $lng->txt("to_client_list"));
			$tpl->setVariable("CMD_CLIENT_LIST",
				$this->ctrl->getLinkTarget($this, "showClientList"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("logout"));
		$tpl->setVariable("TXT_LOGOUT_TEXT", $lng->txt("logout_text"));
		$tpl->setVariable("TXT_LOGIN", $lng->txt("login_to_ilias"));
		$tpl->setVariable("CLIENT_ID","?client_id=".$client_id."&lang=".$_GET['lang']);

		$tpl->show();
	}

	/**
	* Show user selection screen, if external account could not be mapped
	* to an ILIAS account, but the provided e-mail address is known.
	*/
	function showUserMappingSelection()
	{
		global $ilAuth, $tpl, $lng;

		$valid = $ilAuth->getValidationData();

		$tpl->addBlockFile("CONTENT", "content", "tpl.user_mapping_selection.html");
		$email_user = ilObjUser::_getLocalAccountsForEmail($valid["email"]);


		if ($ilAuth->sub_status == AUTH_WRONG_LOGIN)
		{
			$tpl->setCurrentBlock("msg");
			$tpl->setVariable("TXT_MSG_LOGIN_FAILED", $lng->txt("err_wrong_login"));
			$tpl->parseCurrentBlock();
		}

		include_once('./Services/User/classes/class.ilObjUser.php');
		if (count($email_user) == 1)
		{
			//$user = new ilObjUser(key($email_user));
			$tpl->setCurrentBlock("one_user");
			$tpl->setVariable("TXT_USERNAME", $lng->txt("username"));
			$tpl->setVariable("VAL_USERNAME", current($email_user));
			$tpl->setVariable("USER_ID", key($email_user));
			$tpl->parseCurrentBlock();
		}
		else
		{
			foreach($email_user as $key => $login)
			{
				$tpl->setCurrentBlock("user");
				$tpl->setVariable("USR_ID", $key);
				$tpl->setVariable("VAL_USER", $login);
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("multpiple_user");
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("content");
		$this->ctrl->setParameter($this, "ext_uid", urlencode($_GET["ext_uid"]));
		$this->ctrl->setParameter($this, "soap_pw", urlencode($_GET["soap_pw"]));
		$this->ctrl->setParameter($this, "auth_stat", $_GET["auth_stat"]);
		$tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_ILIAS_LOGIN", $lng->txt("login_to_ilias"));
		if (count($email_user) == 1)
		{
			$tpl->setVariable("TXT_EXPLANATION", $lng->txt("ums_explanation"));
			$tpl->setVariable("TXT_EXPLANATION_2", $lng->txt("ums_explanation_2"));
		}
		else
		{
			$tpl->setVariable("TXT_EXPLANATION", $lng->txt("ums_explanation_3"));
			$tpl->setVariable("TXT_EXPLANATION_2", $lng->txt("ums_explanation_4"));
		}
		$tpl->setVariable("TXT_CREATE_USER", $lng->txt("ums_create_new_account"));
		$tpl->setVariable("TXT_PASSWORD", $lng->txt("password"));
		$tpl->setVariable("PASSWORD", ilUtil::prepareFormOutput($_POST["password"]));
		$tpl->setVariable("TXT_SUBMIT", $lng->txt("login"));

		$tpl->show();
	}

	/**
	* show client list
	*/
	function showClientList()
	{
		global $tpl, $ilIliasIniFile, $ilCtrl;
//echo "1";
		if (!$ilIliasIniFile->readVariable("clients","list"))
		{
			$this->processIndexPHP();
			return;
		}
//echo "2";
		$tpl = new ilTemplate("tpl.main.html", true, true);

		// to do: get standard style
		$tpl->setVariable("PAGETITLE","Client List");
		$tpl->setVariable("LOCATION_STYLESHEET","./templates/default/delos.css");

		// load client list template
		$tpl->addBlockfile("CONTENT", "content", "tpl.client_list.html");

		// load template for table
		$tpl->addBlockfile("CLIENT_LIST", "client_list", "tpl.table.html");

		// load template for table content data
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		// load table content data
		require_once("setup/classes/class.ilClientList.php");
		require_once("setup/classes/class.ilClient.php");
		require_once("setup/classes/class.ilDBConnections.php");
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$this->db_connections = new ilDBConnections();
		$clientlist = new ilClientList($this->db_connections);
		$list = $clientlist->getClients();

		if (count($list) == 0)
		{
			header("Location: ./setup/setup.php");
			exit();
		}

		foreach ($list as $key => $client)
		{
			if ($client->checkDatabaseExists() and $client->ini->readVariable("client","access") and $client->getSetting("setup_ok"))
			{
				$this->ctrl->setParameter($this, "client_id", $key);
				//$data[] = array(
				//				$client->getName(),
				//				"<a href=\"".$ilCtrl->getLinkTarget($this, "processIndexPHP")."\">Start page</a>",
				//				"<a href=\"".$ilCtrl->getLinkTarget($this, "showLogin")."\">Login page</a>"
				//				);
				$data[] = array(
								$client->getName(),
								"<a href=\"".$ilCtrl->getLinkTarget($this, "processIndexPHP")."\">Start page</a>",
								"<a href=\""."login.php?cmd=force_login&client_id=".urlencode($key)."\">Login page</a>"
								);
			}
		}
		$this->ctrl->setParameter($this, "client_id", "");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle("Available Clients");
		$tbl->setHeaderNames(array("Installation Name","Public Access","Login"));
		$tbl->setHeaderVars(array("name","index","login"));
		$tbl->setColumnWidth(array("50%","25%","25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"],"name");
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);

		// content
		$tbl->setData($data);

		// footer
		$tbl->setFooter("tblfooter");

		// styles
		$tbl->setStyle("table","std");

		$tbl->disable("icon");
		$tbl->disable("numinfo");

		// render table
		$tbl->render();
		$tpl->show();
	}

	/**
	* show help screen, if cookies are disabled
	*
	* to do: link to online help here
	*/
	function showNoCookiesScreen()
	{
		global $tpl;

		$str = "<p style=\"margin:15px;\">
			You need to enable Session Cookies in your Browser to use ILIAS.
			<br/>
			<br/><b>Firefox</b>
			<br/>Tools -> Options -> Privacy -> Cookies
			<br/>Enable 'Allow sites to set cookies' and activate option 'Keep
			<br/>cookies' auf 'until I close Firefox'
			<br/>
			<br/><b>Mozilla/Netscape</b>
			<br/>Edit -> Preferences -> Privacy&Security -> Cookies
			<br/>Go to 'Cookie Lifetime Policy' and check option 'Accept for current
			<br/>session only'.
			<br/>
			<br/><b>Internet Explorer</b>
			<br/>Tools -> Internet Options -> Privacy -> Advanced
			<br/>- Check 'Override automatic cookie handling'
			<br/>- Check 'Always allow session cookies'
			</p>";
		$tpl->setVariable("CONTENT", $str);
		$tpl->show();
	}

	/**
	* get user agreement acceptance
	*/
	function getAcceptance()
	{
		$this->showUserAgreement();
	}

	/**
	* show user agreement
	*/
	function showUserAgreement()
	{
		global $lng, $tpl, $ilUser;

		require_once "./Services/User/classes/class.ilUserAgreement.php";

		$tpl->addBlockFile("CONTENT", "content", "tpl.view_usr_agreement.html");
		$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		ilUtil::sendInfo();
		// display infopanel if something happened
		ilUtil::infoPanel();

		$tpl->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));
		$tpl->setVariable("TXT_OK", $lng->txt("ok"));

		// language selection
		$languages = $lng->getInstalledLanguages();

		$count = (int) round(count($languages) / 2);
		$num = 1;

		foreach ($languages as $lang_key)
		{
			$tpl->setCurrentBlock("languages");
			$tpl->setVariable("LANG_VAL_CMD", $this->ctrl->getCmd());
			$tpl->setVariable("AGR_LANG_ACTION",
				$this->ctrl->getFormAction($this));
			$tpl->setVariable("LANG_NAME",
				ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_".$lang_key));
			$tpl->setVariable("LANG_ICON", $lang_key);
			$tpl->setVariable("LANG_KEY", $lang_key);
			$tpl->setVariable("BORDER", 0);
			$tpl->setVariable("VSPACE", 0);
			$tpl->parseCurrentBlock();

			$num++;
		}
		$tpl->setCurrentBlock("content");

		// display tabs
		$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("usr_agreement"));
		$tpl->setVariable("TXT_PAGETITLE", "ILIAS3 - ".$lng->txt("usr_agreement"));
		$tpl->setVariable("TXT_USR_AGREEMENT", ilUserAgreement::_getText());

		if ($this->ctrl->getCmd() == "getAcceptance")
		{
			if ($_POST["status"]=="accepted")
			{
				$ilUser->writeAccepted();
				ilUtil::redirect("index.php?target=".$_GET["target"]."&client_id=".CLIENT_ID);
			}
			$tpl->setVariable("VAL_CMD", "getAcceptance");
			$tpl->setVariable("AGR_LANG_ACTION",
				$this->ctrl->getFormAction($this));
			$tpl->setCurrentBlock("get_acceptance");
			$tpl->setVariable("FORM_ACTION",
				$this->ctrl->getFormAction($this));
			$tpl->setVariable("ACCEPT_CHECKBOX", ilUtil::formCheckbox(0, "status", "accepted"));
			$tpl->setVariable("ACCEPT_AGREEMENT", $lng->txt("accept_usr_agreement"));
			$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("back");
			$tpl->setVariable("BACK", $lng->txt("back"));
			$tpl->setVariable("LINK_BACK",
				$this->ctrl->getLinkTargetByClass("ilstartupgui", "showLogin"));
			$tpl->parseCurrentBlock();
		}

		$tpl->show();


	}

	/**
	* process index.php
	*/
	function processIndexPHP()
	{
		global $ilIliasIniFile, $ilAuth, $ilSetting, $ilInit;

		// display client selection list if enabled
		if (!isset($_GET["client_id"]) &&
			$_GET["cmd"] == "" &&
			$ilIliasIniFile->readVariable("clients","list"))
		{
			$this->showClientList();
			//include_once "./include/inc.client_list.php";
			exit();
		}

		/*
		if ($_GET["cmd"] == "login")
		{
			$rep_ref_id = $_SESSION["il_rep_ref_id"];

			$ilAuth->logout();
			session_destroy();

			// reset cookie
			$client_id = $_COOKIE["ilClientId"];
			setcookie("ilClientId","");
			$_COOKIE["ilClientId"] = "";

			$_GET["client_id"] = $client_id;
			$_GET["rep_ref_id"] = $rep_ref_id;


			ilUtil::redirect("login.php?client_id=".$client_id."&lang=".$_GET['lang'].
				"&rep_ref_id=".$rep_ref_id);
		}*/


		// if no start page was given, ILIAS defaults to the standard login page
		if ($start == "")
		{
			$start = "login.php";
		}


		//
		// index.php is called and public section is enabled
		//
		// && $ilAuth->status == -101 is important for soap auth (public section on + user mapping, alex)
		// $ilAuth->status -1 is given, if session ends (if public section -> jump to public section)
		if ($ilSetting->get("pub_section") && $_POST["sendLogin"] != "1"
			&& ($ilAuth->getStatus() != -101 && $_GET["soap_pw"] == ""))
		{
			//
			// TO DO: THE FOLLOWING BLOCK IS COPY&PASTED FROM HEADER.INC

			$_POST["username"] = "anonymous";
			$_POST["password"] = "anonymous";
			$ilAuth->start();
			if (ANONYMOUS_USER_ID == "")
			{
				die ("Public Section enabled, but no Anonymous user found.");
			}
			if (!$ilAuth->getAuth())
			{
				die("ANONYMOUS user with the object_id ".ANONYMOUS_USER_ID." not found!");
			}

			// get user id
			$ilInit->initUserAccount();
			$this->processStartingPage();
			exit;
		}
		else
		{
			// index.php is called and public section is disabled
			$this->showLogin();
		}
	}


	/**
	* open start page (personal desktop or repository)
	*
	* precondition: authentication (maybe anonymous) successfull
	*/
	function processStartingPage()
	{
		global $ilBench, $ilCtrl, $ilAccess, $lng;
//echo "here";
		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID || !empty($_GET["ref_id"]))
		{
//echo "A";
			// if anonymous and a target given...
			if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID && $_GET["target"] != "")
			{
				// target is accessible -> goto target
				if	($this->_checkGoto($_GET["target"]))
				{
//echo "B";
					ilUtil::redirect("./goto.php?target=".$_GET["target"]);
				}
				else	// target is not accessible -> login
				{
//echo "C";
					$this->showLogin();
				}
			}

			// just go to public section
			if (empty($_GET["ref_id"]))
			{
				$_GET["ref_id"] = ROOT_FOLDER_ID;
			}
			$ilCtrl->initBaseClass("");
			$ilCtrl->setCmd("frameset");
			$start_script = "repository.php";
			include($start_script);
			return true;
		}
		else
		{
			if	(!$this->_checkGoto($_GET["target"]))
			{
				// message if target given but not accessible
				if ($_GET["target"] != "")
				{
					$tarr = explode("_", $_GET["target"]);
					if ($tarr[0] != "pg" && $tarr[0] != "st" && $tarr[1] > 0)
					{
						ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
							ilObject::_lookupTitle(ilObject::_lookupObjId($tarr[1]))), true);
					}
				}

				// show personal desktop
				#$ilCtrl->initBaseClass("ilPersonalDesktopGUI");
				#$start_script = "ilias.php";
				// Redirect here to switch back to http if desired
				ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
			}
			else
			{
//echo "3";
				ilUtil::redirect("./goto.php?target=".$_GET["target"]);
			}
		}

		include($start_script);
	}

	function _checkGoto($a_target)
	{
		global $objDefinition;

		if ($a_target == "")
		{
			return false;
		}

		$t_arr = explode("_", $_GET["target"]);
		$type = $t_arr[0];

		if ($type == "git")
		{
			$type = "glo";
		}

		if ($type == "pg" | $type == "st")
		{
			$type = "lm";
		}

		$class = $objDefinition->getClassName($type);
		if ($class == "")
		{
			return false;
		}
		$location = $objDefinition->getLocation($type);
		$full_class = "ilObj".$class."Access";
		include_once($location."/class.".$full_class.".php");

		return call_user_func(array($full_class, "_checkGoto"),
			$a_target);
	}

}
?>