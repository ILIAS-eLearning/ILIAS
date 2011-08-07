<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* StartUp GUI class. Handles Login and Registration.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
* @ilCtrl_Calls ilStartUpGUI: ilAccountRegistrationGUI, ilPasswordAssistanceGUI, ilPageObjectGUI
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
		$cmd = $this->ctrl->getCmd("processIndexPHP",array('processIndexPHP','showLogin'));
		$GLOBALS['ilLog']->write(__METHOD__.' cmd = '.$cmd);
		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class)
		{
			case 'ilpageobjectgui':
				break;


			case "ilaccountregistrationgui":
				require_once("Services/Registration/classes/class.ilAccountRegistrationGUI.php");
				return $this->ctrl->forwardCommand(new ilAccountRegistrationGUI());
				break;

			case "ilpasswordassistancegui":
				require_once("Services/Init/classes/class.ilPasswordAssistanceGUI.php");
				return $this->ctrl->forwardCommand(new ilPasswordAssistanceGUI());
				break;

			default:
				$r = $this->$cmd();
				return $r;
				break;
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
		global $ilSetting, $ilAuth, $ilUser, $tpl, $ilIliasIniFile, $ilias, $lng;

		// if authentication of soap user failed, but email address is
		// known, show users and ask for password
		$status = $ilAuth->getStatus();
		if ($status == "" && isset($_GET["auth_stat"]))
		{
			$status = $_GET["auth_stat"];
		}
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
            	if((int)$_GET['forceShoppingCartRedirect'])# && (int)$_SESSION['price_id'] && (int)$_SESSION['pobject_id'])
            	{
                	$additional_params .= '&login_to_purchase_object=1&forceShoppingCartRedirect=1';
            	}
				
				ilUtil::setCookie("iltest","cookie",false);
				header("Location: login.php?target=".$_GET["target"]."&soap_pw=".$_GET["soap_pw"]."&ext_uid=".$_GET["ext_uid"]."&cookies=nocookies&client_id=".
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
				// session_destroy();

				if($ilSetting->get('user_reactivate_code'))
				{				
					return $this->showCodeForm();
				}
				
				// to do: get rid of this
				ilUtil::redirect('login.php?time_limit=true');
			}

			include_once './Services/Tracking/classes/class.ilOnlineTracking.php';
			ilOnlineTracking::_addUser($ilUser->getId());

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

		//
		// Start new implementation here
		//
		//
		//

		// Instantiate login template
		// Use Shibboleth-only authentication if auth_mode is set to Shibboleth
		$tpl->addBlockFile("CONTENT", "content", "tpl.login.html","Services/Init");

		#$this->ctrl->setTargetScript("login.php");
		if(isset($_GET['forceShoppingCartRedirect']) && (int)$_GET['forceShoppingCartRedirect'] == 1)
		{
  			$this->ctrl->setParameter($this, 'forceShoppingCartRedirect', 1);
		}

		$page_editor_html = $this->getLoginPageEditorHTML();
		$page_editor_html = $this->showLoginInformation($page_editor_html);
		$page_editor_html = $this->showLoginForm($page_editor_html);
		$page_editor_html = $this->showCASLoginForm($page_editor_html);
		$page_editor_html = $this->showShibbolethLoginForm($page_editor_html);
		$page_editor_html = $this->showOpenIdLoginForm($page_editor_html);
		$page_editor_html = $this->showLanguageSelection($page_editor_html);
		$page_editor_html = $this->showRegistrationLinks($page_editor_html);
		$page_editor_html = $this->showUserAgreementLink($page_editor_html);

		$page_editor_html = $this->purgePlaceholders($page_editor_html);

		// not controlled by login page editor

		$tpl->setVariable("PAGETITLE", $lng->txt("startpage"));
		$tpl->setVariable("ILIAS_RELEASE", $ilSetting->get("ilias_version"));

		if (isset($_GET['inactive']) && $_GET['inactive'])
		{
			$this->showFailure($lng->txt("err_inactive"));
		}
		else if (isset($_GET['expired']) && $_GET['expired'])
		{
			$this->showFailure($lng->txt("err_session_expired"));
		}
		else if (isset($_GET['login_to_purchase_object']) && $_GET['login_to_purchase_object'])
		{
			$lng->loadLanguageModule('payment');
			$this->showFailure($lng->txt("payment_login_to_buy_object"));
			$_SESSION['forceShoppingCartRedirect'] = '1';
		}
		else if (isset($_GET['reg_confirmation_msg']) && strlen(trim($_GET['reg_confirmation_msg'])))
		{
			$lng->loadLanguageModule('registration');
			if($_GET['reg_confirmation_msg'] == 'reg_account_confirmation_successful')
				$this->showSuccess($lng->txt(trim($_GET['reg_confirmation_msg'])));
			else
				$this->showFailure($lng->txt(trim($_GET['reg_confirmation_msg'])));
		}
		elseif(isset($_GET['reached_session_limit']) && $_GET['reached_session_limit'])
		{
			$this->showFailure($lng->txt("reached_session_limit"));
		}

		// TODO: Move this to header.inc since an expired session could not detected in login script
		$status = $ilAuth->getStatus();
		
		if ($status == "" && isset($_GET["auth_stat"]))
		{
			$status = $_GET["auth_stat"];
		}
		$auth_error = $ilias->getAuthError();

		if (!empty($status))
		{
			switch ($status)
			{
				case AUTH_EXPIRED:
					$this->showFailure($lng->txt("err_session_expired"));
					break;
				case AUTH_IDLED:
					// lang variable err_idled not existing
					//$tpl->setVariable(TXT_MSG_LOGIN_FAILED, $lng->txt("err_idled"));
					break;

				case AUTH_CAS_NO_ILIAS_USER:
					$this->showFailure($lng->txt("err_auth_cas_no_ilias_user"));
					break;

				case AUTH_SOAP_NO_ILIAS_USER:
					$this->showFailure($lng->txt("err_auth_soap_no_ilias_user"));
					break;

				case AUTH_LDAP_NO_ILIAS_USER:
					$this->showFailure($lng->txt("err_auth_ldap_no_ilias_user"));
					break;
				
				case AUTH_RADIUS_NO_ILIAS_USER:
					$this->showFailure($lng->txt("err_auth_radius_no_ilias_user"));
					break;
					
				case AUTH_MODE_INACTIVE:
					$this->showFailure($lng->txt("err_auth_mode_inactive"));
					break;

				case AUTH_APACHE_FAILED:
					$this->showFailure($lng->txt("err_auth_apache_failed"));
					break;
					
				case AUTH_WRONG_LOGIN:
				default:
					$add = "";
					if (is_object($auth_error))
					{
						$add = "<br>".$auth_error->getMessage();
					}
					$this->showFailure($lng->txt("err_wrong_login").$add);
					break;
			}
		}


		if (isset($_GET['cu']) && $_GET['cu'])
		{
			$lng->loadLanguageModule("auth");
			$this->showSuccess($lng->txt("auth_account_code_used"));
		}
		
		if (isset($_GET['time_limit']) && $_GET['time_limit'])
		{
			$this->showFailure($lng->txt("time_limit_reached"));
		}

		// output wrong IP message
		if (isset($_GET['wrong_ip']) && $_GET['wrong_ip'])
		{
			$this->showFailure($lng->txt("wrong_ip_detected")." (".$_SERVER["REMOTE_ADDR"].")");
		}
		
		// outout simultaneous login message
		if (isset($_GET['simultaneous_login']) && $_GET['simultaneous_login'])
		{
			$this->showFailure($lng->txt("simultaneous_login_detected"));
		}

		$this->ctrl->setTargetScript("ilias.php");
		$tpl->setVariable("PHP_SELF", $_SERVER['PHP_SELF']);

		// browser does not accept cookies
		if (isset($_GET['cookies']) && $_GET['cookies'] == 'nocookies')
		{
			$this->showFailure($lng->txt("err_no_cookies"));
			$tpl->setVariable("COOKIES_HOWTO", $lng->txt("cookies_howto"));
			$tpl->setVariable("LINK_NO_COOKIES",
				$this->ctrl->getLinkTarget($this, "showNoCookiesScreen"));
		}


		if(strlen($page_editor_html))
		{
			$tpl->setVariable('LPE',$page_editor_html);
		}


		$tpl->show("DEFAULT", false);
	}
	
	protected function showCodeForm($a_form = null)
	{
		global $tpl, $ilCtrl, $lng;
		
		$tpl->addBlockFile("CONTENT", "content", "tpl.login_reactivate_code.html","Services/Init");
		
		$this->showFailure($lng->txt("time_limit_reached"));
		
		if(!$a_form)
		{
			$a_form = $this->initCodeForm();
		}
		
		if($_POST["username"])
		{
			$_SESSION["username"] = $_POST["username"];
		}
	
		$tpl->setVariable("FORM", $a_form->getHTML());
		$tpl->show("DEFAULT", false);
	}
	
	protected function initCodeForm()
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
		
		$form->addCommandButton('processcode', $lng->txt('send'));
		
		return $form;
	}
	
	protected function processCode()
	{
		global $lng, $ilAuth, $ilCtrl;
		
		$form = $this->initCodeForm();
		if($form->checkInput())
		{
			$code = $form->getInput("code");			
						
			include_once "Services/User/classes/class.ilAccountCode.php";
			if(ilAccountCode::isUnusedCode($code))
			{
				$valid_until = ilAccountCode::getCodeValidUntil($code);			
				
				if(!$user_id = ilObjUser::_lookupId($_SESSION["username"]))
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
					$user->update();
					
					ilAccountCode::useCode($code);

					$ilCtrl->setParameter($this, "cu", 1);
					$ilCtrl->redirect($this, "showLogin");		
				}
			}
			
			$lng->loadLanguageModule("user");
			$field = $form->getItemByPostVar("code");
			$field->setAlert($lng->txt("user_account_code_not_valid"));						
		}
		
		$form->setValuesByPost();
		$this->showCodeForm($form);		
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
			$form->setFormAction($this->ctrl->getFormAction($this,''));
			$form->setShowTopButtons(false);
			$form->setTitle($lng->txt("login_to_ilias"));

			// auth selection
			include_once('./Services/Authentication/classes/class.ilAuthModeDetermination.php');
			$det = ilAuthModeDetermination::_getInstance();
			if(ilAuthUtils::_hasMultipleAuthenticationMethods() and $det->isManualSelection())
			{
				$radg = new ilRadioGroupInputGUI($lng->txt("auth_selection"), "auth_mode");
				foreach(ilAuthUtils::_getMultipleAuthModeOptions($lng) as $key => $option)
				{
					$op1 = new ilRadioOption($option['txt'], $key);
					$radg->addOption($op1);
					if (isset($option['checked']))
					{
						$radg->setValue($key);
					}
				}

				$form->addItem($radg);
			}

			// username
			$ti = new ilTextInputGUI($lng->txt("username"), "username");
			$ti->setSize(20);
			$form->addItem($ti);

			// password
			$pi = new ilPasswordInputGUI($lng->txt("password"), "password");
			$pi->setRetype(false);
			$pi->setSize(20);
			$form->addItem($pi);
			$form->addCommandButton("showLogin", $lng->txt("log_in"));
			#$form->addCommandButton("butSubmit", $lng->txt("log_in"));

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
			$tpl->setVariable("TXT_CAS_LOGIN_BUTTON", ilUtil::getImagePath("cas_login_button.gif"));
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

		// shibboleth login link
		if ($ilSetting->get("shib_active"))
		{
			$tpl = new ilTemplate('tpl.login_form_shibboleth.html',true,true,'Services/Init');

			$tpl->setVariable('SHIB_FORMACTION',$this->ctrl->getFormAction($this));
			
			if($ilSetting->get("shib_hos_type") == 'external_wayf')
			{
				$tpl->setCurrentBlock("shibboleth_login");
				$tpl->setVariable("TXT_SHIB_LOGIN", $lng->txt("login_to_ilias_via_shibboleth"));
				$tpl->setVariable("IL_TARGET", $_GET["target"]);
				$tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $ilSetting->get("shib_federation_name"));
				$tpl->setVariable("TXT_SHIB_LOGIN_BUTTON", $ilSetting->get("shib_login_button"));
				$tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS",
					sprintf(
						$lng->txt("shib_general_login_instructions"),
						$ilSetting->get("shib_federation_name")) .
						' <a href="mailto:' . $ilSetting->get("admin_email") . '">ILIAS ' . $lng->txt("administrator") . '</a>.'
					);
				$tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS",$ilSetting->get("shib_login_instructions"));
				$tpl->parseCurrentBlock();
			}
			elseif($ilSetting->get("shib_hos_type") == 'embedded_wayf')
			{
				$tpl->setCurrentBlock("shibboleth_custom_login");
				$customInstructions = stripslashes( $ilSetting->get("shib_login_instructions"));
				$tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $customInstructions);
				$tpl->parseCurrentBlock();
			} 
			else
			{
				$tpl->setCurrentBlock("shibboleth_wayf_login");
				$tpl->setVariable("TXT_SHIB_LOGIN", $lng->txt("login_to_ilias_via_shibboleth"));
				$tpl->setVariable("TXT_SHIB_FEDERATION_NAME", $ilSetting->get("shib_federation_name"));
				$tpl->setVariable(
					"TXT_SELECT_HOME_ORGANIZATION",
					sprintf($lng->txt("shib_select_home_organization"), $ilSetting->get("shib_federation_name")));
				$tpl->setVariable("TXT_CONTINUE", $lng->txt("btn_next"));
				$tpl->setVariable("TXT_SHIB_HOME_ORGANIZATION", $lng->txt("shib_home_organization"));
				$tpl->setVariable("TXT_SHIB_LOGIN_INSTRUCTIONS",
					$lng->txt("shib_general_wayf_login_instructions").
					' <a href="mailto:'.$ilSetting->get("admin_email").'">ILIAS '. $lng->txt("administrator").'</a>.'
				);
				$tpl->setVariable("TXT_SHIB_CUSTOM_LOGIN_INSTRUCTIONS", $ilSetting->get("shib_login_instructions"));

				require_once "./Services/AuthShibboleth/classes/class.ilShibbolethWAYF.php";
				$WAYF = new ShibWAYF();

				$tpl->setVariable("TXT_SHIB_INVALID_SELECTION", $WAYF->showNotice());
				$tpl->setVariable("SHIB_IDP_LIST", $WAYF->generateSelection());
				$tpl->setVariable("ILW_TARGET", $_GET["target"]);
				$tpl->parseCurrentBlock();

				return $this->substituteLoginPageElements(
					$GLOBALS['tpl'],
					$page_editor_html,
					$tpl->get(),
					'[list-shibboleth-login-form]',
					'SHIB_LOGIN_FORM'
				);
			}
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
		if(!ilPageUtil::_existsAndNotEmpty('auth', $active_lang))
		{
			return '';
		}

		include_once './Services/COPage/classes/class.ilPageObject.php';
		include_once './Services/COPage/classes/class.ilPageObjectGUI.php';

		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",ilObjStyleSheet::getContentStylePath(0));
		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();

		// get page object
		$page_gui = new ilPageObjectGUI('auth',  ilLanguage::lookupId($active_lang));

		/*
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
			$this->object->getStyleSheetId(), $this->object->getType()));
		 */
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$page_gui->setStyleId(0, 'auth');

		$page_gui->setIntLinkHelpDefault("RepositoryItem", $active_lang);
		//$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
		//$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "showMediaFullscreen"));
		//$page_gui->setLinkParams($this->ctrl->getUrlParameterString()); // todo
