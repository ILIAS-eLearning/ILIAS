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
* Password assistance facility for users who have forgotten their password
* or for users for whom no password has been assigned yet.
*
* @author Werner Randelshofer <wrandels@hsw.fhz.ch>
* @version $Id$
*
* @package ilias-core
*/

/// MAIN ////

// start correct client
// if no client_id is given, default client is loaded (in class.ilias.php)
if (isset($_GET["client_id"]))
{	
	setcookie("ilClientId",$_GET["client_id"]);
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

require_once "include/inc.check_pear.php";
require_once "include/inc.header.php";


// catch hack attempts
if (! $ilias->getSetting("password_assistance") || AUTH_CURRENT != AUTH_LOCAL)
{
    if (empty($_SESSION["AccountId"]) and $_SESSION["AccountId"] !== false)
    {
        $ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
    }
}

// check correct setup
if (!$ilias->getSetting("setup_ok"))
{
	echo "setup is not completed. Please run setup routine again. (pwassist.php)";
	exit();
}


// Change the language, if necessary. 
// And load the 'pwassist' language module
$lang = $_GET['lang'];
if ($lang != null && $lang != "" && $lng->getLangKey() != $lang)
{
	$lng = new ilLanguage($lang);
}
$lng->loadLanguageModule('pwassist');

// Determine which function to perform
switch ($_GET["cmd"])
{
	case "submitassign":
		submitAssignPasswordForm();
		break;

	case "submitassist":
		submitAssistanceForm();
		break;
	
	case "submitusernameassign":
		break;
		
	case "submitusernameassist":
		submitUsernameAssistanceForm();
		break;
		
	case "forgot_username":
		showUsernameAssistanceForm();
		break;
	
	default :
		if (!empty($_GET["key"])) {
			showAssignPasswordForm();
		} else {
			showAssistanceForm();
		}
		break;
}

// Logout current session
$ilias->auth->logout();
session_destroy();

/// END OF MAIN ////


/* Shows the password assistance form.
 * This form is used to request a password assistance mail from ILIAS.
 *
 * This form contains the following fields: 
 * username 
 * email 
 *
 * When the user submits the form, then this script is invoked with the cmd
 * 'submitassist'.
 *
 * @param message  A message to display on the form.
 * @param username The user name to be shown in the form.
 * @param email    The e-mail to be shown in the form.
 */
function showAssistanceForm($message="", $username="", $email="")
{
	global $tpl, $ilias, $lng;
	
	// Create the form
	$tpl->addBlockFile("CONTENT", "content", "tpl.pwassist_assistance.html");
	$tpl->setVariable("FORMACTION","pwassist.php?cmd=submitassist&lang=".$lng->getLangKey());
	$tpl->setVariable("TARGET","target=\"_parent\"");
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("password_assistance"));
	$tpl->setVariable("TXT_MESSAGE", str_replace("\\n","<br>",$message));

	$contact_address = $ilias->getSetting("admin_email");
	$tpl->setVariable
	(
		"TXT_ENTER_USERNAME_AND_EMAIL", 
		str_replace
		(
			"\\n","<br>",
			sprintf
				(
				$lng->txt("pwassist_enter_username_and_email"),
				"<a href=\"mailto:".$contact_address."\">".$contact_address."</a>"
				)
		)
	);
	$tpl->setVariable("TXT_USERNAME", $lng->txt("username"));
	$tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
	$tpl->setVariable("USERNAME", $username);
	$tpl->setVariable("EMAIL", $email);
	$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
	$tpl->setVariable("BACK", $lng->txt("back"));
	$tpl->setVariable("LANG", $lng->getLangKey());

	$tpl->show();
}


/* Shows the password assistance form.
 * This form is used to request a password assistance mail from ILIAS.
 *
 * This form contains the following fields: 
 * username 
 * email 
 *
 * When the user submits the form, then this script is invoked with the cmd
 * 'submitassist'.
 *
 * @param message  A message to display on the form.
 * @param username The user name to be shown in the form.
 * @param email    The e-mail to be shown in the form.
 */
