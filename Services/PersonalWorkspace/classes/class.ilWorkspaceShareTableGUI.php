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
	protected $parent_node_id; // [int]

	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj parent gui object
	 * @param string $a_parent_cmd parent default command
	 * @param object $a_handler workspace access handler
	 * @param int $a_parent_node_id
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_handler, $a_parent_node_id)
	{
		global $ilCtrl, $lng;

		$this->handler = $a_handler;
		$this->parent_node_id = $a_parent_node_id;

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("il_tbl_wspsh");

		$this->setTitle($lng->txt("wsp_shared_resources"));

		$this->addColumn($this->lng->txt("lastname"), "lastname");
		$this->addColumn($this->lng->txt("firstname"), "firstname");		
		$this->addColumn($this->lng->txt("login"), "login");
		$this->addColumn($this->lng->txt("type"), "obj_type");
		$this->addColumn($this->lng->txt("wsp_shared_date"), "acl_date");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("wsp_shared_type"));
		$this->addColumn($this->lng->txt("action"));
	
		$this->setDefaultOrderField("content");
		$this->setDefaultOrderDirection("asc");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.shared_row.html", "Services/PersonalWorkspace");
		
		$this->setDisableFilterHiding(true);
		$this->setResetCommand("resetsharefilter");
		$this->setFilterCommand("applysharefilter");
		
		$this->importData();
	}
	
	/**
	 * Import data from DB
	 * 
	 * @param int $a_user_id
	 */
	protected function importData()
	{
		global $lng;
		
		$data = array();
		
		$user_data = array();
		
		$objects = $this->handler->findSharedObjects();
		if($objects)
		{
			foreach($objects as $wsp_id => $item)
			{				
				if(!isset($user_data[$item["owner"]]))
				{
					$user_data[$item["owner"]] = ilObjUser::_lookupName($item["owner"]);
				}				
				
				$data[] = array(
					"wsp_id" => $wsp_id,
					"obj_id" => $item["obj_id"],
					"type" => $item["type"],
					"obj_type" => $lng->txt("wsp_type_".$item["type"]),
					"title" => $item["title"],
					"owner_id" => $item["owner"], 
					"lastname" => $user_data[$item["owner"]]["lastname"],
					"firstname" => $user_data[$item["owner"]]["firstname"],
					"login" => $user_data[$item["owner"]]["login"],
					"acl_type" => $item["acl_type"],
					"acl_date" => $item["acl_date"],
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
		global $ilCtrl, $lng;
				
		$this->tpl->setVariable("LASTNAME", $node["lastname"]);
		$this->tpl->setVariable("FIRSTNAME", $node["firstname"]);		
		$this->tpl->setVariable("LOGIN", $node["login"]);
		
		$this->tpl->setVariable("TYPE", $node["obj_type"]);
		$this->tpl->setVariable("ICON_ALT", $node["obj_type"]);
		$this->tpl->setVariable("ICON", ilObject::_getIcon("", "tiny", $node["type"]));			
		
		$this->tpl->setVariable("TITLE", $node["title"]);
		$this->tpl->setVariable("URL_TITLE", 
			$this->handler->getGotoLink($node["wsp_id"], $node["obj_id"]));
		
		$this->tpl->setVariable("ACL_DATE", ilDatePresentation::formatDate(new ilDateTime($node["acl_date"], IL_CAL_UNIX))); 
						
		foreach($node["acl_type"] as $obj_id)
		{
			// see ilWorkspaceAccessTableGUI
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
			
			if($icon)
			{
				$this->tpl->setCurrentBlock("acl_type_icon_bl");
				$this->tpl->setVariable("ACL_ICON", $icon);
				$this->tpl->setVariable("ACL_ICON_ALT", $icon_alt);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("acl_type_bl");
			$this->tpl->setVariable("ACL_TYPE", $title);
			$this->tpl->parseCurrentBlock();
		}
		
		// files may be copied to own workspace
		if($node["type"] == "file")
		{
			$ilCtrl->setParameter($this->parent_obj, "wsp_id",
				$this->parent_node_id);									
			$ilCtrl->setParameter($this->parent_obj, "item_ref_id", 
				$node["wsp_id"]);
			$url = $ilCtrl->getLinkTarget($this->parent_obj, "copyshared");
			
			$this->tpl->setCurrentBlock("action_bl");
			$this->tpl->setVariable("URL_ACTION", $url);
			$this->tpl->setVariable("ACTION", $lng->txt("copy"));
			$this->tpl->parseCurrentBlock();
		}
	}
}

?>