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
   * soap server
   *
   * @author Stefan Meyer <smeyer@databay.de>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/lib/nusoap.php';

// These functions are wrappers for nusoap, since it cannot register methods inside classes
function login($client,$username,$password)
{
	$sua =& new ilSoapUserAdministration();
	
	return $sua->login($client,$username,$password);
}

function logout($sid)
{
	$sua =& new ilSoapUserAdministration();

	return $sua->logout($sid);
}
function lookupUser($sid,$user_name)
{
	$sua =& new ilSoapUserAdministration();

	return $sua->lookupUser($sid,$user_name);
}

function getUser($sid,$user_id)
{
	$sua =& new ilSoapUserAdministration();

	return $sua->getUser($sid,$user_id);
}

function updateUser($sid,$user_data)
{
	$sua =& new ilSoapUserAdministration();

	return $sua->updateUser($sid,$user_data);
}
function addUser($sid,$user_data,$global_role_id)
{
	$sua =& new ilSoapUserAdministration();

	return $sua->addUser($sid,$user_data,$global_role_id);
}
function deleteUser($sid,$user_id)
{
	$sua =& new ilSoapUserAdministration();

	return $sua->deleteUser($sid,$user_id);
}

class ilSoapUserAdministration
{
	/*
	 * object which handles php's authentication
	 * @var object
	 */
	var $sauth = null;

	/*
	 * Defines type of error handling (PHP5 || NUSOAP)
	 * @var object
	 */
	var $error_method = null;


	function ilSoapUserAdministration($use_nusoap = true)
	{
		define('USER_FOLDER_ID',7);
		define('NUSOAP',1);
		define('PHP5',2);

		if($use_nusoap)
		{
			$this->error_method = NUSOAP;
		}
	}
		

	// Service methods
	function login($client,$username,$password)
	{
		$this->__initAuthenticationObject();

		$this->sauth->setClient($client);
		$this->sauth->setUsername($username);
		$this->sauth->setPassword($password);

		if(!$this->sauth->authenticate())
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		return $this->sauth->getSid().'::'.$client;
	}

	function logout($sid)
	{
		list($sid,$client) = $this->__explodeSid($sid);

		$this->__initAuthenticationObject();

		$this->sauth->setClient($client);
		$this->sauth->setSid($sid);


		if(!$this->sauth->logout())
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		
		return true;
	}
	