function showUsernameAssistanceForm($message="", $username="", $email="")
{
	global $tpl, $ilias, $lng;
	
	// Create the form
	$tpl->addBlockFile("CONTENT", "content", "tpl.pwassist_username_assistance.html");
	$tpl->setVariable("FORMACTION","pwassist.php?cmd=submitusernameassist&lang=".$lng->getLangKey());
	$tpl->setVariable("TARGET","target=\"_parent\"");
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("password_assistance"));
	$tpl->setVariable("TXT_MESSAGE", str_replace("\\n","<br>",$message));

	$contact_address = $ilias->getSetting("admin_email");
	$tpl->setVariable
	(
		"TXT_ENTER_USERNAME_AND_EMAIL", 
		str_replace
		(
			"\\n","<br>",
			sprintf
				(
				$lng->txt("pwassist_enter_email"),
				"<a href=\"mailto:".$contact_address."\">".$contact_address."</a>"
				)
		)
	);
	$tpl->setVariable("TXT_USERNAME", $lng->txt("username"));
	$tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
	$tpl->setVariable("USERNAME", $username);
	$tpl->setVariable("EMAIL", $email);
	$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
	$tpl->setVariable("BACK", $lng->txt("back"));
	$tpl->setVariable("LANG", $lng->getLangKey());

	$tpl->show();
}

/** Reads the submitted data from the password assistance form.
 * 
 * The following form fields are read as HTTP POST parameters:
 * username
 * email
 *
 * If the submitted username and email address matches an entry in the user data 
 * table, then ILIAS creates a password assistance session for the user, and
 * sends a password assistance mail to the email address.
 * For details about the creation of the session and the e-mail see function
 * sendPasswordAssistanceMail().
 */
function submitAssistanceForm()
{
	global $tpl, $ilias, $lng, $rbacadmin, $rbacreview;
	
	require_once "classes/class.ilObjUser.php";
	require_once "classes/class.ilUtil.php";
	
	// Retrieve form data
	$username = $_POST["username"];
	$email = $_POST["email"];
	
	// Retrieve a user object with matching user name and email address.
	$userObj = null;
	$userid = ilObjUser::getUserIdByLogin($username);
	if ($userid != 0)
	{
		$userObj = new ilObjUser($userid);
		if (strcasecmp($userObj->getEmail(), $email) != 0) 
		{
			$userObj = null;
		}
	}
	
	// No matching user object found?
	// Show the password assistance form again, and display an error message.
	if ($userObj == null) 
	{
		showAssistanceForm
		(
			$lng->txt("pwassist_invalid_username_or_email"),
			$username,
			$email
		);
	}

	// Matching user object found?
	// Check if the user is permitted to use the password assistance function,
	// and then send a password assistance mail to the email address.
	else
	{
		// FIXME: Extend this if-statement to check whether the user
		// has the permission to use the password assistance function.
		// The anonymous user and users who are system administrators are
		// not allowed to use this feature
		if ($rbacreview->isAssigned($userObj->getID, ANONYMOUS_ROLE_ID)
		|| $rbacreview->isAssigned($userObj->getID, SYSTEM_ROLE_ID)
		) 
		{
			showAssistanceForm
			(
				$lng->txt("pwassist_not_permitted"),
				$username,
				$email
			);
		}
		else
		{
			sendPasswordAssistanceMail($userObj);
			showMessageForm
			(
				null,
				sprintf
				(
					$lng->txt("pwassist_mail_sent"),
					$email
				)
			);
		}
	}
}

/** Reads the submitted data from the password assistance form.
 * 
 * The following form fields are read as HTTP POST parameters:
 * username
 * email
 *
 * If the submitted username and email address matches an entry in the user data 
 * table, then ILIAS creates a password assistance session for the user, and
 * sends a password assistance mail to the email address.
 * For details about the creation of the session and the e-mail see function
 * sendPasswordAssistanceMail().
 */
