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
	protected $selection; // [array]
	
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
			$tax_exp->setSelectMode("clsfct_tax_node[".$tax_id."]", true);
			$tax_exp->setSkipRootNode(true);
			$tax_exp->activateHighlight($this->active_filter);
			
			if(is_array($this->selection[$tax_id]))
			{
				foreach($this->selection[$tax_id] as $node_id)
				{
					$tax_exp->setNodeSelected($node_id);
				}
			}
			
			if (!$tax_exp->handleCommand())
			{											
				$a_html[] = array(
					"title" => ilObject::_lookupTitle($tax_id),
					"html" => $tax_exp->getHTML()
				);
			}					
		}									
	}
	
	public function importPostData()
	{
		if(is_array($_POST["clsfct_tax_node"]))
		{
			return $_POST["clsfct_tax_node"];			
		}
	}
	
	public function setSelection($a_value)
	{
		$this->selection = $a_value;
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
	
	public function getFilteredObjects()
	{				
		include_once "Services/Taxonomy/classes/class.ilTaxonomyTree.php";
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");		
		
		$tax_obj_ids = array();
		
		foreach($this->selection as $tax_id => $node_ids)
		{
			$tax_tree = new ilTaxonomyTree($tax_id);
			
			// combine taxonomy nodes OR
			$tax_nodes = array();
			foreach($node_ids as $node_id)
			{											
				$tax_nodes = array_merge($tax_nodes, $tax_tree->getSubTreeIds($node_id));
				$tax_nodes[] = $node_id;
			}
						
			$tax_obj_ids[$tax_id] = ilTaxNodeAssignment::findObjectsByNode($tax_id, $tax_nodes, "obj");								
		}
					
		// combine taxonomies AND
		$obj_ids = null;		
		foreach($tax_obj_ids as $tax_objs)
		{
			if($obj_ids === null)
			{
				$obj_ids = $tax_objs;
			}
			else
			{	
				$obj_ids = array_intersect($obj_ids, $tax_objs);
			}			
		}
		
		return (array)$obj_ids;		
	}
}