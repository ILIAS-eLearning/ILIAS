<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

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
				$this->addColumn($lng->txt("created"), "", "33%");
				$this->addColumn($lng->txt("wiki_page"), "", "33%");
				$this->addColumn($lng->txt("wiki_created_by"), "", "34%");
				$this->setRowTemplate("tpl.table_row_wiki_new_page.html",
					"Modules/Wiki");
				break;
				
			case IL_WIKI_POPULAR_PAGES:
				$this->addColumn($lng->txt("wiki_page"), "", "50%");
				$this->addColumn($lng->txt("wiki_page_hits"), "", "50%");
				$this->setRowTemplate("tpl.table_row_wiki_popular_page.html",
					"Modules/Wiki");
				break;

			case IL_WIKI_ORPHANED_PAGES:
				$this->addColumn($lng->txt("wiki_page"), "", "100%");
				$this->setRowTemplate("tpl.table_row_wiki_orphaned_page.html",
					"Modules/Wiki");
				break;

			default:
				$this->addColumn($lng->txt("wiki_page"), "", "33%");
				$this->addColumn($lng->txt("wiki_last_changed"), "", "33%");
				$this->addColumn($lng->txt("wiki_last_changed_by"), "", "34%");
				$this->setRowTemplate("tpl.table_row_wiki_page.html",
					"Modules/Wiki");
				break;
		}
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->getPages();
		
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

		$this->setData($pages);
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
			$this->tpl->setVariable("DATE", $a_set["created"]);
		}
		else if ($this->pg_list_mode == IL_WIKI_POPULAR_PAGES)
		{
			$this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
			$this->tpl->setVariable("HITS", $a_set["cnt"]);
		}
		else
		{
			$this->tpl->setVariable("TXT_PAGE_TITLE", $a_set["title"]);
			$this->tpl->setVariable("DATE", $a_set["date"]);
		}
		$this->tpl->setVariable("HREF_PAGE",
			$this->parent_obj->getGotoLink($a_set["title"]));

		if (ilObject::_exists($a_set["user"]))
		{
			// user name
			$user = ilObjUser::_lookupName($a_set["user"]);
			$login = ilObjUser::_lookupLogin($a_set["user"]);
			$this->tpl->setVariable("TXT_LINKED_USER",
				$user["lastname"].", ".$user["firstname"]." [".$login."]");
				
			// profile link
			$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user", $a_set["user"]);
			$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
				rawurlencode($ilCtrl->getLinkTarget($this->getParentObject(), $this->getParentCmd())));
			$this->tpl->setVariable("USER_LINK",
				$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML"));
			$img = ilObjUser::_getPersonalPicturePath($a_set["user"], "xxsmall", true);
			$this->tpl->setVariable("IMG_USER", $img);
		}

	}

}
?>