function submitUsernameAssistanceForm()
{
	global $tpl, $ilias, $lng, $rbacadmin, $rbacreview;
	
	require_once "classes/class.ilObjUser.php";
	require_once "classes/class.ilUtil.php";
	
	// Retrieve form data
	$email = $_POST["email"];
	
	// Retrieve a user object with matching user name and email address.
	$logins = ilObjUser::_getUserIdsByEmail($email);
	
	// No matching user object found?
	// Show the password assistance form again, and display an error message.
	if (count($logins)< 1)  
	{
		showUsernameAssistanceForm
		(
			$lng->txt("pwassist_invalid_username_or_email"),
			"",
			$email
		);
	}

	// Matching user object found?
	// Check if the user is permitted to use the password assistance function,
	// and then send a password assistance mail to the email address.
	else
	{
		// FIXME: Extend this if-statement to check whether the user
		// has the permission to use the password assistance function.
		// The anonymous user and users who are system administrators are
		// not allowed to use this feature
/*		if ($rbacreview->isAssigned($userObj->getID, ANONYMOUS_ROLE_ID)
		|| $rbacreview->isAssigned($userObj->getID, SYSTEM_ROLE_ID)
		) 
		{
			showAssistanceForm
			(
				$lng->txt("pwassist_not_permitted"),
				$username,
				$email
			);
		} 
		else */
		{
			sendUsernameAssistanceMail($email, $logins);
			showMessageForm
			(
				null,
				sprintf
				(
					$lng->txt("pwassist_mail_sent"),
					$email
				)
			);
		}
	}
}

/** Creates (or reuses) a password assistance session, and sends a password
 * assistance mail to the specified user.
 * 
 * Note: To prevent DOS attacks, a new session is created only, if no session
 * exists, or if the existing session has been expired.
 *
 * The password assistance mail contains an URL, which points to this script
 * and contains the following URL parameters:
 * client_id
 * key
 *
 * @param usrObj An instance of class.ilObjUserObject.php.
 */
function sendPasswordAssistanceMail($userObj)
{
	global $lng, $ilias;

	include_once "classes/class.ilMailbox.php";
	include_once "classes/class.ilMimeMail.php";
	require_once "include/inc.pwassist_session_handler.php";
	

	// Check if we need to create a new session
	$pwassist_session = db_pwassist_session_find($userObj->getId());
	if (count($pwassist_session) == 0 || $pwassist_session["expires"] < time())
	{
		// Create a new session id
		$pwassist_session["pwassist_id"] = db_pwassist_create_id();
		
		db_pwassist_session_write(
			$pwassist_session["pwassist_id"],
			3600, 
			$userObj->getId()
		);
	}
	
	// Compose the mail
	$server_url='http://'.$_SERVER['HTTP_HOST'].
		substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/')).
		'/';
	$pwassist_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']
	."?client_id=".$ilias->getClientId()
	."&lang=".$lng->getLangKey()
	."&key=".$pwassist_session["pwassist_id"];
	$contact_address=$ilias->getSetting("admin_email");

	$mm = new ilMimeMail();
	$mm->Subject($lng->txt("pwassist_mail_subject"));
	$mm->From($contact_address);
	$mm->To($userObj->getEmail());
	
	$mm->Body
	(
		str_replace
		(
			array("\\n","\\t"),
			array("\n","\t"),
			sprintf
			(
			$lng->txt("pwassist_mail_body"),
			$pwassist_url,
			$server_url,
			$_SERVER['REMOTE_ADDR'],
			$userObj->getLogin(),
			'mailto:'.$contact_address
			)
		)
	);
	
	$mm->Send();
}


/** Creates (or reuses) a password assistance session, and sends a password
 * assistance mail to the specified user.
 * 
 * Note: To prevent DOS attacks, a new session is created only, if no session
 * exists, or if the existing session has been expired.
 *
 * The password assistance mail contains an URL, which points to this script
 * and contains the following URL parameters:
 * client_id
 * key
 *
 * @param usrObj An instance of class.ilObjUserObject.php.
 */
