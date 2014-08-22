<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
 * Classification block, displayed in different contexts, e.g. categories
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilClassificationBlockGUI: ilColumnGUI
 *
 * @ingroup ServicesClassification
 */
class ilClassificationBlockGUI extends ilBlockGUI
{		
	protected $parent_obj_type; // [string]
	protected $parent_obj_id; // [int]
	protected $parent_ref_id; // [int]		
	protected $providers; // [array]
	protected $item_list_gui; // [array]
	
	public function __construct()
	{		
		global $lng;
		
		parent::__construct();
							
		$this->parent_ref_id = (int)$_GET["ref_id"];
		$this->parent_obj_id = ilObject::_lookupObjId($this->parent_ref_id);
		$this->parent_obj_type = ilObject::_lookupType($this->parent_obj_id);	
		
		$lng->loadLanguageModule("classification");
		$this->setTitle($lng->txt("clsfct_block_title"));
		$this->setFooterInfo($lng->txt("clsfct_block_info"));
		
		include_once "Services/Classification/classes/class.ilClassificationProvider.php";
		$this->providers = ilClassificationProvider::getValidProviders(
				$this->parent_ref_id,
				$this->parent_obj_id,
				$this->parent_obj_typ
		);
	}
	
	public static function getBlockType()
	{
		return 'clsfct';
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
	
	public function getHTML()
	{	
		if(!$this->validate())
		{
			return "";
		}
				
		return parent::getHTML();		
	}		
	
	public function fillDataSection()
	{		
		$html = array();
		
		foreach($this->providers as $provider)
		{
			$provider->render(
				$html,
				$this,
				"",
				get_class($this),
				"filterContainer"
			);
		}		
		
		if(sizeof($html))
		{
			$btpl = new ilTemplate("tpl.classification_block.html", true, true, "Services/Classification");
			foreach($html as $item)
			{
				$btpl->setCurrentBlock("provider_chunk_bl");
				$btpl->setVariable("TITLE", $item["title"]);
				$btpl->setVariable("CHUNK", $item["html"]);
				$btpl->parseCurrentBlock();
			}

			return $this->tpl->setVariable("DATA", $btpl->get());
		}
	}
	
	protected function validate()
	{				
		return sizeof($this->providers);
	}	
	
	protected function filterContainer()
	{
		global $ilCtrl, $tpl, $objDefinition, $lng;
				
		// :TODO:
		
		$node_id = (int)$_REQUEST["tax_node"];
		if(!$node_id)
		{
			$ilCtrl->returnToParent($this);
		}
		
		$valid_objects = $this->providers[0]->getReadableSubObjectsForTaxNodeId($node_id);	
		
		
		if(sizeof($valid_objects))
		{	
			// see ilPDTaggingBlockGUI::showResourcesForTag()
			
			$ltpl = new ilTemplate("tpl.classification_object_list.html", true, true, "Services/Classification");
			
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