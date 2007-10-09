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
* Personal desktop news table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilPDNewsTableGUI extends ilTable2GUI
{

	function ilPDNewsTableGUI($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("");
		//$this->addColumn($lng->txt("date"), "creation_date", "1");
		//$this->addColumn($lng->txt("news_news_item_content"), "");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_pd_news.html",
			"Services/News");
		$this->setDefaultOrderField("update_date");
		$this->setDefaultOrderDirection("desc");
		$this->setEnableTitle(false);
		$this->setEnableHeader(false);
		//$this->setCloseCommand($ilCtrl->getParentReturnByClass("ilpdnewsgui"));
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

		// context
		$obj_id = ilObject::_lookupObjId($a_set["ref_id"]);
		$obj_type = ilObject::_lookupType($obj_id);
		$obj_title = ilObject::_lookupTitle($obj_id);
			
		// user
		if ($a_set["user_id"] > 0)
		{
			$this->tpl->setCurrentBlock("user_info");
			if ($obj_type == "frm")
			{
				include_once("./Modules/Forum/classes/class.ilForumProperties.php");
				if (ilForumProperties::_isAnonymized($a_set["context_obj_id"]))
				{
					if ($a_set["context_sub_obj_type"] == "pos" &&
						$a_set["context_sub_obj_id"] > 0)
					{
						include_once("./Modules/Forum/classes/class.ilForumPost.php");
						$post = new ilForumPost($a_set["context_sub_obj_id"]);
						if ($post->getUserAlias() != "") $this->tpl->setVariable("VAL_AUTHOR", ilUtil::stripSlashes($post->getUserAlias()));
						else $this->tpl->setVariable("VAL_AUTHOR", $lng->txt("forums_anonymous"));
					}
					else
					{
						$this->tpl->setVariable("VAL_AUTHOR", $lng->txt("forums_anonymous"));
					}
				}
				else
				{
					$user_obj = new ilObjUser($a_set["user_id"]);
					$this->tpl->setVariable("VAL_AUTHOR", $user_obj->getLogin());
				}
			}
			else
			{
				$user_obj = new ilObjUser($a_set["user_id"]);
				$this->tpl->setVariable("VAL_AUTHOR", $user_obj->getLogin());
			}
			$this->tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
			$this->tpl->parseCurrentBlock();
		}
		
		// media player
		if ($a_set["content_type"] == NEWS_AUDIO &&
			$a_set["mob_id"] > 0 && ilObject::_exists($a_set["mob_id"]))
		{
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
			$mob = new ilObjMediaObject($a_set["mob_id"]);
			$med = $mob->getMediaItem("Standard");
			$mpl = new ilMediaPlayerGUI();
			$mpl->setFile(ilObjMediaObject::_getDirectory($a_set["mob_id"])."/".
				$med->getLocation());
			$this->tpl->setCurrentBlock("player");
			$this->tpl->setVariable("PLAYER",
				$mpl->getMp3PlayerHtml());
			$this->tpl->parseCurrentBlock();
		}
		
		// access
		if ($enable_internal_rss)
		{
			$this->tpl->setCurrentBlock("access");
			include_once("./Services/Block/classes/class.ilBlockSetting.php");
			$this->tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
			if ($a_set["visibility"] == NEWS_PUBLIC ||
				($a_set["priority"] == 0 &&
				ilBlockSetting::_lookup("news", "public_notifications",
				0, $obj_id)))
			{
				$this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_public"));
			}
			else
			{
				$this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_users"));
			}
			$this->tpl->parseCurrentBlock();
		}

		// content
		if ($a_set["content"] != "")
		{
			$this->tpl->setCurrentBlock("content");
			$this->tpl->setVariable("VAL_CONTENT", ilUtil::makeClickable($a_set["content"], true));
			$this->tpl->parseCurrentBlock();
		}
		if ($a_set["content_long"] != "")
		{
			$this->tpl->setCurrentBlock("long");
			$this->tpl->setVariable("VAL_LONG_CONTENT", ilUtil::makeClickable($a_set["content_long"], true));
			$this->tpl->parseCurrentBlock();
		}
		if ($a_set["update_date"] != $a_set["creation_date"])	// update date
		{
			$this->tpl->setCurrentBlock("ni_update");
			$this->tpl->setVariable("TXT_LAST_UPDATE", $lng->txt("last_update"));
			$this->tpl->setVariable("VAL_LAST_UPDATE",
				ilFormat::formatDate($a_set["update_date"], "datetime", true));
			$this->tpl->parseCurrentBlock();
		}

		// forum hack, not nice
		$add = "";
		if ($obj_type == "frm" && $a_set["context_sub_obj_type"] == "pos"
			&& $a_set["context_sub_obj_id"] > 0)
		{
			include_once("./Modules/Forum/classes/class.ilObjForumAccess.php");
			$pos = $a_set["context_sub_obj_id"];
			$thread = ilObjForumAccess::_getThreadForPosting($pos);
			if ($thread > 0)
			{
				$add = "_".$thread."_".$pos;
			}
		}
		$url_target = "./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".
			$obj_type."_".$a_set["ref_id"].$add;
		$this->tpl->setCurrentBlock("context");
		$cont_loc = new ilLocatorGUI();
		$cont_loc->addContextItems($a_set["ref_id"], true);
		$this->tpl->setVariable("CONTEXT_LOCATOR",
			$cont_loc->getHTML());
		$this->tpl->setVariable("HREF_CONTEXT_TITLE", $url_target);
		$this->tpl->setVariable("CONTEXT_TITLE", $obj_title);
		$this->tpl->setVariable("IMG_CONTEXT_TITLE",
			ilUtil::getImagePath("icon_".$obj_type."_b.gif"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("HREF_TITLE", $url_target);
		
		// title
		if ($a_set["content_is_lang_var"])
		{
			$this->tpl->setVariable("VAL_TITLE", $lng->txt($a_set["title"]));
		}
		else
		{
			$this->tpl->setVariable("VAL_TITLE", ilUtil::stripSlashes($a_set["title"]));			// title
		}

		// creation date
		$this->tpl->setVariable("VAL_CREATION_DATE",
			ilFormat::formatDate($a_set["creation_date"], "datetime", true));
		$this->tpl->setVariable("TXT_CREATED", $lng->txt("created"));
		
		$this->tpl->parseCurrentBlock();
	}

}
?>