function sendUsernameAssistanceMail($email, $logins)
{
	global $lng, $ilias;

	include_once "classes/class.ilMailbox.php";
	include_once "classes/class.ilMimeMail.php";
	require_once "include/inc.pwassist_session_handler.php";
	

	// Compose the mail
	$server_url='http://'.$_SERVER['HTTP_HOST'].
		substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/')).
		'/';
	$login_url=$server_url."pwassist.php"
				."?client_id=".$ilias->getClientId()
				."&lang=".$lng->getLangKey();
	
	$contact_address=$ilias->getSetting("admin_email");

	$mm = new ilMimeMail();
	$mm->Subject($lng->txt("pwassist_mail_subject"));
	$mm->From($contact_address);
	$mm->To($email);
	
	$mm->Body
	(
		str_replace
		(
			array("\\n","\\t"),
			array("\n","\t"),
			sprintf
			(
			$lng->txt("pwassist_username_mail_body"),
						join ($logins,",\n"), 
			$server_url, 
			$_SERVER['REMOTE_ADDR'], 
			$email,
			$login_url,
			'mailto:'.$contact_address
			
			)
		)
	);
	
	$mm->Send();
}

/* Assign password form.
 * This form is used to assign a password to a username.
 *
 * To use this form, the following data must be provided as HTTP GET parameter,
 * or in argument pwassist_id:
 * key
 *
 * The key is used to retrieve the password assistance session.
 * If the key is missing, or if the password assistance session has expired, the
 * password assistance form will be shown instead of this form.
 *
 * @param message  A message to display on the form.
 * @param username The user name to be shown in the form.
 * @param password The password1 to be shown in the form.
 * @param password The password2 to be shown in the form.
 * @param pwassist_id The session key for the password assistance use case.
 *                  If this parameter is omitted, the key is retrieved from
 *                  the form data.
 */
function showAssignPasswordForm($message="", $username="", $password1="", $password2="", $pwassist_id="")
{
	global $tpl, $ilias, $lng, $rbacadmin, $rbacreview;
	
	require_once "include/inc.pwassist_session_handler.php";
	require_once "classes/class.ilLanguage.php";
	
	// Retrieve form data
	if ($pwassist_id == "") 
	{
		$pwassist_id = $_GET["key"];
	}

	// Retrieve the session, and check if it is valid
	$pwassist_session = db_pwassist_session_read($pwassist_id);
	if (count($pwassist_session) == 0 || $pwassist_session["expires"] < time())
	{
		showAssistanceForm($lng->txt("pwassist_session_expired"));
	}
	else
	{
		$tpl->addBlockFile("CONTENT", "content", "tpl.pwassist_assignpassword.html");
		$tpl->setVariable("FORMACTION","pwassist.php?cmd=submitassign&lang=".$lng->getLangKey());
		$tpl->setVariable("TARGET","target=\"_parent\"");
		$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("password_assistance"));
		$tpl->setVariable("TXT_MESSAGE",str_replace("\\n","<br>",$message));
		$tpl->setVariable("TXT_ENTER_USERNAME_AND_NEW_PASSWORD", $lng->txt("pwassist_enter_username_and_new_password"));
		$tpl->setVariable("TXT_USERNAME", $lng->txt("username"));
		$tpl->setVariable("TXT_PASSWORD1", $lng->txt("password"));
		$tpl->setVariable("TXT_PASSWORD2", $lng->txt("retype_password"));
		$tpl->setVariable("USERNAME", $username);
		$tpl->setVariable("PASSWORD1", $password1);
		$tpl->setVariable("PASSWORD2", $password2);
		$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
		$tpl->setVariable("KEY", $pwassist_id);
		$tpl->setVariable("BACK", $lng->txt("back"));
		$tpl->setVariable("LANG", $lng->getLangKey());
	
		$tpl->show();
	}
}

