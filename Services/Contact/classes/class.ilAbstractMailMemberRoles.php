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

	public final function getMailboxRoleAddress($role_id)
	{
		/**
		 * @var $rbacreview ilRbacReview
		 */
		global $rbacreview;

		require_once 'Services/AccessControl/classes/class.ilObjRole.php';
		return $rbacreview->getRoleMailboxAddress($role_id);
	}
}