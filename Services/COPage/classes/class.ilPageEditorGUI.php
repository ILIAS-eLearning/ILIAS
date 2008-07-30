<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once ("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once ("classes/class.ilTabsGUI.php");

/**
* Page Editor GUI class
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilPageEditorGUI: ilPCParagraphGUI, ilPCTableGUI, ilPCTableDataGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCMediaObjectGUI, ilPCListGUI, ilPCListItemGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCFileListGUI, ilPCFileItemGUI, ilObjMediaObjectGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCSourceCodeGUI, ilInternalLinkGUI, ilPCQuestionGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCSectionGUI, ilPCDataTableGUI, ilPCResourcesGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCMapGUI, ilPCPluggedGUI, ilPCTabsGUI
*
* @ingroup ServicesCOPage
*/
class ilPageEditorGUI
{
	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;
	var $ctrl;
	var $objDefinition;
	var $page;
	var $target_script;
	var $return_location;
	var $header;
	var $tabs;
	var $cont_obj;

	/**
	* Constructor
	*
	* @param	object		$a_page_object		page object
	* @access	public
	*/
	function ilPageEditorGUI(&$a_page_object, &$a_page_object_gui)
	{
		global $ilias, $tpl, $lng, $objDefinition, $ilCtrl,$ilTabs;

		// initiate variables
		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition = $objDefinition;
		$this->tabs_gui =& $ilTabs;
		$this->page =& $a_page_object;
		$this->page_gui =& $a_page_object_gui;

		$this->ctrl->saveParameter($this, "hier_id");
	}


	/**
	* set header title
	*
	* @param	string		$a_header		header title
	*/
	function setHeader($a_header)
	{
		$this->header = $a_header;
	}

	/**
	* get header title
	*
	* @return	string		header title
	*/
	function getHeader()
	{
		return $this->header;
	}

	/**
	* set locator object
	*
	* @param	object		$a_locator		locator object
	*/
	function setLocator(&$a_locator)
	{
		$this->locator =& $a_locator;
	}

	/**
	* redirect to parent context
	*/
	function returnToContext()
	{
		$this->ctrl->returnToParent($this);
	}

	function setIntLinkHelpDefault($a_type, $a_id)
	{
		$this->int_link_def_type = $a_type;
		$this->int_link_def_id = $a_id;
	}
	
	function setIntLinkReturn($a_return)
	{
		$this->int_link_return = $a_return;
	}

	
	function setPageBackTitle($a_title)
	{
		$this->page_back_title = $a_title;
	}



	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
//$this->ctrl->debug("ilPageEditorGUI->execute");
		$cmd = $this->ctrl->getCmd("displayPage");
		$cmdClass = strtolower($this->ctrl->getCmdClass());

		$hier_id = $_GET["hier_id"];
		if(isset($_POST["new_hier_id"]))
		{
			$hier_id = $_POST["new_hier_id"];
		}
//echo "GEThier_id:".$_GET["hier_id"]."<br>";
//$this->ctrl->debug("hier_id:".$hier_id);

		$new_type = (isset($_GET["new_type"]))
			? $_GET["new_type"]
			: $_POST["new_type"];

		if (substr($cmd, 0, 5) == "exec_")
		{
			$cmd = explode("_", key($_POST["cmd"]));
			unset($cmd[0]);
			$hier_id = implode($cmd, "_");
			$cmd = $_POST["command".$hier_id];
		}
		
		// strip "c" "r" of table ids from hierarchical id
		$first_hier_character = substr($hier_id, 0, 1);
		if ($first_hier_character == "c" ||
			$first_hier_character == "r" ||
			$first_hier_character == "i")
		{
			$hier_id = substr($hier_id, 1);
		}

		$this->page->buildDom();
		$this->page->addHierIDs();

		// determine command and content object
		$com = explode("_", $cmd);
		$cmd = $com[0];

//$this->ctrl->debug("hier_id:$hier_id:cmd:$cmd:");
		$next_class = $this->ctrl->getNextClass($this);

		// determine content type
		if ($cmd == "insert" || $cmd == "create")
		{
			$ctype = $com[1];
			$add_type = $com[2];
			if ($ctype == "mob") $ctype = "media";
		}
		else
		{
			// setting cmd and cmdclass for editing of linked media
			if ($cmd == "editLinkedMedia")
			{
				$this->ctrl->setCmd("edit");
				$cmd = "edit";
				$_GET["pgEdMediaMode"] = "editLinkedMedia";
				$_GET["mob_id"] = $_POST["mob_id"];
			}
			if ($_GET["pgEdMediaMode"] == "editLinkedMedia")
			{
				$this->ctrl->setParameter($this, "pgEdMediaMode", "editLinkedMedia");
				$this->ctrl->setParameter($this, "mob_id", $_GET["mob_id"]);
				if ($cmdClass != "ilinternallinkgui" && $cmdClass != "ilmdeditorgui")
				{
					$this->ctrl->setCmdClass("ilobjmediaobjectgui");
					$cmdClass = "ilobjmediaobjectgui";
				}
			}
//echo "-$cmd-".$this->ctrl->getCmd()."-";
//var_dump($_POST);
			// note: ilinternallinkgui for page: no cont_obj is received
			// ilinternallinkgui for mob: cont_obj is received
			if ($cmd != "insertFromClipboard" && $cmd != "pasteFromClipboard" &&
				$cmd != "setMediaMode" && $cmd != "copyLinkedMediaToClipboard" &&
				$cmd != "activatePage" && $cmd != "deactivatePage" &&
				$cmd != "copyLinkedMediaToMediaPool" &&
				$cmd != "deleteSelected" &&
				$cmd != "activateSelected" &&
				$cmd != "cancelCreate" && $cmd != "popup" &&
				$cmdClass != "ileditclipboardgui" && $cmd != "addChangeComment" &&
				($cmdClass != "ilinternallinkgui" || ($next_class == "ilobjmediaobjectgui")))
			{
				if ($_GET["pgEdMediaMode"] != "editLinkedMedia")
				{
//$this->ctrl->debug("gettingContentObject (no linked media)");
					$cont_obj =& $this->page->getContentObject($hier_id);
					$ctype = $cont_obj->getType();
				}
			}
		}
//$this->ctrl->debug("+ctype:".$ctype."+");
//		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
//		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");


		if ($ctype != "media" || !is_object ($cont_obj))
		{
			if ($this->getHeader() != "")
			{
				$this->tpl->setTitle($this->getHeader());
			}
			$this->displayLocator();
		}

		$this->cont_obj =& $cont_obj;

		// special command / command class handling
		$this->ctrl->setParameter($this, "hier_id", $hier_id);
		$this->ctrl->setCmd($cmd);
		//$next_class = $this->ctrl->getNextClass($this);
$this->ctrl->debug("+next_class:".$next_class."+");
//echo("+next_class:".$next_class."+".$ctype."+");
		if ($next_class == "")
		{
			switch($ctype)
			{
				case "src":
					$this->ctrl->setCmdClass("ilPCSourcecodeGUI");
					break;

				case "par":
					$this->ctrl->setCmdClass("ilPCParagraphGUI");
					break;

				// advanced table
				case "tab":
					$this->ctrl->setCmdClass("ilPCTableGUI");
					break;

				// data table
				case "dtab":
					$this->ctrl->setCmdClass("ilPCDataTableGUI");
					break;

				case "td":
					$this->ctrl->setCmdClass("ilPCTableDataGUI");
					break;

				case "media":
					$this->ctrl->setCmdClass("ilPCMediaObjectGUI");
					break;

				case "list":
					$this->ctrl->setCmdClass("ilPCListGUI");
					break;

				case "li":
					$this->ctrl->setCmdClass("ilPCListItemGUI");
					break;

				case "flst":
					$this->ctrl->setCmdClass("ilPCFileListGUI");
					break;

				case "flit":
					$this->ctrl->setCmdClass("ilPCFileItemGUI");
					break;

				case "pcqst":
					$this->ctrl->setCmdClass("ilPCQuestionGUI");
					break;

				case "sec":
					$this->ctrl->setCmdClass("ilPCSectionGUI");
					break;
					
				case "repobj":
					$this->ctrl->setCmdClass("ilPCResourcesGUI");
					break;

				case "map":
					$this->ctrl->setCmdClass("ilPCMapGUI");
					break;

				case "tabs":
					$this->ctrl->setCmdClass("ilPCTabsGUI");
					break;
					
				case "tabstab":
					$this->ctrl->setCmdClass("ilPCTabGUI");
					break;

				case "plug":
					$this->ctrl->setCmdClass("ilPCPluggedGUI");
					break;

			}
			$next_class = $this->ctrl->getNextClass($this);
		}

//echo "hier_id:$hier_id:type:$type:cmd:$cmd:ctype:$ctype:next_class:$next_class:<br>";
		switch($next_class)
		{
			case "ilinternallinkgui":
				$link_gui = new ilInternalLinkGUI(
					$this->int_link_def_type, $this->int_link_def_id);
				$link_gui->setMode("normal");
				$link_gui->setSetLinkTargetScript(
					$this->ctrl->getLinkTarget($this, "setInternalLink"));
				$link_gui->setReturn($this->int_link_return);
				$ret =& $this->ctrl->forwardCommand($link_gui);
				break;

			// Sourcecode
			case "ilpcsourcecodegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCSourcecodeGUI.php");
				$src_gui =& new ilPCSourcecodeGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($src_gui);
				break;

			// Paragraph
			case "ilpcparagraphgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCParagraphGUI.php");
				$par_gui =& new ilPCParagraphGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($par_gui);
				break;

			// Table
			case "ilpctablegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCTableGUI.php");
				$tab_gui =& new ilPCTableGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($tab_gui);
				break;

			// Table Cell
			case "ilpctabledatagui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCTableDataGUI.php");
				$td_gui =& new ilPCTableDataGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($td_gui);
				break;

			// Data Table
			case "ilpcdatatablegui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCDataTableGUI.php");
				$tab_gui =& new ilPCDataTableGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($tab_gui);
				break;

			// PC Media Object
			case "ilpcmediaobjectgui":
				include_once ("./Services/COPage/classes/class.ilPCMediaObjectGUI.php");

				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->page_gui->page_back_title,
					$ilCtrl->getLinkTarget($this->page_gui, "edit"));


