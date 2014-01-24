<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for tabs
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTabsTableGUI extends ilTable2GUI
{

	function ilPCTabsTableGUI($a_parent_obj, $a_parent_cmd,
		$a_tabs)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "", "1");
		$this->addColumn($lng->txt("cont_position"), "", "1");
		$this->addColumn($lng->txt("title"), "", "100%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.tabs_row.html",
			"Services/COPage");
			
		$this->tabs = $a_tabs;
		$caps = $this->tabs->getCaptions();
		$this->setData($this->tabs->getCaptions());
		$this->setLimit(0);
		
		$this->addMultiCommand("confirmTabsDeletion", $lng->txt("delete"));
		$this->addCommandButton("saveTabs", $lng->txt("save"));
		
		$this->setTitle($lng->txt("cont_tabs"));
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->pos += 10;
		$this->tpl->setVariable("POS", ilUtil::prepareFormOutput($this->pos));
		$this->tpl->setVariable("TID", $a_set["hier_id"].":".$a_set["pc_id"]);
		$this->tpl->setVariable("VAL_CAPTION", ilUtil::prepareFormOutput($a_set["caption"]));
	}

}
?>