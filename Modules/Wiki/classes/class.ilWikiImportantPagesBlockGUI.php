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
	protected $export = false;
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
		
		$lng->loadLanguageModule("wiki");
		$this->setEnableNumInfo(false);
		
		$this->setTitle($lng->txt("wiki_navigation"));
		$this->allow_moving = false;
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
	function getHTML($a_export = false)
	{
		global $ilCtrl, $lng, $ilUser, $ilAccess;

		$this->export = $a_export;
		
		if (!$this->export && $ilAccess->checkAccess("write", "", $_GET["ref_id"]))
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

		$cpar[0] = $cpar[1] = 0;
		include_once("./Services/UIComponent/NestedList/classes/class.ilNestedList.php");
		
		$list = new ilNestedList();
		$list->setItemClass("ilWikiBlockItem");
		$list->setListClass("ilWikiBlockList");
		$list->setListClass("ilWikiBlockListNoIndent", 1);
		
		$cnt = 1;
		$title = ilObjWiki::_lookupStartPage(ilObject::_lookupObjId($_GET["ref_id"]));
		if (!$this->export)
		{
			$list->addListNode("<p class='small'><a href='".
				$ilCtrl->getLinkTargetByClass("ilobjwikigui", "gotoStartPage")
				."'>".$title."</a></p>", 1, 0);
		}
		else
		{
			$list->addListNode("<p class='small'><a href='".
				"index.html".
				"'>".$title."</a></p>", 1, 0);
		}
		$cpar[0] = 1;
		
		$ipages = ilObjWiki::_lookupImportantPagesList(ilObject::_lookupObjId($_GET["ref_id"]));
		foreach ($ipages as $p)
		{
			$cnt++;
			$title = ilWikiPage::lookupTitle($p["page_id"]);
			if (!$this->export)
			{
				$list->addListNode("<p class='small'><a href='".
					ilObjWikiGUI::getGotoLink($_GET["ref_id"], $title)
					."'>".$title."</a></p>", $cnt, (int) $cpar[$p["indent"] - 1]);
			}
			else
			{
				$list->addListNode("<p class='small'><a href='".
					"wpg_".$p["page_id"].".html".
					"'>".$title."</a></p>", $cnt, (int) $cpar[$p["indent"] - 1]);
			}
			$cpar[$p["indent"]] = $cnt;
		}
		
		$this->setDataSection($list->getHTML());
return;
		// old style

		// the start page
		$tpl->setCurrentBlock("item");
		$title = ilWikiPage::lookupTitle($p["page_id"]);
		$tpl->setVariable("ITEM_TITLE", $lng->txt("wiki_start_page"));
		$tpl->setVariable("PAD", (int) 5 + (0 * 20));
		$tpl->setVariable("ITEM_HREF", $ilCtrl->getLinkTargetByClass("ilobjwikigui", "gotoStartPage"));
		$tpl->parseCurrentBlock();

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
