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
* Class ilObjAccountMail
*
* Sends e-mail to newly created accounts.
*
* @author Stefan Schneider <stefan.schneider@hrz.uni-giessen.de>
* @author Alex Killing <alex.killing@hrz.uni-giessen.de>
*
*/
class ilObjAccountMail
{
	/**
	* object language 
	* @var string langauge_key property
	* @access	private
	*/
	var $object_lang;
	
	/**
	*  mail body with placeholders
	* @var string body property
	* @access	private
	*/
	var $account_mail_body;
	
	/**
	*  mail subject with placeholders
	* @var string subject property
	* @access	private
	*/
	var $account_mail_subject;
	
	/**
	*  mail sender with placeholders
	* @var string sender property
	* @access	private
	*/
	var $account_mail_sender;
	
	/**
	*  users password as plain text
	* @var string password property
	* @access	private
	*/
	var $account_user_password;	
	
	/**
	* reference to the included array of language and client dependant mail_account texts
	* @var reference array $account_mail_lang
	* @access	private
	*/
	var $account_mail_array;
		
	var $ilias;
	var $lng;
	var $ilUser;
	
	/**
	* constructor
	* @access	public
	* @param	optional string language key 
	*/
	function ilObjAccountMailGUI($a_lang='')
	{		
		include CLIENT_WEB_DIR.'/accountmail/accountmail.php';
		global $ilias,$lng,$ilUser;
		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->ilUser =& $ilUser;
		$this->object_lang = ($a_lang!='') ? $a_lang : $this->lng->lang_key;
		$this->account_mail_array =& $account_mail_lang;
		$this->account_mail_body = $this->account_mail_array['body_'.$this->object_lang];
		$this->account_mail_subject = $this->account_mail_array['subject_'.$this->object_lang];
		$this->account_mail_sender = $this->ilUser->getEmail();
		$this->account_user_password = "************";
	}
	
	/**
	* Prepares a mail for serial transmission by setting its object properties
	* It first tries to read the mail body, subject and sender address from posted named formular fields. 
	* If no field values found the defaults are used.
	* @access	public
	*/
	function prepare()
	{		
		$this->account_mail_body = (isset($_POST["txt_account_mail_body"]))? $_POST["txt_account_mail_body"] : $this->getBody();
		$this->account_mail_subject = (isset($_POST["txt_account_mail_subject"]))? $_POST["txt_account_mail_subject"] : $this->getSubject();
		$this->account_mail_sender = (isset($_POST["send_with_system_address"]))? $this->ilias->getSetting('feedback_recipient') : $this->ilUser->getEmail();
	}
	
	/**
	* Sends the mail with its object properties as MimeMail
	* It first tries to read the mail body, subject and sender address from posted named formular fields. 
	* If no field values found the defaults are used.
	* Placehoders will be replaced by the appropriate data.
	* @access	public
	* @param object ilUser
	*/
	function send($user_object)
	{
		if (!isset($_POST["send_account_data"])) return '';
		include_once "classes/class.ilMimeMail.php";		
		$gender_salut = ($user_object->getGender() == "f") ? $this->account_mail_array['salut_f_'.$this->object_lang] : $this->account_mail_array['salut_m_'.$this->object_lang]; 
		$gender_salut .= " " . $user_object->getTitle();
		$mail_subject = "[" . CLIENT_NAME . "] ". $this->account_mail_subject;
		$mail_body = str_ireplace("[MAIL_SALUTATION]", $gender_salut, $this->account_mail_body);
		$mail_body = str_ireplace("[LOGIN]", $user_object->getLogin(), $mail_body);		
		$mail_body  = str_ireplace("[PASSWORD]", $this->account_user_password , $mail_body);
		$mmail = new ilMimeMail();
		$mmail->autoCheck(false);
		$mmail->From($this->account_mail_sender);																		
		$mmail->Subject($mail_subject);
		$mmail->To($user_object->getEmail());
		$mmail->Body($mail_body);
		$mmail->Send();
		return $this->lng->txt("mail_sent");
	}
	
	/**
	* Reads the body text from the client and language dependant array	
	* If no language param is passed, the language of the current user is used
	* @access	public
	* @param optional string language key
	*/
	function getBody($lang='')
	{		
		return ($lang != '') ? $this->account_mail_array['body_'.$lang] : $this->account_mail_body;
	}
	
	/**
	* Reads the subject text from the client and language dependant array	
	* If no language param is passed, the language of the current user is used
	* @access	public
	* @param optional string language key
	*/
	function getSubject($lang='')
	{		
		return ($lang != '') ? $this->account_mail_array['subject_'.$lang] : $this->account_mail_subject;
	}
	
	/**
	* Sets the password property of its object	
	* Whereas the login name can be read from the passed user object in the send() method, 
	* the password must be passed explicitly as plain text before the mail body can be proceeded
	* @access	public
	* @param optional string users password as plain text
	*/
	function setPassword($a_pwd)
	{		
		$this->account_user_password = $a_pwd;
	}
}
?>
