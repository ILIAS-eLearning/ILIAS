<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for taxonomy list
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilTaxonomyListTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_assigned_object_id, $a_info = null)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->assigned_object_id = $a_assigned_object_id;
		
		include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
		$this->setData(ilObjTaxonomy::getUsageOfObject($this->assigned_object_id, true));
		$this->setTitle($lng->txt("obj_taxf"));		
		$this->setDescription($a_info);
		
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.taxonomy_list_row.html", "Services/Taxonomy");

		//$this->addMultiCommand("", $lng->txt(""));
		//$this->addCommandButton("", $lng->txt(""));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$ilCtrl->setParameter($this->parent_obj, "tax_id", $a_set["tax_id"]);
		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "listNodes"));
		$this->tpl->setVariable("CMD", $lng->txt("edit"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("cmd");
		$this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "confirmDeleteTaxonomy"));
		$this->tpl->setVariable("CMD", $lng->txt("delete"));
		$this->tpl->parseCurrentBlock();
		$ilCtrl->setParameter($this->parent_obj, "tax_id", $_GET["tax_id"]);

		$this->tpl->setVariable("TITLE", $a_set["title"]);
	}

}
?>
