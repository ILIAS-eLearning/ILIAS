<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
 * Advanced metadata wiki block
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Is+++CalledBy ilWikiAdvMetaDataBlockGUI: ilColumnGUI
 *
 * @ingroup ModulesWiki
 */
class ilWikiAdvMetaDataBlockGUI extends ilBlockGUI
{
	static $block_type = "wikiadvmd";
	static $st_data;
	
	protected $export = false;
	protected $obj_id; // [int]
	protected $ref_id; // [int]
	protected $page_id; // [int]
	
	static protected $records = array(); // [array]
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
		
		//$this->setImage(ilUtil::getImagePath("icon_news_s.png"));

		$lng->loadLanguageModule("wiki");
		//$this->setBlockId(...);
		/*$this->setLimit(5);
		$this->setAvailableDetailLevels(3);*/
		$this->setEnableNumInfo(false);
		
//		$this->setTitle($lng->txt("wiki_important_pages"));
		$this->setTitle($lng->txt("wiki_advmd_block_title"));
		//$this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		//$this->setData($data);
		$this->allow_moving = false;
		//$this->handleView();
	}

	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	* Is this a repository object
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		return IL_SCREEN_SIDE;
	}
	
	public function setObject($a_wiki_ref_id, $a_wiki_obj_id, $a_page_id)
	{
		$this->ref_id = $a_wiki_ref_id;
		$this->obj_id = $a_wiki_obj_id;
		$this->page_id = $a_page_id;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	* Get bloch HTML code.
	*/
	function getHTML($a_export = false)
	{
		global $ilCtrl, $lng, $ilAccess;

		$this->export = $a_export;
		
		if (!$this->export && $ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilwikipagegui", "editAdvancedMetaData"),
				$lng->txt("edit"), "_top");
		}
		
		return parent::getHTML();
	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{		
		$btpl = new ilTemplate("tpl.wiki_advmd_block.html", true, true, "Modules/Wiki");		
		
		// see ilAdvancedMDRecordGUI::parseInfoPage()
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		include_once('Services/ADT/classes/class.ilADTFactory.php');	
		
		foreach(self::getRecords($this->obj_id) as $record)
		{		
			// record title currently not required
			// ilAdvancedMDRecord::_lookupTitle($record->getRecordId()); 
		
			$values = new ilAdvancedMDValues($record->getRecordId(), $this->obj_id, "wpg", $this->page_id);
			
			// this correctly binds group and definitions
			$values->read();
			
			$defs = $values->getDefinitions();									
			foreach($values->getADTGroup()->getElements() as $element_id => $element)				
			{																								
				$btpl->setCurrentBlock("item");
				$btpl->setVariable("CAPTION", $defs[$element_id]->getTitle());
				if($element->isNull())
				{	
					$value = "-";
				}
				else
				{
					$value = ilADTFactory::getInstance()->getPresentationBridgeForInstance($element)->getHTML();
				}
				$btpl->setVariable("VALUE", $value);
				$btpl->parseCurrentBlock();				
			}			
		}
		
		$this->setDataSection($btpl->get());		
		
		return;		
	}		
	
	protected static function getRecords($a_wiki_obj_id)
	{
		if(!array_key_exists($a_wiki_obj_id, self::$records))
		{
			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');						
			self::$records[$a_wiki_obj_id] = ilAdvancedMDRecord::_getSelectedRecordsByObject("wiki", $a_wiki_obj_id, "wpg");
		}
		return self::$records[$a_wiki_obj_id];
	}
	
	public static function isActive($a_wiki_obj_id)
	{		
		return (bool)sizeof(self::getRecords($a_wiki_obj_id));	
	}
}

?>
