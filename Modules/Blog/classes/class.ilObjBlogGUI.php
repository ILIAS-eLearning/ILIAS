<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
include_once("./Modules/Blog/classes/class.ilBlogPosting.php");

/**
* Class ilObjBlogGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjBlogGUI: ilBlogPostingGUI
*
* @extends ilObject2GUI
*/
class ilObjBlogGUI extends ilObject2GUI
{
	protected $month; // [string]

	function getType()
	{
		return "blog";
	}

	protected function initCustomEditForm()
	{
		global $lng;

		$lng->loadLanguageModule("blog");
		
		$notes = new ilCheckboxInputGUI($lng->txt("blog_enable_notes"), "notes");
		$this->form->addItem($notes);		
	}

	protected function initCustomEditValues(array &$a_values)
	{
		$a_values["notes"] = $this->object->getNotesStatus();
	}

	protected function afterUpdate()
	{
		$this->object->updateNotesStatus($this->form->getInput("notes"));

		parent::afterUpdate();
	}

	function setTabs()
	{
		global $lng, $ilTabs;

		$this->ctrl->setParameter($this,"wsp_id",$this->node_id);

		if ($this->getAccessHandler()->checkAccess('read', '', $this->node_id))
		{
			$this->tabs_gui->addTab('view_content', $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}

		if ($this->getAccessHandler()->checkAccess('write', '', $this->node_id))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this)
				, "", $force_active);
		}

		/*
		$ilTabs->setBackTarget($lng->txt("back"),
			$this->ctrl->getLinkTargetByClass("ilobjworkspacefoldergui", ""));
		*/
	}

	function &executeCommand()
	{
		global $ilCtrl, $tpl, $ilTabs, $lng;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			case 'ilblogpostinggui':

				$ilCtrl->setParameter($this, "bmn", $_REQUEST["bmn"]);
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, ""));

				include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");
				$bpost_gui = new ilBlogPostingGUI($this->node_id, $this->getAccessHandler(),
					$_GET["page"], $_GET["old_nr"], $this->object->getNotesStatus());
				
				if (!$this->getAccessHandler()->checkAccess("write", "", $this->node_id))
				{
					$bpost_gui->setEnableEditing(false);
				}

				/*
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$bpost_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
					$this->object->getStyleSheetId(), "blog"));
				$this->setContentStyleSheet();
				*/

				$ret = $ilCtrl->forwardCommand($bpost_gui);
				if ($ret != "")
				{
					$tpl->setContent($ret);
				}
				break;

			default:
				$this->prepareOutput();
				if(!$cmd)
				{
					$cmd = "render";
				}
				$this->$cmd();
				break;
		}

		return true;
	}

	/**
	 * Create new posting
	 */
	function createPosting()
	{
		global $ilCtrl, $lng;

		if($_POST["title"])
		{
			// create new posting
			include_once("./Modules/Blog/classes/class.ilBlogPosting.php");
			$posting = new ilBlogPosting();
			$posting->setTitle($_POST["title"]);
			$posting->setBlogId($this->object->getId());
			$posting->create();

			$ilCtrl->setParameterByClass("ilblogpostinggui", "page", $posting->getId());
			$ilCtrl->redirectByClass("ilblogpostinggui", "edit");
		}
		else
		{
			$ilCtrl->redirect($this, "render");
		}
	}

	/**
	* Render root folder
	*/
	function render()
	{
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilToolbar;

		if(!$this->getAccessHandler()->checkAccess("read", "", $this->node_id))
		{
			ilUtil::sendInfo($lng->txt("no_permission"), true);
			// $ilCtrl->redirect($this, "infoScreen");
			return;
		}

		$this->month = $_REQUEST["bmn"];
		$lng->loadLanguageModule("blog");

		// gather postings by month
		$items = array();
		foreach(ilBlogPosting::getAllPostings($this->object->getId()) as $posting)
		{
			$month = substr($posting["created"]->get(IL_CAL_DATE), 0, 7);
			$items[$month][$posting["id"]] = $posting;
		}

		$this->renderToolbar();
		
		if($items)
		{			
			// current month (if none given or empty)
			if(!$this->month || !$items[$this->month])
			{
				$this->month = array_keys($items);
				$this->month = array_shift($this->month);
			}

			if($items[$this->month])
			{
				include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
				$tpl->setDescription(ilCalendarUtil::_numericMonthToString(substr($this->month, 6)).
					" ".substr($this->month, 0, 4));

				$ilCtrl->setParameter($this, "bmn", $this->month);
				$tpl->setContent($this->renderList($items[$this->month]));
				$tpl->setRightContent($this->renderNavigation($items));
			}
		}
	}

	function renderToolbar()
	{
		global $lng, $ilCtrl, $ilToolbar;

		if($this->getAccessHandler()->checkAccess("write", "", $this->node_id))
		{
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));

			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$title = new ilTextInputGUI($lng->txt("title"), "title");
			$ilToolbar->addInputItem($title, $lng->txt("title"));

			$ilToolbar->addFormButton($lng->txt("blog_add_posting"), "createPosting");
		}
	}

	function renderList(array $items)
	{
		global $lng, $ilCtrl;
		
		include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
		$wtpl = new ilTemplate("tpl.blog_list.html", true, true, "Modules/blog");
		
		foreach($items as $item)
		{
			$ilCtrl->setParameterByClass("ilblogpostinggui", "page", $item["id"]);
			$preview = $ilCtrl->getLinkTargetByClass("ilblogpostinggui", "preview");

			// actions
			if($this->getAccessHandler()->checkAccess("write", "", $this->node_id))
			{
				include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
				$alist = new ilAdvancedSelectionListGUI();
				$alist->setId($item["id"]);
				$alist->setListTitle($lng->txt("actions"));
				$alist->addItem($lng->txt("edit"), "edit", 
					$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "edit"));
				$alist->addItem($lng->txt("delete"), "delete",
					$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "deleteBlogPostingConfirmationScreen"));

				$wtpl->setCurrentBlock("actions");
				$wtpl->setVariable("ACTION_SELECTOR", $alist->getHTML());
				$wtpl->parseCurrentBlock();
			}

			// comments
			if($this->object->getNotesStatus())
			{
				// count (public) notes
				include_once("Services/Notes/classes/class.ilNote.php");
				$count = sizeof(ilNote::_getNotesOfObject($this->obj_id, 
					$item["id"], "wpg", IL_NOTE_PUBLIC));
				
				$wtpl->setCurrentBlock("comments");
				$wtpl->setVariable("TEXT_COMMENTS", $lng->txt("blog_comments"));
				$wtpl->setVariable("URL_COMMENTS", $preview);
				$wtpl->setVariable("COUNT_COMMENTS", $count);
				$wtpl->parseCurrentBlock();
			}

			$wtpl->setCurrentBlock("posting");
			
			// title
			$wtpl->setVariable("URL_TITLE", $preview);
			$wtpl->setVariable("TITLE", $item["title"]);
			$wtpl->setVariable("DATETIME",
				ilDatePresentation::formatDate($item["created"], IL_CAL_DATE));

			// permanent link
			$wtpl->setVariable("URL_PERMALINK", $preview); // :TODO:
			$wtpl->setVariable("TEXT_PERMALINK", $lng->txt("blog_permanent_link"));

			// content
			$page = new ilBlogPosting($item["id"]);
			$page->buildDom();
			$wtpl->setVariable("CONTENT", $page->getFirstParagraphText());
			$wtpl->setVariable("URL_MORE", $preview); 
			$wtpl->setVariable("TEXT_MORE", $lng->txt("blog_list_more"));

			$wtpl->parseCurrentBlock();
		}

		return $wtpl->get();
	}

	function renderNavigation(array $items)
	{
		global $ilCtrl;

		$max_detail_postings = 10;
		
		$wtpl = new ilTemplate("tpl.blog_list_navigation.html",	true, true,
			"Modules/blog");

		$counter = 0;
		foreach($items as $month => $postings)
		{
			$month_name = ilCalendarUtil::_numericMonthToString(substr($month, 6)).
				" ".substr($month, 0, 4);

			$ilCtrl->setParameter($this, "bmn", $month);
			$month_url = $ilCtrl->getLinkTarget($this, "render");

			// list postings for month
			if($counter < $max_detail_postings)
			{
				$wtpl->setCurrentBlock("navigation_item");
				foreach($postings as $id => $posting)
				{
					$counter++;

					$caption = /* ilDatePresentation::formatDate($posting["created"], IL_CAL_DATETIME).
						", ".*/ $posting["title"];

					$ilCtrl->setParameterByClass("ilblogpostinggui", "page", $id);
					$wtpl->setVariable("NAV_ITEM_URL",
						$ilCtrl->getLinkTargetByClass("ilblogpostinggui", "preview"));
					$wtpl->setVariable("NAV_ITEM_CAPTION", $caption);
					$wtpl->parseCurrentBlock();
				}

				$wtpl->setCurrentBlock("navigation_month_details");
				$wtpl->setVariable("NAV_MONTH", $month_name);
				$wtpl->setVariable("URL_MONTH", $month_url);
				$wtpl->parseCurrentBlock();
			}
			// summarized month
			else
			{
				$ilCtrl->setParameter($this, "bmn", $month);

				$wtpl->setCurrentBlock("navigation_month");
				$wtpl->setVariable("MONTH_NAME", $month_name);				
				$wtpl->setVariable("URL_MONTH", $month_url);
				$wtpl->setVariable("MONTH_COUNT", sizeof($postings));
				$wtpl->parseCurrentBlock();
			}
		}

		$ilCtrl->setParameter($this, "bmn", $this->month);

		return $wtpl->get();
	}

	function _goto($a_target)
	{
		$id = explode("_", $a_target);

		// :TODO: doesn't seem to work
		$_GET["cmd"] = "preview";
		$_GET["wsp_id"] = $id[0];
		$_GET["page"] = $id[1];
		$_GET["baseClass"] = "ilPersonalDesktopGUI";
		$_GET["cmdClass"] = "ilblogpostinggui";
		include("ilias.php");
		exit;
	}
}

?>