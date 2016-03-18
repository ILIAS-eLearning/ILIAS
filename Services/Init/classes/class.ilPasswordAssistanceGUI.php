<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Password assistance facility for users who have forgotten their password
 * or for users for whom no password has been assigned yet.
 * @author  Werner Randelshofer <wrandels@hsw.fhz.ch>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesInit
 */
class ilPasswordAssistanceGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilRbacReview
	 */
	protected $rbacreview;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ILIAS
	 */
	protected $ilias;

	/**
	 * @var ilHTTPS
	 */
	protected $https;

	/**
	 *
	 */
	public function __construct()
	{
		/**
		 * @var $ilCtrl     ilCtrl
		 * @var $lng        ilLanguage
		 * @var $rbacreview ilRbacReview
		 * @var $tpl        ilTemplate
		 * @var $ilSetting  ilSetting
		 * @var $ilias      ILIAS
		 * @var $https      ilHTTPS
		 */
		global $ilCtrl, $lng, $rbacreview, $tpl, $ilSetting, $ilias, $https;

		$this->ctrl       = $ilCtrl;
		$this->lng        = $lng;
		$this->rbacreview = $rbacreview;
		$this->tpl        = $tpl;
		$this->settings   = $ilSetting;
		$this->ilias      = $ilias;
		$this->https      = $https;
	}

	/**
	 * @return mixed
	 */
	public function executeCommand()
	{
		// check hack attempts
		if(!$this->settings->get('password_assistance')) // || AUTH_DEFAULT != AUTH_LOCAL)
		{
			if(empty($_SESSION['AccountId']) && $_SESSION['AccountId'] !== false)
			{
				$this->ilias->error_obj->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->WARNING);
			}
		}

		// check correct setup
		if(!$this->settings->get('setup_ok'))
		{
			die('Setup is not completed. Please run setup routine again.');
		}

		// Change the language, if necessary. 
		// And load the 'pwassist' language module
		$lang = $_GET['lang'];
		if($lang != null && $lang != '' && $this->lng->getLangKey() != $lang)
		{
			$lng = new ilLanguage($lang);
		}
		$this->lng->loadLanguageModule('pwassist');

		$cmd        = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class)
		{
			default:
				if($cmd != '')
				{
					return $this->$cmd();
				}
				else
				{
					if(!empty($_GET['key']))
					{
						$this->showAssignPasswordForm();
					}
					else
					{
						$this->showAssistanceForm();
					}
				}
				break;
		}
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getAssistanceForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();

		$form->setFormAction($this->ctrl->getFormAction($this, 'submitAssistanceForm'));
		$form->setTarget('_parent');

		$username = new ilTextInputGUI($this->lng->txt('username'), 'username');
		$username->setRequired(true);
		$form->addItem($username);

		$email = new ilTextInputGUI($this->lng->txt('email'), 'email');
		$email->setRequired(true);
		$form->addItem($email);

		$form->addCommandButton('submitAssistanceForm', $this->lng->txt('submit'));

		return $form;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function showAssistanceForm(ilPropertyFormGUI $form = null)
	{
		ilStartUpGUI::initStartUpTemplate('tpl.pwassist_assistance.html', true);
		$this->tpl->setVariable('IMG_PAGEHEADLINE', ilUtil::getImagePath('icon_auth.svg'));
		$this->tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));

		$this->tpl->setVariable
		(
			'TXT_ENTER_USERNAME_AND_EMAIL',
			str_replace
			(
				"\\n", '<br />',
				sprintf
				(
					$this->lng->txt('pwassist_enter_username_and_email'),
					'<a href="mailto:' . ilUtil::prepareFormOutput($this->settings->get('admin_email')) . '">' . ilUtil::prepareFormOutput($this->settings->get('admin_email')) . '</a>'
				)
			)
		);

		if(!$form)
		{
			$form = $this->getAssistanceForm();
		}
		$this->tpl->setVariable('FORM', $form->getHTML());
		$this->tpl->show();
	}

	/**
	 * Reads the submitted data from the password assistance form.
	 * The following form fields are read as HTTP POST parameters:
	 * username
	 * email
	 * If the submitted username and email address matches an entry in the user data
	 * table, then ILIAS creates a password assistance session for the user, and
	 * sends a password assistance mail to the email address.
	 * For details about the creation of the session and the e-mail see function
	 * sendPasswordAssistanceMail().
	 */
	public function submitAssistanceForm()
	{
		$form = $this->getAssistanceForm();
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->showAssistanceForm($form);
			return;
		}

		$username = $form->getInput('username');
		$email    = $form->getInput('email');

		$userObj = null;
		$userid  = ilObjUser::getUserIdByLogin($username);
		$txt_key = 'pwassist_invalid_username_or_email';
		if($userid != 0)
		{
			$userObj = new ilObjUser($userid);
			if(strcasecmp($userObj->getEmail(), $email) != 0)
			{
				$userObj = null;
			}
			elseif(!strlen($email))
			{
				$userObj = null;
				$txt_key = 'pwassist_no_email_found';
			}
			else if(
				$userObj->getAuthMode(true) != AUTH_LOCAL ||
				($userObj->getAuthMode(true) == AUTH_DEFAULT && AUTH_DEFAULT != AUTH_LOCAL)
			)
			{
				$userObj = null;
				$txt_key = 'pwassist_invalid_auth_mode';
			}
		}

		// No matching user object found?
		// Show the password assistance form again, and display an error message.
		if($userObj == null)
		{
			ilUtil::sendFailure(str_replace("\\n", '', $this->lng->txt($txt_key)));
			$form->setValuesByPost();
			$this->showAssistanceForm($form);
		}
		else
		{
			// Matching user object found?
			// Check if the user is permitted to use the password assistance function,
			// and then send a password assistance mail to the email address.
			// FIXME: Extend this if-statement to check whether the user
			// has the permission to use the password assistance function.
			// The anonymous user and users who are system administrators are
			// not allowed to use this feature
			if(
				$this->rbacreview->isAssigned($userObj->getId(), ANONYMOUS_ROLE_ID) ||
				$this->rbacreview->isAssigned($userObj->getId(), SYSTEM_ROLE_ID)
			)
			{
				ilUtil::sendFailure(str_replace("\\n", '', $this->lng->txt('pwassist_not_permitted')));
				$form->setValuesByPost();
				$this->showAssistanceForm($form);
			}
			else
			{
				$this->sendPasswordAssistanceMail($userObj);
				$this->showMessageForm(sprintf($this->lng->txt('pwassist_mail_sent'), $email));
			}
		}
	}

	/**
	 * Creates (or reuses) a password assistance session, and sends a password
	 * assistance mail to the specified user.
	 * Note: To prevent DOS attacks, a new session is created only, if no session
	 * exists, or if the existing session has been expired.
	 * The password assistance mail contains an URL, which points to this script
	 * and contains the following URL parameters:
	 * client_id
	 * key
	 * @param $userObj ilObjUser
	 */
	public function sendPasswordAssistanceMail(ilObjUser $userObj)
	{
		require_once 'Services/Mail/classes/class.ilMailbox.php';
		require_once 'Services/Mail/classes/class.ilMail.php';
		require_once 'Services/Mail/classes/class.ilMimeMail.php';
		require_once 'include/inc.pwassist_session_handler.php';

		// Check if we need to create a new session
		$pwassist_session = db_pwassist_session_find($userObj->getId());
		if(
			count($pwassist_session) == 0 ||
			$pwassist_session['expires'] < time() ||
			true // comment by mjansen: wtf? :-)
		)
		{
			// Create a new session id
			// #9700 - this didn't do anything before?!
			// db_set_save_handler();
			session_start();
			$pwassist_session['pwassist_id'] = db_pwassist_create_id();
			session_destroy();
			db_pwassist_session_write(
				$pwassist_session['pwassist_id'],
				3600,
				$userObj->getId()
			);
		}
		$protocol = $this->https->isDetected() ? 'https://' : 'http://';
		// Compose the mail
		$server_url = $protocol . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')) . '/';
		// XXX - Werner Randelshofer - Insert code here to dynamically get the
		//      the delimiter. For URL's that are sent by e-mail to a user,
		//      it is best to use semicolons as parameter delimiter
		$delimiter                = '&';
		$pwassist_url             = $protocol . $_SERVER['HTTP_HOST']
			. str_replace('ilias.php', 'pwassist.php', $_SERVER['PHP_SELF'])
			. '?client_id=' . $this->ilias->getClientId()
			. $delimiter . 'lang=' . $this->lng->getLangKey()
			. $delimiter . 'key=' . $pwassist_session['pwassist_id'];
		$alternative_pwassist_url = $protocol . $_SERVER['HTTP_HOST']
			. str_replace('ilias.php', 'pwassist.php', $_SERVER['PHP_SELF'])
			. '?client_id=' . $this->ilias->getClientId()
			. $delimiter . 'lang=' . $this->lng->getLangKey()
			. $delimiter . 'key=' . $pwassist_session['pwassist_id'];

		$contact_address = ilMail::getIliasMailerAddress();

		$mm = new ilMimeMail();
		$mm->Subject($this->lng->txt('pwassist_mail_subject'));
		$mm->From($contact_address);
		$mm->To($userObj->getEmail());
		$mm->Body
		(
			str_replace
			(
				array("\\n", "\\t"),
				array("\n", "\t"),
				sprintf
				(
					$this->lng->txt('pwassist_mail_body'),
					$pwassist_url,
					$server_url,
					$_SERVER['REMOTE_ADDR'],
					$userObj->getLogin(),
					'mailto:' . $contact_address,
					$alternative_pwassist_url
				)
			)
		);
		$mm->Send();
	}

	/**
	 * @param string $pwassist_id
	 * @return ilPropertyFormGUI
	 */
	protected function getAssignPasswordForm($pwassist_id)
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();

		$form->setFormAction($this->ctrl->getFormAction($this, 'submitAssignPasswordForm'));
		$form->setTarget('_parent');

		$username = new ilTextInputGUI($this->lng->txt('username'), 'username');
		$username->setRequired(true);
		$form->addItem($username);

		$password = new ilPasswordInputGUI($this->lng->txt('password'), 'password');
		$password->setRequired(true);
		$form->addItem($password);

		$key = new ilHiddenInputGUI('key');
		$key->setValue($pwassist_id);
		$form->addItem($key);

		$form->addCommandButton('submitAssignPasswordForm', $this->lng->txt('submit'));

		return $form;
	}

	/**
	 * Assign password form.
	 * This form is used to assign a password to a username.
	 * To use this form, the following data must be provided as HTTP GET parameter,
	 * or in argument pwassist_id:
	 * key
	 * The key is used to retrieve the password assistance session.
	 * If the key is missing, or if the password assistance session has expired, the
	 * password assistance form will be shown instead of this form.
	 * @param ilPropertyFormGUI $form
	 * @param string            $pwassist_id
	 */
	public function showAssignPasswordForm(ilPropertyFormGUI $form = null, $pwassist_id = '')
	{
		require_once 'include/inc.pwassist_session_handler.php';
		require_once 'Services/Language/classes/class.ilLanguage.php';

		// Retrieve form data
		if(!$pwassist_id)
		{
			$pwassist_id = $_GET['key'];
		}

		// Retrieve the session, and check if it is valid
		$pwassist_session = db_pwassist_session_read($pwassist_id);
		if(
			count($pwassist_session) == 0 ||
			$pwassist_session['expires'] < time()
		)
		{
			ilUtil::sendFailure($this->lng->txt('pwassist_session_expired'));
			$this->showAssistanceForm(null);
		}
		else
		{
			ilStartUpGUI::initStartUpTemplate('tpl.pwassist_assignpassword.html', true);
			$this->tpl->setVariable('IMG_PAGEHEADLINE', ilUtil::getImagePath('icon_auth.svg'));
			$this->tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));

			$this->tpl->setVariable('TXT_ENTER_USERNAME_AND_NEW_PASSWORD', $this->lng->txt('pwassist_enter_username_and_new_password'));

			if(!$form)
			{
				$form = $this->getAssignPasswordForm($pwassist_id);
			}
			$this->tpl->setVariable('FORM', $form->getHTML());
			$this->tpl->show();
		}
	}

	/**
	 * Reads the submitted data from the password assistance form.
	 * The following form fields are read as HTTP POST parameters:
	 * key
	 * username
	 * password1
	 * password2
	 * The key is used to retrieve the password assistance session.
	 * If the key is missing, or if the password assistance session has expired, the
	 * password assistance form will be shown instead of this form.
	 * If the password assistance session is valid, and if the username matches the
	 * username, for which the password assistance has been requested, and if the
	 * new password is valid, ILIAS assigns the password to the user.
	 * Note: To prevent replay attacks, the session is deleted when the
	 * password has been assigned successfully.
	 */
	public function submitAssignPasswordForm()
	{

		require_once 'include/inc.pwassist_session_handler.php';

		// We need to fetch this before form instantiation
		$pwassist_id = ilUtil::stripSlashes($_POST['key']);

		$form = $this->getAssignPasswordForm($pwassist_id);
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->showAssistanceForm($form);
			return;
		}

		$username    = $form->getInput('username');
		$password    = $form->getInput('password');
		$pwassist_id = $form->getInput('key');

		// Retrieve the session
		$pwassist_session = db_pwassist_session_read($pwassist_id);

		if(
			count($pwassist_session) == 0 ||
			$pwassist_session['expires'] < time()
		)
		{
			ilUtil::sendFailure(str_replace("\\n", '', $this->lng->txt('pwassist_session_expired')));
			$form->setValuesByPost();
			$this->showAssistanceForm($form);
			return;
		}
		else
		{
			$is_successful = true;
			$message       = '';

			$userObj = new ilObjUser($pwassist_session['user_id']);
			if($userObj == null)
			{
				$message       = $this->lng->txt('user_does_not_exist');
				$is_successful = false;
			}

			// check if the username entered by the user matches the
			// one of the user object.
			if($is_successful && strcasecmp($userObj->getLogin(), $username) != 0)
			{
				$message       = $this->lng->txt('pwassist_login_not_match');
				$is_successful = false;
			}

			$error_lng_var = '';
			if(!ilUtil::isPasswordValidForUserContext($password, $userObj, $error_lng_var))
			{
				$message       = $this->lng->txt($error_lng_var);
				$is_successful = false;
			}

			// End of validation
			// If the validation was successful, we change the password of the
			// user.
			// ------------------
			if($is_successful)
			{
				$is_successful = $userObj->resetPassword($password, $password);
				if(!$is_successful)
				{
					$message = $this->lng->txt('passwd_invalid');
				}
			}

			// If we are successful so far, we update the user object.
			// ------------------
			if($is_successful)
			{
				$userObj->update();
			}

			// If we are successful, we destroy the password assistance
			// session and redirect to the login page.
			// Else we display the form again along with an error message.
			// ------------------
			if($is_successful)
			{
				db_pwassist_session_destroy($pwassist_id);
				$this->showMessageForm(sprintf($this->lng->txt('pwassist_password_assigned'), $username));
			}
			else
			{
				ilUtil::sendFailure(str_replace("\\n", '', $message));
				$form->setValuesByPost();
				$this->showAssignPasswordForm($form, $pwassist_id);
			}
		}
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getUsernameAssistanceForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();

		$form->setFormAction($this->ctrl->getFormAction($this, 'submitUsernameAssistanceForm'));
		$form->setTarget('_parent');

		$email = new ilTextInputGUI($this->lng->txt('email'), 'email');
		$email->setRequired(true);
		$form->addItem($email);

		$form->addCommandButton('submitUsernameAssistanceForm', $this->lng->txt('submit'));

		return $form;
	}

	/**
	 * Shows the password assistance form.
	 * This form is used to request a password assistance mail from ILIAS.
	 * This form contains the following fields:
	 * username
	 * email
	 * When the user submits the form, then this script is invoked with the cmd
	 * 'submitAssistanceForm'.
	 * @param ilPropertyFormGUI $form
	 */
	public function showUsernameAssistanceForm(ilPropertyFormGUI $form = null)
	{
		ilStartUpGUI::initStartUpTemplate('tpl.pwassist_username_assistance.html', true);
		$this->tpl->setVariable('IMG_PAGEHEADLINE', ilUtil::getImagePath('icon_auth.svg'));
		$this->tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));

		$this->tpl->setVariable
		(
			'TXT_ENTER_USERNAME_AND_EMAIL',
			str_replace
			(
				"\\n", '<br />',
				sprintf
				(
					$this->lng->txt('pwassist_enter_email'),
					'<a href="mailto:' . ilUtil::prepareFormOutput($this->settings->get('admin_email')) . '">' . ilUtil::prepareFormOutput($this->settings->get('admin_email')) . '</a>'
				)
			)
		);

		if(!$form)
		{
			$form = $this->getUsernameAssistanceForm();
		}
		$this->tpl->setVariable('FORM', $form->getHTML());
		$this->tpl->show();
	}

	/**
	 * Reads the submitted data from the password assistance form.
	 * The following form fields are read as HTTP POST parameters:
	 * username
	 * email
	 * If the submitted username and email address matches an entry in the user data
	 * table, then ILIAS creates a password assistance session for the user, and
	 * sends a password assistance mail to the email address.
	 * For details about the creation of the session and the e-mail see function
	 * sendPasswordAssistanceMail().
	 */
	public function submitUsernameAssistanceForm()
	{
		require_once 'Services/User/classes/class.ilObjUser.php';
		require_once 'Services/Utilities/classes/class.ilUtil.php';

		$form = $this->getUsernameAssistanceForm();
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->showUsernameAssistanceForm($form);
			return;
		}

		// Retrieve form data
		$email = $form->getInput('email');

		// Retrieve a user object with matching user name and email address.
		$logins = ilObjUser::_getUserIdsByEmail($email);

		// No matching user object found?
		// Show the password assistance form again, and display an error message.
		if(!is_array($logins) || count($logins) < 1)
		{
			ilUtil::sendFailure(str_replace("\\n", '', $this->lng->txt('pwassist_invalid_email')));
			$form->setValuesByPost();
			$this->showUsernameAssistanceForm($form);
		}
		else
		{
			// Matching user object found?
			// Check if the user is permitted to use the password assistance function,
			// and then send a password assistance mail to the email address.

			// FIXME: Extend this if-statement to check whether the user
			// has the permission to use the password assistance function.
			// The anonymous user and users who are system administrators are
			// not allowed to use this feature
			/*		if ($rbacreview->isAssigned($userObj->getID, ANONYMOUS_ROLE_ID)
					|| $rbacreview->isAssigned($userObj->getID, SYSTEM_ROLE_ID)
					) 
					{
						$this->showAssistanceForm
						(
							$lng->txt("pwassist_not_permitted"),
							$username,
							$email
						);
					} 
					else */
			{
				$this->sendUsernameAssistanceMail($email, $logins);
				$this->showMessageForm(sprintf($this->lng->txt('pwassist_mail_sent'), $email));
			}
		}
	}

	/**
	 * Creates (or reuses) a password assistance session, and sends a password
	 * assistance mail to the specified user.
	 * Note: To prevent DOS attacks, a new session is created only, if no session
	 * exists, or if the existing session has been expired.
	 * The password assistance mail contains an URL, which points to this script
	 * and contains the following URL parameters:
	 * client_id
	 * key
	 * @param $email
	 * @param $logins
	 */
	public function sendUsernameAssistanceMail($email, array $logins)
	{
		require_once 'Services/Mail/classes/class.ilMailbox.php';
		require_once 'Services/Mail/classes/class.ilMail.php';
		require_once 'Services/Mail/classes/class.ilMimeMail.php';
		require_once 'include/inc.pwassist_session_handler.php';

		$protocol = $this->https->isDetected() ? 'https://' : 'http://';

		$server_url      = $protocol . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')) . '/';
		$login_url       = $server_url . 'pwassist.php' . '?client_id=' . $this->ilias->getClientId() . '&lang=' . $this->lng->getLangKey();
		$contact_address = ilMail::getIliasMailerAddress();

		$mm = new ilMimeMail();
		$mm->Subject($this->lng->txt('pwassist_mail_subject'));
		$mm->From($contact_address);
		$mm->To($email);
		$mm->Body
		(
			str_replace
			(
				array("\\n", "\\t"),
				array("\n", "\t"),
				sprintf
				(
					$this->lng->txt('pwassist_username_mail_body'),
					join($logins, ",\n"),
					$server_url,
					$_SERVER['REMOTE_ADDR'],
					$email,
					'mailto:' . $contact_address,
					$login_url
				)
			)
		);
		$mm->Send();
	}

	/**
	 * This form is used to show a message to the user.
	 * @param string $text
	 */
	public function showMessageForm($text)
	{
		ilStartUpGUI::initStartUpTemplate('tpl.pwassist_message.html', true);
		$this->tpl->setVariable('TXT_PAGEHEADLINE', $this->lng->txt('password_assistance'));
		$this->tpl->setVariable('IMG_PAGEHEADLINE', ilUtil::getImagePath('icon_auth.svg'));

		$this->tpl->setVariable('TXT_TEXT', str_replace("\\n", '<br />', $text));
		$this->tpl->show();
	}
}