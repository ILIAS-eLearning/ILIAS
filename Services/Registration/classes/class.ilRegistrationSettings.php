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


define('IL_REG_DISABLED',1);
define('IL_REG_DIRECT',2);
define('IL_REG_APPROVE',3);
define('IL_REG_ACTIVATION',4);

define('IL_REG_ROLES_FIXED',1);
define('IL_REG_ROLES_EMAIL',2);

define('IL_REG_ERROR_UNKNOWN',1);
define('IL_REG_ERROR_NO_PERM',2);

/**
* Class ilObjAuthSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @ingroup ServicesRegistration
*/
class ilRegistrationSettings
{
	private $reg_hash_life_time = 0;
	
	function ilRegistrationSettings()
	{
		$this->__read();
	}

	function getRegistrationType()
	{
		return $this->registration_type;
	}
	function setRegistrationType($a_type)
	{
		$this->registration_type = $a_type;
	}

	function _lookupRegistrationType()
	{
		global $ilias;

		return $ilias->getSetting('new_registration_type',IL_REG_DISABLED);
	}

	function enabled()
	{
		return $this->registration_type != IL_REG_DISABLED;
	}
	function directEnabled()
	{
		return $this->registration_type == IL_REG_DIRECT;
	}
	function approveEnabled()
	{
		return $this->registration_type == IL_REG_APPROVE;
	}
	public function activationEnabled()
	{
		return $this->registration_type == IL_REG_ACTIVATION;
	}
	
	function passwordGenerationEnabled()
	{
		return $this->password_generation_enabled;
	}
	function setPasswordGenerationStatus($a_status)
	{
		$this->password_generation_enabled = $a_status;
	}
	
	function getAccessLimitation()
	{
		return $this->access_limitation;
	}

	function setAccessLimitation($a_access_limitation)
	{
		$this->access_limitation = $a_access_limitation;
	}

	function setApproveRecipientLogins($a_rec_string)
	{
		$this->approve_recipient_logins = $a_rec_string;
		$this->approve_recipient_ids = array();

		// convert logins to array of ids
		foreach(explode(',',trim($this->approve_recipient_logins)) as $login)
		{
			if($uid = ilObjUser::_lookupId(trim($login)))
			{
				$this->approve_recipient_ids[] = $uid;
			}
		}
	}
	function getApproveRecipientLogins()
	{
		return $this->approve_recipient_logins;
	}
	function getApproveRecipients()
	{
		return $this->approve_recipient_ids ? $this->approve_recipient_ids : array();
	}
	function getUnknown()
	{
		return implode(',',$this->unknown);
	}

	function roleSelectionEnabled()
	{
		return $this->role_type == IL_REG_ROLES_FIXED;
	}
	function automaticRoleAssignmentEnabled()
	{
		return $this->role_type == IL_REG_ROLES_EMAIL;
	}
	function setRoleType($a_type)
	{
		$this->role_type = $a_type;
	}
	
	public function setRegistrationHashLifetime($a_lifetime)
	{
		$this->reg_hash_life_time = $a_lifetime;
		
		return $this;
	}
	
	public function getRegistrationHashLifetime()
	{
		return $this->reg_hash_life_time;
	}
	
	function validate()
	{
		global $ilAccess;

		$this->unknown = array();
		$this->mail_perm = array();

		$login_arr = explode(',',$this->getApproveRecipientLogins());
		$login_arr = $login_arr ? $login_arr : array();
		foreach($login_arr as $recipient)
		{
			if(!$recipient = trim($recipient))
			{
				continue;
			}
			if(!ilObjUser::_lookupId($recipient))
			{
				$this->unknown[] = $recipient;
				continue;
			}
		}
		return count($this->unknown) ? 1 : 0;
	}

			
	function save()
	{
		global $ilias;

		$ilias->setSetting('reg_role_assignment',$this->role_type);
		$ilias->setSetting('new_registration_type',$this->registration_type);
		$ilias->setSetting('passwd_reg_auto_generate',$this->password_generation_enabled);
		$ilias->setSetting('approve_recipient',addslashes(serialize($this->approve_recipient_ids)));
		$ilias->setSetting('reg_access_limitation',$this->access_limitation);
		$ilias->setSetting('reg_hash_life_time',$this->reg_hash_life_time);

		return true;
	}

	function __read()
	{
		global $ilias;

		$this->registration_type = $ilias->getSetting('new_registration_type');
		$this->role_type = $ilias->getSetting('reg_role_assignment',1);
		$this->password_generation_enabled = $ilias->getSetting('passwd_reg_auto_generate');
		$this->access_limitation = $ilias->getSetting('reg_access_limitation');
		$this->reg_hash_life_time = $ilias->getSetting('reg_hash_life_time');
		
		$this->approve_recipient_ids = unserialize(stripslashes($ilias->getSetting('approve_recipient')));
		$this->approve_recipient_ids = $this->approve_recipient_ids ? 
			$this->approve_recipient_ids : 
			array();

		// create login array
		$tmp_logins = array();
		foreach($this->approve_recipient_ids as $id)
		{
			if($login = ilObjUser::_lookupLogin($id))
			{
				$tmp_logins[] = $login;
			}
		}
		$this->approve_recipient_logins = implode(',',$tmp_logins);
	}
}
?>