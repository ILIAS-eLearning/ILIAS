<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* Tag cloud block, displayed in different contexts, e.g. courses and groups
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilTagCloudBlockGUI: ilColumnGUI
*
* @ingroup ServicesTagging
*/
class ilTagCloudBlockGUI extends ilBlockGUI
{		
	protected $parent_obj_type; // [string]
	protected $parent_obj_id; // [int]
	protected $parent_ref_id; // [int]	
	protected $enable_all_users; // [bool]
	
	public function __construct()
	{		
		parent::__construct();
							
		$this->parent_ref_id = (int)$_GET["ref_id"];
		$this->parent_obj_id = ilObject::_lookupObjId($this->parent_ref_id);
		$this->parent_obj_type = ilObject::_lookupType($this->parent_obj_id);	
	}
	
	public static function getBlockType()
	{
		return 'tagcld';
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
		global $lng;
		
		if(!$this->validateTagCloud())
		{
			return "";
		}
		
		// :TODO:
		$this->setTitle($lng->txt("obj_tool_setting_tag_cloud"));
				
		return parent::getHTML();		
	}		
	
	public function fillDataSection()
	{		
		global $ilCtrl;
		
		$html = "";
		
		$tags = $this->getSubTreeTags();
		if($tags)
		{
			// see ilPDTaggingBlockGUI::getTagCloud();
			
			$max = 1;
			foreach($tags as $tag => $counter)
			{
				$max = max($counter, $max);
			}
			reset($tags);
			
			$tpl = new ilTemplate("tpl.tag_cloud_block.html", true, true, "Services/Tagging");
			
			$current = $_REQUEST["tag"];
				
			$tpl->setCurrentBlock("tag_bl");
			foreach($tags as $tag => $counter)
			{
				$ilCtrl->setParameter($this, "tag", rawurlencode($tag));
				$tpl->setVariable("HREF_TAG",
					$ilCtrl->getLinkTarget($this, "filterContainer"));
				$tpl->setVariable("TAG_TITLE", $tag);
				$tpl->setVariable("FONT_SIZE",
					ilTagging::calculateFontSize($counter, $max)."%");
				
				if($current == $tag)
				{
					$tpl->setVariable("TAG_CLASS", ' class="ilHighlighted"');
				}
				
				$tpl->parseCurrentBlock();
			}
			$tpl->setVariable("CLOUD_STYLE", ' class="small"');
			
			$html = $tpl->get();
		}
			
		return $this->tpl->setVariable("DATA", $html);
	}
	
	protected function validateTagCloud()
	{				
		global $ilUser;
		
		// we currently only check for the parent object setting
		// might change later on (parent containers)
		include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
		$valid = ilContainer::_lookupContainerSetting(
			$this->parent_obj_id,
			ilObjectServiceSettingsGUI::TAG_CLOUD,
			false
		);		
		
		if($valid)
		{
			$tags_set = new ilSetting("tags");			
			$this->enable_all_users = $tags_set->get("enable_all_users", false);
			if(!$this->enable_all_users &&
				$ilUser->getId() == ANONYMOUS_USER_ID)
			{
				$valid = false;
			}
		}
		
		return $valid;
	}	
	
	protected function getSubTreeTags()
	{
		global $tree, $ilUser;
		
		$sub_ids = array();
		foreach($tree->getSubTree($tree->getNodeData($this->parent_ref_id)) as $sub_item)
		{
			if($sub_item["ref_id"] != $this->parent_ref_id &&
				$sub_item["type"] != "rolf" &&
				!$tree->isDeleted($sub_item["ref_id"]))
			{				
				$sub_ids[$sub_item["obj_id"]] = $sub_item["type"];
			}
		}
		
		if($sub_ids)
		{
			$only_user = null;		
			if(!$this->enable_all_users)
			{
				$only_user = $ilUser->getId();
			}						
			
			include_once "Services/Tagging/classes/class.ilTagging.php"; 
			return ilTagging::_getTagCloudForObjects($sub_ids, $only_user);						
		}
	}
	
	protected function getReadableSubObjectsForTag($a_tag)
	{
		global $tree, $ilAccess, $ilUser;
		
		$res = array();
		
		$only_user = null;		
		if(!$this->enable_all_users)
		{
			$only_user = $ilUser->getId();
		}		
		
		include_once "Services/Tagging/classes/class.ilTagging.php"; 		
		$obj_ids = ilTagging::_findObjectsByTag($a_tag, $only_user);		
		if(sizeof($obj_ids))
		{			
			$fields = array(
				"object_reference.ref_id"
				,"object_data.obj_id" 
				,"object_data.type" 
				,"object_data.title"
				,"object_data.description"
			);
			$matching = $tree->getSubTreeFilteredByObjIds($this->parent_ref_id, array_keys($obj_ids), $fields);
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
				
		$tag = trim($_REQUEST["tag"]);
		if(!$tag)
		{
			$ilCtrl->returnToParent($this);
		}
		
		$valid_objects = $this->getReadableSubObjectsForTag($tag);				
		if(sizeof($valid_objects))
		{	
			// see ilPDTaggingBlockGUI::showResourcesForTag()
			
			$ltpl = new ilTemplate("tpl.resources_for_tag.html", true, true, "Services/Tagging");
			
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