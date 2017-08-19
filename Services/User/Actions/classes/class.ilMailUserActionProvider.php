<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/User/Actions/classes/class.ilUserActionProvider.php");

/**
 * Adds link to mail
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilMailUserActionProvider extends ilUserActionProvider
{
	static $user_access = array();

	/**
	 * Check user chat access
	 *
	 * @param
	 * @return
	 */
	function checkUserMailAccess($a_user_id)
	{
		global $rbacsystem;

		if (!isset(self::$user_access[$a_user_id]))
		{
			include_once("./Services/Mail/classes/class.ilMailGlobalServices.php");
			self::$user_access[$a_user_id] =
				$rbacsystem->checkAccessOfUser($a_user_id, 'internal_mail', ilMailGlobalServices::getMailObjectRefId());
		}
		return self::$user_access[$a_user_id];
	}

	/**
	 * @inheritdoc
	 */
	function getComponentId()
	{
		return "mail";
	}

	/**
	 * @inheritdoc
	 */
	function getActionTypes()
	{
		return array(
			"compose" => $this->lng->txt("mail")
		);
	}

	/**
	 * Collect user actions
	 *
	 * @param int $a_target_user target user
	 * @return ilUserActionCollection collection
	 */
	function collectActionsForTargetUser($a_target_user)
	{
		$coll = ilUserActionCollection::getInstance();
		include_once("./Services/User/Actions/classes/class.ilUserAction.php");
		include_once("./Services/Mail/classes/class.ilMailFormCall.php");

		// check mail permission of user
		if ($this->getUserId() == ANONYMOUS_USER_ID || !$this->checkUserMailAccess($this->getUserId()))
		{
			return $coll;
		}

		// check mail permission of target user
		if ($this->checkUserMailAccess($a_target_user))
		{
			$f = new ilUserAction();
			$f->setType("compose");
			$f->setText($this->lng->txt("mail"));
			$tn = ilObjUser::_lookupName($a_target_user);
			$f->setHref(ilMailFormCall::getLinkTarget("", '', array(), array('type' => 'new', 'rcp_to' => $tn["login"])));
			$coll->addAction($f);
		}

		return $coll;
	}
}
?>