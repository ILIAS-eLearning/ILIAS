<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilAbstractMailMemberRoles
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
abstract class ilAbstractMailMemberRoles
{
	/**
	 * @param int $ref_id
	 * @return array
	 */
	abstract public function getMailRoles($ref_id);
	
	/**
	 * @return string
	 */
	abstract public function getRadioOptionTitle();
	
	/**
	 * @param int $role_id
	 * @return string 
	 */
	public final function getMailboxRoleAddress($role_id)
	{
		global $rbacreview, $ilSetting, $ilObjDataCache;

		include_once './Services/AccessControl/classes/class.ilObjRole.php';		
		$role_addr = $rbacreview->getRoleMailboxAddress($role_id);

		// check if role title is unique. if not force use pear mail for roles
		$ids_for_role_title = ilObject::_getIdsForTitle(ilObject::_lookupTitle($role_id), 'role');
		if(count($ids_for_role_title) >= 2)
		{
			$ilSetting->set('pear_mail_enable', 1);
		}

		if(ilMail::_usePearMail() && substr($role_addr, 0, 4) != '#il_')
		{
			// if pear mail is enabled, mailbox addresses are already localized in the language of the user
			$mailbox =  $role_addr;
		}
		else
		{
			// if pear mail is not enabled, we need to localize mailbox addresses in the language of the user
			$mailbox = ilObjRole::_getTranslation($ilObjDataCache->lookupTitle($role_id)) . " (" . $role_addr . ")";
		}

		return $mailbox;
	}
}