<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Classification/classes/class.ilClassificationProvider.php");

/**
 * Taxonomy classification provider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesTaxonomy
 */
class ilTaxonomyClassificationProvider extends ilClassificationProvider
{			
	protected static $valid_tax_map = array();
	
	public static function isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)
	{		
		return (bool)self::getActiveTaxonomiesForParentRefId($a_parent_ref_id);
	}	
	
	public function render(array &$a_html, $a_parent_gui, $a_parent_cmd, $a_target_gui, $a_target_cmd)
	{			
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");	
	
		foreach(self::$valid_tax_map[$this->parent_ref_id] as $tax_id)
		{
			$tax_exp = new ilTaxonomyExplorerGUI($a_parent_gui, $a_parent_cmd, 
				$tax_id, $a_target_gui, $a_target_cmd);
			if (!$tax_exp->handleCommand())
			{			
				$tax_exp->setSkipRootNode(true);
				
				$a_html[] = array(
					"title" => ilObject::_lookupTitle($tax_id),
					"html" => $tax_exp->getHTML()
				);
			}					
		}									
	}
	
	protected static function getActiveTaxonomiesForParentRefId($a_parent_ref_id)
	{
		global $tree;
		
		if(!isset(self::$valid_tax_map[$a_parent_ref_id]))
		{				
			include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
			include_once "Services/Taxonomy/classes/class.ilObjTaxonomy.php";
			include_once "Modules/Category/classes/class.ilObjCategoryGUI.php";
						
			$prefix = ilObjCategoryGUI::CONTAINER_SETTING_TAXBLOCK;

			$all_valid = array();
			foreach($tree->getPathFull($a_parent_ref_id) as $node)
			{			
				if($node["type"] == "cat")
				{				
					$node_valid = array();
					
					if(ilContainer::_lookupContainerSetting(
						$node["obj_id"],
						ilObjectServiceSettingsGUI::TAXONOMIES,
						false
						))
					{									
						$all_valid = array_merge($all_valid,
							ilObjTaxonomy::getUsageOfObject($node["obj_id"]));		
						
						$active = array();
						foreach(ilContainer::_getContainerSettings($node["obj_id"]) as $keyword => $value)
						{
							if(substr($keyword, 0, strlen($prefix)) == $prefix && (bool)$value)
							{
								$active[] = substr($keyword, strlen($prefix));
							}			
						}
						
						$node_valid = array_intersect($all_valid, $active);						
					}		
					self::$valid_tax_map[$node["ref_id"]] = $node_valid;		
				}	
			}					
		}

		return sizeof(self::$valid_tax_map[$a_parent_ref_id]);		
	}
	
	public function getReadableSubObjectsForTaxNodeId($a_node_id)
	{
		global $tree, $ilAccess;
		
		$res = array();
		
		include_once "Services/Taxonomy/classes/class.ilTaxonomyNode.php";
		$node = new ilTaxonomyNode($a_node_id);		
		$tax_id = $node->getTaxonomyId();
		
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$tax_tree = new ilTaxonomyTree($tax_id);
		$sub_nodes = $tax_tree->getSubTreeIds($a_node_id);
		$sub_nodes[] = $a_node_id;
					
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");		
		$obj_ids = ilTaxNodeAssignment::findObjectsByNode($tax_id, $sub_nodes, "obj");
		if(sizeof($obj_ids))
		{			
			$fields = array(
				"object_reference.ref_id"
				,"object_data.obj_id" 
				,"object_data.type" 
				,"object_data.title"
				,"object_data.description"
			);
			$matching = $tree->getSubTreeFilteredByObjIds($this->parent_ref_id, $obj_ids, $fields);
			if(sizeof($matching))
			{				
				// :TODO: not sure if this makes sense...
				include_once "Services/Object/classes/class.ilObjectListGUIPreloader.php";
				$preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_REPOSITORY);
				
				foreach($matching as $item)
				{								
					if(!$tree->isDeleted($item["ref_id"]) &&
						$ilAccess->checkAccess("read", "", $item["ref_id"]))
					{
						$res[] = $item;
						
						$preloader->addItem($item["obj_id"], $item["type"], $item["ref_id"]);					
					}
				}	
				
				$preloader->preload();
			}			
		}
		
		return $res;
	}
}