//				if ($_GET["pgEdMediaMode"] != "editLinkedMedia")
//				{
					$pcmob_gui =& new ilPCMediaObjectGUI($this->page, $cont_obj, $hier_id);
					$pcmob_gui->setEnabledMapAreas($this->page_gui->getEnabledInternalLinks());
					/*
					if (is_object ($cont_obj))
					{
						//$this->tpl->setCurrentBlock("header_image");
						//$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mob_b.gif"));
						//$this->tpl->parseCurrentBlock();
						$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob_b.gif"));
						$pcmob_gui->getTabs($this->tabs_gui);
						$this->tpl->setVariable("HEADER", $this->lng->txt("mob").": ".
							$cont_obj->getTitle());
						$this->displayLocator("mob");
						$mob_gui =& new ilObjMediaObjectGUI("", $cont_obj->getId(),false, false);
						$mob_gui->setEnabledMapAreas($this->page_gui->getEnabledInternalLinks());
						$mob_gui->setBackTitle($this->page_back_title);
						$mob_gui->getTabs($this->tabs_gui);
					}
					else
					{
						$pcmob_gui->getTabs($this->tabs_gui, true);
					}
					*/
/*				}
				else
				{
					$mob_gui =& new ilObjMediaObjectGUI("", $_GET["mob_id"],false, false);
					$mob_gui->setEnabledMapAreas($this->page_gui->getEnabledInternalLinks());
					$mob_gui->getTabs($this->tabs_gui);
					$this->tpl->setVariable("HEADER", $this->lng->txt("mob").": ".
						ilObject::_lookupTitle($_GET["mob_id"]));
				}
*/

				#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());

