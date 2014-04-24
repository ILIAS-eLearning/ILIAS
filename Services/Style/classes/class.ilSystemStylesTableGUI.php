<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for system styles
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesStyle
 */
class ilSystemStylesTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->getStyles();
//		$this->setTitle($lng->txt(""));

		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("default"));
		$this->addColumn($this->lng->txt("users"));
		$this->addColumn($this->lng->txt("active"));
		$this->addColumn($this->lng->txt("sty_substyles"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.sys_styles_row.html", "Services/Style");

		if ($rbacsystem->checkAccess("write", (int) $_GET["ref_id"]))
		{
			$this->addCommandButton("saveStyleSettings", $lng->txt("save"));
		}
	}
	
	/**
	 * Get styles
	 *
	 * @param
	 * @return
	 */
	function getStyles()
	{
		global $styleDefinition;
		
		$all_styles = ilStyleDefinition::getAllSkinStyles();

		// get all user assigned styles
		$all_user_styles = ilObjUser::_getAllUserAssignedStyles();
		
		// output "other" row for all users, that are not assigned to
		// any existing style
		$users_missing_styles = 0;
		foreach($all_user_styles as $style)
		{
			if (!isset($all_styles[$style]))
			{
				$style_arr = explode(":", $style);
				$users_missing_styles += ilObjUser::_getNumberOfUsersForStyle($style_arr[0], $style_arr[1]);
			}
		}

		if ($users_missing_styles > 0)
		{
			$all_styles["other"] =
				array (
					"title" => $this->lng->txt("other"),
					"id" => "other",
					"template_id" => "",
					"style_id" => "",
					"template_name" => "",
					"style_name" => "",
					"users" => $users_missing_styles
					);
		}


		$this->setData($all_styles);
	}
	
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilClientIniFile, $ilCtrl;

		$cat_ass = ilStyleDefinition::getSystemStyleCategoryAssignments($a_set["template_id"],
			$a_set["style_id"]);

		if (is_array($a_set["substyle"]))
		{
			foreach ($a_set["substyle"] as $substyle)
			{
				reset($cat_ass);
				$cats = false;
				foreach($cat_ass as $ca)
				{
					if ($ca["substyle"] == $substyle["id"])
					{
						$this->tpl->setCurrentBlock("cat");
						$this->tpl->setVariable("CAT", ilObject::_lookupTitle(
							ilObject::_lookupObjId($ca["ref_id"])));
						$this->tpl->parseCurrentBlock();
						$cats = true;
					}
				}
				if ($cats)
				{
					$this->tpl->touchBlock("cats");
				}
				
				$this->tpl->setCurrentBlock("substyle");
				$this->tpl->setVariable("SUB_STYLE", $substyle["name"]);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->touchBlock("substyles");
			
			$ilCtrl->setParameter($this->parent_obj, "style_id", urlencode($a_set["id"]));
			$this->tpl->setCurrentBlock("cmd");
			$this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj,
				"assignStylesToCats"));
			$this->tpl->setVariable("TXT_CMD", $lng->txt("sty_assign_categories"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("ID", $a_set["id"]);
		
		// number of users
		$this->tpl->setVariable("USERS", $a_set["users"]);

		// activation
		if (ilObjStyleSettings::_lookupActivatedStyle($a_set["template_id"], $a_set["style_id"]))
		{
			$this->tpl->setVariable("CHECKED", ' checked="checked" ');
		}

		if ($ilClientIniFile->readVariable("layout","skin") == $a_set["template_id"] &&
			$ilClientIniFile->readVariable("layout","style") == $a_set["style_id"])
		{
			$this->tpl->setVariable("CHECKED_DEFAULT", ' checked="checked" ');
		}

	}

}
?>
