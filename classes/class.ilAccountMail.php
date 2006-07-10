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
* Class ilAccountMail
*
* Sends e-mail to newly created accounts.
*
* @author Stefan Schneider <stefan.schneider@hrz.uni-giessen.de>
* @author Alex Killing <alex.killing@hrz.uni-giessen.de>
*
*/
class ilAccountMail
{
	/**
	* user password 
	* @var	string	user password (plain text)
	* @access	private
	*/
	var $u_password = "";

	/**
	* user object (instance of ilObjUser)
	* @var	object
	* @access	private
	*/
	var $user = "";

	/**
	* repository item target (e.g. "crs_123" 
	* @var	string	target
	* @access	private
	*/
	var $target = "";

	var $lng;
	
	/**
	* constructor
	* @access	public 
	*/
	function ilObjAccountMail()
	{		
		global $lng;

		$this->lng =& $lng;
	}
	
	/**
	* set user password
	*
	* @access	public
	* @param	string	$a_pwd		users password as plain text
	*/
	function setUserPassword($a_pwd)
	{		
		$this->u_password = $a_pwd;
	}

	/**
	* get user password
	*
	* @access	public
	* @return	string		users password as plain text
	*/
	function getUserPassword()
	{		
		return $this->u_password;
	}

	/**
	* Set user. The user object should provide email, language
	* login, gender, first and last name
	*
	* @access	public
	* @param	object	$a_user		user object
	*/
	function setUser(&$a_user)
	{		
		$this->user =& $a_user;
	}

	/**
	* get user object
	*
	* @access	public
	* @return	object		user object
	*/
	function &getUser()
	{		
		return $this->user;
	}

	/**
	* set repository item target
	*
	* @access	public
	* @param	string	$a_target		target as used in permanent links, e.g. crs_123
	*/
	function setTarget($a_target)
	{		
		$this->u_target = $a_target;
	}

	/**
	* get target
	*
	* @access	public
	* @return	string		repository item target
	*/
	function getTarget()
	{		
		return $this->target;
	}

	/**
	* reset all values
	*/
	function reset()
	{
		$this->u_password = "";
		$this->user = "";
		$this->target = "";
	}
	
	/**
	* get new account mail array (including subject and message body)
	*/
	function readAccountMail($a_lang)
	{
		if (!is_array($this->amail[$a_lang]))
		{
			include_once("classes/class.ilObjUserFolder.php");
			$this->amail[$a_lang] = ilObjUserFolder::_lookupNewAccountMail($a_lang);
			$amail["body"] = trim($amail["body"]);
			$amail["subject"] = trim($amail["subject"]);
		}

		return $this->amail[$a_lang];
	}
	
	/**
	* Sends the mail with its object properties as MimeMail
	* It first tries to read the mail body, subject and sender address from posted named formular fields. 
	* If no field values found the defaults are used.
	* Placehoders will be replaced by the appropriate data.
	* @access	public
	* @param object ilUser
	*/
	function send()
	{
		global $lng, $ilSetting;
		
		$user =& $this->getUser();
		
		if (!$user->getEmail())
		{
			return false;
		}
		
		// determine language and get account mail data
		// fall back to default language if acccount mail data is not given for user language.
		$amail = $this->readAccountMail($user->getLanguage());
		if ($amail["body"] == "" || $amail["subject"] == "")
		{
			$amail = $this->readAccountMail($lng->getDefaultLanguage());
		}

		// replace placeholders
		$mail_subject = $this->replacePlaceholders($amail["subject"], $user, $amail);
		$mail_body = $this->replacePlaceholders($amail["body"], $user, $amail);
		
		// send the mail
		include_once "classes/class.ilMimeMail.php";
		$mmail = new ilMimeMail();
		$mmail->autoCheck(false);
		$mmail->From($ilSetting->get("admin_email"));																		
		$mmail->Subject($mail_subject);
		$mmail->To($user->getEmail());
		$mmail->Body($mail_body);
/*
echo "<br><br><b>From</b>:".$ilSetting->get("admin_email");
echo "<br><br><b>To</b>:".$user->getEmail();
echo "<br><br><b>Subject</b>:".$mail_subject;
echo "<br><br><b>Body</b>:".$mail_body;
return true;*/
		$mmail->Send();
		
		return true;
	}
	
	function replacePlaceholders($a_string, &$a_user, $a_amail)
	{
		global $ilSetting;
		
		// determine salutation
		$gender_salut = ($a_user->getGender() == "f")
			? $a_amail["sal_f"]
			: ($a_user->getGender() == "m")
				? $a_amail["sal_m"]
				: $a_amail["sal_g"];

		$a_string = str_ireplace("[MAIL_SALUTATION]", $gender_salut, $a_string);
		$a_string = str_ireplace("[LOGIN]", $a_user->getLogin(), $a_string);
		$a_string = str_ireplace("[FIRST_NAME]", $a_user->getFirstname(), $a_string);
		$a_string = str_ireplace("[LAST_NAME]", $a_user->getLastname(), $a_string);
		$a_string  = str_ireplace("[PASSWORD]", $this->getUserPassword(), $a_string);
		$a_string  = str_ireplace("[ILIAS_URL]",
			ILIAS_HTTP_PATH."/login.php&client_id=".CLIENT_ID, $a_string);
		$a_string  = str_ireplace("[CLIENT_NAME]", CLIENT_NAME, $a_string);
		$a_string  = str_ireplace("[ADMIN_MAIL]", $ilSetting->get("admin_email"),
			$a_string);

		return $a_string;
	}
		
}
?>