//				if ($next_class == "ilpcmediaobjectgui")
//				{
					$ret =& $this->ctrl->forwardCommand($pcmob_gui);
/*				}
				else
				{
					$ret =& $this->ctrl->forwardCommand($mob_gui);
				}
*/
				break;

			
			// only for "linked" media
			case "ilobjmediaobjectgui":
				$mob_gui =& new ilObjMediaObjectGUI("", $_GET["mob_id"],false, false);
				$mob_gui->getTabs($this->tabs_gui);
				$mob_gui->setEnabledMapAreas($this->page_gui->getEnabledInternalLinks());
				$this->tpl->setTitle($this->lng->txt("mob").": ".
					ilObject::_lookupTitle($_GET["mob_id"]));
				$ret =& $this->ctrl->forwardCommand($mob_gui);
				break;

			// List
			case "ilpclistgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCListGUI.php");
				$list_gui =& new ilPCListGUI($this->page, $cont_obj, $hier_id);
				//$ret =& $list_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($list_gui);
				break;

			// List Item
			case "ilpclistitemgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCListItemGUI.php");
				$list_item_gui =& new ilPCListItemGUI($this->page, $cont_obj, $hier_id);
				//$ret =& $list_item_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($list_item_gui);
				break;

			// File List
			case "ilpcfilelistgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCFileListGUI.php");
				$file_list_gui =& new ilPCFileListGUI($this->page, $cont_obj, $hier_id);
				//$ret =& $file_list_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($file_list_gui);
				break;

			// File List Item
			case "ilpcfileitemgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCFileItemGUI.php");
				$file_item_gui =& new ilPCFileItemGUI($this->page, $cont_obj, $hier_id);
				//$ret =& $file_item_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($file_item_gui);
				break;

			// File List Item
			case "ilpcquestiongui":
			
				// clear tabs!?
				//$this->tabs_gui->clearTargets();
				
				include_once("./Services/COPage/classes/class.ilPCQuestionGUI.php");
				$pc_question_gui =& new ilPCQuestionGUI($this->page, $cont_obj, $hier_id);
				$cmd = $this->ctrl->getCmd();
				$pc_question_gui->$cmd();
				$this->ctrl->redirectByClass(array("ilobjquestionpoolgui", get_class($cont_obj)), "editQuestion");
				break;

			// Section
			case "ilpcsectiongui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCSectionGUI.php");
				$sec_gui =& new ilPCSectionGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($sec_gui);
				break;

			// Resources
			case "ilpcresourcesgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCResourcesGUI.php");
				$res_gui =& new ilPCResourcesGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($res_gui);
				break;

			// Map
			case "ilpcmapgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCMapGUI.php");
				$map_gui =& new ilPCMapGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($map_gui);
				break;

			// Tabs
			case "ilpctabsgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCTabsGUI.php");
				$tabs_gui =& new ilPCTabsGUI($this->page, $cont_obj, $hier_id);
				$ret =& $this->ctrl->forwardCommand($tabs_gui);
				break;

			// Tab
			case "ilpctabgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCTabGUI.php");
				$tab_gui = new ilPCTabGUI($this->page, $cont_obj, $hier_id);
				//$ret =& $list_item_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($tab_gui);
				break;

			// Plugged Component
			case "ilpcpluggedgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCPluggedGUI.php");
				$plugged_gui =& new ilPCPluggedGUI($this->page, $cont_obj, $hier_id,
					$add_type);
				$ret =& $this->ctrl->forwardCommand($plugged_gui);
				break;

			default:
				if ($cmd == "pasteFromClipboard")
				{
					$ret =& $this->pasteFromClipboard($hier_id);
				}
				else
				{
					$ret =& $this->$cmd();
				}
				break;

		}

		return $ret;
	}
	
	/**
	* checks if current user has activated js editing and
	* if browser is js capable
	*/
	function _doJSEditing()
	{
		global $ilUser, $ilias;

		if ($ilUser->getPref("ilPageEditor_JavaScript") == "enable"
			&& $ilias->getSetting("enable_js_edit")
			&& ilPageEditorGUI::_isBrowserJSEditCapable())
		{
			return true;
		}
		return false;
	}

	/**
	* checks wether browser is javascript editing capable
	*/
	function _isBrowserJSEditCapable()
	{
		global $ilBrowser;
return true;
		$version = $ilBrowser->getVersion();

		if ($ilBrowser->isFirefox() ||
			($ilBrowser->isIE() && !$ilBrowser->isMac()) ||
			($ilBrowser->isMozilla() && $version[0] >= 5))
		{
			return true;
		}
		return false;
	}

	function activatePage()
	{
		$this->page_gui->activatePage();
	}

	function deactivatePage()
	{
		$this->page_gui->deactivatePage();
	}

	/**
	* set media and editing mode
	*/
	function setMediaMode()
	{
		global $ilUser, $ilias;

		$ilUser->writePref("ilPageEditor_MediaMode", $_POST["media_mode"]);
		$ilUser->writePref("ilPageEditor_HTMLMode", $_POST["html_mode"]);
		if ($ilias->getSetting("enable_js_edit"))
		{
			if ($ilUser->getPref("ilPageEditor_JavaScript") != $_POST["js_mode"])
			{
				// not nice, should be solved differently in the future
				if ($this->page->getParentType() == "lm" ||
					$this->page->getParentType() == "dbk")
				{
					$this->ctrl->setParameterByClass("illmpageobjectgui", "reloadTree", "y");
				}
			}
			$ilUser->writePref("ilPageEditor_JavaScript", $_POST["js_mode"]);
		}
		
		// again not so nice...
		if ($this->page->getParentType() == "lm" ||
			$this->page->getParentType() == "dbk")
		{
			$this->ctrl->redirectByClass("illmpageobjectgui", "edit");
		}
		else
		{
			$this->ctrl->returnToParent($this);
		}
	}
	
	/**
	* copy linked media object to clipboard
	*/
	function copyLinkedMediaToClipboard()
	{
		global $ilUser;
		
		ilUtil::sendInfo($this->lng->txt("copied_to_clipboard"), true);
		$ilUser->addObjectToClipboard($_POST["mob_id"], "mob", ilObject::_lookupTitle($_POST["mob_id"]));
		$this->ctrl->returnToParent($this);
	}

	/**
	* copy linked media object to media pool
	*/
	function copyLinkedMediaToMediaPool()
	{
		global $ilUser;
		
		$this->ctrl->setParameterByClass("ilmediapooltargetselector", "mob_id", $_POST["mob_id"]); 
		$this->ctrl->redirectByClass("ilmediapooltargetselector", "listPools");
	}
	
	/**
	* add change comment to history
	*/
	function addChangeComment()
	{
		include_once("classes/class.ilHistory.php");
		ilHistory::_createEntry($this->page->getId(), "update",
			"", $this->page->getParentType().":pg",
			ilUtil::stripSlashes($_POST["change_comment"]), true);
		ilUtil::sendInfo($this->lng->txt("cont_added_comment"), true);
		$this->ctrl->returnToParent($this);
	}
	
	/**
	* delete selected items
	*/
	function deleteSelected()
	{
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$updated = $this->page->deleteContents($_POST["target"]);
			if($updated !== true)
			{
				$_SESSION["il_pg_error"] = $updated;
			}
			else
			{
				unset($_SESSION["il_pg_error"]);
			}
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	* (de-)activate selected items
	*/
	function activateSelected()
	{
//var_dump($_POST);
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$updated = $this->page->switchEnableMultiple($_POST["target"]);
			if($updated !== true)
			{
				$_SESSION["il_pg_error"] = $updated;
			}
			else
			{
				unset($_SESSION["il_pg_error"]);
			}
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	* paste from clipboard (redirects to clipboard)
	*/
	function pasteFromClipboard($a_hier_id)
	{
		global $ilCtrl;

		$ilCtrl->setParameter($this, "hier_id", $a_hier_id);
		$ilCtrl->setParameterByClass("ilEditClipboardGUI", "returnCommand",
			rawurlencode($ilCtrl->getLinkTarget($this,
			"insertFromClipboard")));
//echo ":".$ilCtrl->getLinkTarget($this, "insertFromClipboard").":";
		$ilCtrl->redirectByClass("ilEditClipboardGUI", "getObject");
	}

	/**
	* insert object from clipboard
	*/
	function insertFromClipboard()
	{
		include_once ("./Services/COPage/classes/class.ilPCMediaObject.php");
		if ($_GET["clip_obj_id"] != "")
		{
			if ($_GET["clip_obj_type"] == "mob")
			{
				$this->content_obj = new ilPCMediaObject($this->page->getDom());
				$this->content_obj->readMediaObject($_GET["clip_obj_id"]);
				$this->content_obj->createAlias($this->page, $_GET["hier_id"]);
				$this->updated = $this->page->update();
			}
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	* Default for POST reloads and missing 
	*/
	function displayPage()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	* display locator
	*/
	function displayLocator()
	{
		if(is_object($this->locator))
		{
			$this->locator->display();
		}
	}

}
?>
