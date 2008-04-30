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


/**
* Class ilContainerGUI
*
* This is a base GUI class for all container objects in ILIAS:
* root folder, course, group, category, folder
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilObjectGUI
*/

require_once "./classes/class.ilObjectGUI.php";
require_once "./Services/Container/classes/class.ilContainer.php";

class ilContainerGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilContainerGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $rbacsystem;

		$this->rbacsystem =& $rbacsystem;

		//$this->ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		
		// prepare output things should generally be made in executeCommand
		// method (maybe dependent on current class/command
		$this->ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);
	}

	/**
	* execute command
	* note: this method is overwritten in all container objects
	*/
	function &executeCommand()
	{
		global $tpl;
		
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd("render");

		switch($next_class)
		{
			// page editing
			case "ilpageobjectgui":
				$ret = $this->forwardToPageObject();
				$tpl->setContent($ret);
				break;
			
			default:
				$this->prepareOutput();
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	* forward command to page object
	*/
	function &forwardToPageObject()
	{
		global $lng, $ilTabs;
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"), "./goto.php?target=".$this->object->getType()."_".
			$this->object->getRefId(), "_top");

		// page object
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");

		$lng->loadLanguageModule("content");
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));

		if (!ilPageObject::_exists($this->object->getType(),
			$this->object->getId()))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilPageObject($this->object->getType());
			$new_page_object->setParentId($this->object->getId());
			$new_page_object->setId($this->object->getId());
			$new_page_object->createFromXML();
		}
		
		// get page object
//		$page_object = new ilPageObject($this->object->getType(),
//			$this->object->getId(), 0, true);
		$this->ctrl->setReturnByClass("ilpageobjectgui", "edit");
		//$page_object =& $this->obj->getPageObject();
//		$page_object->buildDom();
		//$page_object->addUpdateListener($this, "updateHistory");
