<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings UI class for system styles
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesStyle
 */
class ilSystemStyleSettingsGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ILIAS\DI\Container
	 */
	protected $DIC;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC, $ilIliasIniFile;

		$this->ini = $ilIliasIniFile;
		$this->dic = $DIC;
		$this->ctrl = $DIC->ctrl();
		$this->rbacsystem = $DIC->rbac()->system();
		$this->toolbar = $DIC->toolbar();
		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];
		$this->tree = $DIC["tree"];

		$this->ref_id = (int) $_GET["ref_id"];
	}


	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("edit");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("edit", "moveUserStyles", "saveStyleSettings",
					"assignStylesToCats", "addStyleCatAssignment", "saveStyleCatAssignment", "deleteSysStyleCatAssignments")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Check permission
	 *
	 * @param string $a_perm permission(s)
	 * @return bool
	 * @throws ilObjectException
	 */
	function checkPermission($a_perm, $a_throw_exc = true)
	{
		if (!$this->rbacsystem->checkAccess($a_perm, $this->ref_id))
		{
			if ($a_throw_exc)
			{
				include_once "Services/Object/exceptions/class.ilObjectException.php";
				throw new ilObjectException($this->lng->txt("permission_denied"));
			}
			return false;
		}
		return true;
	}

	/**
	 * Edit
	 */
	function edit()
	{
		$this->checkPermission("visible,read");

		// default skin/style
		if ($this->checkPermission("sty_write_system", false))
		{
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$options = array();
			foreach (ilStyleDefinition::getAllSkinStyles() as $st)
			{
				$options[$st["id"]] = $st["title"];
			}

			// from styles selector
			$si = new ilSelectInputGUI($this->lng->txt("sty_move_user_styles").": ".$this->lng->txt("sty_from"), "from_style");
			$si->setOptions($options + array("other" => $this->lng->txt("other")));
			$this->toolbar->addInputItem($si, true);

			// from styles selector
			$si = new ilSelectInputGUI($this->lng->txt("sty_to"), "to_style");
			$si->setOptions($options);
			$this->toolbar->addInputItem($si, true);
			$this->toolbar->addFormButton($this->lng->txt("sty_move_style"), "moveUserStyles");

			$this->toolbar->setFormAction($this->ctrl->getFormAction($this));
		}

		include_once("./Services/Style/System/classes/class.ilSystemStylesTableGUI.php");
		$tab = new ilSystemStylesTableGUI($this, "editSystemStyles");
		$this->tpl->setContent($tab->getHTML());

	}

	/**
	 * Move user styles
	 */
	function moveUserStyles()
	{
		$this->checkPermission("sty_write_system");

		$to = explode(":", $_POST["to_style"]);

		if ($_POST["from_style"] != "other")
		{
			$from = explode(":", $_POST["from_style"]);
			ilObjUser::_moveUsersToStyle($from[0],$from[1],$to[0],$to[1]);
		}
		else
		{
			// get all user assigned styles
			$all_user_styles = ilObjUser::_getAllUserAssignedStyles();

			include_once("./Services/Style/System/classes/class.ilStyleDefinition.php");
			$all_styles = ilStyleDefinition::getAllSkinStyles();

			// move users that are not assigned to
			// currently existing style
			foreach($all_user_styles as $style)
			{
				if (isset($all_styles[$style]))
				{
					$style_arr = explode(":", $style);
					ilObjUser::_moveUsersToStyle($style_arr[0],$style_arr[1],$to[0],$to[1]);
				}
			}
		}

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this , "edit");
	}


	/**
	 * Save skin and style settings
	 */
	function saveStyleSettings()
	{
		$this->checkPermission("sty_write_system");

		// check if one style is activated
		if (count($_POST["st_act"]) < 1)
		{
			ilUtil::sendFailure($this->lng->txt("at_least_one_style"), true);
			$this->ctrl->redirect($this, "edit");
		}

		//set default skin and style
		if ($_POST["default_skin_style"] != "")
		{
			$sknst = explode(":", $_POST["default_skin_style"]);

			if ($this->ini->readVariable("layout","style") != $sknst[1] ||
				$this->ini->readVariable("layout","skin") != $sknst[0])
			{
				$this->ini->setVariable("layout","skin", $sknst[0]);
				$this->ini->setVariable("layout","style",$sknst[1]);
			}
			$this->ini->write();
		}

		// check if a style should be deactivated, that still has
		// a user assigned to
		$all_styles = ilStyleDefinition::getAllSkinStyles();
		foreach ($all_styles as $st)
		{
			if (!isset($_POST["st_act"][$st["id"]]))
			{
				if (ilObjUser::_getNumberOfUsersForStyle($st["template_id"], $st["style_id"]) > 1)
				{
					ilUtil::sendFailure($this->lng->txt("cant_deactivate_if_users_assigned"), true);
					$this->ctrl->redirect($this, "edit");
				}
				else
				{
					include_once("./Services/Style/System/classes/class.ilSystemStyleSettings.php");
					ilSystemStyleSettings::_deactivateStyle($st["template_id"], $st["style_id"]);
				}
			}
			else
			{
				include_once("./Services/Style/System/classes/class.ilSystemStyleSettings.php");
				ilSystemStyleSettings::_activateStyle($st["template_id"], $st["style_id"]);
			}
		}

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this , "edit");
	}

	////
	//// Substyles
	////

	/**
	 * Assign styles to categories
	 *
	 * @param
	 * @return
	 */
	function assignStylesToCats()
	{
		$this->ctrl->setParameter($this, "style_id", urlencode($_GET["style_id"]));

		$this->checkPermission("sty_write_system");

		$all_styles = ilStyleDefinition::getAllSkinStyles();
		$sel_style = $all_styles[$_GET["style_id"]];

		$options = array();
		if (is_array($sel_style["substyle"]))
		{
			foreach ($sel_style["substyle"] as $subst)
			{
				$options[$subst["id"]] = $subst["name"];
			}
		}

		// substyle
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($this->lng->txt("sty_substyle"), "substyle");
		$si->setOptions($options);
		$this->toolbar->addInputItem($si, true);

		$this->toolbar->addFormButton($this->lng->txt("sty_add_assignment"), "addStyleCatAssignment");
		$this->toolbar->setFormAction($this->ctrl->getFormAction($this));

		include_once("./Services/Style/System/classes/class.ilSysStyleCatAssignmentTableGUI.php");
		$tab = new ilSysStyleCatAssignmentTableGUI($this, "assignStylesToCats");

		$this->tpl->setContent($tab->getHTML());
	}


	/**
	 * Add style category assignment
	 *
	 * @param
	 * @return
	 */
	function addStyleCatAssignment()
	{
		$this->checkPermission("sty_write_system");

		$this->ctrl->setParameter($this, "style_id", urlencode($_GET["style_id"]));
		$this->ctrl->setParameter($this, "substyle", urlencode($_REQUEST["substyle"]));

		include_once 'Services/Search/classes/class.ilSearchRootSelector.php';
		$exp = new ilSearchRootSelector(
			$this->ctrl->getLinkTarget($this,'addStyleCatAssignment'));
		$exp->setExpand($_GET["search_root_expand"] ? $_GET["search_root_expand"] : $this->tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'addStyleCatAssignment'));
		$exp->setTargetClass(get_class($this));
		$exp->setCmd('saveStyleCatAssignment');
		$exp->setClickableTypes(array("cat"));

		// build html-output
		$exp->setOutput(0);
		$this->tpl->setContent($exp->getOutput());
	}


	/**
	 * Save style category assignment
	 *
	 * @param
	 * @return
	 */
	function saveStyleCatAssignment()
	{
		$this->checkPermission("sty_write_system");

		$this->ctrl->setParameter($this, "style_id", urlencode($_GET["style_id"]));

		$style_arr = explode(":", $_GET["style_id"]);
		ilStyleDefinition::writeSystemStyleCategoryAssignment($style_arr[0], $style_arr[1],
			$_GET["substyle"], $_GET["root_id"]);
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

		$this->ctrl->redirect($this, "assignStylesToCats");
	}

	/**
	 * Delete system style to category assignments
	 */
	function deleteSysStyleCatAssignments()
	{
		$this->checkPermission("sty_write_system");

		$this->ctrl->setParameter($this, "style_id", urlencode($_GET["style_id"]));
		$style_arr = explode(":", $_GET["style_id"]);
		if (is_array($_POST["id"]))
		{
			foreach ($_POST["id"] as $id)
			{
				$id_arr = explode(":", $id);
				ilStyleDefinition::deleteSystemStyleCategoryAssignment($style_arr[0], $style_arr[1],
					$id_arr[0], $id_arr[1]);
			}
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}

		$this->ctrl->redirect($this, "assignStylesToCats");
	}

}

?>