//		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this, ""));
		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader("");
		$page_gui->setEnabledRepositoryObjects(true);
		$page_gui->setEnabledLoginPage(true);
		$page_gui->setEnabledFileLists(false);
		$page_gui->setEnabledPCTabs(false);
		$page_gui->setEnabledMaps(true);
		$ret = $page_gui->showPage();

		return $ret;
	}

	/**
	 * Show language selection
	 * @global ilTemplate $tpl
	 */
	protected function showLanguageSelection($page_editor_html)
	{
		global $lng;

		$languages = $lng->getInstalledLanguages();
		if(count($languages) <= 1)
		{
			return '';
		}

		$ltpl = new ilTemplate('tpl.login_form_lang_selection.html',true,true,'Services/Init');
		foreach ($languages as $lang_key)
		{
			$ltpl->setCurrentBlock("languages");
			$ltpl->setVariable("LANG_KEY", $lang_key);
			$ltpl->setVariable("LANG_NAME",
				ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_".$lang_key));
			$ltpl->setVariable("BORDER", 0);
			$ltpl->setVariable("VSPACE", 0);
			$ltpl->parseCurrentBlock();
		}
		$ltpl->setCurrentBlock('lang_selection');
		$ltpl->setVariable("TXT_OK", $lng->txt("ok"));
		$ltpl->setVariable("LANG_FORM_ACTION",$this->ctrl->getFormAction($this));
		$ltpl->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));
		$ltpl->setVariable("LANG_ID", $lng->getLangKey());
		$ltpl->parseCurrentBlock();

		return $this->substituteLoginPageElements(
			$GLOBALS['tpl'],
			$page_editor_html,
			$ltpl->get(),
			'[list-language-selection]',
			'LANG_SELECTION'
		);

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
		global $lng, $ilSetting, $ilIliasIniFile;

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

		if ($ilSetting->get("pub_section"))
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
	 * Show user agreement link 
	 * @global ilLanguage $lng
	 * @param string $page_editor_html 
	 */
	protected function showUserAgreementLink($page_editor_html)
	{
		global $lng;

		$utpl = new ilTemplate('tpl.login_user_agreement_link.html',true,true,'Services/Init');
		$utpl->setVariable("USER_AGREEMENT", $lng->txt("usr_agreement"));
		$utpl->setVariable("LINK_USER_AGREEMENT",$this->ctrl->getLinkTarget($this, "showUserAgreement"));

		return $this->substituteLoginPageElements(
			$GLOBALS['tpl'],
			$page_editor_html,
			$utpl->get(),
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


	function showFailure($a_mess)
	{
		global $tpl, $lng;
		
		$tpl->setCurrentBlock("warning");
		$tpl->setVariable('TXT_MSG_LOGIN_FAILED', $a_mess);
		$tpl->setVariable("MESSAGE_HEADING", $lng->txt("failure_message"));
		$tpl->setVariable("ALT_IMAGE", $lng->txt("icon")." ".$lng->txt("failure_message"));
		$tpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_failure.gif"));
		$tpl->parseCurrentBlock();
	}
	
	public function showSuccess($a_mess)
	{
		global $tpl, $lng;
		
		$tpl->setCurrentBlock('success');
		$tpl->setVariable('TXT_MSG_LOGIN_SUCCESS', $a_mess);
		$tpl->setVariable('MESSAGE_HEADING', $lng->txt('success_message'));
		$tpl->setVariable('ALT_IMAGE', $lng->txt('icon').' '.$lng->txt('success_message'));
		$tpl->setVariable('SRC_IMAGE', ilUtil::getImagePath('mess_success.gif'));
		$tpl->parseCurrentBlock();
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
	 	$tpl->addBlockFile("CONTENT", 
			"content", 
			"tpl.login_account_migration.html",
			"Services/Init");
	 	
	 	include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
	 	$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'migrateAccount'));
		
		$form->setTitle($lng->txt('auth_account_migration'));
		$form->addCommandButton('migrateAccount', $lng->txt('save'));
		$form->addCommandButton('showLogin', $lng->txt('cancel'));
		
		$rad = new ilRadioGroupInputGUI($lng->txt('auth_account_migration_name'),'account_migration');
		$rad->setValue(1);
		
		$keep = new ilRadioOption($lng->txt('auth_account_migration_keep'),1,$lng->txt('auth_info_migrate'));
		$user = new ilTextInputGUI($lng->txt('login'),'mig_username');
		$user->setValue(ilUtil::prepareFormOutput($_POST['mig_username']));
		$user->setSize(32);
		$user->setMaxLength(128);
		$keep->addSubItem($user);
		
		$pass = new ilPasswordInputGUI($lng->txt('password'),'mig_password');
		$pass->setValue(ilUtil::prepareFormOutput($_POST['mig_password']));
		$pass->setRetype(false);
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
			$this->showFailure($a_message);
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
			$user->setAuthMode($_SESSION['tmp_auth_mode']);
			$user->setExternalAccount($_SESSION['tmp_external_account']);
			$user->update();
			
			// Assign to default role
			if(is_array($_SESSION['tmp_roles']))
			{
				foreach($_SESSION['tmp_roles'] as $role)
				{
					$rbacadmin->assignUser((int) $role,$user->getId());
				}
			}

			// Log migration
			$ilLog->write(__METHOD__.': Migrated '.$_SESSION['tmp_external_account'].' to ILIAS account '.$user->getLogin().'.');
	 	}
	 	elseif($_POST['account_migration'] == 2)
	 	{
			switch($_SESSION['tmp_auth_mode'])
			{
                                case 'apache':
					$_POST['username'] = $_SESSION['tmp_external_account'];
					$_POST['password'] = $_SESSION['tmp_pass'];

					include_once('Services/Database/classes/class.ilAuthContainerApache.php');
					$container = new ilAuthContainerApache();
					$container->forceCreation(true);
					$ilAuth = ilAuthFactory::factory($container);
					$ilAuth->start();
					break;

				case 'ldap':
					$_POST['username'] = $_SESSION['tmp_external_account'];
					$_POST['password'] = $_SESSION['tmp_pass'];
					
					include_once('Services/LDAP/classes/class.ilAuthContainerLDAP.php');
					$container = new ilAuthContainerLDAP();
					$container->forceCreation(true);
					$ilAuth = ilAuthFactory::factory($container);
					$ilAuth->start();
					break;
				
				case 'radius':
					$_POST['username'] = $_SESSION['tmp_external_account'];
					$_POST['password'] = $_SESSION['tmp_pass'];
					
					include_once './Services/Authentication/classes/class.ilAuthFactory.php';
					include_once './Services/Radius/classes/class.ilAuthContainerRadius.php';
					
					$container = new ilAuthContainerRadius();
					$container->forceCreation(true);
					$ilAuth = ilAuthFactory::factory($container);
					$ilAuth->start();
					break;
					
				case 'openid':
					$_POST['username'] = $_SESSION['tmp_external_account'];
					$_POST['password'] = $_SESSION['tmp_pass'];
					$_POST['oid_username'] = $_SESSION['tmp_oid_username'];
					$_SESSION['force_creation'] = true;
					
					include_once './Services/Authentication/classes/class.ilAuthFactory.php';
					include_once './Services/OpenId/classes/class.ilAuthContainerOpenId.php';
					
					$container = new ilAuthContainerOpenId();
					$container->forceCreation(true);
					ilAuthFactory::setContext(ilAuthFactory::CONTEXT_OPENID);
					$ilAuth = ilAuthFactory::factory($container);
					$ilAuth->callProvider($_POST['username'], null, null);
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

		$ilAuth->logout();
		session_destroy();

		// reset cookie
		$client_id = $_COOKIE["ilClientId"];
		ilUtil::setCookie("ilClientId","");

		//instantiate logout template
		$tpl->addBlockFile("CONTENT", "content", "tpl.logout.html",
			"Services/Init");

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

		$tpl->addBlockFile("CONTENT", "content", "tpl.user_mapping_selection.html",
			"Services/Init");
		$email_user = ilObjUser::_getLocalAccountsForEmail($valid["email"]);


		if ($ilAuth->sub_status == AUTH_WRONG_LOGIN)
		{
			$this->showFailure($lng->txt("err_wrong_login"));
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
		$tpl->addBlockfile("CONTENT", "content", "tpl.client_list.html",
			"Services/Init");

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
			$client->setDSN();
			if ($client->checkDatabaseExists() and $client->ini->readVariable("client","access") and $client->getSetting("setup_ok"))
			{
				$this->ctrl->setParameter($this, "client_id", $key);
				//$data[] = array(
				//				$client->getName(),
				//				"<a href=\"".$ilCtrl->getLinkTarget($this, "processIndexPHP")."\">Start page</a>",
				//				"<a href=\"".$ilCtrl->getLinkTarget($this, "showLogin")."\">Login page</a>"
				//				);
				//$data[] = array(
				//				$client->getName(),
				//				"<a href=\"".$ilCtrl->getLinkTarget($this, "processIndexPHP")."\">Start page</a>",
				//				"<a href=\""."login.php?cmd=force_login&client_id=".urlencode($key)."\">Login page</a>"
				//				);
				$data[] = array(
								$client->getName(),
								"<a href=\""."repository.php?client_id=".urlencode($key)."\">Start page</a>",
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
		$tbl->disable("sort");

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

		$tpl->addBlockFile("CONTENT", "content", "tpl.view_usr_agreement.html",
			"Services/Init");
		$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

//		ilUtil::sendInfo();
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
			
			$oldSid = session_id();
			
			$ilAuth->start();
			if (ANONYMOUS_USER_ID == "")
			{
				die ("Public Section enabled, but no Anonymous user found.");
			}
			if (!$ilAuth->getAuth())
			{
				die("ANONYMOUS user with the object_id ".ANONYMOUS_USER_ID." not found!");
			}

			$newSid = session_id();
			include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
			ilPaymentShoppingCart::_migrateShoppingCart($oldSid, $newSid);

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
		global $ilBench, $ilCtrl, $ilAccess, $lng, $ilUser;
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
			if(IS_PAYMENT_ENABLED)
			{
                  $usr_id = $ilUser->getId();

				include_once './Services/Payment/classes/class.ilShopLinkBuilder.php';
				$shop_classes = array_keys(ilShopLinkBuilder::$linkArray);

				include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
				ilPaymentShoppingCart::_assignObjectsToUserId($usr_id);

				if((int)$_GET['forceShoppingCartRedirect'])
				{
					$class = 'ilshopshoppingcartgui';
					  ilUtil::redirect('ilias.php?baseClass=ilShopController&cmd=redirect&redirect_class=ilshopshoppingcartgui');
				}

				// handle goto_ links for shop after login
				$tarr = explode("_", $_GET["target"]);
				if(in_array($tarr[0], $shop_classes))
				{
					$class = $tarr[0];
					 ilUtil::redirect('ilias.php?baseClass='.ilShopLinkBuilder::$linkArray[strtolower($class)]['baseClass']
						.'&cmdClass='.strtolower(ilShopLinkBuilder::$linkArray[strtolower($class)]['cmdClass']));
					  exit;
				}
			}
						
			if	(!$this->_checkGoto($_GET["target"]))
			{
				// message if target given but not accessible
				if ($_GET["target"] != "")
				{
					$tarr = explode("_", $_GET["target"]);
					if ($tarr[0] != "pg" && $tarr[0] != "st" && $tarr[1] > 0)
					{
						ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
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
		global $objDefinition, $ilPluginAdmin;

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

		return call_user_func(array($full_class, "_checkGoto"),
			$a_target);
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
			
			if($lng->getLangKey() != $oUser->getPref('language'))
			{
				$lng = new ilLanguage($oUser->getPref('language'));
			}
			
			// send email
			// try individual account mail in user administration
			include_once("Services/Mail/classes/class.ilAccountMail.php");
			include_once './Services/User/classes/class.ilObjUserFolder.php';
			$amail = ilObjUserFolder::_lookupNewAccountMail($oUser->getPref('language'));
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
			
			ilUtil::redirect('./login.php?cmd=force_login&reg_confirmation_msg=reg_account_confirmation_successful');
		}
		catch(ilRegConfirmationLinkExpiredException $exception)
		{
			include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';			
			$soap_client = new ilSoapClient();
			$soap_client->setTimeout(1);
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
			
			ilUtil::redirect('./login.php?cmd=force_login&reg_confirmation_msg='.$exception->getMessage());
		}
		catch(ilRegistrationHashNotFoundException $exception)
		{
			ilUtil::redirect('./login.php?cmd=force_login&reg_confirmation_msg='.$exception->getMessage());
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
}
?>