//		$int_links = $page_object->getInternalLinks();
		//$link_xml = $this->getLinkXML($int_links);
		$page_gui =& new ilPageObjectGUI($this->object->getType(),
			$this->object->getId());

		// $view_frame = ilFrameTargetInfo::_getFrame("MainContent");
		//$page_gui->setViewPageLink(ILIAS_HTTP_PATH."/goto.php?target=pg_".$this->obj->getId(),
		//	$view_frame);

		$page_gui->setIntLinkHelpDefault("StructureObject", $_GET["ref_id"]);
		$page_gui->setTemplateTargetVar("ADM_CONTENT");
		$page_gui->setLinkXML($link_xml);
		//$page_gui->enableChangeComments($this->content_object->isActiveHistoryUserComments());
		$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
		$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "showMediaFullscreen"));
		//$page_gui->setLinkParams($this->ctrl->getUrlParameterString()); // todo
		$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this, ""));
		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);
		//$page_gui->setLocator($contObjLocator);
		$page_gui->setHeader("");
		$page_gui->setEnabledRepositoryObjects(true);
		$page_gui->setEnabledFileLists(false);
		$ret =& $this->ctrl->forwardCommand($page_gui);

		//$ret =& $page_gui->executeCommand();
		return $ret;
	}
	
	/**
	* prepare output
	*/
	function prepareOutput($a_show_subobjects = true)
	{
		if (parent::prepareOutput())	// return false in admin mode
		{
			if ($this->getCreationMode() != true && $a_show_subobjects)
			{
				// This method is called directly from ilContainerGUI::renderObject
				#$this->showPossibleSubObjects();
				$this->showTreeFlatIcon();
			}
		}
	}
	
	function showTreeFlatIcon()
	{
		global $tpl;
		
		// dont show icon, if role (permission gui->rolegui) is edited
		if ($_GET["obj_id"] != "")
		{
			return;
		}
		
		$mode = ($_SESSION["il_rep_mode"] == "flat")
			? "tree"
			: "flat";
		$link = "repository.php?cmd=frameset&set_mode=".$mode."&ref_id=".$this->object->getRefId();
		$tpl->setTreeFlatIcon($link, $mode);
	}
	
	/**
	* called by prepare output 
	*/
	function setTitleAndDescription()
	{
		global $ilias;
//echo "1-".get_class($this)."-".$this->object->getTitle()."-";
		$this->tpl->setTitle($this->object->getTitle());
		$this->tpl->setDescription($this->object->getLongDescription());

		// set tile icon
		$icon = ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif");
		if ($ilias->getSetting("custom_icons") &&
			in_array($this->object->getType(), array("cat","grp","crs", "root")))
		{
			require_once("./Services/Container/classes/class.ilContainer.php");
			if (($path = ilContainer::_lookupIconPath($this->object->getId(), "big")) != "")
			{
				$icon = $path;
			}
		}
		$this->tpl->setTitleIcon($icon, $this->lng->txt("obj_".$this->object->getType()));
	}


	/**
	* show possible sub objects selection list
	*/
	function showPossibleSubObjects()
	{
		global $ilAccess,$ilCtrl;

		$found = false;
		$cmd = ($this->cmd != "")
			? $this->cmd
			: $this->ctrl->getCmd();

		#if ($cmd != "" && $cmd != "showList" && $cmd != "render"
		#	&& $cmd != "view")
		#{
		#	return;
		#}
		
		$type = $this->object->getType();

		$d = $this->objDefinition->getCreatableSubObjects($type);

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					//var_dump($this->data);
					// this is broken
					/*
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}*/
				}

				if ($row["max"] == "" || $count < $row["max"])
				{
					//if (in_array($row["name"], array("sahs", "alm", "hlm", "lm", "grp", "frm", "mep","crs", "mcst", "wiki",
					//								 "cat", "glo", "dbk","exc", "qpl", "tst", "svy", "spl", "chat", 
					//								 "htlm","fold","linkr","file","icrs","icla","crsg",'webr',"feed",'rcrs')))
					//{
					if (!in_array($row["name"], array("rolf")))
					{
						if ($this->rbacsystem->checkAccess("create", $this->object->getRefId(), $row["name"]))
						{
							$subobj[] = $row["name"];
						}
					}
				}
			}
		}
		if (is_array($subobj))
		{
			// show addEvent button
			if($this->object->getType() == 'crs')
			{
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tpl->setCurrentBlock("event_button");
					$this->tpl->setVariable("E_FORMACTION",$this->ctrl->getFormActionByClass('ileventadministrationgui'));
					$this->tpl->setVariable("BTN_NAME_EVENT",'addEvent');
					$this->tpl->setVariable("TXT_ADD_EVENT",$this->lng->txt('add_event'));
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->tpl->setCurrentBlock("add_commands");
			// convert form to inline element, to show them in one row
			if($this->object->getType() == 'crs')
			{
				$this->tpl->setVariable("FORMSTYLE",'display:inline');
			}
			$formaction = "repository.php?ref_id=".$this->object->getRefId()."&cmd=post";
			$formaction = $ilCtrl->appendRequestTokenParameterString($formaction);
			$this->tpl->setVariable("H_FORMACTION",$formaction);
			// possible subobjects
			$opts = ilUtil::formSelect("", "new_type", $subobj);
			$this->tpl->setVariable("SELECT_OBJTYPE_REPOS", $opts);
			$this->tpl->setVariable("BTN_NAME_REPOS", "create");
			$this->tpl->setVariable("TXT_ADD_REPOS", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* render the object
	*/
	function renderObject()
	{
		// BEGIN ChangeEvent: record read event.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			if ($this->object != null)
			{
				global $ilUser;
				ilChangeEvent::_recordReadEvent($this->object->getId(), $ilUser->getId());
			}
		}
		// END ChangeEvent: record read event.

		$this->getCenterColumnHTML(true);
		if ($this->type == 'cat' || $this->type == 'grp')
		{
			$this->tpl->setRightContent($this->getRightColumnHTML());
		}
	}

	/**
	* get container content (list of subitems)
	* (this should include multiple lists in the future that together
	* build the blocks of a container page)
	*/
	function getContent()
	{
		global $ilBench, $tree;

		// course content interface methods could probably
		// move to this class
		if($this->type != 'icrs' and $tree->checkForParentType($this->ref_id,'crs'))
		{
			include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
			$course_content_obj = new ilCourseContentGUI($this);
			
			$this->ctrl->setCmd('view');
			$this->ctrl->setCmdClass(get_class($course_content_obj));
			$this->ctrl->forwardCommand($course_content_obj);

			return;
		}

		// 'add object'
		$this->showPossibleSubObjects();

		$ilBench->start("ilContainerGUI", "0000__renderObject");

		$tpl = new ilTemplate ("tpl.container_page.html", true, true);
		
		// get all sub items
		$ilBench->start("ilContainerGUI", "0100_getSubItems");
		$this->getSubItems();
		$ilBench->stop("ilContainerGUI", "0100_getSubItems");

		// Show introduction, if repository is empty
		if (count($this->items) == 1 && is_array($this->items["adm"]) && $this->object->getRefId() == ROOT_FOLDER_ID)
		{
			$html = $this->getIntroduction();
			$tpl->setVariable("CONTAINER_PAGE_CONTENT", $html);
		}
		else	// show item list otherwise
		{
			$ilBench->start("ilContainerGUI", "0200_renderItemList");
			$html = $this->renderItemList();
			$tpl->setVariable("CONTAINER_PAGE_CONTENT", $html);
			$ilBench->stop("ilContainerGUI", "0200_renderItemList");
		}
		
		$this->showAdministrationPanel($tpl);
		$this->showPermanentLink($tpl);
		
		$this->html = $tpl->get();
		
		$ilBench->stop("ilContainerGUI", "0000__renderObject");
	}

	/**
	* show administration panel
	*/
	function showAdministrationPanel(&$tpl)
	{
		global $ilAccess, $ilSetting;
		global $ilUser;
		
		if ($this->isActiveAdministrationPanel())
		{
			$tpl->setCurrentBlock("admin_button_off");
			$tpl->setVariable("ADMIN_MODE_LINK",
				$this->ctrl->getLinkTarget($this, "disableAdministrationPanel"));
			$tpl->setVariable("TXT_ADMIN_MODE",
				$this->lng->txt("admin_panel_disable"));
			$tpl->parseCurrentBlock();
			
			// administration panel
			if ($ilAccess->checkAccess("write", "", $this->object->getRefId())
				&& in_array($this->object->getType(), array("cat", "root")))
			{
				if ($ilSetting->get("enable_cat_page_edit"))
				{
					$tpl->setCurrentBlock("edit_cmd");
					$tpl->setVariable("TXT_EDIT_PAGE", $this->lng->txt("edit_page"));
					$tpl->setVariable("LINK_EDIT_PAGE", $this->ctrl->getLinkTarget($this, "editPageFrame"));
					$tpl->setVariable("FRAME_EDIT_PAGE", ilFrameTargetInfo::_getFrame("MainContent"));
					$tpl->parseCurrentBlock();
				}
			}
			
			$tpl->setCurrentBlock("admin_panel_cmd");
			$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt("delete_selected_items"));
			$tpl->setVariable("PANEL_CMD", "delete");
			// BEGIN WebDAV: Show check all / uncheck all buttons
			$tpl->setVariable("TXT_CHECK_ALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECK_ALL", $this->lng->txt("uncheck_all"));
			// END WebDAV: Show check all / uncheck all buttons
			$tpl->parseCurrentBlock();
			if (!$_SESSION["clipboard"])
			{
				$tpl->setCurrentBlock("admin_panel_cmd");
				$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt("move_selected_items"));
				$tpl->setVariable("PANEL_CMD", "cut");
				$tpl->parseCurrentBlock();
				// BEGIN WebDAV: Support a copy command in the repository
				$tpl->setCurrentBlock("admin_panel_cmd");
				$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt("copy_selected_items"));
				$tpl->setVariable("PANEL_CMD", "copy");
				$tpl->parseCurrentBlock();
				// END WebDAV: Support a copy command in the repository
				$tpl->setCurrentBlock("admin_panel_cmd");
				$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt("link_selected_items"));
				$tpl->setVariable("PANEL_CMD", "link");
				$tpl->parseCurrentBlock();
			}
			else
			{
				$tpl->setCurrentBlock("admin_panel_cmd");
				$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt("paste_clipboard_items"));
				$tpl->setVariable("PANEL_CMD", "paste");
				$tpl->parseCurrentBlock();
				$tpl->setCurrentBlock("admin_panel_cmd");
				$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt("clear_clipboard"));
				$tpl->setVariable("PANEL_CMD", "clear");
				$tpl->parseCurrentBlock();
				
			}
			if ($ilAccess->checkAccess("write", "", $this->object->getRefId())
				&& in_array($this->object->getType(), array("cat", "root")))
			{
				include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
				if(ilContainerSortingSettings::_lookupSortMode($this->object->getId()) == ilContainerSortingSettings::MODE_MANUAL)
				{
					$tpl->setCurrentBlock('admin_panel_cmd');
					$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt('sorting_save'));
					$tpl->setVariable("PANEL_CMD", "saveSorting");
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setCurrentBlock("admin_panel");
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
			$tpl->setVariable("TXT_ADMIN_PANEL", $this->lng->txt("admin_panel"));
			$tpl->parseCurrentBlock();
			$this->ctrl->setParameter($this, "type", "");
			$this->ctrl->setParameter($this, "item_ref_id", "");
			$GLOBALS["tpl"]->setPageFormAction($this->ctrl->getFormAction($this));
		}
		// BEGIN WebDAV: Always show administration commands button except for anonymous
		else if ($this->adminCommands || (is_object($this->object) && 
			($ilAccess->checkAccess("write", "", $this->object->getRefId()) ||
			$ilUser->getId() != ANONYMOUS_USER_ID)
			))
		// END WebDAV: Always show administration commands button except for anonymous
		{
			#$this->__showTimingsButton($tpl);

			$tpl->setCurrentBlock("admin_button");
			$tpl->setVariable("ADMIN_MODE_LINK",
				$this->ctrl->getLinkTarget($this, "enableAdministrationPanel"));
			$tpl->setVariable("TXT_ADMIN_MODE",
				$this->lng->txt("admin_panel_enable"));
			$tpl->parseCurrentBlock();
		}
	}

	function __showTimingsButton(&$tpl)
	{
		global $tree;

		if(!$tree->checkForParentType($this->object->getRefId(),'crs'))
		{
			return false;
		}
		$tpl->setCurrentBlock("custom_button");
		$tpl->setVariable("ADMIN_MODE_LINK",$this->ctrl->getLinkTargetByClass('ilcoursecontentgui','editTimings'));
		$tpl->setVariable("TXT_ADMIN_MODE",$this->lng->txt('timings_edit'));
		$tpl->parseCurrentBlock();
		return true;
	}
	/**
	* show permanent link
	*/
	function showPermanentLink(&$tpl)
	{
		include_once('classes/class.ilLink.php');
		$tpl->setCurrentBlock('perma_link');
		$tpl->setVariable('PERMA_LINK',ilLink::_getStaticLink($this->object->getRefId(),$this->object->getType()));
		$tpl->setVariable("TXT_PERMA_LINK", $this->lng->txt("perma_link"));
		$tpl->setVariable("PERMA_TARGET", "_top");
		$tpl->parseCurrentBlock();

		/*		
		$tpl->setCurrentBlock("perma_link");
		$tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH.
			"/goto.php?target=".
			$this->object->getType().
			"_".$this->object->getRefId()."&client_id=".CLIENT_ID);
		$tpl->setVariable("TXT_PERMA_LINK", $this->lng->txt("perma_link"));
		$tpl->setVariable("PERMA_TARGET", "_top");
		$tpl->parseCurrentBlock();
		*/
	}

	/**
	* show page editor frameset
	*/
	function editPageFrameObject()
	{
		include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
		$fs_gui = new ilFramesetGUI();
		
		$fs_gui->setFramesetTitle($this->object->getTitle());
		$fs_gui->setMainFrameName("content");
		$fs_gui->setSideFrameName("link_list");
		
		//$fs_gui->setSideFrameSource(
		//	$this->ctrl->getLinkTargetByClass("ilcontainerlinklistgui", "show"));

		// to do: check this
		$fs_gui->setSideFrameSource("");
			
		/* old tiny stuff
		$fs_gui->setMainFrameSource(
			$this->ctrl->getLinkTarget(
				$this, "editPageContent"));		*/
			
		// page object stuff
		$fs_gui->setMainFrameSource(
			$this->ctrl->getLinkTargetByClass(
				array("ilpageobjectgui"), "edit"));
				
		$fs_gui->show();
		exit;
	}

	/**
	* edit page content (for repository root node and categories)
	*
	* @access	public
	*/
	function editPageContentObject()
	{
		global $rbacsystem, $tpl;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
		$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
			"xhtml_page");
		if ($xpage_id > 0)
		{
			include_once("Services/XHTMLPage/classes/class.ilXHTMLPage.php");
			$xpage = new ilXHTMLPage($xpage_id);
			$content = $xpage->getContent();
		}
		
		// get template
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.container_edit_page_content.html");
		$tpl->setVariable("VAL_CONTENT", ilUtil::prepareFormOutput($content));
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_EDIT_PAGE_CONTENT",
			$this->lng->txt("edit_page_content"));
		$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		
		include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");
		include_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
		//$ta = new ilTextAreaInputGUI();
		//$tags = $ta->getRteTagSet("extended_table_img");
		
		// add rte support
		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		//$rte->addPlugin("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = ilObject::_lookupObjectId($_GET["ref_id"]);
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type);
		//$rte->setStyleSelect(true);
		//$rte->addCustomRTESupport($obj_id, $obj_type, $tags);
	}
	
	function savePageContentObject()
	{
		include_once("Services/XHTMLPage/classes/class.ilXHTMLPage.php");
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
			"xhtml_page");
		
		/*include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");
		include_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
		$ta = new ilTextAreaInputGUI();
		$ta->setRteTagSet("extended_table_img");
		$tags = $ta->getRteTagString();*/

		//$text = ilUtil::stripSlashes($_POST["page_content"],
		//		true,
		//		$tags);
				
		$text = ilUtil::stripSlashes($_POST["page_content"],
				true,
				ilObjAdvancedEditing::_getUsedHTMLTagsAsString());
		if ($xpage_id > 0)
		{
			$xpage = new ilXHTMLPage($xpage_id);
			$xpage->setContent($text);
			$xpage->save();
		}
		else
		{
			$xpage = new ilXHTMLPage();
			$xpage->setContent($text);
			$xpage->save();
			ilContainer::_writeContainerSetting($this->object->getId(),
				"xhtml_page", $xpage->getId());
		}
		
		include_once("Services/RTE/classes/class.ilRTE.php");
		ilRTE::_cleanupMediaObjectUsage($text, $this->object->getType().":html",
			$this->object->getId());

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "");
	}
	
	function cancelPageContentObject()
	{
		ilUtil::sendInfo($this->lng->txt("action_aborted"), true);
		$this->ctrl->redirect($this, "");
	}

	/**
	* Get grouped repository object types.
	*
	* @return	array	array of object types
	*/
	function getGroupedObjTypes()
	{
		global $objDefinition;
		
		if (empty($this->type_grps))
		{
			$this->type_grps = $objDefinition->getGroupedRepositoryObjectTypes($this->object->getType());
		}
		return $this->type_grps;
	}
	
	/**
	* get all subitems of the container
	*/
	function getSubItems()
	{
		global $objDefinition, $ilBench;

		$type_grps = $this->getGroupedObjTypes();

		$objects = $this->tree->getChilds($this->object->getRefId(), "title");

		$found = false;

		include_once('Services/Container/classes/class.ilContainerSorting.php');
		$sort = new ilContainerSorting($this->object->getId());

		foreach ($objects as $key => $object)
		{

			// hide object types in devmode
			if ($objDefinition->getDevMode($object["type"]))
			{
				continue;
			}
			// BEGIN WebDAV: Don't display hidden files.
			require_once 'Modules/File/classes/class.ilObjFileAccess.php';
			if (!$this->isActiveAdministrationPanel() && ilObjFileAccess::_isFileHidden($object['title']))
			{
				continue;
			}
			// END WebDAV: Don't display hidden files.

			
			// group object type groups together (e.g. learning resources)
			$type = $objDefinition->getGroupOfObj($object["type"]);
			if ($type == "")
			{
				$type = $object["type"];
			}
						
			$this->items[$type][$key] = $object;
		}
		$this->items = $sort->sortTreeDataByType($this->items);
	}

	function renderItemList($a_type = "all")
	{
		global $objDefinition, $ilBench, $ilSetting;
		
		include_once("classes/class.ilObjectListGUIFactory.php");

		$output_html = "";
		$this->clearAdminCommandsDetermination();
		
		$type_grps = $this->getGroupedObjTypes();
		
		switch ($a_type)
		{
			// render all items list
			case "all":
							
				$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
					"xhtml_page");
				if ($xpage_id > 0 && $ilSetting->get("enable_cat_page_edit"))
				{
					include_once("Services/XHTMLPage/classes/class.ilXHTMLPage.php");
					$xpage = new ilXHTMLPage($xpage_id);
					$output_html.= $xpage->getContent();
				}
				
				// all item types
				/*
				$type_ordering = array(
					"cat", "fold", "crs","rcrs", "icrs", "icla", "grp", "chat", "frm", "lres",
					"glo", "webr", "mcst", "wiki", "file", "exc",
					"tst", "svy", "mep", "qpl", "spl");*/

				$cur_obj_type = "";
				$overall_tpl =& $this->newBlockTemplate();
				$this->type_template = array();
				$first = true;
				
				// iterate all types
				foreach ($type_grps as $type => $v)
				{
					// set template (overall or type specific)
					if (is_int(strpos($output_html, "[list-".$type."]")))
					{
						$tpl =& $this->newBlockTemplate();
						$overall = false;			// individual
					}
					else
					{
						$tpl =& $overall_tpl;
						$overall = true;			// put to the rest
					}
						
					if (is_array($this->items[$type]))
					{
						
						$item_html = array();

						foreach($this->items[$type] as $key => $item)
						{
							// get list gui class for each object type
							if ($cur_obj_type != $item["type"])
							{
								$item_list_gui =& ilObjectListGUIFactory::_getListGUIByType($item["type"]);
								$item_list_gui->setContainerObject($this);
							}
							// render item row
							$ilBench->start("ilContainerGUI", "0210_getListHTML");
							
							// show administration command buttons (or not)
							if (!$this->isActiveAdministrationPanel())
							{
								$item_list_gui->enableDelete(false);
								$item_list_gui->enableLink(false);
								$item_list_gui->enableCut(false);
							}
							
							$html = $item_list_gui->getListItemHTML($item["ref_id"],
								$item["obj_id"], $item["title"], $item["description"]);
								
							// check whether any admin command is allowed for
							// the items
							$this->determineAdminCommands($item["ref_id"],
								$item_list_gui->adminCommandsIncluded());
							$ilBench->stop("ilContainerGUI", "0210_getListHTML");
							if ($html != "")
							{
								// BEGIN WebDAV: Use $item_list_gui to determine icon image type
								$item_html[] = array(
									"html" => $html, 
									"item_ref_id" => $item["ref_id"],
									"item_obj_id" => $item["obj_id"],
									'item_icon_image_type' => (method_exists($item_list_gui, 'getIconImageType')) ?
											$item_list_gui->getIconImageType() :
											$item['type']
									);
								// END WebDAV: Use $item_list_gui to determine icon image type
							}
						}

						// output block for resource type
						if (count($item_html) > 0)
						{
							// separator row
							if (!$first && $overall)
							{
								$this->addSeparatorRow($tpl);
							}
							
							if ($overall)
							{
								$first = false;
							}

							// add a header for each resource type
							if ($this->ilias->getSetting("icon_position_in_lists") == "item_rows")
							{
								$this->addHeaderRow($tpl, $type, false);
							}
							else
							{
								$this->addHeaderRow($tpl, $type);
							}
							$this->resetRowType();

							// content row
							$this->current_position = 1;
							foreach($item_html as $item)
							{
								// BEGIN WebDAV: Use $item_list_gui to determine image type
								$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], $item['item_icon_image_type']);
								// END WebDAV: Use $item_list_gui to determine image type
							}

							// store type specific templates in array
							if (is_int(strpos($output_html, "[list-".$type."]")))
							{
								$this->type_template[$type] = $tpl;
							}
						}
						else
						{
							// [list-...] tag available, but no item of type accessible
							if (!$overall)
							{
								$this->addHeaderRow($tpl, $type);
								$this->resetRowType();
								$this->addMessageRow($tpl, 
									$this->lng->txt("msg_no_type_accessible"), $type);
								$this->type_template[$type] = $tpl;
							}
						}
					}
					else
					{
						// [list-...] tag available, but no item of type exists
						if (!$overall)
						{
							$this->addHeaderRow($tpl, $type);
							$this->resetRowType();
							$this->addMessageRow($tpl,
								$this->lng->txt("msg_no_type_available"), $type);
							$this->type_template[$type] = $tpl;
						}
					}

				}


				// I don't know why but executing this
				// line before the following foreach loop seems to be crucial
				if ($output_html != "")
				{
					//$output_html.= "<br /><br />";
				}
				$output_html.= $overall_tpl->get();
				//$output_html = str_replace("<br>++", "++", $output_html);
				//$output_html = str_replace("<br>++", "++", $output_html);
				//$output_html = str_replace("++<br>", "++", $output_html);
				//$output_html = str_replace("++<br>", "++", $output_html);
				foreach ($this->type_template as $type => $tpl)
				{
					$output_html = eregi_replace("\[list-".$type."\]",
						"</p>".$tpl->get()."<p class=\"ilc_Standard\">",
						$output_html);
				}

				//if (ilPageObject::_exists($this->object->getType(),
				//	$this->object->getId()))
				if ($xpage_id > 0)
				{				
					$page_block = new ilTemplate("tpl.container_page_block.html", false, false);
					$page_block->setVariable("CONTAINER_PAGE_CONTENT", $output_html);
					$output_html = $page_block->get();
				}

				break;

			default:
				// to do:
				break;
		}
		return $output_html;
	}

	/**
	* cleaer administration commands determination
	*/
	function clearAdminCommandsDetermination()
	{
		$this->adminCommands = false;
	}
	
	/**
	* determin admin commands
	*/
	function determineAdminCommands($a_ref_id, $a_admin_com_included_in_list = false)
	{
		if (!$this->adminCommands)
		{
			if (!$this->isActiveAdministrationPanel())
			{
				if ($this->rbacsystem->checkAccess("delete", $a_ref_id))
				{
					$this->adminCommands = true;
				}
			}
			else
			{
				$this->adminCommands = $a_admin_com_included_in_list;
			}
		}
	}

	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	function &newBlockTemplate()
	{
		$tpl = new ilTemplate ("tpl.container_list_block.html", true, true);
		$this->cur_row_type = "row_type_1";
		return $tpl;
	}

	/**
	* adds a header row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_type		object type
	* @access	private
	*/
	function addHeaderRow(&$a_tpl, $a_type, $a_show_image = true)
	{
		$icon = ilUtil::getImagePath("icon_".$a_type.".gif");
		$title = $this->lng->txt("objs_".$a_type);
		
		if ($a_show_image)
		{
			$a_tpl->setCurrentBlock("container_header_row_image");
			$a_tpl->setVariable("HEADER_IMG", $icon);
			$a_tpl->setVariable("HEADER_ALT", $title);
		}
		else
		{
			$a_tpl->setCurrentBlock("container_header_row");
		}
		
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	* adds a standard row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_html		html code
	* @access	private
	*/
	function addStandardRow(&$a_tpl, $a_html, $a_item_ref_id = "", $a_item_obj_id = "",
		$a_image_type = "")
	{
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		$a_tpl->touchBlock($this->cur_row_type);
		
		$nbsp = true;
		if ($this->ilias->getSetting("icon_position_in_lists") == "item_rows")
		{
			$icon = ilUtil::getImagePath("icon_".$a_image_type.".gif");
			$alt = $this->lng->txt("obj_".$a_image_type);
			
			// custom icon
			if ($this->ilias->getSetting("custom_icons") &&
				in_array($a_image_type, array("cat","grp","crs")))
			{
				require_once("./Services/Container/classes/class.ilContainer.php");
				if (($path = ilContainer::_lookupIconPath($a_item_obj_id, "small")) != "")
				{
					$icon = $path;
				}
			}

			$a_tpl->setCurrentBlock("block_row_image");
			$a_tpl->setVariable("ROW_IMG", $icon);
			$a_tpl->setVariable("ROW_ALT", $alt);
			$a_tpl->parseCurrentBlock();
			$nbsp = false;
		}

		if ($this->isActiveAdministrationPanel())
		{
			$a_tpl->setCurrentBlock("block_row_check");
			$a_tpl->setVariable("ITEM_ID", $a_item_ref_id);
			$a_tpl->parseCurrentBlock();
			$nbsp = false;
		}
		include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
		if($this->isActiveAdministrationPanel() && 
			ilContainerSortingSettings::_lookupSortMode($this->object->getId()) == ilContainerSortingSettings::MODE_MANUAL)
		{
			$a_tpl->setCurrentBlock('block_position');
			$a_tpl->setVariable('POS_TYPE',$a_image_type);
			$a_tpl->setVariable('POS_ID',$a_item_ref_id);
			$a_tpl->setVariable('POSITION',sprintf('%.1f',$this->current_position++));
			$a_tpl->parseCurrentBlock();
		}
		if ($nbsp)
		{
			$a_tpl->setVariable("ROW_NBSP", "&nbsp;");
		}
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	* add message row
	*/
	function addMessageRow(&$a_tpl, $a_message, $a_type)
	{
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		$a_tpl->touchBlock($this->cur_row_type);
		
		$type = $this->lng->txt("obj_".$a_type);
		$a_message = str_replace("[type]", $type, $a_message);
		
		$a_tpl->setVariable("ROW_NBSP", "&nbsp;");

		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT",
			$a_message);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	function resetRowType()
	{
		$this->cur_row_type = "";
	}

	function addSeparatorRow(&$a_tpl)
	{
		$a_tpl->touchBlock("separator_row");
		$a_tpl->touchBlock("container_row");
	}
	
	function setPageEditorTabs()
	{
		global $lng;
		
		if (!$this->isActiveAdministrationPanel()
			|| strtolower($this->ctrl->getCmdClass()) != "ilpageobjectgui")
		{
			return;
		}

		$lng->loadLanguageModule("content");
		//$tabs_gui = new ilTabsGUI();
		//$tabs_gui->setSubTabs();
		
		// back to upper context
		$this->tabs_gui->setBackTarget($this->lng->txt("obj_cat"),
			$this->ctrl->getLinkTarget($this, "frameset"),
			ilFrameTargetInfo::_getFrame("MainContent"));

		$this->tabs_gui->addTarget("edit", $this->ctrl->getLinkTargetByClass("ilpageobjectgui", "view")
			, array("", "view"), "ilpageobjectgui");

		//$this->tpl->setTabs($tabs_gui->getHTML());
	}


	/**
	* common tabs for all container objects (should be called
	* at the end of child getTabs() method
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		// edit permissions
		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}

		// show clipboard
		if ($this->ctrl->getTargetScript() == "repository.php" and !empty($_SESSION["clipboard"]))
		{
			$tabs_gui->addTarget("clipboard",
				 $this->ctrl->getLinkTarget($this, "clipboard"), "clipboard", get_class($this));
		}

	}

	//*****************
	// COMMON METHODS (may be overwritten in derived classes
	// if special handling is necessary)
	//*****************

	/**
	* enable administration panel
	*/
	function enableAdministrationPanelObject()
	{
		$_SESSION["il_cont_admin_panel"] = true;
		$this->ctrl->redirect($this, "render");
	}

	/**
	* enable administration panel
	*/
	function disableAdministrationPanelObject()
	{
		$_SESSION["il_cont_admin_panel"] = false;
		$this->ctrl->redirect($this, "render");
	}
	
	/**
	* subscribe item
	*/
	function addToDeskObject()
	{
		if ($_GET["item_ref_id"] and $_GET["type"])
		{
			$this->ilias->account->addDesktopItem($_GET["item_ref_id"], $_GET["type"]);
		}
		else
		{
			if ($_POST["items"])
			{
				foreach ($_POST["items"] as $item)
				{
					$type = ilObject::_lookupType($item, true);
					$this->ilias->account->addDesktopItem($item, $type);
				}
			}
		}
		$this->renderObject();
	}
	// BEGIN WebDAV: Lock/Unlock objects
	function lockObject()
	{
		global $tree, $ilUser, $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$_GET['item_ref_id']))
		{
				$this->ilErr->raiseError($this->lng->txt('err_no_permission'),$this->ilErr->MESSAGE);
		}


		require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
		if (ilDAVServer::_isActive() && ilDAVServer::_isActionsVisible())
		{
			require_once 'Services/WebDAV/classes/class.ilDAVLocks.php';
			$locks = new ilDAVLocks();

			$result = $locks->lockRef($_GET['item_ref_id'],
					$ilUser->getId(), $ilUser->getLogin(), 
					'ref_'.$_GET['item_ref_id'].'_usr_'.$ilUser->getId(), 
					time() + /*30*24*60**/60, 0, 'exclusive'
					);

			ilUtil::sendInfo(
						$this->lng->txt(
								($result === true) ? 'object_locked' : $result
								),
						true);
		}
		$this->renderObject();
	}
	// END WebDAV: Lock/Unlock objects

	/**
	* Get Actions
	*/
	function getActions()
	{
		// standard actions for container
		return array(
			"cut" => array("name" => "cut", "lng" => "cut"),
			"delete" => array("name" => "delete", "lng" => "delete"),
			"link" => array("name" => "link", "lng" => "link"),
			"paste" => array("name" => "paste", "lng" => "paste"),
			"clear" => array("name" => "clear", "lng" => "clear")
		);
	}

	
	/**
	* unsubscribe item
	*/
	function removeFromDeskObject()
	{
		if ($_GET["item_ref_id"] and $_GET["type"])
		{
			$this->ilias->account->dropDesktopItem($_GET["item_ref_id"], $_GET["type"]);
		}
		else
		{
			if ($_POST["items"])
			{
				foreach ($_POST["items"] as $item)
				{
					$type = ilObject::_lookupType($item, true);
					$this->ilias->account->dropDesktopItem($item, $type);
					unset($tmp_obj);
				}
			}
		}
		$this->renderObject();
	}


	/**
	* cut object(s) out from a container and write the information to clipboard
	*
	*
	* @access	public
	*/
	function cutObject()
	{
		global $rbacsystem;
		
		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		//$this->ilias->raiseError("move operation does not work at the moment and is disabled",$this->ilias->error_obj->MESSAGE);

//echo "CUT";
//echo $_SESSION["referer"];
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// FOR ALL OBJECTS THAT SHOULD BE COPIED
		foreach ($_POST["id"] as $ref_id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $this->tree->getNodeData($ref_id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
			foreach ($subtree_nodes as $node)
			{
				if($node['type'] == 'rolf')
				{
					continue;
				}
				
				if (!$rbacsystem->checkAccess('delete',$node["ref_id"]))
				{
					$no_cut[] = $node["ref_id"];
				}
			}
		}
		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'delete'
		if (count($no_cut))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_cut")." ".implode(',',$this->getTitlesByRefId($no_cut)),
									 $this->ilias->error_obj->MESSAGE);
		}
		//echo "GET";var_dump($_GET);echo "POST";var_dump($_POST);
		$_SESSION["clipboard"]["parent"] = $_GET["ref_id"];
		$_SESSION["clipboard"]["cmd"] = ($_GET["cmd"] != "" && $_GET["cmd"] != "post")
			? $_GET["cmd"]
			: key($_POST["cmd"]);
