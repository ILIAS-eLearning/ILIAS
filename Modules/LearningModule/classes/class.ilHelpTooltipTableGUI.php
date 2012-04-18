<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/Help/classes/class.ilHelpMapping.php");


/**
 * Help mapping
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilHelpTooltipTableGUI extends ilTable2GUI
{
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_comp)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setId("lm_help_tooltips");

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		include_once("./Services/Help/classes/class.ilHelp.php");
		$this->setData(ilHelp::getAllTooltips($a_comp));

		$this->setTitle($lng->txt("help_tooltips"));

		$this->addColumn("", "", "1px", true);
		$this->addColumn($this->lng->txt("help_tooltip_id"));
		$this->addColumn($this->lng->txt("help_tt_text"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.help_tooltip.html", "Modules/LearningModule");
		$this->setDefaultOrderField("tt_id");
		$this->setDefaultOrderDirection("asc");

		$this->addCommandButton("saveTooltips", $lng->txt("save"));
		$this->addMultiCommand("deleteTooltips", $lng->txt("delete"));
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("ID", $a_set["id"]);
		$this->tpl->setVariable("TEXT", ilUtil::prepareFormOutput($a_set["text"]));
		$this->tpl->setVariable("TT_ID", ilUtil::prepareFormOutput($a_set["tt_id"]));
		
	}

}

?>