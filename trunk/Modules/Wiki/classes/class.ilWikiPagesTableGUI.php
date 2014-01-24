<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

define ("IL_WIKI_ALL_PAGES", "all");
define ("IL_WIKI_NEW_PAGES", "new");
define ("IL_WIKI_POPULAR_PAGES", "popular");
define ("IL_WIKI_WHAT_LINKS_HERE", "what_links");
define ("IL_WIKI_ORPHANED_PAGES", "orphaned");

/**
* TableGUI class for wiki pages table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilWikiPagesTableGUI extends ilTable2GUI
{

	function ilWikiPagesTableGUI($a_parent_obj, $a_parent_cmd = "",
		$a_wiki_id, $a_mode = IL_WIKI_ALL_PAGES, $a_page_id = 0)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->pg_list_mode = $a_mode;
		$this->wiki_id = $a_wiki_id;
		$this->page_id = $a_page_id;
		
		switch($this->pg_list_mode)
		{
			case IL_WIKI_NEW_PAGES:
				$this->addColumn($lng->txt("created"), "created", "33%");
				$this->addColumn($lng->txt("wiki_page"), "title", "33%");
				$this->addColumn($lng->txt("wiki_created_by"), "user_sort", "34%");
				$this->setRowTemplate("tpl.table_row_wiki_new_page.html",
					"Modules/Wiki");
				break;
				
			case IL_WIKI_POPULAR_PAGES:
				$this->addColumn($lng->txt("wiki_page"), "title", "50%");
				$this->addColumn($lng->txt("wiki_page_hits"), "cnt", "50%");
				$this->setRowTemplate("tpl.table_row_wiki_popular_page.html",
					"Modules/Wiki");
				break;

			case IL_WIKI_ORPHANED_PAGES:
				$this->addColumn($lng->txt("wiki_page"), "title", "100%");
				$this->setRowTemplate("tpl.table_row_wiki_orphaned_page.html",
					"Modules/Wiki");
				break;

			default:
				$this->addColumn($lng->txt("wiki_page"), "title", "33%");
				$this->addColumn($lng->txt("wiki_last_changed"), "date", "33%");
				$this->addColumn($lng->txt("wiki_last_changed_by"), "user_sort", "34%");
				$this->setRowTemplate("tpl.table_row_wiki_page.html",
					"Modules/Wiki");
				break;
		}
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->getPages();
		
		$this->setShowRowsSelector(true);
		
		switch($this->pg_list_mode)
		{
			case IL_WIKI_WHAT_LINKS_HERE:
				$this->setTitle(sprintf($lng->txt("wiki_what_links_to_page"),
					ilWikiPage::lookupTitle($this->page_id)));
				break;
				
			default:
				$this->setTitle($lng->txt("wiki_".$a_mode."_pages"));
				break;
		}
	}
	
	/**
	* Get pages for list.
	*/
	function getPages()
	{
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		
		$pages = array();
		$this->setDefaultOrderField("title");

		switch ($this->pg_list_mode)
		{
			case IL_WIKI_WHAT_LINKS_HERE:
				$pages = ilWikiPage::getLinksToPage($this->wiki_id, $this->page_id);
				break;

			case IL_WIKI_ALL_PAGES:
				$pages = ilWikiPage::getAllPages($this->wiki_id);
				break;

			case IL_WIKI_NEW_PAGES:
				$this->setDefaultOrderField("created");
				$this->setDefaultOrderDirection("desc");
				$pages = ilWikiPage::getNewPages($this->wiki_id);
				break;

			case IL_WIKI_POPULAR_PAGES:
				$this->setDefaultOrderField("cnt");
				$this->setDefaultOrderDirection("desc");
				$pages = ilWikiPage::getPopularPages($this->wiki_id);
				break;
				
			case IL_WIKI_ORPHANED_PAGES:
				$pages = ilWikiPage::getOrphanedPages($this->wiki_id);
				break;
		}
				
		if($pages)
		{
			// enable sorting
			include_once("./Services/User/classes/class.ilUserUtil.php");	
			foreach(array_keys($pages) as $idx)
			{
				$pages[$idx]["user_sort"] = ilUserUtil::getNamePresentation($pages[$idx]["user"], false, false);					
			}
		}

		$this->setData($pages);
	}
	
	function numericOrdering($a_field)
	{
		if ($a_field == "cnt")
		{
			return true;
		}
		return false;
	}

	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		
		if ($this->pg_list_mode == IL_WIKI_NEW_PAGES)
		{
			$this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
			$this->tpl->setVariable("DATE",
				ilDatePresentation::formatDate(new ilDateTime($a_set["created"], IL_CAL_DATETIME)));
		}
		else if ($this->pg_list_mode == IL_WIKI_POPULAR_PAGES)
		{
			$this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
			$this->tpl->setVariable("HITS", $a_set["cnt"]);
		}
		else
		{
			$this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
			$this->tpl->setVariable("DATE",
				ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME)));
		}
		$this->tpl->setVariable("HREF_PAGE",
			ilObjWikiGUI::getGotoLink($_GET["ref_id"], $a_set["title"]));

		// user name
		include_once("./Services/User/classes/class.ilUserUtil.php");
		$this->tpl->setVariable("TXT_USER",
			ilUserUtil::getNamePresentation($a_set["user"], true, true,
			$ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())
			));
	}

}
?>
