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
	 * @param int $a_user_id 
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_handler, $a_user_id)
	{
		global $ilCtrl, $lng;

		$this->handler = $a_handler;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("il_tbl_wspsh");

		$this->setTitle($lng->txt("wsp_shared_resources"));

		$this->addColumn($this->lng->txt("content"));
	
		$this->setDefaultOrderField("content");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.list_row.html", "Modules/WorkspaceFolder");
		
		$this->setDisableFilterHiding(true);
		$this->setResetCommand("resetsharefilter");
		$this->setFilterCommand("applysharefilter");
		
		$this->importData($a_user_id);
	}
	
	/**
	 * Import data from DB
	 * 
	 * @param int $a_user_id
	 */
	protected function importData($a_user_id)
	{
		global $lng;
		
		$data = array();
		
		$objects = $this->handler->getSharedObjects($a_user_id);
		if($objects)
		{
			foreach($objects as $wsp_id => $obj_id)
			{
				$data[] = array(
					"wsp_id" => $wsp_id,
					"obj_id" => $obj_id,
					"type" => ilObject::_lookupType($obj_id),
					"title" => ilObject::_lookupTitle($obj_id)
					);					
			}
		}			
					
		$this->setData($data);
	}
	
	/**
	 * Fill table row
	 *
	 * @param array $a_set data array
	 */
	protected function fillRow($node)
	{
		global $objDefinition;
		
		$class = $objDefinition->getClassName($node["type"]);
		$location = $objDefinition->getLocation($node["type"]);
		$full_class = "ilObj".$class."ListGUI";

		include_once($location."/class.".$full_class.".php");
		$item_list_gui = new $full_class();
		
		$item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
		$item_list_gui->enableDelete(false);
		$item_list_gui->enableCut(false);		
		$item_list_gui->enableSubscribe(false);
		$item_list_gui->enablePayment(false);
		$item_list_gui->enableLink(false);
		$item_list_gui->enablePath(false);
		$item_list_gui->enableLinkedPath(false);
		$item_list_gui->enableSearchFragments(false);
		$item_list_gui->enableRelevance(false);
		$item_list_gui->enableIcon(true);
		// $item_list_gui->enableCheckbox(false);
		// $item_list_gui->setSeparateCommands(true);
		
		$item_list_gui->enableCopy($objDefinition->allowCopy($node["type"]));
		
		if($node["type"] == "file")
		{
			$item_list_gui->enableRepositoryTransfer(true);
		}

		$item_list_gui->setContainerObject($this->parent_obj);
		
		if($html = $item_list_gui->getListItemHTML($node["wsp_id"], $node["obj_id"],
				$node["title"], $node["description"], false, false, "", ilObjectListGUI::CONTEXT_WORKSPACE_SHARING))
		{
			$this->tpl->setVariable("ITEM_LIST_NODE", $html);
		} 
	}
}

?>