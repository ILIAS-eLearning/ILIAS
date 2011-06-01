<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Workspace share handler table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCountryTableGUI.php 27876 2011-02-25 16:51:38Z jluetzen $
 *
 * @ingroup ServicesPersonalWorkspace
 */
class ilWorkspaceShareTableGUI extends ilTable2GUI
{
	protected $handler; // [ilWorkspaceAccessHandler]

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param object $a_handler workspace access handler
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_handler)
	{
		global $ilCtrl, $lng;

		$this->handler = $a_handler;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("il_tbl_wspsh");

		$this->setTitle($lng->txt("wsp_shared_resources"));

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("type"), "type");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.share_row.html", "Services/PersonalWorkspace");
		
		$this->setDisableFilterHiding(true);
		$this->setResetCommand("resetsharefilter");
		$this->setFilterCommand("applysharefilter");
		
		$this->initFilter();

		$this->importData();
	}
	
	public function initFilter()
	{
		global $lng;
				
		$users = $this->handler->getSharedOwners();
	
		// user selection
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($lng->txt("user"), "user");
		$si->setOptions(array(""=>"-")+$users);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["user"] = $si->getValue();				
	}			

	/**
	 * Import data from DB
	 */
	protected function importData()
	{
		global $lng;
		
		$data = array();
		
		if($this->filter["user"])
		{
			$objects = $this->handler->getSharedObjects($this->filter["user"]);
			if($objects)
			{
				foreach($objects as $obj_id)
				{
					$data[] = array(
						"id" => $obj_id,
						"type" => $lng->txt("obj_".ilObject::_lookupType($obj_id)),
						"title" => ilObject::_lookupTitle($obj_id)
						);					
				}
			}			
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
		global $ilCtrl;
		
		// properties
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("TYPE", $a_set["type"]);

		/*
		$ilCtrl->setParameter($this->parent_obj, "obj_id", $a_set["id"]);
		$this->tpl->setVariable("HREF_CMD",
			$ilCtrl->getLinkTarget($this->parent_obj, "removePermission"));
		$this->tpl->setVariable("TXT_CMD", $this->lng->txt("remove"));		
		*/
	}
}

?>