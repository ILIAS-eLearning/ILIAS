<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Important pages table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilImportantPagesTableGUI extends ilTable2GUI
{
	
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setData(ilObjWiki::_lookupImportantPagesList($a_parent_obj->object->getId()));
		$this->setTitle($lng->txt(""));
		$this->setLimit(9999);
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("wiki_ordering"), "order");
		$this->addColumn($this->lng->txt("wiki_indentation"));
		$this->addColumn($this->lng->txt("wiki_page"));
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.imp_pages_row.html", "Modules/Wiki");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		
		$this->addMultiCommand("confirmRemoveImportantPages", $lng->txt("remove"));
		$this->addCommandButton("saveOrderingAndIndent", $lng->txt("wiki_save_ordering_and_indent"));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("PAGE_ID", $a_set["page_id"]);
		$this->tpl->setVariable("VAL_ORD", $a_set["ord"]);
		$this->tpl->setVariable("PAGE_TITLE",
			ilWikiPage::lookupTitle($a_set["page_id"]));
		$this->tpl->setVariable("SEL_INDENT",
			ilUtil::formSelect($a_set["indent"], "indent[".$a_set["page_id"]."]",
			array(0 => "0", 1 => "1", 2 => "2"), false, true));
	}
	
}
?>
