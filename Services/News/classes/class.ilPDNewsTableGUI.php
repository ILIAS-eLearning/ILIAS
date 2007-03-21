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
		
		// user
		if ($a_set["user_id"] > 0)
		{
			$this->tpl->setCurrentBlock("user_info");
			$user_obj = new ilObjUser($a_set["user_id"]);
			$this->tpl->setVariable("USR_IMAGE",
				$user_obj->getPersonalPicturePath("xxsmall"));
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

		if ($a_set["creation_date"] != $a_set["update_date"])
		{
			$this->tpl->setCurrentBlock("ni_update");
			$this->tpl->setVariable("VAL_LAST_UPDATE", $a_set["update_date"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("VAL_CREATION_DATE", $a_set["creation_date"]);
		$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
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
		
		// context link
		//if ($_GET["news_context"] != "")		// link
		{
			$obj_id = ilObject::_lookupObjId($a_set["ref_id"]);
			$obj_type = ilObject::_lookupType($obj_id);
			$this->tpl->setCurrentBlock("link");
			$this->tpl->setVariable("HREF_LINK",
				"./goto.php?client_id=".rawurlencode(CLIENT_ID)."&target=".$obj_type."_".$a_set["ref_id"]);
			$txt = in_array($obj_type, array("sahs", "lm", "dbk", "htlm"))
				? "lres"
				: "obj_".$obj_type;
			$this->tpl->setVariable("ALT_LINK", $lng->txt($txt));
			$this->tpl->setVariable("TXT_LINK", ilObject::_lookupTitle($obj_id));
			$this->tpl->setVariable("IMG_LINK", ilUtil::getImagePath("icon_".$obj_type."_s.gif"));
			$this->tpl->parseCurrentBlock();
		}

	}

}
?>
