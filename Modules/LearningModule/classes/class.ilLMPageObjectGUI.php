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
		global $tpl;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
//echo "<br>:cmd:".$this->ctrl->getCmd().":cmdClass:".$this->ctrl->getCmdClass().":".
//	":nextClass:".$next_class.":"; flush();

		switch($next_class)
		{
			/*case 'ilmdeditorgui':

				$this->setTabs();
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->content_object->getID(),
					$this->obj->getId(), $this->obj->getType());
				$md_gui->addObserver($this->obj,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;*/

			case "ilpageobjectgui":

				// Determine whether the view of a learning resource should
				// be shown in the frameset of ilias, or in a separate window.
				//$showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";
				$showViewInFrameset = true;

				$this->ctrl->setReturn($this, "view");
/*				$page_object =& $this->obj->getPageObject();
				$page_object->buildDom();
				$page_object->addUpdateListener($this, "updateHistory");
				$int_links = $page_object->getInternalLinks();
				$link_xml = $this->getLinkXML($int_links);
*/
//				$page_gui =& new ilPageObjectGUI($page_object);
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

				$page_gui->setIntLinkHelpDefault("StructureObject", $_GET["ref_id"]);
				$page_gui->setTemplateTargetVar("ADM_CONTENT");
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
				$page_gui->setActivationListener($this, "activatePage");
				$page_gui->setActivated($this->obj->getActive());
				
				$tpl->setTitleIcon(ilUtil::getImagePath("icon_pg_b.gif"));
				$tpl->setTitle($this->lng->txt("page").": ".$this->obj->getTitle());
				
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
		$this->setTabs();
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
		ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
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
								$href = $this->ctrl->getLinkTargetByClass(get_class($this), "view", "", true);
							}
							else
							{
								$this->ctrl->setParameterByClass("ilstructureobjectgui", "obj_id", $target_id);
								$href = $this->ctrl->getLinkTargetByClass("ilstructureobjectgui", "view", "", true);
							}
							$href = str_replace("&", "&amp;", $href);
							$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
						}
						else
						{
							if ($type == "PageObject")
							{
								$href = "../goto.php?target=pg_".$target_id;
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
				$link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".
					"TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" />";
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
	}

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

	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target, $a_target_ref_id = "")
	{
		global $rbacsystem, $ilErr, $lng, $ilAccess;

		// determine learning object
		$lm_id = ilLMObject::_lookupContObjID($a_target);

		// get all references
		$ref_ids = ilObject::_getAllReferences($lm_id);

		// always try passed ref id first
		if (in_array($a_target_ref_id, $ref_ids))
		{
			$ref_ids = array_merge(array($a_target_ref_id), $ref_ids);
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
				$_GET["obj_id"] = $a_target;
				$_GET["ref_id"] = $ref_id;
				include_once("ilias.php");
				exit;
			}
		}

		if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle($lm_id)), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}

}
?>
