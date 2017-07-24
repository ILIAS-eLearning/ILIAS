<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/User/Actions/classes/class.ilUserActionProvider.php");

/**
 * Group user actions (add to group)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGroup
 */
class ilGroupUserActionProvider extends ilUserActionProvider
{

	/**
	 * @inheritdoc
	 */
	function getComponentId()
	{
		return "grp";
	}

	/**
	 * @inheritdoc
	 */
	function getActionTypes()
	{
		return array(
			"add_to" => $this->lng->txt("grp_add_to_group")
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
		global $DIC;

		$ctrl = $DIC->ctrl();

		$coll = ilUserActionCollection::getInstance();

		$f = new ilUserAction();
		$f->setType("add_to");
		$f->setText($this->lng->txt("grp_add_to_group"));
		$f->setHref("#");
		$f->setData(array(
			"grp-action-add-to" => "1",
			"url" => $ctrl->getLinkTargetByClass(array("ilPersonalDesktopGUI", "ilGroupUserActionsGUI", "ilGroupAddToGroupActionGUI"), "", "", true, false)
		));
		$coll->addAction($f);

		return $coll;
	}

	/**
	 * Get js scripts
	 *
	 * @param string $a_action_type
	 * @return array
	 */
	function getJsScripts($a_action_type)
	{
		switch ($a_action_type)
		{
			case "add_to":
				return array(
					"./Modules/Group/UserActions/js/GroupUserActions.js",
					"./Services/UIComponent/Modal/js/Modal.js"
				);
				break;
		}
		return array();
	}

}
?>