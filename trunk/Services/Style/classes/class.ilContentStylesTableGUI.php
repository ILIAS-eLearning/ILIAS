<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

/**
 * Content styles table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesStyle
 */
class ilContentStylesTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_data, $a_style_settings)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilSetting, $rbacsystem;

		$this->fixed_style = $ilSetting->get("fixed_content_style_id");
		$this->default_style = $ilSetting->get("default_content_style_id");

		$this->setId("sty_cs");
		$this->sty_settings = $a_style_settings;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData($a_data);
		$this->setTitle($lng->txt("content_styles"));

		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("title"));
		$this->addColumn($this->lng->txt("sty_nr_learning_modules"));
		$this->addColumn($this->lng->txt("purpose"));
		$this->addColumn($this->lng->txt("sty_scope"));
		$this->addColumn($this->lng->txt("active"));
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.content_style_row.html", "Services/Style");

		if ($rbacsystem->checkAccess("write",$this->parent_obj->object->getRefId()))
		{
			$this->addMultiCommand("deleteStyle", $lng->txt("delete"));
			$this->addCommandButton("saveActiveStyles", $lng->txt("sty_save_active_styles"));
		}
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $rbacsystem;

		if ($a_set["id"] > 0)
		{
			$this->tpl->setCurrentBlock("cb");
			$this->tpl->setVariable("ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("cb_act");
			if ($a_set["active"])
			{
				$this->tpl->setVariable("ACT_CHECKED", "checked='checked'");
			}
			$this->tpl->setVariable("ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("edit_link");
			$ilCtrl->setParameterByClass("ilobjstylesheetgui", "obj_id", $a_set["id"]);
			$this->tpl->setVariable("EDIT_LINK", $ilCtrl->getLinkTargetByClass("ilobjstylesheetgui", ""));
			$ilCtrl->setParameterByClass("ilobjstylesheetgui", "obj_id", "");
			$this->tpl->setVariable("EDIT_TITLE", $a_set["title"]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setVariable("TITLE", $a_set["title"]);
		}

		$ilCtrl->setParameter($this->parent_obj, "id", $a_set["id"]);
		if ($a_set["id"] > 0 && $rbacsystem->checkAccess("write",$this->parent_obj->object->getRefId()))
		{
			$list = new ilAdvancedSelectionListGUI();
			$list->setListTitle($lng->txt("actions"));
			$list->setId("sty_act_".$a_set["id"]);

			// default style
			if ($this->default_style == $a_set["id"])
			{
				$list->addItem($lng->txt("sty_remove_global_default_state"), "",
					$ilCtrl->getLinkTarget($this->parent_obj, "toggleGlobalDefault"));
			}
			else if ($a_set["active"])
			{
				$list->addItem($lng->txt("sty_make_global_default"), "",
					$ilCtrl->getLinkTarget($this->parent_obj, "toggleGlobalDefault"));
			}

			// fixed style
			if ($this->fixed_style == $a_set["id"])
			{
				$list->addItem($lng->txt("sty_remove_global_fixed_state"), "",
					$ilCtrl->getLinkTarget($this->parent_obj, "toggleGlobalFixed"));
			}
			else if ($a_set["active"])
			{
				$list->addItem($lng->txt("sty_make_global_fixed"), "",
					$ilCtrl->getLinkTarget($this->parent_obj, "toggleGlobalFixed"));
			}
			$list->addItem($lng->txt("sty_set_scope"), "",
				$ilCtrl->getLinkTarget($this->parent_obj, "setScope"));

			$this->tpl->setVariable("ACTIONS", $list->getHTML());

			if ($a_set["id"] == $this->fixed_style)
			{
				$this->tpl->setVariable("PURPOSE", $lng->txt("global_fixed"));
			}
			if ($a_set["id"] == $this->default_style)
			{
				$this->tpl->setVariable("PURPOSE", $lng->txt("global_default"));
			}

		}
		$ilCtrl->setParameter($this->parent_obj, "id", "");

		$this->tpl->setVariable("NR_LM", $a_set["lm_nr"]);

		if ($a_set["category"] > 0)
		{
			$this->tpl->setVariable("SCOPE",
				ilObject::_lookupTitle(
				ilObject::_lookupObjId($a_set["category"])
				));
		}
	}

}
?>
