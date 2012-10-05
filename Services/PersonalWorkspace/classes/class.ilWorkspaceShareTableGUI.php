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
	 * @param int $a_parent_node_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_handler, $a_user_id, $a_parent_node_id)
	{
		global $ilCtrl, $lng;

		$this->handler = $a_handler;
		$this->parent_node_id = $a_parent_node_id;

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
		global $ilUser;
		
		$data = array();
		
		$objects = $this->handler->getSharedObjects($a_user_id);
		if($objects)
		{
			foreach($objects as $wsp_id => $obj_id)
			{
				// #9848: flag if current share access is password-protected 
				$perms = ilWorkspaceAccessHandler::getPermissions($wsp_id);
				$is_password = (!in_array($ilUser->getId(), $perms) &&
					!in_array(ilWorkspaceAccessGUI::PERMISSION_REGISTERED, $perms) &&
					!in_array(ilWorkspaceAccessGUI::PERMISSION_ALL, $perms) &&
					in_array(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, $perms));
													
				$data[] = array(
					"wsp_id" => $wsp_id,
					"obj_id" => $obj_id,
					"type" => ilObject::_lookupType($obj_id),
					"title" => ilObject::_lookupTitle($obj_id),
					"password" => $is_password
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
		global $objDefinition, $ilCtrl;
		
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
		$item_list_gui->restrictToGoto(true);
		$item_list_gui->enableInfoScreen(true);
		
		// files may be copied to own workspace
		if($node["type"] == "file")
		{
			$ilCtrl->setParameter($this->parent_obj, "wsp_id",
				$this->parent_node_id);									
			$ilCtrl->setParameter($this->parent_obj, "item_ref_id", 
				$node["wsp_id"]);
			$copy = $ilCtrl->getLinkTarget($this->parent_obj, "copy");
			$item_list_gui->addCustomCommand($copy, "copy");			
		}
		
		$item_list_gui->setContainerObject($this->parent_obj);
		
		if($node["password"])
		{			
			$item_list_gui->addCustomProperty($this->lng->txt("status"), 
				$this->lng->txt("wsp_password_protected_resource"), true, true);			
		}
		
		if($html = $item_list_gui->getListItemHTML($node["wsp_id"], $node["obj_id"],
				$node["title"], $node["description"], false, false, "", ilObjectListGUI::CONTEXT_WORKSPACE_SHARING))
		{
			$this->tpl->setVariable("ITEM_LIST_NODE", $html);
		} 
	}
}

?>