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
	
	public function render(array &$a_html, $a_parent_gui)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");	
	
		foreach(self::$valid_tax_map[$this->parent_ref_id] as $tax_id)
		{
			$tax_exp = new ilTaxonomyExplorerGUI($a_parent_gui, null, $tax_id, null, null);			
			$tax_exp->setSkipRootNode(true);
			$tax_exp->setOnClick("il.Classification.toggle({tax_node: '{NODE_CHILD}'});");
			
			if(is_array($this->selection))
			{
				foreach($this->selection as $node_id)
				{
					$tax_exp->setPathOpen($node_id);
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
	
	public function importPostData($a_saved = null)
	{
		$incoming_id = (int)$_REQUEST["tax_node"];
		if($incoming_id)
		{			
			if(is_array($a_saved))
			{
				foreach($a_saved as $idx => $node_id)
				{
					if($node_id == $incoming_id)
					{
						unset($a_saved[$idx]);
						return $a_saved;
					}
				}
				$a_saved[] = $incoming_id;
				return $a_saved;
			}
			else
			{
				return array($incoming_id);
			}
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
		include_once "Services/Taxonomy/classes/class.ilTaxNodeAssignment.php";				
		include_once "Services/Taxonomy/classes/class.ilTaxonomyNode.php";				
		
		$tax_obj_ids = $tax_map = array();
			
		// :TODO: this could be smarter
		foreach($this->selection as $node_id)
		{		
			$node = new ilTaxonomyNode($node_id);
			$tax_map[$node->getTaxonomyId()][] = $node_id;
		}
		
		foreach($tax_map as $tax_id => $node_ids)
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