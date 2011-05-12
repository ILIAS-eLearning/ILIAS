<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Workspace access handler table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCountryTableGUI.php 27876 2011-02-25 16:51:38Z jluetzen $
 *
 * @ingroup ServicesPersonalWorkspace
 */
class ilWorkspaceAccessTableGUI extends ilTable2GUI
{
	protected $node_id; // [int]
	protected $handler; // [ilWorkspaceAccessHandler]

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param int $a_node_id current workspace object
	 * @param object $a_handler workspace access handler
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_node_id, $a_handler)
	{
		global $ilCtrl, $lng;

		$this->node_id = $a_node_id;
		$this->handler = $a_handler;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("il_tbl_wsacl");

		$this->setTitle($lng->txt("permission"));

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("type"), "type");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.access_row.html", "Services/PersonalWorkspace");

		$this->importData();
	}

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		$data = array();
		foreach($this->handler->getPermissions($this->node_id) as $obj_id)
		{
			$data[] = array("id" => $obj_id,
				"title" => ilObject::_lookupTitle($obj_id),
				"type" => ilObject::_lookupType($obj_id));
		}
	
		$this->setData($data);
	}
	
	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		
		// properties
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("TYPE", $lng->txt("obj_".$a_set["type"]));

		$ilCtrl->setParameter($this->parent_obj, "obj_id", $a_set["id"]);
		$this->tpl->setVariable("HREF_CMD",
			$ilCtrl->getLinkTarget($this->parent_obj, "removePermission"));
		$this->tpl->setVariable("TXT_CMD", $lng->txt("remove"));
	}
}

?>