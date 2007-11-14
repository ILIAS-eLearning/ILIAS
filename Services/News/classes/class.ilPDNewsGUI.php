<?php

/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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

/**
* News on PD
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPDNewsGUI:
*
*/

class ilPDNewsGUI
{

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilPDNewsGUI()
	{
		global $tpl, $lng, $ilCtrl;

		// initiate variables
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		
		$lng->loadLanguageModule("news");
		
		$this->ctrl->saveParameter($this, "news_ref_id");
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
				
			default:
				$cmd = $this->ctrl->getCmd("view");
				$this->displayHeader();
				$this->$cmd();
				break;
		}
		$this->tpl->show(true);
		return true;
	}

	/**
	* display header and locator
	*/
	function displayHeader()
	{
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
		
		// catch feedback message
		ilUtil::sendInfo();
		// display infopanel if something happened
		ilUtil::infoPanel();
	}

	/*
	* display notes
	*/
	function view()
	{
		global $ilUser, $lng, $tpl, $ilCtrl;

		$news_tpl = new ilTemplate("tpl.pd_news.html", true, true, "Services/News");

		include_once("Services/News/classes/class.ilNewsSubscription.php");
		include_once("Services/News/classes/class.ilNewsItem.php");
		
		// get news
		//$ref_ids = ilNewsSubscription::_getSubscriptionsOfUser($ilUser->getId());
		
		$ref_ids = array();
		$obj_ids = array();
		if ($ilUser->prefs["pd_items_news"] != "n")
		{
			$pd_items = $ilUser->getDesktopItems();
			foreach($pd_items as $item)
			{
				$ref_ids[] = $item["ref_id"];
				$obj_ids[] = $item["obj_id"];
			}
		}
		
		$sel_ref_id = ($_GET["news_ref_id"] > 0)
			? $_GET["news_ref_id"]
			: $ilUser->getPref("news_sel_ref_id");
		
		// todo: time period
		$per = ilNewsItem::_lookupUserPDPeriod($ilUser->getId());
		$news_obj_ids = ilNewsItem::filterObjIdsPerNews($obj_ids, $per);
		
		// related objects (contexts) of news
		$news_tpl->setCurrentBlock("related_option");
		$news_tpl->setVariable("VAL_RELATED", "0");
		$news_tpl->setVariable("TXT_RELATED", $lng->txt("news_all_items"));
		$news_tpl->parseCurrentBlock();
		
		$conts = array();
		$sel_has_news = false;
		foreach ($ref_ids as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$title = ilObject::_lookupTitle($obj_id);
			if (in_array($obj_id, $news_obj_ids))
			{
				$conts[$ref_id] = $title;
				if ($sel_ref_id == $ref_id)
				{
					$sel_has_news = true;
				}
			}
		}
		// reset selected news ref id, if no news are given for id
		if (!$sel_has_news)
		{
			$sel_ref_id = "";
		}
		asort($conts);
		foreach($conts as $ref_id => $title)
		{
			$news_tpl->setCurrentBlock("related_option");
			$news_tpl->setVariable("VAL_RELATED", $ref_id);
			$news_tpl->setVariable("TXT_RELATED", $title);
			if ($sel_ref_id == $ref_id)
			{
				$news_tpl->setVariable("SEL", ' selected="selected" ');
			}
			$news_tpl->parseCurrentBlock();
		}
		
		$news_tpl->setCurrentBlock("related_selection");
		$news_tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));
		$news_tpl->setVariable("TXT_RELATED_TO", $lng->txt("news_related_to"));
		$news_tpl->setVariable("TXT_CHANGE", $lng->txt("change"));
		$news_tpl->parseCurrentBlock();
		
		$nitem = new ilNewsItem();
		if ($sel_ref_id > 0)
		{
			$obj_id = ilObject::_lookupObjId($sel_ref_id);
			$obj_type = ilObject::_lookupType($obj_id);
			$nitem->setContextObjId($obj_id);
			$nitem->setContextObjType($obj_type);
			$news_items = $nitem->getNewsForRefId($sel_ref_id, false,
				false, $per, true);
		}
		else
		{
			$news_items = $nitem->_getNewsItemsOfUser($ilUser->getId(), false,
				true, $per);
		}
				
		include_once("./Services/News/classes/class.ilPDNewsTableGUI.php");
		$pd_news_table = new ilPDNewsTableGUI($this, "view");
		$pd_news_table->setData($news_items);

		$news_tpl->setVariable("NEWS", $pd_news_table->getHTML());

		$tpl->setContent($news_tpl->get());
	}
	
	/**
	* change related object
	*/
	function changeRelatedObject()
	{
		global $ilUser;
		
		$this->ctrl->setParameter($this, "news_ref_id", $_POST["news_ref_id"]);
		$ilUser->writePref("news_sel_ref_id", $_POST["news_ref_id"]);
		$this->ctrl->redirect($this, "view");
	}

}
?>
