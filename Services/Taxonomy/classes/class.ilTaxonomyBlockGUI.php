<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* Taxonomy blocks, displayed in different contexts, e.g. categories
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilTaxonomyBlockGUI: ilColumnGUI
*
* @ingroup ServicesTaxonomy
*/
class ilTaxonomyBlockGUI extends ilBlockGUI
{		
	protected $parent_obj_type; // [string]
	protected $parent_obj_id; // [int]
	protected $parent_ref_id; // [int]	
	protected $tax_data; // [array]
	protected $item_list_gui; // [array]
	
	public function __construct()
	{		
		parent::__construct();
							
		$this->parent_ref_id = (int)$_GET["ref_id"];
		$this->parent_obj_id = ilObject::_lookupObjId($this->parent_ref_id);
		$this->parent_obj_type = ilObject::_lookupType($this->parent_obj_id);	
	}
	
	public static function getBlockType()
	{
		return 'tax';
	}

	public static function isRepositoryObject()
	{
		return false;
	}
	
	public function executeCommand()
	{
		global $ilCtrl;
		
		$cmd = $ilCtrl->getCmd();
		$next_class = $ilCtrl->getNextClass($this);
		
		switch ($next_class)
		{
			default:
				// explorer call
				if($ilCtrl->isAsynch())
				{
					$this->getHTML();
				}
				else
				{
					$this->$cmd();
				}
				break;
		}
	}
	
	static function getScreenMode()
	{
		global $ilCtrl;
				
		switch($ilCtrl->getCmd())
		{
			case "filterContainer":
				return IL_SCREEN_CENTER;			
		}
	}
	
	protected function getActiveTaxonomies()
	{	
		global $tree;
		
	// currently only active for categories
		if($this->parent_obj_type != "cat")
		{
			return array();
		}
		
		include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
		include_once "Services/Taxonomy/classes/class.ilObjTaxonomy.php";
		
		// see ilTaxMDGUI::getSelectableTaxonomies()
		
		$res = array();
		foreach($tree->getPathFull($this->parent_ref_id) as $node)
		{
			if($node["type"] == "cat")
			{				
				if(ilContainer::_lookupContainerSetting(
					$node["obj_id"],
					ilObjectServiceSettingsGUI::TAXONOMIES,
					false
					))
				{
					$node_taxes = ilObjTaxonomy::getUsageOfObject($node["obj_id"], true);
					if(sizeof($node_taxes))
					{
						foreach($node_taxes as $node_tax)
						{					
							$res[$node_tax["tax_id"]] = $node_tax["title"];
						}
					}
				}
			}
		}
		
		asort($res);
		return $res;				
	}
	
	public function getHTML()
	{	
		$this->tax_data = $this->getActiveTaxonomies();
		if(!is_array($this->tax_data))
		{
			return "";
		}
				
		return parent::getHTML();		
	}		
	
	public function fillDataSection()
	{
		global $lng, $objDefinition;
		
		$this->setTitle($lng->txt("obj_tax"));
		
		$html = "";
		
		$class_name = $objDefinition->getClassName($this->parent_obj_type);
		$gui = "il".$class_name."GUI";
		
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");
		foreach($this->tax_data as $tax_id => $tax_title)
		{						
			$tax_exp = new ilTaxonomyExplorerGUI($this, "", $tax_id,
				get_class($this), "filterContainer");
			if (!$tax_exp->handleCommand())
			{
				// :TODO:
				$html .= "<div>".$tax_title."</div>";
						
				$html .= $tax_exp->getHTML()."&nbsp;";
			}			
		}
		
		return $this->tpl->setVariable("DATA", $html);
	}
	
	protected function getReadableSubObjectsForTaxNodeId($a_node_id)
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
				foreach($matching as $item)
				{					
					if($ilAccess->checkAccess("read", "", $item["ref_id"]))
					{
						$res[] = $item;
					}
				}								
			}			
		}
		
		return $res;
	}
	
	protected function filterContainer()
	{
		global $ilCtrl, $tpl, $objDefinition, $lng;
				
		$node_id = (int)$_REQUEST["tax_node"];
		if(!$node_id)
		{
			$ilCtrl->returnToParent($this);
		}
		
		$valid_objects = $this->getReadableSubObjectsForTaxNodeId($node_id);				
		if(sizeof($valid_objects))
		{	
			// see ilPDTaggingBlockGUI::showResourcesForTag()
			
			$ltpl = new ilTemplate("tpl.taxonomy_object_list.html", true, true, "Services/Taxonomy");
			
			$this->item_list_gui = array();
			foreach($valid_objects as $obj)
			{
				$type = $obj["type"];
								
				// get list gui class for each object type
				if (empty($this->item_list_gui[$type]))
				{
					$class = $objDefinition->getClassName($type);
					$location = $objDefinition->getLocation($type);
			
					$full_class = "ilObj".$class."ListGUI";
			
					include_once($location."/class.".$full_class.".php");
					$this->item_list_gui[$type] = new $full_class();
					$this->item_list_gui[$type]->enableDelete(false);
					$this->item_list_gui[$type]->enablePath(true);
					$this->item_list_gui[$type]->enableCut(false);
					$this->item_list_gui[$type]->enableCopy(false);
					$this->item_list_gui[$type]->enableSubscribe(false);
					$this->item_list_gui[$type]->enablePayment(false);
					$this->item_list_gui[$type]->enableLink(false);
					$this->item_list_gui[$type]->enableIcon(true);
				}
				
				$html = $this->item_list_gui[$type]->getListItemHTML(
					$obj["ref_id"],
					$obj["obj_id"], 
					$obj["title"],
					$obj["description"]);
					
				if ($html != "")
				{
					$css = ($css != "tblrow1") ? "tblrow1" : "tblrow2";
						
					$ltpl->setCurrentBlock("res_row");
					$ltpl->setVariable("ROWCLASS", $css);
					$ltpl->setVariable("RESOURCE_HTML", $html);
					$ltpl->setVariable("ALT_TYPE", $lng->txt("obj_".$type));
					$ltpl->setVariable("IMG_TYPE",
						ilUtil::getImagePath("icon_".$type.".png"));
					$ltpl->parseCurrentBlock();
				}
			}
	
			$tpl->setContent($ltpl->get());
		}		
	}
}