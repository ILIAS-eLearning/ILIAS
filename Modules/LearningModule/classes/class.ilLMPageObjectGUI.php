<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once("./Modules/LearningModule/classes/class.ilLMObjectGUI.php");
require_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
require_once ("./Modules/LearningModule/classes/class.ilInternalLinkGUI.php");

/**
* Class ilLMPageObjectGUI
*
* User Interface for Learning Module Page Objects Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilLMPageObjectGUI: ilPageObjectGUI
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMPageObjectGUI extends ilLMObjectGUI
{
	var $obj;

	/**
	* Constructor
	*
	* @param	object		$a_content_obj		content object (lm | dbk)
	* @access	public
	*/
	function ilLMPageObjectGUI(&$a_content_obj)
	{
		global $ilias, $tpl, $lng;

		parent::ilLMObjectGUI($a_content_obj);

	}


	/**
	* set content object dependent page object (co page)
	*/
	function setLMPageObject(&$a_pg_obj)
	{
		$this->obj =& $a_pg_obj;
		$this->obj->setLMId($this->content_object->getId());
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tpl, $ilCtrl, $ilTabs;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
//echo "<br>:cmd:".$this->ctrl->getCmd().":cmdClass:".$this->ctrl->getCmdClass().":".
//	":nextClass:".$next_class.":"; flush();

		switch($next_class)
		{
			case "ilpageobjectgui":

				// Determine whether the view of a learning resource should
				// be shown in the frameset of ilias, or in a separate window.
				//$showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";
				$showViewInFrameset = true;
				$lm_set = new ilSetting("lm");

				$this->ctrl->setReturn($this, "edit");
				$page_gui =& new ilPageObjectGUI($this->obj->content_object->getType(),
					$this->obj->getId());
				$page_gui->setEditPreview(true);
				$page_gui->activateMetaDataEditor($this->content_object->getID(),
					$this->obj->getId(), $this->obj->getType(),
					$this->obj, "MDUpdateListener");

				// set page view link
				if ($showViewInFrameset)
				{
					$view_frame = ilFrameTargetInfo::_getFrame("MainContent");
				}
				else
				{
					$view_frame = "ilContObj".$this->content_object->getID();
				}
				$page_gui->setViewPageLink(ILIAS_HTTP_PATH."/goto.php?target=pg_".$this->obj->getId().
					"_".$_GET["ref_id"],
					$view_frame);

				$page_gui->setStyleId($this->content_object->getStyleSheetId());
				$page_gui->setIntLinkHelpDefault("StructureObject", $_GET["ref_id"]);
				$page_gui->setTemplateTargetVar("ADM_CONTENT");
				$page_gui->getPageObject()->buildDom();
				$int_links = $page_gui->getPageObject()->getInternalLinks();
				$link_xml = $this->getLinkXML($int_links);
				$page_gui->setLinkXML($link_xml);

				$page_gui->enableChangeComments($this->content_object->isActiveHistoryUserComments());
				$page_gui->setFileDownloadLink("ilias.php?cmd=downloadFile&ref_id=".$_GET["ref_id"]."&baseClass=ilLMPresentationGUI");
				$page_gui->setFullscreenLink("ilias.php?cmd=fullscreen&ref_id=".$_GET["ref_id"]."&baseClass=ilLMPresentationGUI");
				$page_gui->setLinkParams("ref_id=".$this->content_object->getRefId());
				$page_gui->setSourcecodeDownloadScript("ilias.php?ref_id=".$_GET["ref_id"]."&baseClass=ilLMPresentationGUI");
				$page_gui->setPresentationTitle(
					ilLMPageObject::_getPresentationTitle($this->obj->getId(),
					$this->content_object->getPageHeader(), $this->content_object->isActiveNumbering()));
				$page_gui->setLocator($contObjLocator);
				$page_gui->setHeader($this->lng->txt("page").": ".$this->obj->getTitle());
				$page_gui->setEnabledActivation(true);
				if ($lm_set->get("time_scheduled_page_activation"))
				{
					$page_gui->setEnabledScheduledActivation(true);
				}
				$page_gui->setActivationListener($this, "activatePage");
				//$page_gui->setActivated($this->obj->getActive());
				
				$up_gui = ($this->content_object->getType() == "dbk")
					? "ilobjdlbookgui"
					: "ilobjlearningmodulegui";
				$ilCtrl->setParameterByClass($up_gui, "active_node", $this->obj->getId());
				$page_gui->setExplorerUpdater("tree", "tree_div",
				$ilCtrl->getLinkTargetByClass($up_gui, "explorer", "", true));
				
				$tpl->setTitleIcon(ilUtil::getImagePath("icon_pg_b.gif"));
				$tpl->setTitle($this->lng->txt("page").": ".$this->obj->getTitle());
				if ($this->content_object->getLayoutPerPage())
				{
					$page_gui->setTabHook($this, "addPageTabs");
				}
				$ret = $this->ctrl->forwardCommand($page_gui);
				
				//$ret =& $page_gui->executeCommand();
				$tpl->setContent($ret);
				break;

			default:
				$ret =& $this->$cmd();
				break;
		}
	}


	/*
	* display content of page (edit view)
	*/
	function edit()
	{
//echo "<br>umschuss";
		$this->ctrl->setCmdClass("ilpageobjectgui");
		$this->ctrl->setCmd("edit");
		$this->executeCommand();
		//$this->setTabs();
	}

	/*
	* display content of page (edit view)
	*/
	function preview()
	{
		$this->ctrl->setCmdClass("ilpageobjectgui");
		$this->ctrl->setCmd("preview");
		$this->executeCommand();
//		$this->setTabs();
	}

	/**
	* save co page object
	*/
	function save()
	{
		$this->obj =& new ilLMPageObject($this->content_object);
		$this->obj->setType("pg");
		$this->obj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->obj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->obj->setLMId($this->content_object->getId());
		$this->obj->create();

		// obj_id is empty, if page is created from "all pages" screen
		// -> a free page is created (not in the tree)
//echo "<br>savePage:".$_GET["obj_id"].":";
		if ($_GET["obj_id"] != 0)
		{
			$this->putInTree();

			// check the tree
			$this->checkTree();

			ilUtil::redirect($this->ctrl->getLinkTargetByClass("ilStructureObjectGUI",
				"edit", "", true));
		}
	}

	/**
	* cancel
	*/
	function cancel()
	{
		if ($_GET["obj_id"] != 0)
		{
			ilUtil::redirect($this->ctrl->getLinkTargetByClass("ilStructureObjectGUI",
				"view", "", true));
		}
		//$this->ctrl->returnToParent($this);
	}

	/**
	* get link targets
	*/
	function getLinkXML($a_int_links)
	{
		if ($a_layoutframes == "")
		{
			$a_layoutframes = array();
		}
		$link_info = "<IntLinkInfos>";
		foreach ($a_int_links as $int_link)
		{
			$target = $int_link["Target"];
			if (substr($target, 0, 4) == "il__")
			{
				$target_arr = explode("_", $target);
				$target_id = $target_arr[count($target_arr) - 1];
				$type = $int_link["Type"];
				$targetframe = ($int_link["TargetFrame"] != "")
					? $int_link["TargetFrame"]
					: "None";
					
				// anchor
				$anc = $anc_add = "";
				if ($int_link["Anchor"] != "")
				{
					$anc = $int_link["Anchor"];
					$anc_add = "_".rawurlencode($int_link["Anchor"]);
				}

				switch($type)
				{
					case "PageObject":
					case "StructureObject":
						$lm_id = ilLMObject::_lookupContObjID($target_id);
						$cont_obj =& $this->content_object;
						if ($lm_id == $cont_obj->getId())
						{
							$ltarget = "";
							if ($type == "PageObject")
							{
								$this->ctrl->setParameter($this, "obj_id", $target_id);
								$href = $this->ctrl->getLinkTargetByClass(get_class($this), "edit");
							}
							else
							{
								$this->ctrl->setParameterByClass("ilstructureobjectgui", "obj_id", $target_id);
								$href = $this->ctrl->getLinkTargetByClass("ilstructureobjectgui", "view");
							}
							$href = str_replace("&", "&amp;", $href);
							$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
						}
						else
						{
							if ($type == "PageObject")
							{
								$href = "../goto.php?target=pg_".$target_id.$anc_add;
							}
							else
							{
								$href = "../goto.php?target=st_".$target_id;
							}
							$ltarget = "ilContObj".$lm_id;
						}
						break;

					case "GlossaryItem":
						$ltarget = $nframe = "_blank";
						$href = "ilias.php?cmdClass=illmpresentationgui&amp;baseClass=ilLMPresentationGUI&amp;".
							"obj_type=$type&amp;cmd=glossary&amp;ref_id=".$_GET["ref_id"].
							"&amp;obj_id=".$target_id."&amp;frame=$nframe";
						break;

					case "MediaObject":
						$ltarget = $nframe = "_blank";
						$href = "ilias.php?cmdClass=illmpresentationgui&amp;baseClass=ilLMPresentationGUI&amp;obj_type=$type&amp;cmd=media&amp;ref_id=".$_GET["ref_id"].
							"&amp;mob_id=".$target_id."&amp;frame=$nframe";
						break;
						
					case "RepositoryItem":
						$obj_type = ilObject::_lookupType($target_id, true);
						$obj_id = ilObject::_lookupObjId($target_id);
						$href = "./goto.php?target=".$obj_type."_".$target_id;
						$t_frame = ilFrameTargetInfo::_getFrame("MainContent", $obj_type);
						$ltarget = $t_frame;
						break;
				}
				
				$anc_par = 'Anchor="'.$anc.'"';
				
				$link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".
					"TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" $anc_par/>";
			}
		}
		$link_info.= "</IntLinkInfos>";
//echo ":".htmlentities($link_info).":";
		return $link_info;
	}
	
	
	/**
	* history
	*
	* @access	public
	*/
