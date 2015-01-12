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
	
	protected static $providers_cache; // [array]
	
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
				if($ilCtrl->isAsynch() && $cmd != "getAjax" && $cmd != "filterContainer")
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
		
		if($ilCtrl->isAsynch())
		{
			return;
		}
				
		switch($ilCtrl->getCmd())
		{
			case "filterContainer":
				return IL_SCREEN_CENTER;			
		}
	}
	
	public function getHTML()
	{			
		global $tpl, $ilCtrl;
		
		if(!$ilCtrl->isAsynch())
		{
			unset($_SESSION[self::getBlockType()]);					
		}
		
		$this->initProviders();
		
		if(!$this->validate())
		{
			return "";
		}
		
		$tpl->addJavaScript("Services/Classification/js/ilClassification.js");
				
		return parent::getHTML();		
	}		
	
	public function getAjax()
	{
		global $tpl;
		
		$this->initProviders(true);		
		
		echo $this->getHTML();
		echo $tpl->getOnLoadCodeForAsynch();

		exit();
	}
	
	public function fillDataSection()
	{		
		global $ilCtrl, $tpl;
		
		$html = array();		
		foreach($this->providers as $provider)
		{
			$provider->render($html, $this);
		}		
		
		$this->tpl->setVariable("BLOCK_ROW", "");
					
		$ajax_block_id = "block_".$this->getBlockType()."_0";
		$ajax_block_url = $ilCtrl->getLinkTarget($this, "getAjax", "", true, false);
		$ajax_content_id = "il_center_col";
		$ajax_content_url = $ilCtrl->getLinkTarget($this, "filterContainer", "", true, false);

		// #15008 - always load regardless of content (because of redraw)
		$tpl->addOnLoadCode('il.Classification.setAjax("'.$ajax_block_id.'", "'.
			$ajax_block_url.'", "'.$ajax_content_id.'", "'.$ajax_content_url.'");');
			
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
			
			$this->tpl->setVariable("DATA", $btpl->get());
		}
	}
	
	protected function validate()
	{				
		return sizeof($this->providers);
	}			
	
	protected function filterContainer()
	{
		global $objDefinition, $lng, $tree, $ilAccess, $ilCtrl;
		
		$this->initProviders();
			
		// empty selection is invalid
		if(!$_SESSION[self::getBlockType()])
		{
			exit();
		}
		
		$all_matching_provider_object_ids = null;
	
		foreach($this->providers as $provider)
		{
			$id = get_class($provider);
			$current = $_SESSION[self::getBlockType()][$id];				
			if($current)
			{
				// combine providers AND
				$provider_object_ids = $provider->getFilteredObjects();
				if(is_array($all_matching_provider_object_ids))
				{
					$all_matching_provider_object_ids = array_intersect($matching_provider_object_ids, $provider_object_ids);
				}
				else
				{
					$all_matching_provider_object_ids = $provider_object_ids;
				}
			}
		}
		
		$has_content = false;
			
		$ltpl = new ilTemplate("tpl.classification_object_list.html", true, true, "Services/Classification");
		
		if(sizeof($all_matching_provider_object_ids))
		{			
			$fields = array(
				"object_reference.ref_id"
				,"object_data.obj_id" 
				,"object_data.type" 
				,"object_data.title"
				,"object_data.description"
			);
			$matching = $tree->getSubTreeFilteredByObjIds($this->parent_ref_id, $all_matching_provider_object_ids, $fields);
			if(sizeof($matching))
			{			
				$valid_objects = array();				
			
				// :TODO: not sure if this makes sense...
				include_once "Services/Object/classes/class.ilObjectListGUIPreloader.php";
				$preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_REPOSITORY);
				
				foreach($matching as $item)
				{								
					if($item["ref_id"] != $this->parent_ref_id && 
						!$tree->isDeleted($item["ref_id"]) &&
						$ilAccess->checkAccess("read", "", $item["ref_id"]))
					{
						$valid_objects[] = $item;
						
						$preloader->addItem($item["obj_id"], $item["type"], $item["ref_id"]);					
					}
				}	
				
				if(sizeof($valid_objects))
				{
					$has_content = true;
					
					$preloader->preload();

					// see ilPDTaggingBlockGUI::showResourcesForTag()

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
							$this->item_list_gui[$type]->enablePath(true, $this->parent_ref_id); // relative path
							$this->item_list_gui[$type]->enableCut(false);
							$this->item_list_gui[$type]->enableCopy(false);
							$this->item_list_gui[$type]->enableSubscribe(false);
							$this->item_list_gui[$type]->enablePayment(false);
							$this->item_list_gui[$type]->enableLink(false);
							$this->item_list_gui[$type]->enableIcon(true);
							
							// :TOOD: for each item or just for each list?
							foreach($this->providers as $provider)
							{
								$provider->initListGUI($this->item_list_gui[$type]);
							}							
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
								ilUtil::getImagePath("icon_".$type.".svg"));
							$ltpl->parseCurrentBlock();
						}
					}
				}
			}				
		}	
			
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();		
		$content_block->setTitle($lng->txt("clsfct_content_title"));		
		$content_block->addHeaderCommand($ilCtrl->getParentReturn($this), "", true);
				
		if($has_content)
		{
			$content_block->setContent($ltpl->get());
		}
		else
		{
			$content_block->setContent($lng->txt("clsfct_content_no_match"));
		}
				
		echo $content_block->getHTML();
		exit();
	}	
	
	protected function initProviders($a_check_post = false)
	{			
		if(!isset(self::$providers_cache[$this->parent_ref_id]))
		{		
			include_once "Services/Classification/classes/class.ilClassificationProvider.php";
			self::$providers_cache[$this->parent_ref_id] = ilClassificationProvider::getValidProviders(
					$this->parent_ref_id,
					$this->parent_obj_id,
					$this->parent_obj_typ			
			);			
		}
		$this->providers = self::$providers_cache[$this->parent_ref_id];	
		
		if($a_check_post && (bool)!$_REQUEST["rdrw"])
		{
			foreach($this->providers as $provider)
			{	
				$id = get_class($provider);
				$current = $provider->importPostData($_SESSION[self::getBlockType()][$id]);
				if($current)
				{
					$_SESSION[self::getBlockType()][$id] = $current;
				}
				else
				{
					unset($_SESSION[self::getBlockType()][$id]);
				}
			}
		}	
		
		foreach($this->providers as $provider)
		{
			$id = get_class($provider);
			$current = $_SESSION[self::getBlockType()][$id];			
			if($current)
			{
				$provider->setSelection($current);
			}
		}
	}
}