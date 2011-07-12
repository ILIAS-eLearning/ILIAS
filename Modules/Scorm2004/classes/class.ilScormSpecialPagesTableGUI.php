<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Scorm special pages table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesScormAicc
 */
class ilScormSpecialPagesTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_slm)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
die("deprecated");
		$this->slm = $a_slm;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->getSpecialPages();
		$this->setTitle($lng->txt("cont_special_pages"));
		$this->setLimit(9999);

		$this->addColumn("", "", "1");
		$this->addColumn($this->lng->txt("cont_purpose"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.scorm_sp_row.html", "Modules/Scorm2004");
//		$this->disable("footer");
//		$this->setEnableTitle(true);

		$this->addMultiCommand("confirmSpecialPageDeletion", $lng->txt("delete"));
	}


	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$ilCtrl->setParameterByClass("ilscorm2004pagenodegui",
			"obj_id", $a_set["page_id"]);
		$this->tpl->setCurrentBlock("action");
		$this->tpl->setVariable("HREF_CMD",
			$ilCtrl->getLinkTargetByClass("ilscorm2004pagenodegui", "edit"));
		$this->tpl->setVariable("TXT_CMD",
			$lng->txt("edit"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("PAGE_ID", $a_set["page_id"]);
		$this->tpl->setVariable("VAL_TITLE", $a_set["purpose"]);
	}

}
?>
