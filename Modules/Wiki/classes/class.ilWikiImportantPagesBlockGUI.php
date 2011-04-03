<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
 * Important pages wiki block
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Is+++CalledBy ilWikiSearchBlockGUI: ilColumnGUI
 *
 * @ingroup ModulesWiki
 */
class ilWikiImportantPagesBlockGUI extends ilBlockGUI
{
	static $block_type = "wikiimppages";
	static $st_data;
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
		
		//$this->setImage(ilUtil::getImagePath("icon_news_s.gif"));

		$lng->loadLanguageModule("wiki");
		//$this->setBlockId(...);
		/*$this->setLimit(5);
		$this->setAvailableDetailLevels(3);*/
		$this->setEnableNumInfo(false);
		
		$this->setTitle($lng->txt("wiki_important_pages"));
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
	function getHTML()
	{
		global $ilCtrl, $lng, $ilUser, $ilAccess;

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass("ilobjwikigui", "editImportantPages"),
				$lng->txt("edit"), "_top");
		}
		
		return parent::getHTML();
	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $ilCtrl, $lng, $ilAccess;
		
		$tpl = new ilTemplate("tpl.wiki_imp_pages_block.html", true, true, "Modules/Wiki");

		$ipages = ilObjWiki::_lookupImportantPagesList(ilObject::_lookupObjId($_GET["ref_id"]));
		foreach ($ipages as $p)
		{
			$tpl->setCurrentBlock("item");
			$title = ilWikiPage::lookupTitle($p["page_id"]);
			$tpl->setVariable("ITEM_TITLE", $title);
			$tpl->setVariable("PAD", (int) 5 + ($p["indent"] * 20));
			$tpl->setVariable("ITEM_HREF", ilObjWikiGUI::getGotoLink($_GET["ref_id"], $title));
			$tpl->parseCurrentBlock();
		}

		$this->setDataSection($tpl->get());
	}
}

?>
