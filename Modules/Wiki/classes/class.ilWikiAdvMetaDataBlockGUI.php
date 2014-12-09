<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
 * Advanced metadata wiki block
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilWikiAdvMetaDataBlockGUI: ilColumnGUI
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
	protected $record; // [ilAdvancedMDRecord]
	protected $adv_md_hidden; // [bool]
	
	static protected $records = array(); // [array]
	
	/**
	* Constructor
	*/
	function __construct(ilAdvancedMDRecord $a_record)
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
						
		$this->record = $a_record;		
		
		$this->setTitle($this->record->getTitle());		
		$this->setBlockId("advmdwiki_".$this->record->getRecordId());				
		$this->setEnableNumInfo(false);
		// $this->setAvailableDetailLevels(3);		
		$this->allow_moving = false;
		
		$lng->loadLanguageModule("wiki");
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
		
		$has_write = $ilAccess->checkAccess("write", "", $this->ref_id);
		
		if ($this->isHidden())
		{
			if(!$has_write)
			{
				return;
			}			
		}
		
		if (!$this->export && $has_write)
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilwikipagegui", "editAdvancedMetaData"),
				$lng->txt("edit"), "_top");
						
			if (!$this->isHidden())
			{
				$this->addBlockCommand(
					$ilCtrl->getLinkTargetByClass("ilwikipagegui", "hideAdvancedMetaData"),
					$lng->txt("hide"), "_top");		
			}
			else
			{
				$this->addBlockCommand(
					$ilCtrl->getLinkTargetByClass("ilwikipagegui", "unhideAdvancedMetaData"),
					$lng->txt("show"), "_top");		
			}
		}
		
		return parent::getHTML();
	}
	
	/**
	 * Is block currently hidden?
	 * 
	 * @return boolean
	 */
	protected function isHidden()
	{
		if($this->adv_md_hidden === null)
		{
			$this->adv_md_hidden = ilWikiPage::lookupAdvancedMetadataHidden($this->page_id);
		}
		return $this->adv_md_hidden;
	}
		
	/**
	* Fill data section
	*/
	function fillDataSection()
	{		
		global $lng;
		
		$btpl = new ilTemplate("tpl.wiki_advmd_block.html", true, true, "Modules/Wiki");		
		
		// see ilAdvancedMDRecordGUI::parseInfoPage()
		
		$old_dt = ilDatePresentation::useRelativeDates();		
		ilDatePresentation::setUseRelativeDates(false);
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		include_once('Services/ADT/classes/class.ilADTFactory.php');	
				
		$values = new ilAdvancedMDValues($this->record->getRecordId(), $this->obj_id, "wpg", $this->page_id);

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
				$value = ilADTFactory::getInstance()->getPresentationBridgeForInstance($element);

				if($element instanceof ilADTLocation)
				{
					$value->setSize("100%", "200px");
				}

				$value = $value->getHTML();
			}
			$btpl->setVariable("VALUE", $value);
			$btpl->parseCurrentBlock();										
		}
		
		
		if ($this->isHidden())
		{
			$btpl->setVariable("HIDDEN_INFO", $lng->txt("wiki_adv_md_hidden"));			
		}
				
		$this->setDataSection($btpl->get());		
		
		ilDatePresentation::setUseRelativeDates($old_dt);
		
		return;		
	}		
	
	public static function getRecords($a_wiki_obj_id)
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
