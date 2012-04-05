<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Table/classes/class.ilTable2GUI.php');

/**
* Table for object role permissions
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesObject
*/
class ilObjectOwnershipManagementTableGUI extends ilTable2GUI
{
	protected $user_id; // [int]

	public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id, array $a_data = null)
	{
		global $ilCtrl, $lng;
		
		$this->user_id = (int)$a_user_id;
		
		parent::__construct($a_parent_obj,$a_parent_cmd);
		
		$this->setId('objownmgmt');
		
		$this->addColumn($lng->txt("title"), "title");
		$this->addColumn($lng->txt("path"), "");
		$this->addColumn($lng->txt("action"), "");

		// $this->setTitle($this->lng->txt(''));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.obj_ownership_row.html", "Services/Object");
		$this->setDisableFilterHiding(true);
		
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		if($a_data)
		{
			$this->initItems($a_data);
		}
	}
	
	protected function initItems($a_data)
	{		
		global $ilAccess, $lng, $tree;
				
		$data = array();

		foreach($a_data as $id => $item)
		{
			// workspace objects won't have references
			$refs = ilObject::_getAllReferences($id);
			if($refs)
			{			
				$readable = array();
				foreach($refs as $idx => $ref_id)
				{						
					// objects in trash are hidden
					$item_ref_id = false;
					if(!$tree->isDeleted($ref_id))
					{
						$readable[$ref_id] = $ilAccess->checkAccessOfUser($this->user_id, "read", "", $ref_id, $a_type);	
						if($readable[$ref_id] && !$item_ref_id)
						{
							$item_ref_id = $ref_id;
						}
					}
					else
					{
						unset($refs[$idx]);
					}
				}
				
				if($refs)
				{
					$data[$id] = array("obj_id" => $id,
						"ref_id" => $ref_id,
						"type" => ilObject::_lookupType($id),
						"title" => $item,
						"path" => $this->buildPath($refs, $readable),
						"readable" => max($readable));	
				}
			}														
		}

		$this->setData($data);			
	}
	
	public function fillRow($row)
	{			
		global $lng; 
	
		$this->tpl->setCurrentBlock("path");
		foreach($row["path"] as $item)
		{
			$this->tpl->setVariable("PATH_ITEM", $item);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("TITLE", $row["title"]);
		$this->tpl->setVariable("ALT_ICON", $lng->txt("obj_".$row["type"]));
		$this->tpl->setVariable("SRC_ICON", ilObject::_getIcon("", "tiny", $row["type"]));
		
		if($row["readable"])
		{
			$this->tpl->setCurrentBlock("actions");								
			$this->tpl->setVariable("ACTIONS", $this->buildActions($row["ref_id"], $row["type"]));		
			$this->tpl->parseCurrentBlock();
		}
	}
	
	protected function buildActions($a_ref_id, $a_type)
	{
		global $lng, $ilCtrl, $objDefinition;
		
		include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
		$agui = new ilAdvancedSelectionListGUI();
		$agui->setId($this->id."-".$a_ref_id);
		$agui->setListTitle($lng->txt("actions"));
		
		$ilCtrl->setParameter($this->parent_obj, "ownid", $a_ref_id);
		
		$agui->addItem($lng->txt("change_owner"), "", 
			$ilCtrl->getLinkTarget($this->parent_obj, "changeOwner"),
			"", "", "_blank");		
		
		if($objDefinition->allowExport($a_type))
		{
			$agui->addItem($lng->txt("export"), "", 
				$ilCtrl->getLinkTarget($this->parent_obj, "export"),
				"", "", "_blank");
		}
		
		$agui->addItem($lng->txt("delete"), "", 
			$ilCtrl->getLinkTarget($this->parent_obj, "delete"),
			"", "", "_blank");
		
		$ilCtrl->setParameter($this->parent_obj, "ownid", "");
							
		return $agui->getHTML();
	}
	
	protected function buildPath($a_ref_ids, array $a_readable)
	{
		global $tree, $lng, $ilCtrl;

		include_once './Services/Link/classes/class.ilLink.php';
		
		if(!count($a_ref_ids))
		{
			return false;
		}
		
		$result = array();
		foreach($a_ref_ids as $ref_id)
		{
			$path = "...";
			$counter = 0;
			$path_full = $tree->getPathFull($ref_id);
			foreach($path_full as $data)
			{
				if(++$counter < (count($path_full)-2))
				{
					continue;
				}
				$path .= " &raquo; ";
				if($ref_id != $data['ref_id'])
				{
					$path .= $data['title'];
				}
				else
				{					
					if($a_readable[$data['ref_id']])
					{				
						$path .= '<a target="_blank" href="'.
								ilLink::_getLink($data['ref_id'],$data['type']).'">'.
								$lng->txt("show").'</a>';
						
						$ilCtrl->setParameter($this->parent_obj, "ownid", $ref_id);
						
						$path .= ' | <a target="_blank" href="'.
								$ilCtrl->getLinkTarget($this->parent_obj, "move").'">'.
								$lng->txt("move").'</a>';
						
						$ilCtrl->setParameter($this->parent_obj, "ownid", "");
					}					
				}
			}

			$result[] = $path;
		}
		
		return $result;
	}
}

?>