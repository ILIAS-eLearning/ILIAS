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
	protected $parent_obj_type; // [string]
	protected $parent_obj_id; // [int]
	protected $parent_ref_id; // [int]	
	protected $tax_id; // [int]	
	protected $tax_data; // [array]
	protected $item_list_gui; // [array]
	
	protected static $valid_tax_map = array();
	
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
	
	public function setBlock(ilCustomBlock $a_block)
	{
		$this->tax_id = $a_block->getContextSubObjId();
		$this->setTitle($a_block->getTitle());
	}
	
	public function getHTML()
	{	
		if(!$this->validateTax())
		{
			return "";
		}
				
		return parent::getHTML();		
	}		
	
	public function fillDataSection()
	{		
		$html = "";
		
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php");							
		$tax_exp = new ilTaxonomyExplorerGUI($this, "", $this->tax_id,
			get_class($this), "filterContainer");
		if (!$tax_exp->handleCommand())
		{			
			$html = $tax_exp->getHTML()."&nbsp;";
		}			
				
		return $this->tpl->setVariable("DATA", $html);
	}
	
	protected function validateTax()
	{
		global $tree;
		
		if(!$this->tax_id)
		{
			return false;
		}	
		
		if(!isset(self::$valid_tax_map[$this->parent_ref_id]))
		{				
			include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
			include_once "Services/Taxonomy/classes/class.ilObjTaxonomy.php";

			$valid = array();
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
						$valid = array_merge($valid,
							ilObjTaxonomy::getUsageOfObject($node["obj_id"]));							
					}		
					self::$valid_tax_map[$node["ref_id"]] = $valid;		
				}	
			}					
		}

		return in_array($this->tax_id, self::$valid_tax_map[$this->parent_ref_id]);			
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