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

include_once ("Services/News/classes/class.ilNewsItemGUIGen.php");

/**
* User Interface for NewsItem entities.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNewsItemGUI extends ilNewsItemGUIGen
{

	function getHTML()
	{
		global $lng;
		
		$lng->LoadLanguageModule("news");
		return $this->getNewsForContextBlock();
	}
	
	/**
	* BLOCK NewsForContext: Prepare block. (Can be overwritten in derived classes)
	*
	* @param	object	$a_block_gui	ilBlockGUI instance.
	*/
	public function prepareBlockNewsForContext(&$a_block_gui)
	{
		$a_block_gui->setParentClass("ilinfoscreengui");
		$a_block_gui->setParentCmd("showSummary");
		$a_block_gui->setEnableEdit($this->getEnableEdit());
	}

	/**
	* BLOCK NewsForContext: Prepare block query for news block.
	*/
	function prepareBlockQueryNewsForContext(&$a_news_item)
	{
		$a_news_item->setContextObjId($this->ctrl->getContextObjId());
		$a_news_item->setContextObjType($this->ctrl->getContextObjType());
	}
	
	/**
	* FORM NewsItem: Prepare saving.
	*/
	function prepareSaveNewsItem(&$a_news_item)
	{
		$a_news_item->setContextObjId($this->ctrl->getContextObjId());
		$a_news_item->setContextObjType($this->ctrl->getContextObjType());
	}
	
	/**
	* FORM NewsItem: Prepare form.
	*
	* @param	object	$a_form_gui	ilPropertyFormGUI instance.
	*/
	public function prepareFormNewsItem(&$a_form_gui)
	{
		$a_form_gui->setTitleIcon(ilUtil::getImagePath("icon_news.gif"));
	}
	
	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	function saveNewsItem()
	{
		if (!$this->getEnableEdit())
		{
			return;
		}
		parent::saveNewsItem();
		if ($this->checkInputNewsItem())
		{
			return $this->editNews();
		}
	}

	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	function updateNewsItem()
	{
		if (!$this->getEnableEdit())
		{
			return;
		}
		parent::updateNewsItem();
		if ($this->checkInputNewsItem())
		{
			return $this->editNews();
		}
	}

	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	function cancelUpdateNewsItem()
	{
		return $this->editNews();
	}

	/**
	* FORM NewsItem: Save NewsItem.
	*
	*/
	function cancelSaveNewsItem()
	{
		return $this->editNews();
	}

	function editNews()
	{
		if (!$this->getEnableEdit())
		{
			return;
		}
		return $this->getNewsForContextTable();
	}

	
	function cancelUpdate()
	{
		return $this->editNews();
	}
	
	/**
	* TABLE MewsForContext: Prepare the new table
	*/
	function prepareTableNewsForContext(&$a_table_gui)
	{
		global $ilCtrl, $lng;
		
		$a_table_gui->setDefaultOrderField("creation_date");
		$a_table_gui->setDefaultOrderDirection("desc");
		$a_table_gui->addCommandButton("createNewsItem", $lng->txt("add"));
		$a_table_gui->addMultiCommand("confirmDeletionNewsItems", $lng->txt("delete"));
		$a_table_gui->setTitle($lng->txt("news"), "icon_news.gif", $lng->txt("news"));
		$a_table_gui->setSelectAllCheckbox("news_id");
	}

	/**
	* Confirmation Screen.
	*/
	function confirmDeletionNewsItems()
	{
		global $ilCtrl, $lng;

		if (!$this->getEnableEdit())
		{
			return;
		}
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($ilCtrl->getFormAction($this, "deleteNewsItems"));
		$c_gui->setHeaderText($lng->txt("info_delete_sure"));
		$c_gui->setCancel($lng->txt("cancel"), "editNews");
		$c_gui->setConfirm($lng->txt("confirm"), "deleteNewsItems");

		// add items to delete
		foreach($_POST["news_id"] as $news_id)
		{
			$news = new ilNewsItem($news_id);
			$c_gui->addItem("news_id[]", $news_id, $news->getTitle(),
				ilUtil::getImagePath("icon_news.gif"));
		}
		
		return $c_gui->getHTML();
	}

	/**
	* Delete news items.
	*/
	function deleteNewsItems()
	{
		if (!$this->getEnableEdit())
		{
			return;
		}
		// delete all selected news items
		foreach($_POST["news_id"] as $news_id)
		{
			$news = new ilNewsItem($news_id);
			$news->delete();
		}
		
		return $this->editNews();
	}
}
