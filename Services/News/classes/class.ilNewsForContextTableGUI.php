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

/**
* TableGUI class for table NewsForContext
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilNewsForContextTableGUI extends ilTable2GUI
{

	function ilNewsForContextTableGUI($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "f", "1");
		$this->addColumn($lng->txt("news_news_item_content"), "");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_news_for_context.html",
			"Services/News");
		$this->setCloseCommand($ilCtrl->getParentReturnByClass("ilnewsitemgui"));
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");

		// user
		if ($a_set["user_id"] > 0)
		{
			$this->tpl->setCurrentBlock("user_info");
			$user_obj = new ilObjUser($a_set["user_id"]);
			$this->tpl->setVariable("VAL_AUTHOR", $user_obj->getLogin());
			$this->tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
			$this->tpl->parseCurrentBlock();
		}
		
		// access
		if ($enable_internal_rss)
		{
			$this->tpl->setCurrentBlock("access");
			$this->tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
			if ($a_set["visibility"] == NEWS_PUBLIC ||
				($a_set["priority"] == 0 &&
				ilBlockSetting::_lookup("news", "public_notifications",
				0, $a_set["context_obj_id"])))
			{
				$this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_public"));
			}
			else
			{
				$this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_users"));
			}
			$this->tpl->parseCurrentBlock();
		}

		// last update
		if ($a_set["creation_date"] != $a_set["update_date"])
		{
			$this->tpl->setCurrentBlock("ni_update");
			$this->tpl->setVariable("TXT_LAST_UPDATE", $lng->txt("last_update"));
			$this->tpl->setVariable("VAL_LAST_UPDATE",
				ilFormat::formatDate($a_set["update_date"], "datetime", true));
			$this->tpl->parseCurrentBlock();
		}
		
		// creation date
		$this->tpl->setVariable("VAL_CREATION_DATE",
			ilFormat::formatDate($a_set["creation_date"], "datetime", true));
		$this->tpl->setVariable("TXT_CREATED", $lng->txt("created"));
		
		// title
		$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
		
		// content
		if ($a_set["content"] != "")
		{
			$this->tpl->setCurrentBlock("content");
			$this->tpl->setVariable("VAL_CONTENT", $a_set["content"]);
			$this->tpl->parseCurrentBlock();
		}
		if ($a_set["content_long"] != "")
		{
			$this->tpl->setCurrentBlock("long");
			$this->tpl->setVariable("VAL_LONG_CONTENT", $a_set["content_long"]);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
		$ilCtrl->setParameterByClass("ilnewsitemgui", "news_item_id", $a_set["id"]);
		$this->tpl->setVariable("CMD_EDIT",
			$ilCtrl->getLinkTargetByClass("ilnewsitemgui", "editNewsItem"));
	}

}
?>