//echo "-".$clipboard["cmd"]."-";
		$_SESSION["clipboard"]["ref_ids"] = $_POST["id"];
//echo "-".$_SESSION["clipboard"]["cmd"]."-";

		ilUtil::sendInfo($this->lng->txt("msg_cut_clipboard"),true);

		$this->ctrl->returnToParent($this);

	} // END CUT


	/**
	* create an new reference of an object in tree
	* it's like a hard link of unix
	*
	* @access	public
	*/
	function linkObject()
	{
		global $clipboard, $rbacsystem, $rbacadmin;
		
		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// CHECK ACCESS
		foreach ($_POST["id"] as $ref_id)
		{
			if (!$rbacsystem->checkAccess('delete',$ref_id))
			{
				$no_cut[] = $ref_id;
			}

			$object =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);

			if (!$this->objDefinition->allowLink($object->getType()))
			{
				$no_link[] = $object->getType();
			}
		}

		// NO ACCESS
		if (count($no_cut))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_link")." ".
									 implode(',',$no_cut),$this->ilias->error_obj->MESSAGE);
		}

		if (count($no_link))
		{
			$no_link = array_unique($no_link);

			foreach ($no_link as $type)
			{
				$txt_objs[] = $this->lng->txt("objs_".$type);
			}

			$this->ilias->raiseError(implode(', ',$txt_objs)." ".$this->lng->txt("msg_obj_no_link"),$this->ilias->error_obj->MESSAGE);

			//$this->ilias->raiseError($this->lng->txt("msg_not_possible_link")." ".
			//						 implode(',',$no_link),$this->ilias->error_obj->MESSAGE);
		}

		// WRITE TO CLIPBOARD
		$clipboard["parent"] = $_GET["ref_id"];
		$clipboard["cmd"] = ($_GET["cmd"] != "" && $_GET["cmd"] != "post")
			? $_GET["cmd"]
			: key($_POST["cmd"]);

		foreach ($_POST["id"] as $ref_id)
		{
			$clipboard["ref_ids"][] = $ref_id;
		}

		$_SESSION["clipboard"] = $clipboard;

		ilUtil::sendInfo($this->lng->txt("msg_link_clipboard"),true);

		$this->ctrl->returnToParent($this);

	} // END LINK


	/**
	* clear clipboard and go back to last object
	*
	* @access	public
	*/
	function clearObject()
	{
		unset($_SESSION["clipboard"]);
		unset($_SESSION["il_rep_clipboard"]);
		//var_dump($this->getReturnLocation("clear",$this->ctrl->getLinkTarget($this)),get_class($this));

		// only redirect if clipboard was cleared
		if (isset($_POST["cmd"]["clear"]))
		{
			ilUtil::sendInfo($this->lng->txt("msg_clear_clipboard"),true);

			$this->ctrl->returnToParent($this);
			//ilUtil::redirect($this->getReturnLocation("clear",$this->ctrl->getLinkTarget($this)),get_class($this));
		}
	}

	/**
	* paste object from clipboard to current place
	* Depending on the chosen command the object(s) are linked, copied or moved
	*
	* @access	public
 	*/
	function pasteObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview, $log,$tree;
		global $ilUser;

		// BEGIN ChangeEvent: Record paste event.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		// END ChangeEvent: Record paste event.

