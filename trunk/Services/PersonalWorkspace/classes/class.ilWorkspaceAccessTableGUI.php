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

		$this->setTitle($lng->txt("wsp_shared_with"));
		
		$this->addColumn($this->lng->txt("type"), "type");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("type");
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
		include_once("./Services/User/classes/class.ilUserUtil.php");
		
		$data = array();
		foreach($this->handler->getPermissions($this->node_id) as $obj_id)
		{				
			switch($obj_id)
			{
				case ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
					$title = $icon_alt = $this->lng->txt("wsp_set_permission_registered");
					$type = "registered";
					$icon = "";
					break;
				
				case ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
					$title = $icon_alt = $this->lng->txt("wsp_set_permission_all_password");
					$type = "all_password";
					$icon = "";
					break;
				
				case ilWorkspaceAccessGUI::PERMISSION_ALL:
					$title = $icon_alt = $this->lng->txt("wsp_set_permission_all");
					$type = "all_password";
					$icon = "";
					break;	
												
				default:
					$type = ilObject::_lookupType($obj_id);
					$icon = ilUtil::getTypeIconPath($type, null, "tiny");
					$icon_alt = $this->lng->txt("obj_".$type);	
					
					if($type != "usr")
					{					
						$title = ilObject::_lookupTitle($obj_id);											
					}
					else
					{						
						$title = ilUserUtil::getNamePresentation($obj_id, true, true); 
					}
					break;
			}
			
			$data[] = array("id" => $obj_id,
				"title" => $title,
				"type" => $type,
				"icon" => $icon,
				"icon_alt" => $icon_alt);
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
		$this->tpl->setVariable("ICON", $a_set["icon"]);
		$this->tpl->setVariable("ICON_ALT", $a_set["icon_alt"]);

		$ilCtrl->setParameter($this->parent_obj, "obj_id", $a_set["id"]);
		$this->tpl->setVariable("HREF_CMD",
			$ilCtrl->getLinkTarget($this->parent_obj, "removePermission"));
		$this->tpl->setVariable("TXT_CMD", $this->lng->txt("remove"));
	}
}

?>