	function lookupUser($sid,$user_name)
	{
		list($sid,$client) = $this->__explodeSid($sid);

		$this->__initAuthenticationObject();

		$this->sauth->setClient($client);
		$this->sauth->setSid($sid);

		if(!$this->sauth->validateSession())
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		if(!strlen($user_name))
		{
			return $this->__raiseError('No username given. Aborting','Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		if(!$rbacsystem->checkAccess('read',USER_FOLDER_ID))
		{
			return $this->__raiseError('Check access failed.','Server');
		}

		return (int) ilObjUser::getUserIdByLogin($user_name);
	}

	function getUser($sid,$user_id)
	{
		list($sid,$client) = $this->__explodeSid($sid);

		$this->__initAuthenticationObject();

		$this->sauth->setClient($client);
		$this->sauth->setSid($sid);

		if(!$this->sauth->validateSession())
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		// Include main header
		include_once './include/inc.header.php';

		if(!$rbacsystem->checkAccess('read',USER_FOLDER_ID))
		{
			return $this->__raiseError('Check access failed.','Server');
		}

		global $ilUser;

		if($ilUser->getLoginByUserId($user_id))
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($user_id);
			$usr_data = $this->__readUserData($tmp_user);

			return $usr_data;
		}
		return $this->__raiseError('User does not exist','Client');
	}		

	function updateUser($sid,$user_data)
	{
		list($sid,$client) = $this->__explodeSid($sid);


		$this->__initAuthenticationObject();

		$this->sauth->setClient($client);
		$this->sauth->setSid($sid);

		if(!$this->sauth->validateSession())
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			
		
		// Include main header
		include_once './include/inc.header.php';

		if(!$rbacsystem->checkAccess('write',USER_FOLDER_ID))
		{
			return $this->__raiseError('Check access failed.','Server');
		}

		global $ilUser;

		if(!$user_obj =& ilObjectFactory::getInstanceByObjId($user_data['usr_id'],false))
		{
			return $this->__raiseError('User with id '.$user_data['usr_id'].' does not exist.','Client');
		}

		$user_old = $this->__readUserData($user_obj);
		$user_new = $this->__substituteUserData($user_old,$user_data);

		if(!$this->__validateUserData($user_data,false))
		{
			return $this->__raiseError($this->__getMessage(),'Client');
		}

		if(strlen($user_data['passwd']))
		{
			$user_obj->setPasswd($user_data['passwd'],IL_PASSWD_PLAIN);
		}
		$this->__setUserData($user_obj,$user_new);

		$log->write('SOAP: updateUser()');
		$user_obj->update();

		return true;
	}		


	function addUser($sid,$user_data,$global_role_id)
	{
		list($sid,$client) = $this->__explodeSid($sid);

		$this->__initAuthenticationObject();

		$this->sauth->setClient($client);
		$this->sauth->setSid($sid);
		if(!$this->sauth->validateSession())
		{
			return $this->__raiseError($this->sauth->getMessage(),'Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		if(!$rbacsystem->checkAccess('create_user',USER_FOLDER_ID))
		{
			return $this->__raiseError('Check access failed.','Server');
		}

		// Validate user_data
		if(!$this->__validateUserData($user_data))
		{
			return $this->__raiseError($this->__getMessage(),'Client');
		}
		// Validate global role
		if(!$global_role_id)
		{
			return $this->__raiseError('No role id given','Client');
		}

		// Validate global role
		global $rbacreview;
		
		$global_roles = $rbacreview->getGlobalRoles();

		if(!in_array($global_role_id,$global_roles))
		{
			return $this->__raiseError('Role with id: '.$global_role_id.' is not a valid global role','Client');
		}

		$new_user =& new ilObjUser();

		$new_user->setPasswd($user_data['passwd'],IL_PASSWD_PLAIN);
		$this->__setUserData($new_user,$user_data);


		$log->write('SOAP: addUser()');
		$new_user->create();
		$new_user->saveAsNew();

		// Assign role
		$rbacadmin->assignUser($global_role_id,$new_user->getId());

		// Assign user prefs
		$new_user->setLanguage($user_data['user_language']);
		$new_user->setPref('style',$user_data['style']);
		$new_user->setPref('skin',$user_data['skin']);
		$new_user->writePrefs();

		return $new_user->getId();
	}

	function deleteUser($sid,$user_id)
	{
		list($sid,$client) = $this->__explodeSid($sid);

		$this->__initAuthenticationObject();

		$this->sauth->setClient($client);
		$this->sauth->setSid($sid);
		if(!$this->sauth->validateSession())
		{
			return $this->__raiseError($this->sauth->getMessage(),'Client');
		}
		
		if(!isset($user_id))
		{
			return $this->__raiseError('No user_id given. Aborting','Client');
		}

		// Include main header
		include_once './include/inc.header.php';

		if(!$rbacsystem->checkAccess('delete',USER_FOLDER_ID))
		{
			return $this->__raiseError('Check access failed.','Server');
		}

		global $ilUser;

		if(!$ilUser->getLoginByUserId($user_id))
		{
			return $this->__raiseError('User id: '.$user_id.' is not a valid identifier. Aborting','Client');
		}
		if($ilUser->getId() == $user_id)
		{
			return $this->__raiseError('Cannot delete myself. Aborting','Client');
		}
		if($user_id == SYSTEM_USER_ID)
		{
			return $this->__raiseError('Cannot delete root account. Aborting','Client');
		}
		// Delete him
		$log->write('SOAP: deleteUser()');
		$delete_user =& ilObjectFactory::getInstanceByObjId($user_id,false);
		$delete_user->delete();

		return true;
	}
		
		
	// PRIVATE
	function __explodeSid($sid)
	{
		$exploded = explode('::',$sid);

		return is_array($exploded) ? $exploded : array('sid' => '','client' => '');
	}


	function __setMessage($a_str)
	{
		$this->message = $a_str;
	}
	function __getMessage()
	{
		return $this->message;
	}
	function __appendMessage($a_str)
	{
		$this->message .= isset($this->message) ? ' ' : '';
		$this->message .= $a_str;
	}

	function __validateUserData(&$user_data,$check_complete = true)
	{
		global $lng,$styleDefinition;

		$this->__setMessage('');
		
		if($check_complete)
		{
			if(!isset($user_data['login']))
			{
				$this->__appendMessage('No login given.');
			}
			if(!isset($user_data['passwd']))
			{
				$this->__appendMessage('No password given.');
			}
			if(!isset($user_data['email']))
			{
				$this->__appendMessage('No email given');
			}
			if(!isset($user_data['user_language']))
			{
				$user_data['user_language'] = 'en';
			}
		}
		foreach($user_data as $field => $value)
		{
			switch($field)
			{
				case 'login':
					if (!ilUtil::isLogin($value))
					{
						$this->__appendMessage('Login invalid.');
					}

					// check loginname
					if($check_complete)
					{
						if (loginExists($value))
						{
							$this->__appendMessage('Login already exists.');
						}
					}
					break;

				case 'passwd':
					if (!ilUtil::isPassword($value))
					{
						$this->__appendMessage('Password invalid.');
					}
					break;

				case 'email':
					if(!ilUtil::is_email($value))
					{
						$this->__appendMessage('Email invalid.');
					}
					break;

				case 'time_limit_unlimited':
					if($value != 1)
					{
						if($user_data['time_limit_from'] >= $user_data['time_limit_until'])
						{
							$this->__appendMessage('Time limit invalid');
						}
					}
					break;

				case 'user_language':
					$lang_inst = $lng->getInstalledLanguages();

					if(!in_array($user_data['user_language'],$lang_inst))
					{
						$this->__appendMessage('Language: '.$user_data['user_language'].' is not installed');
					}
					break;


				case 'user_skin':
				case 'user_style':
					if(($user_data['user_skin'] and !$user_data['user_style']) or
					   (!$user_data['user_skin'] and $user_data['user_style']))
					{
						$this->__appendMessage('user_skin, user_style not valid.');
					}
					elseif($user_data['user_skin'] and $user_data['user_style'])
					{
						$ok = false;
						foreach($styleDefinition->getAllTemplates() as $template)
						{
							$styleDef =& new ilStyleDefinition($template["id"]);
							$styleDef->startParsing();
							$styles = $styleDef->getStyles();
							foreach ($styles as $style)
							{
								if ($user_data['user_skin'] == $template["id"] &&
									$user_data['user_style'] == $style["id"])
								{
									$ok = true;
								}
							}
						}
						if(!$ok)
						{
							$this->__appendMessage('user_skin, user_style not valid.');
						}
					}
					break;

				default:
					continue;
			}
		}
		return strlen($this->__getMessage()) ? false : true;
	}

	function __setUserData(&$user_obj,&$user_data)
	{
		// Default to unlimited if no access period is given
		if(!$user_data['time_limit_from'] and 
		   !$user_data['time_limit_until'] and
		   !$user_data['time_limit_unlimited'])
		{
			$user_data['time_limit_unlimited'] = 1;
		}
		$user_obj->assignData($user_data,IL_NO_PASSWD);

		if(isset($user_data['user_language']))
		{
			$user_obj->setLanguage($user_data['user_language']);
		}
		if(isset($user_data['user_skin']) and isset($user_data['user_style']))
		{
			$user_obj->setPref('skin',$user_data['skin']);
			$user_obj->setPref('style',$user_data['style']);
		}
		return true;
	}



	function __initAuthenticationObject()
	{
		include_once './webservice/soap/classes/class.ilSoapAuthentication.php';
		
		return $this->sauth = new ilSoapAuthentication();
	}
		

	function __raiseError($a_message,$a_code)
	{
		switch($this->error_method)
		{
			case NUSOAP:

				return new soap_fault($a_code,'',$a_message);
		}
	}

	
	function __readUserData(&$usr_obj)
	{
		$usr_data['usr_id'] = $usr_obj->getId();
		$usr_data['login'] = $usr_obj->getLogin();
		$usr_data['passwd'] = $usr_obj->getPasswd();
		$usr_data['firstname'] = $usr_obj->getFirstname();
		$usr_data['lastname'] = $usr_obj->getLastname();
		$usr_data['title'] = $usr_obj->getUTitle();
		$usr_data['gender'] = $usr_obj->getGender();
		$usr_data['email'] = $usr_obj->getEmail();
		$usr_data['institution'] = $usr_obj->getInstitution();
		$usr_data['street'] = $usr_obj->getStreet();
		$usr_data['city'] = $usr_obj->getCity();
		$usr_data['zipcode'] = $usr_obj->getZipcode();
		$usr_data['country'] = $usr_obj->getCountry();
		$usr_data['phone_office'] = $usr_obj->getPhoneOffice();
		$usr_data['last_login'] = $usr_obj->getLastLogin();
		$usr_data['last_update'] = $usr_obj->getLastUpdate();
		$usr_data['create_date'] = $usr_obj->getCreateDate();
		$usr_data['hobby'] = $usr_obj->getHobby();
		$usr_data['department'] = $usr_obj->getDepartment();
		$usr_data['phone_home'] = $usr_obj->getPhoneHome();
		$usr_data['phone_mobile'] = $usr_obj->getPhoneMobile();
		$usr_data['fax'] = $usr_obj->getFax();
		$usr_data['time_limit_owner'] = $usr_obj->getTimeLimitOwner();
		$usr_data['time_limit_unlimited'] = $usr_obj->getTimeLimitUnlimited();
		$usr_data['time_limit_from'] = $usr_obj->getTimeLimitFrom();
		$usr_data['time_limit_until'] = $usr_obj->getTimeLimitUntil();
		$usr_data['time_limit_message'] = $usr_obj->getTimeLimitMessage();
		$usr_data['referral_commment'] = $usr_obj->getComment();
		$usr_data['matriculation'] = $usr_obj->getMatriculation();
		$usr_data['active'] = $usr_obj->getActive();
		$usr_data['approve_date'] = $usr_obj->getApproveDate();
		$usr_data['user_skin'] = $usr_obj->getPref('skin');
		$usr_data['user_style'] = $usr_obj->getPref('style');
		$usr_data['user_language'] = $usr_obj->getLanguage();
		
		return $usr_data;
	}

	function __substituteUserData($user_old,$user_new)
	{
		foreach($user_new as $key => $value)
		{
			$user_old[$key] = $value;
		}
		return $user_old ? $user_old : array();
	}
}
?>