//var_dump($_SESSION["clipboard"]);exit;
		if (!in_array($_SESSION["clipboard"]["cmd"],array("cut","link","copy")))
		{
			$message = get_class($this)."::pasteObject(): cmd was neither 'cut','link' or 'copy'; may be a hack attempt!";
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		// this loop does all checks
		foreach ($_SESSION["clipboard"]["ref_ids"] as $ref_id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);

			// CHECK ACCESS
			if (!$rbacsystem->checkAccess('create',$this->object->getRefId(), $obj_data->getType()))
			{
				$no_paste[] = $ref_id;
			}

			// CHECK IF REFERENCE ALREADY EXISTS
			if ($this->object->getRefId() == $this->tree->getParentId($obj_data->getRefId()))
			{
				$exists[] = $ref_id;
				break;
			}

			// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
			if ($this->tree->isGrandChild($ref_id,$this->object->getRefId()))
			{
				$is_child[] = $ref_id;
			}

			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			$obj_type = $obj_data->getType();

			if (!in_array($obj_type, array_keys($this->objDefinition->getSubObjects($this->object->getType()))))
			{
				$not_allowed_subobject[] = $obj_data->getType();
			}
		}

		////////////////////////////
		// process checking results
		// BEGIN WebDAV: Copying an object into the same container is allowed
		if (count($exists) && $_SESSION["clipboard"]["cmd"] != "copy")
		// END WebDAV: Copying an object into the same container is allowed
		{
			$this->ilias->raiseError($this->lng->txt("msg_obj_exists"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($is_child))
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_in_itself")." ".implode(',',$is_child),
									 $this->ilias->error_obj->MESSAGE);
		}

		if (count($not_allowed_subobject))
		{
			$this->ilias->raiseError($this->lng->txt("msg_may_not_contain")." ".implode(',',$not_allowed_subobject),
									 $this->ilias->error_obj->MESSAGE);
		}

		if (count($no_paste))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ".
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
		}

		// log pasteObject call
		$log->write("ilObjectGUI::pasteObject(), cmd: ".$_SESSION["clipboard"]["cmd"]);

		////////////////////////////////////////////////////////
		// everything ok: now paste the objects to new location

		// to prevent multiple actions via back/reload button
		$ref_ids = $_SESSION["clipboard"]["ref_ids"];
		unset($_SESSION["clipboard"]["ref_ids"]);

		// BEGIN WebDAV: Support a copy command in the repository
		// process COPY command
		if ($_SESSION["clipboard"]["cmd"] == "copy")
		{
			foreach($ref_ids as $ref_id)
			{
				$revIdMapping = array(); 
				$newRef = $this->cloneNodes($ref_id, $this->object->getRefId(), $refIdMapping, null);

				// BEGIN ChangeEvent: Record copy event.
				if (ilChangeEvent::_isActive() )
				{
					$oldNode_data = $tree->getNodeData($ref_id);
					$old_parent_data = $tree->getParentNodeData($ref_id);
					$newNode_data = $tree->getNodeData($newRef);
					ilChangeEvent::_recordReadEvent($oldNode_data['obj_id'], $ilUser->getId());
					ilChangeEvent::_recordWriteEvent($newNode_data['obj_id'], $ilUser->getId(), 'add', 
						$this->object->getId());
					ilChangeEvent::_catchupWriteEvents($newNode_data['obj_id'], $ilUser->getId());
				}
				// END ChangeEvent: Record copy event.
			}
			$log->write("ilObjectGUI::pasteObject(), copy finished");
		}
		// END WebDAV: Support a Copy command in the repository

		// process CUT command
		if ($_SESSION["clipboard"]["cmd"] == "cut")
		{
			
			foreach($ref_ids as $ref_id)
			{
				// Store old parent
				$old_parent = $tree->getParentId($ref_id);
				$this->tree->moveTree($ref_id,$this->object->getRefId());
				$rbacadmin->adjustMovedObjectPermissions($ref_id,$old_parent);
				
				include_once('classes/class.ilConditionHandler.php');
				ilConditionHandler::_adjustMovedObjectConditions($ref_id);

				// BEGIN ChangeEvent: Record cut event.
				if (ilChangeEvent::_isActive() )
				{
					$node_data = $tree->getNodeData($ref_id);
					$old_parent_data = $tree->getNodeData($old_parent);
					ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'remove', 
						$old_parent_data['obj_id']);
					ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'add', 
						$this->object->getId());
					ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());
				}
				// END PATCH ChangeEvent: Record cut event.
			}
		} // END CUT

		// process LINK command
		if ($_SESSION["clipboard"]["cmd"] == "link")
		{
			foreach ($ref_ids as $ref_id)
			{
				// get node data
				$top_node = $this->tree->getNodeData($ref_id);

				// get subnodes of top nodes
				$subnodes[$ref_id] = $this->tree->getSubtree($top_node);
			}

			// now move all subtrees to new location
			foreach ($subnodes as $key => $subnode)
			{
				// first paste top_node....
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($key);
				$new_ref_id = $obj_data->createReference();
				$obj_data->putInTree($_GET["ref_id"]);
				$obj_data->setPermissions($_GET["ref_id"]);

				// ... remove top_node from list ...
				array_shift($subnode);

				// ... store mapping of old ref_id => new_ref_id in hash array ...
				$mapping[$new_ref_id] = $key;

				// save old ref_id & create rolefolder if applicable
				$old_ref_id = $obj_data->getRefId();
				$obj_data->setRefId($new_ref_id);
				$obj_data->initDefaultRoles();
				$rolf_data = $rbacreview->getRoleFolderOfObject($obj_data->getRefId());

				if (isset($rolf_data["child"]))
				{
					// a role folder was created, so map it to old role folder
					$rolf_data_old = $rbacreview->getRoleFolderOfObject($old_ref_id);

					// ... use mapping array to find out the correct new parent node where to put in the node...
					//$new_parent = array_search($node["parent"],$mapping);
					// ... append node to mapping for further possible subnodes ...
					$mapping[$rolf_data["child"]] = (int) $rolf_data_old["child"];

					// log creation of role folder
					$log->write("ilObjectGUI::pasteObject(), created role folder (ref_id): ".$rolf_data["child"].
						", for object ref_id:".$obj_data->getRefId().", obj_id: ".$obj_data->getId().
						", type: ".$obj_data->getType().", title: ".$obj_data->getTitle());

				}
				// BEGIN ChangeEvent: Record link event.
				if (ilChangeEvent::_isActive() )
				{
					$node_data = $tree->getNodeData($new_ref_id);
					ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'add', 
						$this->object->getId());
					ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());
				}
				// END PATCH ChangeEvent: Record link event.

				// ... insert subtree of top_node if any subnodes exist ...
				if (count($subnode) > 0)
				{
					foreach ($subnode as $node)
					{
						if ($node["type"] != 'rolf')
						{
							$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($node["child"]);
							$new_ref_id = $obj_data->createReference();

							// ... use mapping array to find out the correct new parent node where to put in the node...
							$new_parent = array_search($node["parent"],$mapping);
							// ... append node to mapping for further possible subnodes ...
							$mapping[$new_ref_id] = (int) $node["child"];

							$obj_data->putInTree($new_parent);
							$obj_data->setPermissions($new_parent);

							// save old ref_id & create rolefolder if applicable
							$old_ref_id = $obj_data->getRefId();
							$obj_data->setRefId($new_ref_id);
							$obj_data->initDefaultRoles();
							$rolf_data = $rbacreview->getRoleFolderOfObject($obj_data->getRefId());

							if (isset($rolf_data["child"]))
							{
								// a role folder was created, so map it to old role folder
								$rolf_data_old = $rbacreview->getRoleFolderOfObject($old_ref_id);

								// ... use mapping array to find out the correct new parent node where to put in the node...
								//$new_parent = array_search($node["parent"],$mapping);
								// ... append node to mapping for further possible subnodes ...
								$mapping[$rolf_data["child"]] = (int) $rolf_data_old["child"];

								// log creation of role folder
								$log->write("ilObjectGUI::pasteObject(), created role folder (ref_id): ".$rolf_data["child"].
									", for object ref_id:".$obj_data->getRefId().", obj_id: ".$obj_data->getId().
									", type: ".$obj_data->getType().", title: ".$obj_data->getTitle());

							}
						}

						// re-map $subnodes
						foreach ($subnodes as $old_ref => $subnode)
						{
							$new_ref = array_search($old_ref,$mapping);

							foreach ($subnode as $node)
							{
								$node["child"] = array_search($node["child"],$mapping);
								$node["parent"] = array_search($node["parent"],$mapping);
								$new_subnodes[$ref_id][] = $node;
							}
						}

					}
				}
			}

			$log->write("ilObjectGUI::pasteObject(), link finished");

			// inform other objects in hierarchy about link operation
			//$this->object->notify("link",$this->object->getRefId(),$_SESSION["clipboard"]["parent_non_rbac_id"],$this->object->getRefId(),$subnodes);
		} // END LINK

		// save cmd for correct message output after clearing the clipboard
		$last_cmd = $_SESSION["clipboard"]["cmd"];


		// clear clipboard
		$this->clearObject();

		if ($last_cmd == "cut")
		{
			ilUtil::sendInfo($this->lng->txt("msg_cut_copied"),true);
		}
		// BEGIN WebDAV: Support a copy command in repository
		else if ($last_cmd == "copy")
		{
			ilUtil::sendInfo($this->lng->txt("msg_copied"),true);
		}
		else if ($last_command == 'link')
		// END WebDAV: Support copy command in repository
		{
			ilUtil::sendInfo($this->lng->txt("msg_linked"),true);
		}

		$this->ctrl->returnToParent($this);

	} // END PASTE

	// BEGIN WebDAV: Support a copy command in repository
	/**
	* copy object(s) out from a container and write the information to clipboard
	*
	*
	* @access	public
	*/
	function copyObject()
	{
		global $rbacsystem;
		
		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// FOR ALL OBJECTS THAT SHOULD BE COPIED
		foreach ($_POST["id"] as $ref_id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $this->tree->getNodeData($ref_id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK VIEW AND READ PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
			foreach ($subtree_nodes as $node)
			{
				if($node['type'] == 'rolf')
				{
					continue;
				}
				
				if (!$rbacsystem->checkAccess('visible,read,edit_permission',$node["ref_id"]))
				{
					$no_copy[] = $node["ref_id"];
				}
			}
		}
		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'view,read'
		if (count($no_copy))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_copy")." ".implode(',',$this->getTitlesByRefId($no_copy)),
									 $this->ilias->error_obj->MESSAGE);
		}
		$_SESSION["clipboard"]["parent"] = $_GET["ref_id"];
		$_SESSION["clipboard"]["cmd"] = ($_GET["cmd"] != "" && $_GET["cmd"] != "post")
			? $_GET["cmd"]
			: key($_POST["cmd"]);
		$_SESSION["clipboard"]["ref_ids"] = $_POST["id"];

		ilUtil::sendInfo($this->lng->txt("msg_copy_clipboard"),true);

		$this->ctrl->returnToParent($this);

	} // END COPY
	// BEGIN WebDAV: Support copy command in repository


	/**
	* show clipboard
	*/
	function clipboardObject()
	{
		global $ilErr,$ilLog;

		// function should not be called if clipboard is empty
		if (empty($_SESSION['clipboard']) or !is_array($_SESSION['clipboard']))
		{
			$message = sprintf('%s::clipboardObject(): Illegal access. Clipboard variable is empty!', get_class($this));
			$ilLog->write($message,$ilLog->FATAL);
			$ilErr->raiseError($this->lng->txt("permission_denied"),$ilErr->WARNING);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.rep_clipboard.html");

		// FORMAT DATA
		$counter = 0;
		$f_result = array();

		foreach($_SESSION["clipboard"]["ref_ids"] as $ref_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id,false))
			{
				continue;
			}

			//$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $this->lng->txt("obj_".$tmp_obj->getType());
			$f_result[$counter][] = $tmp_obj->getTitle();
			//$f_result[$counter][] = $tmp_obj->getDescription();
			$f_result[$counter][] = ($_SESSION["clipboard"]["cmd"] == "cut") ? $this->lng->txt("move") :$this->lng->txt($_SESSION["clipboard"]["cmd"]);

			unset($tmp_obj);
			++$counter;
		}

		$this->__showClipboardTable($f_result, "clipboardObject");

		return true;
	}

	
	/**
	* show edit section of custom icons for container
	* 
	*/
	function showCustomIconsEditing($a_input_colspan = 1)
	{
		if ($this->ilias->getSetting("custom_icons"))
		{
			$this->tpl->addBlockFile("CONTAINER_ICONS", "container_icon_settings",
				"tpl.container_icon_settings.html");

			if (($big_icon = $this->object->getBigIconPath()) != "")
			{
				$this->tpl->setCurrentBlock("big_icon");
				$this->tpl->setVariable("SRC_BIG_ICON", $big_icon);
				$this->tpl->parseCurrentBlock();
			}
			if (($small_icon = $this->object->getSmallIconPath()) != "")
			{
				$this->tpl->setCurrentBlock("small_icon");
				$this->tpl->setVariable("SRC_SMALL_ICON", $small_icon);
				$this->tpl->parseCurrentBlock();
			}
			if (($tiny_icon = $this->object->getTinyIconPath()) != "")
			{
				$this->tpl->setCurrentBlock("tiny_icon");
				$this->tpl->setVariable("SRC_TINY_ICON", $tiny_icon);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("container_icon_settings");
			$this->tpl->setVariable("SPAN_TITLE", $a_input_colspan + 1);
			$this->tpl->setVariable("SPAN_INPUT", $a_input_colspan);
			$this->tpl->setVariable("ICON_SETTINGS", $this->lng->txt("icon_settings"));
			$this->tpl->setVariable("BIG_ICON", $this->lng->txt("big_icon"));
			$this->tpl->setVariable("SMALL_ICON", $this->lng->txt("standard_icon"));
			$this->tpl->setVariable("TINY_ICON", $this->lng->txt("tiny_icon"));
			$this->tpl->setVariable("BIG_SIZE", "(".
				$this->ilias->getSetting("custom_icon_big_width")."x".
				$this->ilias->getSetting("custom_icon_big_height").")");
			$this->tpl->setVariable("SMALL_SIZE", "(".
				$this->ilias->getSetting("custom_icon_small_width")."x".
				$this->ilias->getSetting("custom_icon_small_height").")");
			$this->tpl->setVariable("TINY_SIZE", "(".
				$this->ilias->getSetting("custom_icon_tiny_width")."x".
				$this->ilias->getSetting("custom_icon_tiny_height").")");
			$this->tpl->setVariable("TXT_REMOVE", $this->lng->txt("remove"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function isActiveAdministrationPanel()
	{
		return $_SESSION["il_cont_admin_panel"];
	}

	/**
	* Get introduction.
	*/
	function getIntroduction()
	{
		global $ilUser, $lng, $ilCtrl;
		
		$lng->loadLanguageModule("rep");
		
		$tpl = new ilTemplate("tpl.rep_intro.html", true, true, "Services/Repository");
		$tpl->setVariable("IMG_REP_LARGE", ilUtil::getImagePath("icon_root_xxl.gif"));
		$tpl->setVariable("TXT_WELCOME", $lng->txt("rep_intro"));
		$tpl->setVariable("TXT_INTRO_1", $lng->txt("rep_intro1"));
		$tpl->setVariable("TXT_INTRO_2", $lng->txt("rep_intro2"));
		$tpl->setVariable("TXT_INTRO_3", sprintf($lng->txt("rep_intro3"), $lng->txt("add")));
		$tpl->setVariable("TXT_INTRO_4", sprintf($lng->txt("rep_intro4"), $lng->txt("cat_add")));
		$tpl->setVariable("TXT_INTRO_5", $lng->txt("rep_intro5"));
		$tpl->setVariable("TXT_INTRO_6", $lng->txt("rep_intro6"));
		
		return $tpl->get();
	}
	
	/**
	* May be overwritten in subclasses.
	*/
	function setColumnSettings($column_gui)
	{
		global $ilAccess;
		parent::setColumnSettings($column_gui);

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()) &&
			$this->isActiveAdministrationPanel())
		{
			$column_gui->setEnableMovement(true);
		}
		
		$column_gui->setRepositoryItems($this->items);
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$column_gui->setBlockProperty("news", "settings", true);
			//$column_gui->setBlockProperty("news", "public_notifications_option", true);
			$column_gui->setBlockProperty("news", "default_visibility_option", true);
			$column_gui->setBlockProperty("news", "hide_news_block_option", true);
		}
		
		if ($this->isActiveAdministrationPanel())
		{
			$column_gui->setAdminCommands(true);
		}
	}
	
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function cloneWizardPageTreeObject()
	{
	 	$this->cloneWizardPageObject(true);
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function cloneWizardPageListObject()
	{
	 	$this->cloneWizardPageObject(false);
	}
	
	/**
	 * Show clone wizard page for container objects
	 *
	 * @access public
	 * 
	 */
	public function cloneWizardPageObject($a_tree_view = true)
	{
		include_once('Services/CopyWizard/classes/class.ilCopyWizardPageFactory.php');
		
		global $ilObjDataCache,$tree;
		
	 	if(!$_REQUEST['clone_source'])
	 	{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			if(isset($_SESSION['wizard_search_title']))
			{
				$this->searchCloneSourceObject();
			}
			else
			{
				$this->createObject();
			}
			return false;
	 	}
		$source_id = $_REQUEST['clone_source'];
	 	$new_type = $_REQUEST['new_type'];
	 	$this->ctrl->setParameter($this,'clone_source',(int) $_REQUEST['clone_source']);
	 	$this->ctrl->setParameter($this,'new_type',$new_type);
		

		// Generell JavaScript
		$this->tpl->addJavaScript('./Services/CopyWizard/js/ilContainer.js');
		$this->tpl->setVariable('BODY_ATTRIBUTES','onload="ilDisableChilds(\'cmd\');"');

		
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.container_wizard_page.html');
	 	$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
	 	$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$new_type.'.gif'));
	 	$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_'.$new_type));
	 	$this->tpl->setVariable('TXT_DUPLICATE',$this->lng->txt($new_type.'_wizard_page'));
	 	$this->tpl->setVariable('INFO_DUPLICATE',$this->lng->txt($new_type.'_copy_threads_info'));
	 	$this->tpl->setVariable('BTN_COPY',$this->lng->txt('obj_'.$new_type.'_duplicate'));
	 	$this->tpl->setVariable('BTN_BACK',$this->lng->txt('btn_back'));
	 	if(isset($_SESSION['wizard_search_title']))
	 	{
	 		$this->tpl->setVariable('CMD_BACK','searchCloneSource');
	 	}
	 	else
	 	{
	 		$this->tpl->setVariable('CMD_BACK','create');
	 	}
	 	
	 	$this->tpl->setVariable('BTN_TREE',$this->lng->txt('treeview'));
	 	$this->tpl->setVariable('BTN_LIST',$this->lng->txt('flatview'));

		// Fill item rows
		// tree view
		if($a_tree_view)
		{
			$first = true;
			$has_items = false; 
			foreach($subnodes = $tree->getSubtree($source_node = $tree->getNodeData($source_id),true) as $node)
			{
				if($first == true)
				{
					$first = false;
					continue;
				}
				
				if($node['type'] == 'rolf')
				{
					continue;
				}
				
				$has_items = true;

				for($i = $source_node['depth'];$i < $node['depth']; $i++)
				{
					$this->tpl->touchBlock('padding');
					$this->tpl->touchBlock('end_padding');
				}
				// fill options
				$copy_wizard_page = ilCopyWizardPageFactory::_getInstanceByType($source_id,$node['type']);
				$copy_wizard_page->fillTreeSelection($node['ref_id'],$node['type'],$node['depth']);
				
				$this->tpl->setCurrentBlock('tree_row');
				$this->tpl->setVariable('TREE_IMG',ilUtil::getImagePath('icon_'.$node['type'].'_s.gif'));
				$this->tpl->setVariable('TREE_ALT_IMG',$this->lng->txt('obj_'.$node['type']));
				$this->tpl->setVariable('TREE_TITLE',$node['title']);
				$this->tpl->parseCurrentBlock();
			}
			if(!$has_items)
			{
				$this->tpl->setCurrentBlock('no_content');
				$this->tpl->setVariable('TXT_NO_CONTENT',$this->lng->txt('container_no_items'));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock('tree_footer');
				$this->tpl->setVariable('TXT_COPY_ALL',$this->lng->txt('copy_all'));
				$this->tpl->setVariable('TXT_LINK_ALL',$this->lng->txt('link_all'));
				$this->tpl->setVariable('TXT_OMIT_ALL',$this->lng->txt('omit_all'));
				$this->tpl->parseCurrentBlock();
				
			}
		}
		else
		{
			foreach($tree->getSubTreeTypes($source_id,array('rolf','crs')) as $type)
			{
				$copy_wizard_page = ilCopyWizardPageFactory::_getInstanceByType($source_id,$type);
				if(strlen($html = $copy_wizard_page->getWizardPageBlockHTML()))
				{
					$this->tpl->setCurrentBlock('obj_row');
					$this->tpl->setVariable('ITEM_BLOCK',$html);
					$this->tpl->parseCurrentBlock();
				}
			}
		}
	}
	
	/**
	 * Clone all object
	 * Overwritten method for copying container objects
	 *
	 * @access public
	 * 
	 */
	public function cloneAllObject()
	{
		global $ilLog;
		
		include_once('classes/class.ilLink.php');
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		
		global $ilAccess,$ilErr,$rbacsystem,$tree,$ilUser;
		
	 	$new_type = $_REQUEST['new_type'];
	 	$ref_id = (int) $_GET['ref_id'];
	 	$clone_source = (int) $_REQUEST['clone_source'];
	 	
	 	if(!$rbacsystem->checkAccess('create', $ref_id,$new_type))
	 	{
	 		$ilErr->raiseError($this->lng->txt('permission_denied'));
	 	}
		if(!$clone_source)
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->createObject();
			return false;
		}
		if(!$ilAccess->checkAccess('write','', $clone_source,$new_type))
		{
	 		$ilErr->raiseError($this->lng->txt('permission_denied'));
		}

		$options = $_POST['cp_options'] ? $_POST['cp_options'] : array();
		$orig = ilObjectFactory::getInstanceByRefId($clone_source);
		$result = $orig->cloneAllObject($_COOKIE['PHPSESSID'], $_COOKIE['ilClientId'], $new_type, $ref_id, $clone_source, $options);
		
		// Check if copy is in progress
		if ($result == $ref_id)
		{
			ilUtil::sendInfo($this->lng->txt("object_copy_in_progress"),true);
			ilUtil::redirect('repository.php?ref_id='.$ref_id);
		} 
		else 
		{
			ilUtil::sendInfo($this->lng->txt("object_duplicated"),true);
			ilUtil::redirect('repository.php?ref_id='.$result);			
		}	
	}

	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function copyWizardHasOptions($a_mode)
	{
	 	switch($a_mode)
	 	{
	 		case self::COPY_WIZARD_NEEDS_PAGE:
	 			return true;
	 		
	 		default:
	 			return false;
	 	}
	}
	
	/**
	 * Save Sorting
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function saveSortingObject()
	{
		include_once('Services/Container/classes/class.ilContainerSorting.php');
		$sorting = new ilContainerSorting($this->object->getId());
		$sorting->savePost($_POST['position']);
		ilUtil::sendInfo($this->lng->txt('sorting_saved',true));
		$this->ctrl->returnToParent($this);
	}
	
	// BEGIN WebDAV: Support a copy command in the repository
	/**
	* Recursively clones all nodes of the RBAC tree.
	* 
	* @access	private
	* @param	integer ref_id of source object
	* @param	integer ref_id of destination object
	* @param	array	mapping new_ref_id => old_ref_id
	* @param	string the new name of the copy (optional).
	* @return	The ref_id pointing to the cloned object.
	*/
	function cloneNodes($srcRef,$dstRef,&$mapping, $newName=null)
	{
		global $tree;
		global $ilias;
		
		// clone the source node
		$srcObj =& $ilias->obj_factory->getInstanceByRefId($srcRef);
		error_log(__METHOD__.' cloning srcRef='.$srcRef.' dstRef='.$dstRef.'...');
		$newRef = $srcObj->cloneObject($dstRef)->getRefId();
		error_log(__METHOD__.' ...cloning... newRef='.$newRef.'...');
		
		// We must immediately apply a new name to the object, to
		// prevent confusion of WebDAV clients about having two objects with identical
		// name in the repository.
		if (! is_null($newName))
		{
			$newObj =& $ilias->obj_factory->getInstanceByRefId($newRef);
			$newObj->setTitle($newName);
			$newObj->write();
			unset($newObj);
		}
		unset($srcObj);
		$mapping[$newRef] = $srcRef;

		// clone all children of the source node
		$children = $tree->getChilds($srcRef);
		foreach ($tree->getChilds($srcRef) as $child)
		{
			// Don't clone role folders, because it does not make sense to clone local roles
			// FIXME - Maybe it does make sense (?)
			if ($child["type"] != 'rolf')
			{
				$this->cloneNodes($child["ref_id"],$newRef,$mapping);
			}
			else
			{
				if (count($rolf = $tree->getChildsByType($newRef,"rolf")))
				{
					$mapping[$rolf[0]["ref_id"]] = $child["ref_id"];
				}
			}
		}
		error_log(__METHOD__.' ...cloned srcRef='.$srcRef.' dstRef='.$dstRef.' newRef='.$newRef);
		return $newRef;
	}
	// END PATCH WebDAV: Support a copy command in the repository

}
?>