/*
	function history()
	{
		$this->setTabs();
		
		require_once("classes/class.ilHistoryGUI.php");
		$hist_gui =& new ilHistoryGUI($this->obj->getId() ,
			$this->content_object->getType().":pg");
		$hist_html = $hist_gui->getHistoryTable(
			$this->ctrl->getParameterArray($this, "history"),
			$this->content_object->isActiveHistoryUserComments()
			);
		
		$this->tpl->setVariable("ADM_CONTENT", $hist_html);
	}*/

	/**
	* update history
	*/
	function updateHistory()
	{
		require_once("classes/class.ilHistory.php");
		ilHistory::_createEntry($this->obj->getId(), "update",
			"", $this->content_object->getType().":pg",
			"", true);
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		global $ilTabs;
return;
//echo "setTabs";
		// catch feedback message
		#include_once("classes/class.ilTabsGUI.php");
		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($ilTabs);
		
		$this->tpl->setCurrentBlock("header_image");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_pg_b.gif"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("content");

		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
		$this->tpl->setVariable("HEADER",
			$this->lng->txt($this->obj->getType()).": ".$this->obj->getTitle());
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
/*
	function getTabs(&$tabs_gui)
	{
		// back to upper context
		$tabs_gui->addTarget("edit", $this->ctrl->getLinkTarget($this, "view")
			, array("", "view"), "ilpageobjectgui");

		$tabs_gui->addTarget("cont_preview", $this->ctrl->getLinkTarget($this, "preview")
			, "preview", "ilpageobjectgui");
			
		//$tabs_gui->addTarget("properties", $this->ctrl->getLinkTarget($this, "properties")
		//	, "properties", get_class($this));

		$tabs_gui->addTarget("meta_data",
			 $this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
			 "", "ilmdeditorgui");

		$tabs_gui->addTarget("history", $this->ctrl->getLinkTarget($this, "history")
			, "history", get_class($this));

		$tabs = $this->ctrl->getTabs();
		foreach ($tabs as $tab)
		{
			$tabs_gui->addTarget($tab["lang_var"], $tab["link"]
				, $tab["cmd"], $tab["class"]);
		}

		//$tabs_gui->addTarget("clipboard", $this->ctrl->getLinkTargetByClass("ilEditClipboardGUI", "view")
		//	, "view", "ilEditClipboardGUI");

	}
*/

	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target)
	{
		global $rbacsystem, $ilErr, $lng, $ilAccess;

		$first = strpos($a_target, "_");
		$second = strpos($a_target, "_", $first + 1);
		$page_id = substr($a_target, 0, $first);
		if ($first > 0)
		{
			$page_id = substr($a_target, 0, $first);
			if ($second > 0)
			{
				$ref_id = substr($a_target, $first + 1, $second - ($first + 1));
				$anchor = substr($a_target, $second + 1);
			}
			else
			{
				$ref_id = substr($a_target, $first + 1);
			}
		}
		else
		{
			$page_id = $a_target;
		}
		
		// determine learning object
		$lm_id = ilLMObject::_lookupContObjID($page_id);

		// get all references
		$ref_ids = ilObject::_getAllReferences($lm_id);

		// always try passed ref id first
		if (in_array($ref_id, $ref_ids))
		{
			$ref_ids = array_merge(array($ref_id), $ref_ids);
		}

		// check read permissions
		foreach ($ref_ids as $ref_id)
		{
			// check read permissions
			if ($ilAccess->checkAccess("read", "", $ref_id))
			{
				// don't redirect anymore, just set parameters
				// (goto.php includes  "ilias.php")
				$_GET["baseClass"] = "ilLMPresentationGUI";
				$_GET["obj_id"] = $page_id;
				$_GET["ref_id"] = $ref_id;
				$_GET["anchor"] = $anchor;
				include_once("ilias.php");
				exit;
			}
		}

		if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle($lm_id)), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}

	/**
	* Edit layout of page
	*/
	function editLayout()
	{
		global $tpl, $ilCtrl, $ilTabs;
		
		$page_gui =& new ilPageObjectGUI($this->obj->content_object->getType(),
			$this->obj->getId());
		$page_gui->setEditPreview(true);
		$page_gui->activateMetaDataEditor($this->content_object->getID(),
			$this->obj->getId(), $this->obj->getType(),
			$this->obj, "MDUpdateListener");
		$page_gui->setActivationListener($this, "activatePage");
		$page_gui->setTabHook($this, "addPageTabs");
		$page_gui->setEnabledActivation(true);
		$lm_set = new ilSetting("lm");
		if ($lm_set->get("time_scheduled_page_activation"))
		{
			$page_gui->setEnabledScheduledActivation(true);
		}
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_pg_b.gif"));
		$tpl->setTitle($this->lng->txt("page").": ".$this->obj->getTitle());
		$ilCtrl->getHTML($page_gui);
		$ilTabs->setTabActive("cont_layout");
		$this->initEditLayoutForm();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init edit layout form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initEditLayoutForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// default layout
		$layout = new ilRadioMatrixInputGUI($lng->txt("cont_layout"), "layout");
		$option = array();
		if (is_file($im = ilUtil::getImagePath("layout_".$this->content_object->getLayout().".gif")))
		{
			$im_tag = ilUtil::img($im, $this->content_object->getLayout());
		}
		$option[""] =
			"<table><tr><td>".$im_tag."</td><td><b>".$lng->txt("cont_lm_default_layout").
			"</b>: ".$lng->txt("cont_layout_".$this->content_object->getLayout())."</td></tr></table>";
		foreach(ilObjContentObject::getAvailableLayouts() as $l)
		{
			$im_tag = "";
			if (is_file($im = ilUtil::getImagePath("layout_".$l.".gif")))
			{
				$im_tag = ilUtil::img($im, $l);
			}
			$option[$l] = "<table><tr><td>".$im_tag."</td><td><b>".$lng->txt("cont_layout_".$l)."</b>: ".$lng->txt("cont_layout_".$l."_desc")."</td></tr></table>";
		}
		$layout->setOptions($option);
		$layout->setValue($this->obj->getLayout());
		$this->form->addItem($layout);

		$this->form->addCommandButton("saveLayout", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("cont_page_layout"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	 
	}
	
	/**
	* Save layout
	*
	*/
	public function saveLayout()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initEditLayoutForm();
		if ($this->form->checkInput())
		{
			ilLMObject::writeLayout($this->obj->getId(), $this->form->getInput("layout"));
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editLayout");
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	/**
	* Add page tabs
	*/
	function addPageTabs()
	{
		global $ilTabs, $ilCtrl;
		
		$ilTabs->addTarget("cont_layout",
			 $ilCtrl->getLinkTarget($this, 'editLayout'), "editLayout");
	}

}
?>