/** Reads the submitted data from the password assistance form.
 * 
 * The following form fields are read as HTTP POST parameters:
 * key
 * username
 * password1
 * password2
 *
 * The key is used to retrieve the password assistance session.
 * If the key is missing, or if the password assistance session has expired, the
 * password assistance form will be shown instead of this form.
 *
 * If the password assistance session is valid, and if the username matches the
 * username, for which the password assistance has been requested, and if the
 * new password is valid, ILIAS assigns the password to the user.
 *
 * Note: To prevent replay attacks, the session is deleted when the
 * password has been assigned successfully.
 */
function submitAssignPasswordForm() {
	global $tpl, $ilias, $lng, $rbacadmin, $rbacreview;
	
	require_once "include/inc.pwassist_session_handler.php";
	
	// Retrieve form data
	$pwassist_id = $_POST["key"];
	$username = $_POST["username"];
	$password1 = $_POST["password1"];
	$password2 = $_POST["password2"];

	// Retrieve the session
	$pwassist_session = db_pwassist_session_read($pwassist_id);
	
	if (count($pwassist_session) == 0 || $pwassist_session["expires"] < time())
	{
		showAssistanceForm($lng->txt("pwassist_session_expired"));
	}
	else
	{
		$is_successful = true;
		$message = "";
		
		$userObj = new ilObjUser($pwassist_session["user_id"]);

		// Validate the entries of the user
		// ----------------------------------
		// check if the user still exists
		if ($userObj == null)
		{
			$message = $lng->txt("user_does_not_exist");
			$is_successful = false;
		}
		
		// check if the username entered by the user matches the
		// one of the user object.
		if ($is_successful && strcasecmp($userObj->getLogin(), $username) != 0)
		{
			$message = $lng->txt("pwassist_login_not_match");
			$is_successful = false;
		}
		
		// check if the user entered the password correctly into the
		// two entry fields.
		if ($is_successful && $password1 != $password2)
		{
			$message = $lng->txt("passwd_not_match");
			$is_successful = false;
		}

		// validate the password
		if ($is_successful && !ilUtil::isPassword($password1))
		{
			$message = $lng->txt("passwd_invalid");
			$is_successful = false;
		}
		
		// End of validation
		// If the validation was successful, we change the password of the
		// user.
		// ------------------
		if ($is_successful)
		{
			$is_successful = $userObj->resetPassword($password1,$password2);
			if (! $is_successful) 
			{
				$message = $lng->txt("passwd_invalid");
			}
		}

		// If we are successful so far, we update the user object.
		// ------------------
		if ($is_successful) 
		{
			$is_successfull = $userObj->update();
			if (! $is_successful) 
			{
				$message = $lng->txt("update_error");
			}
		}
		
		// If we are successful, we destroy the password assistance
		// session and redirect to the login page.
		// Else we display the form again along with an error message.
		// ------------------
		if ($is_successful)
		{
			db_pwassist_session_destroy($pwassist_id);
			showMessageForm
			(
				null,
				sprintf
				(
					$lng->txt("pwassist_password_assigned"),
					$username
				)
			);
		}
		else
		{
			showAssignPasswordForm
			(
				$message,
				$username,
				$password1,
				$password2,
				$pwassist_id
			);
		}	
	}
}

/* Message form.
 * This form is used to show a message to the user.
 */
function showMessageForm($message="", $text="")
{
	global $tpl, $ilias, $lng;
	

	$tpl->addBlockFile("CONTENT", "content", "tpl.pwassist_message.html");
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("password_assistance"));
	$tpl->setVariable("TXT_MESSAGE",str_replace("\\n","<br>",$message));
	$tpl->setVariable("TXT_TEXT",str_replace("\\n","<br>",$text));
	$tpl->setVariable("BACK", $lng->txt("back"));
	$tpl->setVariable("LANG", $lng->getLangKey());

	$tpl->show();
}

?>