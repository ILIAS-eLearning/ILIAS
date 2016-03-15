<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';

/**
* StartUp GUI class. Handles Login and Registration.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
* @ilCtrl_Calls ilStartUpGUI: ilAccountRegistrationGUI, ilPasswordAssistanceGUI, ilLoginPageGUI
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
		global $ilLog;
		
		$cmd = $this->ctrl->getCmd("processIndexPHP",array('processIndexPHP','showLogin'));
		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class)
		{
			case 'ilLoginPageGUI':
				break;

			case "ilaccountregistrationgui":
				require_once("Services/Registration/classes/class.ilAccountRegistrationGUI.php");
				return $this->ctrl->forwardCommand(new ilAccountRegistrationGUI());

			case "ilpasswordassistancegui":
				require_once("Services/Init/classes/class.ilPasswordAssistanceGUI.php");
				return $this->ctrl->forwardCommand(new ilPasswordAssistanceGUI());

			default:				
				return $this->$cmd();
		}
	}

	/**
	* jump to registration gui
	*/
	function jumpToRegistration()
	{
		$this->ctrl->setCmdClass("ilaccountregistrationgui");
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
	 *
	 * @global ilLanguage $lng
	 */
	function showLogin()
	{
		global $ilSetting, $ilAuth, $tpl, $ilias, $lng;		
														
		$status = $ilAuth->getStatus();
		if ($status == "" && isset($_GET["auth_stat"]))
		{
			$status = $_GET["auth_stat"];
		}
		
		if($ilAuth->getAuth() && !$status)
		{			
			// deprecated?
			if ($_GET["rep_ref_id"] != "")
			{
				$_GET["ref_id"] = $_GET["rep_ref_id"];
			}			
			$this->processStartingPage();
		}
		
		// if authentication of soap user failed, but email address is
		// known, show users and ask for password
		if ($status == AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL)
		{
			$this->showUserMappingSelection();
			return;
		}

		// check for session cookies enabled
		if (!isset($_COOKIE['iltest']))
		{
			if (empty($_GET['cookies']))
			{
				$additional_params = '';     
			
				if(IS_PAYMENT_ENABLED)
				{
					if((int)$_GET['forceShoppingCartRedirect'])# && (int)$_SESSION['price_id'] && (int)$_SESSION['pobject_id'])
					{
						$additional_params .= '&login_to_purchase_object=1&forceShoppingCartRedirect=1';
					}
				}
				
				ilUtil::setCookie("iltest","cookie",false);
				ilUtil::redirect("login.php?target=".$_GET["target"]."&soap_pw=".$_GET["soap_pw"].
					"&ext_uid=".$_GET["ext_uid"]."&cookies=nocookies&client_id=".
					rawurlencode(CLIENT_ID)."&lang=".$lng->getLangKey().$additional_params);
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
	
		if ($ilSetting->get("shib_active") && $ilSetting->get("shib_hos_type"))
		{
			require_once "./Services/AuthShibboleth/classes/class.ilShibbolethWAYF.php";
			// Check if we user selects Home Organization
			$WAYF = new ShibWAYF();
		}

		if (isset($WAYF) && $WAYF->is_selection())
		{
			if ($WAYF->is_valid_selection())
			{
				// Set cookie
				$WAYF->setSAMLCookie();

				// Redirect
				$WAYF->redirect();
			}
		}
		
		$failure = $success = null;
	
		if(IS_PAYMENT_ENABLED)
		{
			if(isset($_GET['forceShoppingCartRedirect']) && (int)$_GET['forceShoppingCartRedirect'] == 1)
			{
				$this->ctrl->setParameter($this, 'forceShoppingCartRedirect', 1);
				ilSession::set('forceShoppingCartRedirect', 1);
			}
									
			if (isset($_GET['login_to_purchase_object']) && $_GET['login_to_purchase_object'])
			{
				$lng->loadLanguageModule('payment');
				$failure = $lng->txt("payment_login_to_buy_object");
				ilSession::set('forceShoppingCartRedirect', 1);
			}
		}

		// :TODO: handle internally?
		if (isset($_GET['reg_confirmation_msg']) && strlen(trim($_GET['reg_confirmation_msg'])))
		{
			$lng->loadLanguageModule('registration');
			if($_GET['reg_confirmation_msg'] == 'reg_account_confirmation_successful')
			{
			    $success = $lng->txt(trim($_GET['reg_confirmation_msg']));
			}
			else
			{
				$failure = $lng->txt(trim($_GET['reg_confirmation_msg']));
			}
		}
		else if(isset($_GET['reached_session_limit']) && $_GET['reached_session_limit'])
		{
			$failure = $lng->txt("reached_session_limit");
		}
		else if(isset($_GET['accdel']) && $_GET['accdel'])
		{
			$lng->loadLanguageModule('user');
			$failure = $lng->txt("user_account_deleted_confirmation");
		}
			
		if (!empty($status))
		{					
			switch ($status)
			{
				case AUTH_IDLED:
					// lang variable err_idled not existing
					// $tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_idled"));
					// fallthrough
				
				case AUTH_EXPIRED:
					$failure = $lng->txt("err_session_expired");
					break;

				case AUTH_CAS_NO_ILIAS_USER:
					$failure = $lng->txt("err_auth_cas_no_ilias_user");
					break;

				case AUTH_SOAP_NO_ILIAS_USER:
					$failure = $lng->txt("err_auth_soap_no_ilias_user");
					break;

				case AUTH_LDAP_NO_ILIAS_USER:
					$failure = $lng->txt("err_auth_ldap_no_ilias_user");
					break;
				
				case AUTH_RADIUS_NO_ILIAS_USER:
					$failure = $lng->txt("err_auth_radius_no_ilias_user");
					break;
					
				case AUTH_MODE_INACTIVE:
					$failure = $lng->txt("err_auth_mode_inactive");
					break;

				case AUTH_APACHE_FAILED:
					$failure = $lng->txt("err_auth_apache_failed");
					break;

				case AUTH_CAPTCHA_INVALID:
					$lng->loadLanguageModule('cptch');
					ilSession::setClosingContext(ilSession::SESSION_CLOSE_CAPTCHA);
					$ilAuth->logout();
					session_destroy();
					$failure = $lng->txt("cptch_wrong_input");
					break;
				
				// special cases: extended user validation failed
				// ilAuth was successful, so we have to logout here
				
				case AUTH_USER_WRONG_IP:				
					ilSession::setClosingContext(ilSession::SESSION_CLOSE_IP);
					$ilAuth->logout();
					session_destroy();

					$failure = sprintf($lng->txt('wrong_ip_detected'), $_SERVER['REMOTE_ADDR']);
					break;

				case AUTH_USER_SIMULTANEOUS_LOGIN:
					ilSession::setClosingContext(ilSession::SESSION_CLOSE_SIMUL);
					$ilAuth->logout();
					session_destroy();

					$failure = $lng->txt("simultaneous_login_detected");
					break;

				case AUTH_USER_TIME_LIMIT_EXCEEDED:
					ilSession::setClosingContext(ilSession::SESSION_CLOSE_TIME);
					$username = $ilAuth->getExceededUserName(); // #16327
					$ilAuth->logout();

					// user could reactivate by code?
					if($ilSetting->get('user_reactivate_code'))
					{				
						return $this->showCodeForm($username);
					}

					session_destroy();				

					$failure = $lng->txt("time_limit_reached");		
					break;	
					
				case AUTH_USER_INACTIVE:
					ilSession::setClosingContext(ilSession::SESSION_CLOSE_INACTIVE);
					$ilAuth->logout();
					session_destroy();
					
					$failure = $lng->txt("err_inactive");
					break;
					
				// special cases end
					
				
				case AUTH_WRONG_LOGIN:					
				default:
					$add = "";
					$auth_error = $ilias->getAuthError();
					if (is_object($auth_error))
					{
						$add = "<br>".$auth_error->getMessage();
					}
					$failure = $lng->txt("err_wrong_login").$add;
					break;								
			}			
		}
		
		if (isset($_GET['cu']) && $_GET['cu'])
		{
			$lng->loadLanguageModule("auth");
		    $success = $lng->txt("auth_account_code_used");
		}
		
		
		// --- render
		
		// Instantiate login template
		self::initStartUpTemplate("tpl.login.html");

		// we need the template for this
		if($failure)
		{
			ilUtil::sendFailure($failure);
		}
		else if($success)
		{
			ilUtil::sendSuccess($success);
		}

		$page_editor_html = $this->getLoginPageEditorHTML();
		$page_editor_html = $this->showLoginInformation($page_editor_html);
		$page_editor_html = $this->showLoginForm($page_editor_html);
		$page_editor_html = $this->showCASLoginForm($page_editor_html);
		$page_editor_html = $this->showShibbolethLoginForm($page_editor_html);
		$page_editor_html = $this->showOpenIdLoginForm($page_editor_html);
		$page_editor_html = $this->showRegistrationLinks($page_editor_html);
		$page_editor_html = $this->showTermsOfServiceLink($page_editor_html);

		$page_editor_html = $this->purgePlaceholders($page_editor_html);
		
		

		// not controlled by login page editor
		$tpl->setVariable("PAGETITLE",  "- ".$lng->txt("startpage"));
		$tpl->setVariable("ILIAS_RELEASE", $ilSetting->get("ilias_version"));
		
		$this->ctrl->setTargetScript("ilias.php");
		$tpl->setVariable("PHP_SELF", $_SERVER['PHP_SELF']);

		// browser does not accept cookies
		if (isset($_GET['cookies']) && $_GET['cookies'] == 'nocookies')
		{
			ilUtil::sendFailure($lng->txt("err_no_cookies"));
		}

		if(strlen($page_editor_html))
		{
			$tpl->setVariable('LPE',$page_editor_html);
		}

		$tpl->fillWindowTitle();
		$tpl->fillCssFiles();
		$tpl->fillJavaScriptFiles();

		$tpl->show("DEFAULT", false);
	}
	
	protected function showCodeForm($a_username = null, $a_form = null)
	{
		global $tpl, $lng;
		
		self::initStartUpTemplate("tpl.login_reactivate_code.html");

		ilUtil::sendFailure($lng->txt("time_limit_reached"));

		if(!$a_form)
		{
			$a_form = $this->initCodeForm($a_username);
		}
		
		$tpl->setVariable("FORM", $a_form->getHTML());
		$tpl->show("DEFAULT", false);
	}
	
	protected function initCodeForm($a_username)
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("auth");
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, 'showcodeform'));
		$form->setTitle($lng->txt('auth_account_code_title'));
		
		$count = new ilTextInputGUI($lng->txt('auth_account_code'), 'code');
		$count->setRequired(true);
		$count->setInfo($lng->txt('auth_account_code_info'));
		$form->addItem($count);
		
		// #11658
		$uname = new ilHiddenInputGUI("uname");
		$uname->setValue($a_username);
		$form->addItem($uname);
		
		$form->addCommandButton('processCode', $lng->txt('send'));
		
		return $form;
	}
	
	protected function processCode()
	{
		global $lng, $ilAuth, $ilCtrl;
		
		$uname = $_POST["uname"];
		
		$form = $this->initCodeForm($uname);
		if($uname && $form->checkInput())
		{
			$code = $form->getInput("code");			
						
			include_once "Services/User/classes/class.ilAccountCode.php";
			if(ilAccountCode::isUnusedCode($code))
			{
				$valid_until = ilAccountCode::getCodeValidUntil($code);
				
				if(!$user_id = ilObjUser::_lookupId($uname))
				{
					$this->showLogin();
					return false;
				}
				
				$invalid_code = false;
				$user = new ilObjUser($user_id);	
								
				if($valid_until === "0")
				{
					$user->setTimeLimitUnlimited(true);
				}
				else
				{					
					if(is_numeric($valid_until))
					{
						$valid_until = strtotime("+".$valid_until."days");							
					}
					else
					{
						$valid_until = explode("-", $valid_until);
						$valid_until = mktime(23, 59, 59, $valid_until[1], 
							$valid_until[2], $valid_until[0]);						
						if($valid_until < time())
						{						
							$invalid_code = true;
						}						
					}		
					
					if(!$invalid_code)
					{						
						$user->setTimeLimitUnlimited(false);					
						$user->setTimeLimitUntil($valid_until);		
					}
				}
				
				if(!$invalid_code)
				{
					$user->setActive(true);	
					
					ilAccountCode::useCode($code);
					
					// apply registration code role assignments
					ilAccountCode::applyRoleAssignments($user, $code);
					
					// apply registration code time limits
					ilAccountCode::applyAccessLimits($user, $code);

					$user->update();

					$ilCtrl->setParameter($this, "cu", 1);
					$ilCtrl->redirect($this, "showLogin");		
				}
			}
			
			$lng->loadLanguageModule("user");
			$field = $form->getItemByPostVar("code");
			$field->setAlert($lng->txt("user_account_code_not_valid"));						
		}
		
		$form->setValuesByPost();
		$this->showCodeForm($uname, $form);		
	}
	
	

	/**
	 * Show login form 
	 * @global ilSetting $ilSetting
	 * @param string $page_editor_html 
	 */
	protected function showLoginForm($page_editor_html)
	{
		global $ilSetting,$lng,$tpl;

		// @todo move this to auth utils.
		// login via ILIAS (this also includes radius and ldap)
		// If local authentication is enabled for shibboleth users, we
		// display the login form for ILIAS here.
		if (($ilSetting->get("auth_mode") != AUTH_SHIBBOLETH ||
			$ilSetting->get("shib_auth_allow_local")) &&
			$ilSetting->get("auth_mode") != AUTH_CAS)
		{
			include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
			$form = new ilPropertyFormGUI();
			//$form->setTableWidth('500');
			$form->setFormAction($this->ctrl->getFormAction($this,''));
			$form->setName("formlogin");
			$form->setShowTopButtons(false);
			$form->setTitle($lng->txt("login_to_ilias"));			

			// auth selection
			include_once('./Services/Authentication/classes/class.ilAuthModeDetermination.php');
			$det = ilAuthModeDetermination::_getInstance();
			if(ilAuthUtils::_hasMultipleAuthenticationMethods() and $det->isManualSelection())
			{
				$visible_auth_methods = array();
				$radg = new ilRadioGroupInputGUI($lng->txt("auth_selection"), "auth_mode");
				foreach(ilAuthUtils::_getMultipleAuthModeOptions($lng) as $key => $option)
				{
					if(isset($option['hide_in_ui']) && $option['hide_in_ui'])
					{
						continue;
					}
					
					$op1 = new ilRadioOption($option['txt'], $key);
					$radg->addOption($op1);
					if (isset($option['checked']))
					{
						$radg->setValue($key);
					}
					$visible_auth_methods[] = $op1;
				}
				
				if(count($visible_auth_methods) == 1)
				{
					$first_auth_method = current($visible_auth_methods);
					$hidden_auth_method = new ilHiddenInputGUI("auth_mode");
					$hidden_auth_method->setValue($first_auth_method->getValue());
					$form->addItem($hidden_auth_method);
				}
				else
				{
					$form->addItem($radg);
				}
			}

			$ti = new ilTextInputGUI($lng->txt("username"), "username");
			$ti->setSize(20);
			$ti->setRequired(true);
			$form->addItem($ti);

			$pi = new ilPasswordInputGUI($lng->txt("password"), "password");
			$pi->setRetype(false);
			$pi->setSize(20);
			$pi->setDisableHtmlAutoComplete(false);
			$pi->setRequired(true);
			$form->addItem($pi);
			$form->addCommandButton("showLogin", $lng->txt("log_in"));

			require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
			if(ilCaptchaUtil::isActiveForLogin())
			{
				require_once 'Services/Captcha/classes/class.ilCaptchaInputGUI.php';
				$captcha = new ilCaptchaInputGUI($lng->txt('captcha_code'), 'captcha_code');
				$captcha->setRequired(true);
				$form->addItem($captcha);
			}

			return $this->substituteLoginPageElements(
				$tpl,
				$page_editor_html,
				$form->getHTML(),
				'[list-login-form]',
				'LOGIN_FORM'
			);

		}
		return $page_editor_html;
	}

	/**
	 * Show login information
	 * @param string $page_editor_html
	 * @return string $page_editor_html
	 */
	protected function showLoginInformation($page_editor_html)
	{
		global $lng,$tpl;

		if(strlen($page_editor_html))
		{
			// page editor active return
			return $page_editor_html;
		}

		$loginSettings = new ilSetting("login_settings");
		$information = $loginSettings->get("login_message_".$lng->getLangKey());

		if(strlen(trim($information)))
		{
			$tpl->setVariable("TXT_LOGIN_INFORMATION", $information);
		}
		return $page_editor_html;
	}

	/**
	 * Show cas login
	 * @global ilSetting $ilSetting
	 * @param string $page_editor_html
	 * @return string $page_editor_html
	 */
	protected function showCASLoginForm($page_editor_html)
	{
		global $ilSetting, $lng;


		// cas login link
		if ($ilSetting->get("cas_active"))
		{
			$tpl = new ilTemplate('tpl.login_form_cas.html', true, true, 'Services/Init');
			$tpl->setVariable("TXT_CAS_LOGIN", $lng->txt("login_to_ilias_via_cas"));
			$tpl->setVariable("TXT_CAS_LOGIN_BUTTON", ilUtil::getImagePath("cas_login_button.png"));
			$tpl->setVariable("TXT_CAS_LOGIN_INSTRUCTIONS", $ilSetting->get("cas_login_instructions"));
			$this->ctrl->setParameter($this, "forceCASLogin", "1");
			$tpl->setVariable("TARGET_CAS_LOGIN",$this->ctrl->getLinkTarget($this, "showLogin"));
			$this->ctrl->setParameter($this, "forceCASLogin", "");

			return $this->substituteLoginPageElements(
				$GLOBALS['tpl'],
				$page_editor_html,
				$tpl->get(),
				'[list-cas-login-form]',
				'CAS_LOGIN_FORM'
			);
		}
		return $page_editor_html;
	}

	/**
	 * Show shibboleth login form
	 * @param string $page_editor_html
	 * @return string $page_editor_html
	 */
	protected function showShibbolethLoginForm($page_editor_html)
	{
		global $ilSetting, $lng;

		// Refactoring with ilFormPropertyGUI
		// [...]

		// shibboleth login link
		if ($ilSetting->get("shib_active")) {
			$tpl = new ilTemplate('tpl.login_form_shibboleth.html', true, true, 'Services/Init');

			$tpl->setVariable('SHIB_FORMACTION', './shib_login.php'); // Bugfix http://ilias.de/mantis/view.php?id=10662 {$tpl->setVariable('SHIB_FORMACTION', $this->ctrl->getFormAction($this));}

			if ($ilSetting->get("shib_hos_type") == 'external_wayf') {
				$tpl->setCurrentBlock("shibboleth_login");
				$tpl->setVariable("TXT_SHIB_LOGIN", $lng->txt("login_to_ilias_via_shibboleth"));
				$tpl->setVariable("IL_TARGET", $_GET["target"]);
				$tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $ilSetting->get("shib_federation_name"));
				$tpl->setVariable("TXT_SHIB_LOGIN_BUTTON", $ilSetting->get("shib_login_button"));
				$tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS", sprintf($lng->txt("shib_general_login_instructions"), $ilSetting->get("shib_federation_name")) . ' <a href="mailto:' . $ilSetting->get("admin_email") . '">ILIAS ' . $lng->txt("administrator") . '</a>.');
				$tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $ilSetting->get("shib_login_instructions"));
				$tpl->parseCurrentBlock();
			} elseif ($ilSetting->get("shib_hos_type") == 'embedded_wayf') {
				$tpl->setCurrentBlock("shibboleth_custom_login");
				$customInstructions = stripslashes($ilSetting->get("shib_login_instructions"));
				$tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $customInstructions);
				$tpl->parseCurrentBlock();
			} else {
				$tpl->setCurrentBlock("shibboleth_wayf_login");
				$tpl->setVariable("TXT_SHIB_LOGIN", $lng->txt("login_to_ilias_via_shibboleth"));
				$tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $ilSetting->get("shib_federation_name"));
				$tpl->setVariable("TXT_SELECT_HOME_ORGANIZATION", sprintf($lng->txt("shib_select_home_organization"), $ilSetting->get("shib_federation_name")));
				$tpl->setVariable("TXT_CONTINUE", $lng->txt("btn_next"));
				$tpl->setVariable("TXT_SHIB_HOME_ORGANIZATION", $lng->txt("shib_home_organization"));
				$tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS", $lng->txt("shib_general_wayf_login_instructions") . ' <a href="mailto:' . $ilSetting->get("admin_email") . '">ILIAS ' . $lng->txt("administrator") . '</a>.');
				$tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $ilSetting->get("shib_login_instructions"));

				require_once "./Services/AuthShibboleth/classes/class.ilShibbolethWAYF.php";
				$WAYF = new ShibWAYF();

				$tpl->setVariable("TXT_SHIB_INVALID_SELECTION", $WAYF->showNotice());
				$tpl->setVariable("SHIB_IDP_LIST", $WAYF->generateSelection());
				$tpl->setVariable("ILW_TARGET", $_GET["target"]);
				$tpl->parseCurrentBlock();
			}

			return $this->substituteLoginPageElements($GLOBALS['tpl'], $page_editor_html, $tpl->get(), '[list-shibboleth-login-form]', 'SHIB_LOGIN_FORM');
		}

		return $page_editor_html;
	}


	/**
	 * Substitute login page elements
	 * @param ilTemplate $tpl
	 * @param string $page_editor_html
	 * @param string $element_html
	 * @param string $placeholder
	 * @param string $fallback_tplvar
	 * return string $page_editor_html
	 */
	protected function substituteLoginPageElements($tpl, $page_editor_html, $element_html, $placeholder, $fallback_tplvar)
	{
		if(!strlen($page_editor_html))
		{
			$tpl->setVariable($fallback_tplvar,$element_html);
			return $page_editor_html;
		}
		// Try to replace placeholders
		if(!stristr($page_editor_html, $placeholder))
		{
			$tpl->setVariable($fallback_tplvar,$element_html);
			return $page_editor_html;
		}
		return str_replace($placeholder, $element_html, $page_editor_html);
	}

	/**
	 * Get HTML of ILIAS login page editor
	 * @return string html
	 */
	protected function getLoginPageEditorHTML()
	{
		global $lng, $tpl;

		include_once './Services/Authentication/classes/class.ilAuthLoginPageEditorSettings.php';
		$lpe = ilAuthLoginPageEditorSettings::getInstance();
		$active_lang = $lpe->getIliasEditorLanguage($lng->getLangKey());

		if(!$active_lang)
		{
			return '';
		}

		// if page does not exist, return nothing
		include_once './Services/COPage/classes/class.ilPageUtil.php';
		if(!ilPageUtil::_existsAndNotEmpty('auth', ilLanguage::lookupId($active_lang)))
		{
			return '';
		}

		include_once './Services/Authentication/classes/class.ilLoginPage.php';
		include_once './Services/Authentication/classes/class.ilLoginPageGUI.php';

		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",ilObjStyleSheet::getContentStylePath(0));
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();

		// get page object
		$page_gui = new ilLoginPageGUI(ilLanguage::lookupId($active_lang));

		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$page_gui->setStyleId(0, 'auth');

		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader("");
		$ret = $page_gui->showPage();

		return $ret;
	}

	/**
	 * Show registration, password forgotten, client slection links
	 * @global ilLanguage $lng
	 * @global ilSetting $ilSetting
	 * @global <type> $ilIliasIniFile
	 * @param string $page_editor_html
	 * @return string
	 */
	protected function showRegistrationLinks($page_editor_html)
	{
		global $lng, $ilSetting, $ilIliasIniFile, $ilAccess;

		$rtpl = new ilTemplate('tpl.login_registration_links.html',true,true,'Services/Init');

	   // allow new registrations?
		include_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
		if (ilRegistrationSettings::_lookupRegistrationType() != IL_REG_DISABLED)
		{
			$rtpl->setCurrentBlock("new_registration");
			$rtpl->setVariable("REGISTER", $lng->txt("registration"));
			$rtpl->setVariable("CMD_REGISTER",
				$this->ctrl->getLinkTargetByClass("ilaccountregistrationgui", ""));
			$rtpl->parseCurrentBlock();
		}
		// allow password assistance? Surpress option if Authmode is not local database
		if ($ilSetting->get("password_assistance"))
		{
			$rtpl->setCurrentBlock("password_assistance");
			$rtpl->setVariable("FORGOT_PASSWORD", $lng->txt("forgot_password"));
			$rtpl->setVariable("FORGOT_USERNAME", $lng->txt("forgot_username"));
			$rtpl->setVariable("CMD_FORGOT_PASSWORD",
				$this->ctrl->getLinkTargetByClass("ilpasswordassistancegui", ""));
			$rtpl->setVariable("CMD_FORGOT_USERNAME",
				$this->ctrl->getLinkTargetByClass("ilpasswordassistancegui", "showUsernameAssistanceForm"));
			$rtpl->setVariable("LANG_ID", $lng->getLangKey());
			$rtpl->parseCurrentBlock();
		}

		if ($ilSetting->get("pub_section") &&
			$ilAccess->checkAccessOfUser(ANONYMOUS_USER_ID, "read", "", ROOT_FOLDER_ID))
		{
			$rtpl->setCurrentBlock("homelink");
			$rtpl->setVariable("CLIENT_ID","?client_id=".$_COOKIE["ilClientId"]."&lang=".$lng->getLangKey());
			$rtpl->setVariable("TXT_HOME",$lng->txt("home"));
			$rtpl->parseCurrentBlock();
		}

		if ($ilIliasIniFile->readVariable("clients","list"))
		{
			$rtpl->setCurrentBlock("client_list");
			$rtpl->setVariable("TXT_CLIENT_LIST", $lng->txt("to_client_list"));
			$rtpl->setVariable("CMD_CLIENT_LIST",$this->ctrl->getLinkTarget($this, "showClientList"));
			$rtpl->parseCurrentBlock();
		}

		return $this->substituteLoginPageElements(
			$GLOBALS['tpl'],
			$page_editor_html,
			$rtpl->get(),
			'[list-registration-link]',
			'REG_PWD_CLIENT_LINKS'
		);
	}

	/**
	 * Show terms of service link 
	 * @global ilLanguage $lng
	 * @param string $page_editor_html 
	 */
	protected function showTermsOfServiceLink($page_editor_html)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		
		try
		{
			require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceSignableDocumentFactory.php';
			if(ilTermsOfServiceHelper::isEnabled() && ilTermsOfServiceSignableDocumentFactory::getByLanguageObject($lng))
			{
				$utpl = new ilTemplate('tpl.login_terms_of_service_link.html', true, true, 'Services/Init');
				$utpl->setVariable('TXT_TERMS_OF_SERVICE', $lng->txt('usr_agreement'));
				$utpl->setVariable('LINK_TERMS_OF_SERVICE', $this->ctrl->getLinkTarget($this, 'showTermsOfService'));
	
				return $this->substituteLoginPageElements(
					$GLOBALS['tpl'],
					$page_editor_html,
					$utpl->get(),
					'[list-user-agreement]',
					'USER_AGREEMENT'
				);
			}
		}
		catch(ilTermsOfServiceNoSignableDocumentFoundException $e)
		{
		}

		return $this->substituteLoginPageElements(
			$GLOBALS['tpl'],
			$page_editor_html,
			'',
			'[list-user-agreement]',
			'USER_AGREEMENT'
		);
	}

	/**
	 * Purge page editor html from unused placeholders
	 * @param string $page_editor_html
	 * @return string 
	 */
	protected function purgePlaceholders($page_editor_html)
	{
		return str_replace(
			array(
				'[list-language-selection] ',
				'[list-registration-link]',
				'[list-user-agreement]',
				'[list-login-form]',
				'[list-cas-login-form]',
				'[list-shibboleth-login-form]',
				'[list-openid-login-form]'
			),
			array('','','','','','',''),
			$page_editor_html
		);
	}

	/**
	 * Show account migration screen
	 * @param string $a_message
	 */
	public function showAccountMigration($a_message = '')
	{
		/**
		 * @var $tpl ilTemplate
		 * @var $lng ilLanguage
		 */
		global $tpl, $lng;
	
		$lng->loadLanguageModule('auth');		
		self::initStartUpTemplate('tpl.login_account_migration.html');
	 
	 	include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
	 	$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'migrateAccount'));
		
		$form->setTitle($lng->txt('auth_account_migration'));
		$form->addCommandButton('migrateAccount', $lng->txt('save'));
		$form->addCommandButton('showLogin', $lng->txt('cancel'));
		
		$rad = new ilRadioGroupInputGUI($lng->txt('auth_account_migration_name'),'account_migration');
		$rad->setValue(1);
		
		$keep = new ilRadioOption($lng->txt('auth_account_migration_keep'),1,$lng->txt('auth_info_migrate'));
		$user = new ilTextInputGUI($lng->txt('login'),'mig_username');
		$user->setRequired(true);
		$user->setValue(ilUtil::prepareFormOutput($_POST['mig_username']));
		$user->setSize(32);
		$user->setMaxLength(128);
		$keep->addSubItem($user);
		
		$pass = new ilPasswordInputGUI($lng->txt('password'),'mig_password');
		$pass->setRetype(false);
		$pass->setRequired(true);
		$pass->setValue(ilUtil::prepareFormOutput($_POST['mig_password']));
		$pass->setSize(12);
		$pass->setMaxLength(128);
		$keep->addSubItem($pass);
		$rad->addOption($keep);
		
		$new = new ilRadioOption($lng->txt('auth_account_migration_new'),2,$lng->txt('auth_info_add'));
		$rad->addOption($new);
		
		$form->addItem($rad);
	 	
		$tpl->setVariable('MIG_FORM',$form->getHTML());
		
		if(strlen($a_message))
		{
			ilUtil::sendFailure($a_message);
		}

		$tpl->show('DEFAULT');
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
	 	
	 	if(!isset($_POST['account_migration']))
	 	{
	 		$this->showAccountMigration($lng->txt('err_choose_migration_type'));
	 		return false;
	 	}
	 	
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

			include_once './Services/Authentication/classes/class.ilAuthFactory.php';
			include_once './Services/Database/classes/class.ilAuthContainerMDB2.php';
			
			$ilAuth = ilAuthFactory::factory(new ilAuthContainerMDB2());
			$ilAuth->start();
			if(!$ilAuth->checkAuth())
			{
				$ilAuth->logout();
				$this->showAccountMigration($lng->txt('err_wrong_login'));
				return false;
			} 

			$user = new ilObjUser($user_id);
			$user->setAuthMode(ilSession::get('tmp_auth_mode'));
			$user->setExternalAccount(ilSession::get('tmp_external_account'));
			$user->setActive(true);
			$user->update();
			
			// Assign to default role
			if(is_array(ilSession::get('tmp_roles')))
			{
				foreach(ilSession::get('tmp_roles') as $role)
				{
					$rbacadmin->assignUser((int) $role,$user->getId());
				}
			}

			// Log migration
			$ilLog->write(__METHOD__.': Migrated '.ilSession::get('tmp_external_account').' to ILIAS account '.$user->getLogin().'.');
	 	}
	 	elseif($_POST['account_migration'] == 2)
	 	{
			switch(ilSession::get('tmp_auth_mode'))
			{
				case 'apache':
					$_POST['username'] = ilSession::get('tmp_external_account');
					$_POST['password'] = ilSession::get('tmp_pass');

					include_once('Services/AuthApache/classes/class.ilAuthContainerApache.php');
					$container = new ilAuthContainerApache();
					$container->forceCreation(true);
					$ilAuth = ilAuthFactory::factory($container);
					$ilAuth->start();
					break;

				case 'ldap':
					$_POST['username'] = ilSession::get('tmp_external_account');
					$_POST['password'] = ilSession::get('tmp_pass');
					
					include_once('Services/LDAP/classes/class.ilAuthContainerLDAP.php');
					$container = new ilAuthContainerLDAP();
					$container->forceCreation(true);
					$ilAuth = ilAuthFactory::factory($container);
					$ilAuth->start();
					break;
				
				case 'radius':
					$_POST['username'] = ilSession::get('tmp_external_account');
					$_POST['password'] = ilSession::get('tmp_pass');
					
					include_once './Services/Authentication/classes/class.ilAuthFactory.php';
					include_once './Services/Radius/classes/class.ilAuthContainerRadius.php';
					
					$container = new ilAuthContainerRadius();
					$container->forceCreation(true);
					$ilAuth = ilAuthFactory::factory($container);
					$ilAuth->start();
					break;
					
				case 'openid':
					$_POST['username'] = ilSession::get('dummy');
					$_POST['password'] = ilSession::get('dummy');
					$_POST['oid_username'] = ilSession::get('tmp_oid_username');
					$_POST['oid_provider'] = ilSession::get('tmp_oid_provider');
					//ilSession::set('force_creation', true);
					
					include_once './Services/Authentication/classes/class.ilAuthFactory.php';
					include_once './Services/OpenId/classes/class.ilAuthContainerOpenId.php';
					
					$container = new ilAuthContainerOpenId();
					$container->forceCreation(true);
					ilAuthFactory::setContext(ilAuthFactory::CONTEXT_OPENID);
					include_once './Services/OpenId/classes/class.ilAuthOpenId.php';
					$ilAuth = ilAuthFactory::factory($container);

					// logout first to initiate a new login session
					$ilAuth->logout();
					ilSession::_destroy(session_id());
					ilSession::set('force_creation', true);
					$ilAuth->start();
			}
			// Redirect to acceptance
			ilUtil::redirect("ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&target=".$_GET["target"]."&cmd=getAcceptance");
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

		ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);		
		$ilAuth->logout();
		session_destroy();

		// reset cookie
		$client_id = $_COOKIE["ilClientId"];
		ilUtil::setCookie("ilClientId","");

		//instantiate logout template
		self::initStartUpTemplate("tpl.logout.html");
		
		if ($ilSetting->get("pub_section"))
		{
			$tpl->setCurrentBlock("homelink");
			$tpl->setVariable("CLIENT_ID","?client_id=".$client_id."&lang=".$lng->getLangKey());
			$tpl->setVariable("TXT_HOME",$lng->txt("home"));
			$tpl->parseCurrentBlock();
		}

		if ($ilIliasIniFile->readVariable("clients","list"))
		{
			$tpl->setCurrentBlock("client_list");
			$tpl->setVariable("TXT_CLIENT_LIST", $lng->txt("to_client_list"));
			$this->ctrl->setParameter($this, "client_id", $client_id);
			$tpl->setVariable("CMD_CLIENT_LIST",
				$this->ctrl->getLinkTarget($this, "showClientList"));
			$tpl->parseCurrentBlock();
			$this->ctrl->setParameter($this, "client_id", "");
		}

		$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("logout"));
		$tpl->setVariable("TXT_LOGOUT_TEXT", $lng->txt("logout_text"));
		$tpl->setVariable("TXT_LOGIN", $lng->txt("login_to_ilias"));
		$tpl->setVariable("CLIENT_ID","?client_id=".$client_id."&lang=".$lng->getLangKey());

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

		self::initStartUpTemplate("tpl.user_mapping_selection.html");
		$email_user = ilObjUser::_getLocalAccountsForEmail($valid["email"]);


		if ($ilAuth->getSubStatus() == AUTH_WRONG_LOGIN)
		{
			ilUtil::sendFailure($lng->txt("err_wrong_login"));
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
		global $tpl, $ilIliasIniFile, $lng;
//echo "1";
		if (!$ilIliasIniFile->readVariable("clients","list"))
		{
			$this->processIndexPHP();
			return;
		}
//echo "2";
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$tpl->setAddFooter(false); // no client yet

		// to do: get standard style
		$tpl->setVariable("PAGETITLE", $lng->txt("clientlist_clientlist"));
		$tpl->setVariable("LOCATION_STYLESHEET","./templates/default/delos.css");

		// load client list template
		self::initStartUpTemplate("tpl.client_list.html");	

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

		$hasPublicSection = false;
		foreach ($list as $key => $client)
		{
			$client->setDSN();
						
			if ($client->checkDatabaseExists(true) and $client->ini->readVariable("client","access") and $client->getSetting("setup_ok"))
			{
				$this->ctrl->setParameter($this, "client_id", $key);
				$tmp = array();
				$tmp[] = $client->getName();
				$tmp[] = "<a href=\""."login.php?cmd=force_login&client_id=".urlencode($key)."\">".$lng->txt("clientlist_login_page")."</a>";

				if($client->getSetting('pub_section'))
				{
					$hasPublicSection = true;
					$tmp[] = "<a href=\"" . "ilias.php?baseClass=ilRepositoryGUI&client_id=" . urlencode($key) . "\">".$lng->txt("clientlist_start_page")."</a>";
				}
				else
				{
					$tmp[] = '';
				}

				$data[] = $tmp;
			}
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		if($hasPublicSection)
		{
			$tbl->setTitle($lng->txt("clientlist_available_clients"));
			$tbl->setHeaderNames(array($lng->txt("clientlist_installation_name"), $lng->txt("clientlist_login"), $lng->txt("clientlist_public_access")));
			$tbl->setHeaderVars(array("name","index","login"));
			$tbl->setColumnWidth(array("50%","25%","25%"));
		}
		else
		{
			$tbl->setTitle($lng->txt("clientlist_available_clients"));
			$tbl->setHeaderNames(array($lng->txt("clientlist_installation_name"), $lng->txt("clientlist_login"), ''));
			$tbl->setHeaderVars(array("name","login",''));
			$tbl->setColumnWidth(array("70%","25%",'1px'));
		}

		// control
		$tbl->setOrderColumn($_GET["sort_by"],"name");
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);

		// content
		$tbl->setData($data);

		$tbl->disable("icon");
		$tbl->disable("numinfo");
		$tbl->disable("sort");
		$tbl->disable("footer");
		
		// render table
		$tbl->render();
		$tpl->show("DEFAULT", true, true);
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
	 * Get terms of service
	 */
	protected function getAcceptance()
	{
		$this->showTermsOfService();
	}

	/**
	 * Show terms of service
	 */
	protected function showTermsOfService()
	{
		/**
		 * @var $lng       ilLanguage
		 * @var $tpl       ilTemplate
		 * @var $ilUser    ilObjUser
		 * @var $ilSetting ilSetting
		 */
		global $lng, $tpl, $ilUser, $ilSetting;

		$back_to_login = ('getAcceptance' != $this->ctrl->getCmd());

		self::initStartUpTemplate('tpl.view_terms_of_service.html', $back_to_login, !$back_to_login);
		$tpl->setVariable('TXT_PAGEHEADLINE', $lng->txt('usr_agreement'));

		try
		{
			require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceSignableDocumentFactory.php';
			$document = ilTermsOfServiceSignableDocumentFactory::getByLanguageObject($lng);

			if('getAcceptance' == $this->ctrl->getCmd())
			{
				if(isset($_POST['status']) && 'accepted' == $_POST['status'])
				{
					require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';
					ilTermsOfServiceHelper::trackAcceptance($ilUser, $document);

					if(ilSession::get('orig_request_target'))
					{
						$target = ilSession::get('orig_request_target');
						ilSession::set('orig_request_target', '');
						ilUtil::redirect($target);
					}
					else
					{
						ilUtil::redirect('index.php?target=' . $_GET['target'] . '&client_id=' . CLIENT_ID);
					}
				}

				$tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, $this->ctrl->getCmd()));
				$tpl->setVariable('ACCEPT_CHECKBOX', ilUtil::formCheckbox(0, 'status', 'accepted'));
				$tpl->setVariable('ACCEPT_TERMS_OF_SERVICE', $lng->txt('accept_usr_agreement'));
				$tpl->setVariable('TXT_SUBMIT', $lng->txt('submit'));
			}

			$tpl->setVariable('TERMS_OF_SERVICE_CONTENT', $document->getContent());
		}
		catch(ilTermsOfServiceNoSignableDocumentFoundException $e)
		{
			include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContacts.php");
			$tpl->setVariable('TERMS_OF_SERVICE_CONTENT', sprintf($lng->txt('no_agreement_description'), 'mailto:' . ilUtil::prepareFormOutput(ilSystemSupportContacts::getMailToAddress())));
		}

		$tpl->show();
	}

	/**
	* process index.php
	*/
	function processIndexPHP()
	{
		global $ilIliasIniFile, $ilAuth, $ilSetting;

		// display client selection list if enabled
		if (!isset($_GET["client_id"]) &&
			$_GET["cmd"] == "" &&
			$ilIliasIniFile->readVariable("clients","list"))
		{
			return $this->showClientList();
		}
		
		if($ilAuth->getAuth() && $ilAuth->getStatus() == "")
		{					
			$this->processStartingPage();
		}
		
		//
		// index.php is called and public section is enabled
		//
		// && $ilAuth->status == -101 is important for soap auth (public section on + user mapping, alex)
		// $ilAuth->status -1 is given, if session ends (if public section -> jump to public section)

		if ($ilSetting->get("pub_section") && $_POST["sendLogin"] != "1"
			&& ($ilAuth->getStatus() != -101 && $_GET["soap_pw"] == ""))
		{
			ilInitialisation::goToPublicSection();
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
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		// fallback, should never happen
		if ($ilUser->getId() == ANONYMOUS_USER_ID)
		{ 
			ilInitialisation::goToPublicSection();
		}
		else
		{										
			// for password change and incomplete profile 
			// see ilPersonalDesktopGUI
			
			if(IS_PAYMENT_ENABLED)
			{                
				include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
				ilPaymentShoppingCart::_assignObjectsToUserId($ilUser->getId());

				if((int)$_GET['forceShoppingCartRedirect'])
				{
					ilUtil::redirect('ilias.php?baseClass=ilShopController&cmd=redirect&redirect_class=ilshopshoppingcartgui');
				}
			}
		
			if(!$_GET["target"])
			{										
				// Redirect here to switch back to http if desired
				include_once './Services/User/classes/class.ilUserUtil.php';						
				ilUtil::redirect(ilUserUtil::getStartingPointAsUrl());
			}
			else
			{
				// will handle shop redirects, too
				ilUtil::redirect("goto.php?target=".$_GET["target"]);
			}
		}
	}

	function _checkGoto($a_target)
	{
		global $objDefinition, $ilPluginAdmin, $ilUser;

		if (is_object($ilPluginAdmin))
		{
			// get user interface plugins
			$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");

			// search
			foreach ($pl_names as $pl)
			{
				$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
				$gui_class = $ui_plugin->getUIClassInstance();
				$resp = $gui_class->checkGotoHook($a_target);
				if ($resp["target"] !== false)
				{
					$a_target = $resp["target"];
					break;
				}
			}
		}

		if ($a_target == "")
		{
			return false;
		}

		$t_arr = explode("_", $a_target);
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

		$ret = call_user_func(array($full_class, "_checkGoto"), $a_target);
				
		// if no access and repository object => check for parent course/group
		if(!$ret &&						
			!stristr($a_target, "_wsp") && 
			$ilUser->getId() != ANONYMOUS_USER_ID && // #10637
			!$objDefinition->isAdministrationObject($type) && 
			$objDefinition->isRBACObject($type) &&
			$t_arr[1]) 
		{			
			global $tree, $rbacsystem, $ilAccess;
			
			// original type "pg" => pg_<page_id>[_<ref_id>]
			if($t_arr[0] == "pg")
			{
				if(isset($t_arr[2]))
				{
					$ref_id = $t_arr[2];
				}
				else
				{
					$lm_id = ilLMObject::_lookupContObjID($t_arr[1]);
					$ref_id = ilObject::_getAllReferences($lm_id);
					if($ref_id)
					{
						$ref_id = array_shift($ref_id);
					}
				}
			}
			else
			{
				$ref_id = $t_arr[1];		
			}
			
			include_once "Services/Membership/classes/class.ilParticipants.php";
			$block_obj = array();			
			
			// walk path to find parent container
			$path = $tree->getPathId($ref_id);
			array_pop($path);			
			foreach($path as $path_ref_id)
			{
				$redirect_infopage = false;
				$add_member_role = false;
												
				$ptype = ilObject::_lookupType($path_ref_id, true);
				$pobj_id = ilObject::_lookupObjId($path_ref_id);		
					
				// core checks: timings/object-specific
				if(!$ilAccess->doActivationCheck("read", "", $path_ref_id, $ilUser->getId(), $pobj_id, $ptype) ||
					!$ilAccess->doStatusCheck("read", "", $path_ref_id, $ilUser->getId(), $pobj_id, $ptype))
				{
					// object in path is inaccessible - aborting
					return false;
				}
				else if($ptype == "crs")
				{												
					// check if already participant
					include_once "Modules/Course/classes/class.ilCourseParticipant.php";
					$participants = new ilCourseParticipant($pobj_id, $ilUser->getId());
					if(!$participants->isAssigned())
					{					
						// subscription currently possible?
						include_once "Modules/Course/classes/class.ilObjCourse.php";				
						if(ilObjCourse::_isActivated($pobj_id) &&
							ilObjCourse::_registrationEnabled($pobj_id))
						{
							$block_obj[] = $path_ref_id;
							$add_member_role = true;
						}			
						else
						{
							$redirect_infopage = true;
						}
					}
				}
				else if($ptype == "grp")
				{					
					// check if already participant
					include_once "Modules/Group/classes/class.ilGroupParticipants.php";					
					if(!ilGroupParticipants::_isParticipant($path_ref_id, $ilUser->getId()))
					{					
						// subscription currently possible?
						include_once "Modules/Group/classes/class.ilObjGroup.php";		
						$group_obj = new ilObjGroup($path_ref_id);
						if($group_obj->isRegistrationEnabled())
						{
							$block_obj[] = $path_ref_id;
							$add_member_role = true;
						}			
						else
						{
							$redirect_infopage = true;
						}
					}
				}
				
				// add members roles for all "blocking" objects	
				if($add_member_role)
				{
					// cannot join? goto will never work, so redirect to current object
					$rbacsystem->resetPACache($ilUser->getId(), $path_ref_id);
					if(!$rbacsystem->checkAccess("join", $path_ref_id))
					{					
						$redirect_infopage = true;					
					}
					else
					{
						$rbacsystem->addTemporaryRole($ilUser->getId(), 
							ilParticipants::getDefaultMemberRole($path_ref_id));		
					}
				}
				
				// redirect to infopage of 1st blocking object in path	
				if($redirect_infopage)
				{					
					if($rbacsystem->checkAccess("visible", $path_ref_id)) 
					{										
						ilUtil::redirect("ilias.php?baseClass=ilRepositoryGUI".
							"&ref_id=".$path_ref_id."&cmd=infoScreen");		
					}
					else
					{
						return false;
					}
				}
			}	
			
			// check if access will be possible with all (possible) member roles added
			$rbacsystem->resetPACache($ilUser->getId(), $ref_id);
			if($rbacsystem->checkAccess("read", $ref_id) && sizeof($block_obj)) // #12128
			{																		
				// this won't work with lm-pages (see above)
				// include_once "Services/Link/classes/class.ilLink.php";
				// $_SESSION["pending_goto"] = ilLink::_getStaticLink($ref_id, $type);					

				// keep original target
				$_SESSION["pending_goto"] = "goto.php?target=".$a_target;

				// redirect to 1st non-member object in path						
				ilUtil::redirect("ilias.php?baseClass=ilRepositoryGUI".
					"&ref_id=".array_shift($block_obj));									 				
			}
		}		
		
		return $ret;
	}

	public function confirmRegistration()
	{
		global $lng, $ilias, $ilLog;

		ilUtil::setCookie('iltest', 'cookie', false);

		if(!isset($_GET['rh']) || !strlen(trim($_GET['rh'])))
		{
			ilUtil::redirect('./login.php?cmd=force_login&reg_confirmation_msg=reg_confirmation_hash_not_passed');
		}	

		try
		{
			require_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
			$oRegSettings = new ilRegistrationSettings();
			
			$usr_id = ilObjUser::_verifyRegistrationHash(trim($_GET['rh']));
			$oUser = ilObjectFactory::getInstanceByObjId($usr_id);
			$oUser->setActive(true);
			if($oRegSettings->passwordGenerationEnabled())
            {
            	$passwd = ilUtil::generatePasswords(1);
				$password =  $passwd[0];				
				$oUser->setPasswd($password, IL_PASSWD_PLAIN);
				$oUser->setLastPasswordChangeTS( time() );				
            }
			$oUser->update();
			
			$usr_lang = $oUser->getPref('language');
			
			if($lng->getLangKey() != $usr_lang)
			{
				$lng = new ilLanguage($usr_lang);
			}
			
			// send email
			// try individual account mail in user administration
			include_once("Services/Mail/classes/class.ilAccountMail.php");
			include_once './Services/User/classes/class.ilObjUserFolder.php';
			$amail = ilObjUserFolder::_lookupNewAccountMail($usr_lang);
			if (trim($amail["body"]) != "" && trim($amail["subject"]) != "")
			{				
	            $acc_mail = new ilAccountMail();
	            $acc_mail->setUser($oUser);
	            if($oRegSettings->passwordGenerationEnabled())
	            {
	                $acc_mail->setUserPassword($password);
	            }
	            $acc_mail->send();
			}
			else	// do default mail
			{				
				include_once 'Services/Mail/classes/class.ilMail.php';
				$mail_obj = new ilMail(ANONYMOUS_USER_ID);			
	
				// mail subject
				$subject = $lng->txt("reg_mail_subject");
	
				// mail body
				$body = $lng->txt("reg_mail_body_salutation")." ".$oUser->getFullname().",\n\n".
					$lng->txt("reg_mail_body_text1")."\n\n".
					$lng->txt("reg_mail_body_text2")."\n".
					ILIAS_HTTP_PATH."/login.php?client_id=".CLIENT_ID."\n";			
				$body .= $lng->txt("login").": ".$oUser->getLogin()."\n";
				
				if($oRegSettings->passwordGenerationEnabled())
				{
					$body.= $lng->txt("passwd").": ".$password."\n";
				}
				
				$body.= "\n";
				$body.= $lng->txt('reg_mail_body_forgot_password_info')."\n";
				
				$body.= "\n";
	
				$body .= ($lng->txt("reg_mail_body_text3")."\n\r");
				$body .= $oUser->getProfileAsString($lng);
				$mail_obj->enableSoap(false);
				$mail_obj->appendInstallationSignature(true);
				$mail_obj->sendMail($oUser->getEmail(), '', '',
					$subject,
					$body,
					array(), array('normal'));
			}	
			
			ilUtil::redirect('./login.php?cmd=force_login&reg_confirmation_msg=reg_account_confirmation_successful&lang='.$usr_lang);
		}
		catch(ilRegConfirmationLinkExpiredException $exception)
		{
			include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';			
			$soap_client = new ilSoapClient();
			$soap_client->setResponseTimeout(1);
			$soap_client->enableWSDL(true);
			$soap_client->init();
			
			$ilLog->write(__METHOD__.': Triggered soap call (background process) for deletion of inactive user objects with expired confirmation hash values (dual opt in) ...');

			$soap_client->call
			(
				'deleteExpiredDualOptInUserObjects',
				array
				(
					$_COOKIE['PHPSESSID'].'::'.$_COOKIE['ilClientId'], // session id and client id, not used for checking access -> not possible for anonymous
					$exception->getCode() // user id
				)
			);
			
			ilUtil::redirect('./login.php?cmd=force_login&reg_confirmation_msg='.$exception->getMessage()."&lang=".$usr_lang);
		}
		catch(ilRegistrationHashNotFoundException $exception)
		{
			ilUtil::redirect('./login.php?cmd=force_login&reg_confirmation_msg='.$exception->getMessage()."&lang=".$usr_lang);
		}				
	}
	
	/**
	 * Show openid login if enabled
	 * @return 
	 */
	protected function showOpenIdLoginForm($page_editor_html)
	{
		global $lng,$tpl;
		
		include_once './Services/OpenId/classes/class.ilOpenIdSettings.php';
		if(!ilOpenIdSettings::getInstance()->isActive())
		{
			return $page_editor_html;
		}
		
		$lng->loadLanguageModule('auth');
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		//$form->setTableWidth('500');
		$form->setShowTopButtons(false);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($lng->txt('login_to_ilias_via_openid'));
		
		$openid = new ilTextInputGUI($lng->txt('auth_openid_login'),'oid_username');
		$openid->setSize(18);
		$openid->setMaxLength(255);
		$openid->setRequired(true);
		$openid->setCssClass('ilOpenIDBox');
		$openid->setInfo($lng->txt('auth_openid_login_info_a'));
		$form->addItem($openid);
		
		include_once './Services/OpenId/classes/class.ilOpenIdProviders.php';
		$pro = new ilSelectInputGUI($lng->txt('auth_openid_provider'),'oid_provider');
		$pro->setOptions(ilOpenIdProviders::getInstance()->getProviderSelection());
		$pro->setValue(ilOpenIdProviders::getInstance()->getSelectedProvider());
		$form->addItem($pro);
		$form->addCommandButton("showLogin", $lng->txt("log_in"));

		return $this->substituteLoginPageElements(
			$tpl,
			$page_editor_html,
			$form->getHTML(),
			'[list-openid-login-form]',
			'OID_LOGIN_FORM'
		);
	}

	/**
	 * This method enriches the global template with some user interface elements (language selection, headlines, back buttons, ...) for public service views
	 * @param mixed   $a_tmpl The template file as a string of as an array (index 0: template file, index 1: template directory)
	 * @param bool    $a_show_back
	 * @param bool    $a_show_logout
	 */
	public static function initStartUpTemplate($a_tmpl, $a_show_back = false, $a_show_logout = false)
	{
		/**
		 * @var $tpl       ilTemplate
		 * @var $lng       ilLanguage
		 * @var $ilCtrl    ilCtrl
		 * @var $ilSetting ilSetting
		 * @var $ilAccess  ilAccessHandler
		 */
		global $tpl, $lng, $ilCtrl, $ilSetting, $ilAccess;

		// #13574 - basic.js is included with ilTemplate, so jQuery is needed, too
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery();

		// framework is needed for language selection
		include_once("./Services/UICore/classes/class.ilUIFramework.php");
		ilUIFramework::init();
		
		$tpl->addBlockfile('CONTENT', 'content', 'tpl.startup_screen.html', 'Services/Init');
		$tpl->setVariable('HEADER_ICON', ilUtil::getImagePath('HeaderIcon.svg'));

		if($a_show_back)
		{
			// #13400
			$param = 'client_id=' . $_COOKIE['ilClientId'] . '&lang=' . $lng->getLangKey();
			
			$tpl->setCurrentBlock('link_item_bl');
			$tpl->setVariable('LINK_TXT', $lng->txt('login_to_ilias'));
			$tpl->setVariable('LINK_URL', 'login.php?cmd=force_login&'.$param);
			$tpl->parseCurrentBlock();

			if($ilSetting->get('pub_section') &&
				$ilAccess->checkAccessOfUser(ANONYMOUS_USER_ID, 'read', '', ROOT_FOLDER_ID))
			{
				$tpl->setVariable('LINK_URL', 'index.php?'.$param);
				$tpl->setVariable('LINK_TXT', $lng->txt('home'));
				$tpl->parseCurrentBlock();
			}
		}
		else if($a_show_logout)
		{
			$tpl->setCurrentBlock('link_item_bl');
			$tpl->setVariable('LINK_TXT', $lng->txt('logout'));
			$tpl->setVariable('LINK_URL', ILIAS_HTTP_PATH . '/logout.php');
			$tpl->parseCurrentBlock();
		}

		if(is_array($a_tmpl))
		{
			$template_file = $a_tmpl[0];
			$template_dir  = $a_tmpl[1];
		}
		else
		{
			$template_file = $a_tmpl;
			$template_dir  = 'Services/Init';
		}

		//Header Title

		include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
		$header_top_title = ilObjSystemFolder::_getHeaderTitle();
		if (trim($header_top_title) != "" && $tpl->blockExists("header_top_title"))
		{
			$tpl->setCurrentBlock("header_top_title");
			$tpl->setVariable("TXT_HEADER_TITLE", $header_top_title);
			$tpl->parseCurrentBlock();
		}

		// language selection
		$selection = self::getLanguageSelection();
		if($selection)
		{
			$tpl->setCurrentBlock("lang_select");
			$tpl->setVariable("TXT_LANGSELECT", $lng->txt("language"));
			$tpl->setVariable("LANG_SELECT", $selection);
			$tpl->parseCurrentBlock();
		}

		$tpl->addBlockFile('STARTUP_CONTENT', 'startup_content', $template_file, $template_dir);
	}

	/**
	 * language selection list
	 * @return string ilGroupedList
	 */
	protected static function getLanguageSelection()
	{
		include_once("./Services/MainMenu/classes/class.ilMainMenuGUI.php");
		return ilMainMenuGUI::getLanguageSelection(true);
	}
}
