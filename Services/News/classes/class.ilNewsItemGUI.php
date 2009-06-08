<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ("Services/News/classes/class.ilNewsItemGUIGen.php");

/**
* User Interface for NewsItem entities.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilNewsItemGUI extends ilNewsItemGUIGen
{

	function __construct()
	{
		global $ilCtrl;
		
		parent::__construct();
		
		$ilCtrl->saveParameter($this, "add_mode");
	}
	
	function getHTML()
	{
		global $lng, $ilCtrl;
		
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
		global $ilUser;
		
		$a_news_item->setContextObjId($this->ctrl->getContextObjId());
		$a_news_item->setContextObjType($this->ctrl->getContextObjType());
		$a_news_item->setUserId($ilUser->getId());

		$news_set = new ilSetting("news");
		if (!$news_set->get("enable_rss_for_internal"))
		{
			$a_news_item->setVisibility("users");
		}
	}
	
	/**
	* FORM NewsItem: Prepare form.
	*
	* @param	object	$a_form_gui	ilPropertyFormGUI instance.
	*/
	public function prepareFormNewsItem(&$a_form_gui)
	{
		$a_form_gui->setTitleIcon(ilUtil::getImagePath("icon_news.gif"));
		
		$news_set = new ilSetting("news");
		if (!$news_set->get("enable_rss_for_internal"))
		{
			$a_form_gui->removeItemByPostVar("news_visibility");
		}
		else
		{
			$nv = $a_form_gui->getItemByPostVar("news_visibility");
			if (is_object($nv))
			{
				$nv->setValue(ilNewsItem::_getDefaultVisibilityForRefId($_GET["ref_id"]));
			}
		}
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
		
		return parent::saveNewsItem();
	}
	
	function exitSaveNewsItem()
	{
		global $ilCtrl;
		
		if ($_GET["add_mode"] == "block")
		{
			$ilCtrl->returnToParent($this);
		}
		else
		{
			$ilCtrl->redirect($this, "editNews");
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
		
		return parent::updateNewsItem();
	}

	function exitUpdateNewsItem()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "editNews");
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
		global $ilCtrl;
		
		if ($_GET["add_mode"] == "block")
		{
			$ilCtrl->returnToParent($this);
		}
		else
		{
			return $this->editNews();
		}
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
		
		// check whether at least one item is selected
		if (count($_POST["news_id"]) == 0)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->editNews();
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
