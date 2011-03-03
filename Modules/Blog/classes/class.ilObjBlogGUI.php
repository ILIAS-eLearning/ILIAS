<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

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
	function getType()
	{
		return "blog";
	}

	function setTabs()
	{
		global $lng;

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
	}

	function &executeCommand()
	{
		global $ilCtrl, $tpl;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{
			case 'ilblogpostinggui':
				include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");
				$bpost_gui = new ilBlogPostingGUI($this->node_id, $this->getAccessHandler(),
					$_GET["page"], $_GET["old_nr"]);

				/*
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$bpost_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
					$this->object->getStyleSheetId(), "blog"));
				$this->setContentStyleSheet();
				*/

				if (!$this->getAccessHandler()->checkAccess("write", "", $this->node_id) &&
					!$this->getAccessHandler()->checkAccess("edit_content", "", $this->node_id))
				{
					$bpost_gui->setEnableEditing(false);
				}
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

		// create new posting
		include_once("./Modules/Blog/classes/class.ilBlogPosting.php");
		$posting = new ilBlogPosting();
		$posting->setTitle($lng->txt("blog_new_posting_title"));
		$posting->setBlogId($this->object->getId());
		$posting->create();

		$ilCtrl->setParameterByClass("ilblogpostinggui", "page", $posting->getId());
		$ilCtrl->redirectByClass("ilblogpostinggui", "edit");
	}

	/**
	* Render root folder
	*/
	function render()
	{
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilToolbar;
		
		include_once("./Modules/Blog/classes/class.ilBlogPosting.php");

		if(!$this->getAccessHandler()->checkAccess("read", "", $this->node_id))
		{
			ilUtil::sendInfo($lng->txt("no_permission"), true);
			// $ilCtrl->redirect($this, "infoScreen");
			return;
		}

		$ilToolbar->addButton($lng->txt("blog_add_posting"),
			$ilCtrl->getLinkTarget($this, "createPosting"));

		$ilTabs->clearTargets();

		$post = ($_GET["page"] != "")
			? $_GET["page"]
			: ilBlogPosting::getLastPost($this->object->getId());
		$_GET["page"] = $post;

		// valid post?
		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		if (!ilBlogPosting::exists($this->object->getId(), $post))
		{
			$post = ilBlogPosting::getLastPost($this->object->getId());
		}

		// only if post
		if ($post && ilBlogPosting::exists($this->object->getId(), $post))
		{
			// page exists, show it !
			$ilCtrl->setParameter($this, "page", $post);

			include_once("./Modules/Blog/classes/class.ilBlogPostingGUI.php");
			$bpost_gui = new ilBlogPostingGUI($this->node_id, $this->getAccessHandler(), $post);

			/*
			include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
			$bpost_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
				$this->object->getStyleSheetId(), "wiki"));
			$this->setContentStyleSheet();
			*/

			if (!$this->getAccessHandler()->checkAccess("write", "", $this->node_id) &&
				!$this->getAccessHandler()->checkAccess("edit_content", "", $this->node_id))
			{
				$bpost_gui->setEnableEditing(false);
			}

			$ilCtrl->setCmdClass("ilblogpostinggui");
			$ilCtrl->setCmd("preview");
			$html = $ilCtrl->forwardCommand($bpost_gui);
			
			$tpl->setContent($html);
		}
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