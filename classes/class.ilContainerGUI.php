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
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

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
		$next_class = $this->ctrl->getNextClass();
		$cmd = $this->ctrl->getCmd("render");
		switch($next_class)
		{
			// page editing
			case "ilpageobjectgui":
				return $this->forwardToPageObject();
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
		global $lng;

		// page object
		include_once("./content/classes/Pages/class.ilPageObject.php");
		include_once("./content/classes/Pages/class.ilPageObjectGUI.php");

		$lng->loadLanguageModule("content");
		
		include_once("./classes/class.ilObjStyleSheet.php");
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
		$page_object = new ilPageObject($this->object->getType(),
			$this->object->getId(), true);

		$this->ctrl->setReturnByClass("ilpageobjectgui", "view");
		//$page_object =& $this->obj->getPageObject();
		$page_object->buildDom();
		//$page_object->addUpdateListener($this, "updateHistory");
		$int_links = $page_object->getInternalLinks();
		//$link_xml = $this->getLinkXML($int_links);
		$page_gui =& new ilPageObjectGUI($page_object);

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
		//$page_gui->setLocator($contObjLocator);
		$page_gui->setHeader("");
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
			in_array($this->object->getType(), array("cat","grp","crs")))
		{
			require_once("classes/class.ilContainer.php");
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
					if (in_array($row["name"], array("sahs", "alm", "hlm", "lm", "grp", "frm", "mep","crs",
													 "cat", "glo", "dbk","exc", "qpl", "tst", "svy", "spl", "chat", 
													 "htlm","fold","linkr","file","icrs","icla","crsg",'webr')))
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
			$this->tpl->parseCurrentBlock("add_commands");
			$this->tpl->setVariable("H_FORMACTION",  "repository.php?ref_id=".$this->object->getRefId().
				"&cmd=post");
			// possible subobjects
			$opts = ilUtil::formSelect("", "new_type", $subobj);
			$this->tpl->setVariable("SELECT_OBJTYPE_REPOS", $opts);
			$this->tpl->setVariable("BTN_NAME_REPOS", "create");
			$this->tpl->setVariable("TXT_ADD_REPOS", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* display tree view
	*/
/*	This is currently implemented in ilRepositoryGUI for all containers
	and a conceptional issue whether it should be moved to this class.
	function showTreeObject()
	{
		$this->tpl = new ilTemplate("tpl.main.html", true, true);
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		//$this->tpl = new ilTemplate("tpl.explorer.html", false, false);
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		include_once ("classes/class.ilRepositoryExplorer.php");
		$exp = new ilRepositoryExplorer("repository.php?cmd=goto");
		$exp->setExpandTarget("repository.php?cmd=showTree&ref_id=".$this->object->getRefId());
		$exp->setTargetGet("ref_id");

		if ($_GET["repexpand"] == "")
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET["repexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("repository"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER", $output);
		//$this->tpl->setVariable("ACTION", "repository.php?repexpand=".$_GET["repexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show(false);
		exit;
	}
*/

	/**
	* render container object
	* (this should include multiple lists in the future that together
	* build the blocks of a container page)
	*/
	function renderObject()
	{
		global $ilBench, $tree;
		
		// 'add object'
		$this->showPossibleSubObjects();

		// course content interface methods could probably
		// move to this class
		if($this->type != 'icrs' and $tree->checkForParentType($this->ref_id,'crs'))
		{
			$this->initCourseContentInterface();
			$this->cci_obj->cci_setContainer($this);
			$this->cci_obj->cci_view();
			
			return;
		}


		$ilBench->start("ilContainerGUI", "0000__renderObject");

		$tpl = new ilTemplate ("tpl.container_page.html", true, true);
		
		// get all sub items
		$ilBench->start("ilContainerGUI", "0100_getSubItems");
		$this->getSubItems();
		$ilBench->stop("ilContainerGUI", "0100_getSubItems");

		$ilBench->start("ilContainerGUI", "0200_renderItemList");
		$html = $this->renderItemList();
		$tpl->setVariable("CONTAINER_PAGE_CONTENT", $html);
		$ilBench->stop("ilContainerGUI", "0200_renderItemList");
		
		$this->showAdministrationPanel($tpl);
		
		$this->html = $tpl->get();
		
		$ilBench->stop("ilContainerGUI", "0000__renderObject");
	}

	/**
	* show administration panel
	*/
	function showAdministrationPanel(&$tpl)
	{
		global $ilAccess;
		
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
				$tpl->setCurrentBlock("edit_cmd");
				$tpl->setVariable("TXT_EDIT_PAGE", $this->lng->txt("edit_page"));
				$tpl->setVariable("LINK_EDIT_PAGE", $this->ctrl->getLinkTarget($this, "editPageFrame"));
				$tpl->setVariable("FRAME_EDIT_PAGE", ilFrameTargetInfo::_getFrame("MainContent"));
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setCurrentBlock("admin_panel_cmd");
			$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt("delete_selected_items"));
			$tpl->setVariable("PANEL_CMD", "delete");
			$tpl->parseCurrentBlock();
			if (!$_SESSION["clipboard"])
			{
				$tpl->setCurrentBlock("admin_panel_cmd");
				$tpl->setVariable("TXT_PANEL_CMD", $this->lng->txt("move_selected_items"));
				$tpl->setVariable("PANEL_CMD", "cut");
				$tpl->parseCurrentBlock();
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
			$tpl->setCurrentBlock("admin_panel");
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
			$tpl->setVariable("TXT_ADMIN_PANEL", $this->lng->txt("admin_panel"));
			$tpl->parseCurrentBlock();
			$this->ctrl->setParameter($this, "type", "");
			$this->ctrl->setParameter($this, "item_ref_id", "");
			$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		}
		else if ($this->adminCommands)
		{
			$tpl->setCurrentBlock("admin_button");
			$tpl->setVariable("ADMIN_MODE_LINK",
				$this->ctrl->getLinkTarget($this, "enableAdministrationPanel"));
			$tpl->setVariable("TXT_ADMIN_MODE",
				$this->lng->txt("admin_panel_enable"));
			$tpl->parseCurrentBlock();
		}
	}

	/**
	* show page editor frameset
	*/
	function editPageFrameObject()
	{
		$this->tpl = new ilTemplate("tpl.container_edit_frameset.html", false, false);
		$this->tpl->setVariable("HREF_LINK_LIST",
			$this->ctrl->getLinkTargetByClass("ilcontainerlinklistgui", "show"));
		$this->tpl->setVariable("HREF_EDITOR",
			$this->ctrl->getLinkTargetByClass(
				array("ilpageobjectgui"), "view"));
		$this->tpl->show("DEFAULT", false);
		exit;
	}

	/**
	* get all subitems of the container
	*/
	function getSubItems()
	{
		global $objDefinition, $ilBench;

		$objects = $this->tree->getChilds($this->object->getRefId(), "title");

		$found = false;

		foreach ($objects as $key => $object)
		{

			// hide object types in devmode
			if ($objDefinition->getDevMode($object["type"]))
			{
				continue;
			}

			// group together types (e.g. ILIAS learning modules
			// and SCORM learning modules to learning materials)
			switch ($object["type"])
			{
				// learning material
				case "sahs":
				case "lm":
				case "dbk":
				case "htlm":
					$type = "lres";
					break;

				default:
					$type = $object["type"];
					break;
			}

			$this->items[$type][$key] = $object;
		}
	}

	function renderItemList($a_type = "all")
	{
		global $objDefinition, $ilBench;
		
		include_once("classes/class.ilObjectListGUIFactory.php");

		$output_html = "";
		$this->clearAdminCommandsDetermination();
		
		switch ($a_type)
		{
			// render all items list
			case "all":
			
				// get container page
				include_once("./content/classes/Pages/class.ilPageObjectGUI.php");
				include_once("./content/classes/Pages/class.ilPageObject.php");
				if (ilPageObject::_exists($this->object->getType(),
					$this->object->getId()))
				{
					$page =& new ilPageObject($this->object->getType(), $this->object->getId());
					$page_gui =& new ilPageObjectGUI($page);
					//$page_gui->setSourcecodeDownloadScript("glossary_presentation.php?ref_id=".$_GET["ref_id"]);
					//$page_gui->setFullscreenLink("glossary_presentation.php?cmd=fullscreen&amp;ref_id=".$_GET["ref_id"]);
					//$page_gui->setFileDownloadLink("glossary_presentation.php?cmd=downloadFile".
					//	"&amp;ref_id=".$_GET["ref_id"]);
						
					$page_gui->setOutputMode("presentation");
					//$page_gui->setLinkXML($this->getLinkXML());
					$page_gui->setTemplateOutput(false);
					$output_html.= $page_gui->presentation($page_gui->getOutputMode());
				}

				// all item types
				$type_ordering = array(
					"cat", "fold", "crs", "icrs", "icla", "grp", "chat", "frm", "lres",
					"glo", "webr", "file", "exc",
					"tst", "svy", "mep", "qpl", "spl");

				$cur_obj_type = "";
				$overall_tpl =& $this->newBlockTemplate();
				$this->type_template = array();
				$first = true;
				
				// iterate all types
				foreach ($type_ordering as $type)
				{
					// set template (overall or type specific)
					if (is_int(strpos($output_html, "++".$type."++")))
					{
						$tpl =& $this->newBlockTemplate();
						$overall = false;
					}
					else
					{
						$tpl =& $overall_tpl;
						$overall = true;
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
								$item_html[] = array("html" => $html, "item_ref_id" => $item["ref_id"]
									, "item_obj_id" => $item["obj_id"]);
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
							foreach($item_html as $item)
							{
								if ($this->ilias->getSetting("icon_position_in_lists") == "item_rows")
								{
									$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], $type);
								}
								else
								{
									$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"]);
								}
							}

							// store type specific templates in array
							if (is_int(strpos($output_html, "++".$type."++")))
							{
								$this->type_template[$type] = $tpl;
							}
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
				$output_html = str_replace("<br>++", "++", $output_html);
				$output_html = str_replace("<br>++", "++", $output_html);
				$output_html = str_replace("++<br>", "++", $output_html);
				$output_html = str_replace("++<br>", "++", $output_html);
				foreach ($this->type_template as $type => $tpl)
				{
					$output_html = str_replace("++".$type."++",
						"</p>".$tpl->get()."<p class=\"ilc_Standard\">",
						$output_html);
				}

				if (ilPageObject::_exists($this->object->getType(),
					$this->object->getId()))
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
		if ($a_type != "lres")
		{
			$icon = ilUtil::getImagePath("icon_".$a_type.".gif");
			$title = $this->lng->txt("objs_".$a_type);
		}
		else
		{
			$icon = ilUtil::getImagePath("icon_lm.gif");
			$title = $this->lng->txt("learning_resources");
		}
		
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
		if ($a_image_type != "")
		{
			if ($a_image_type != "lres")
			{
				$icon = ilUtil::getImagePath("icon_".$a_image_type.".gif");
				$alt = $this->lng->txt("obj_".$a_image_type);
			}
			else
			{
				$icon = ilUtil::getImagePath("icon_lm.gif");
				$alt = $this->lng->txt("learning_resource");
			}
			
			// custom icon
			if ($this->ilias->getSetting("custom_icons") &&
				in_array($a_image_type, array("cat","grp","crs")))
			{
				require_once("classes/class.ilContainer.php");
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
		if ($nbsp)
		{
			$a_tpl->setVariable("ROW_NBSP", "&nbsp;");
		}
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
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
					unset($tmp_obj);
				}
			}
		}
		$this->renderObject();
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

		sendinfo($this->lng->txt("msg_cut_clipboard"),true);

		$this->ctrl->returnToParent($this);
		//ilUtil::redirect($this->getReturnLocation("cut","adm_object.php?ref_id=".$_GET["ref_id"]));

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

		sendinfo($this->lng->txt("msg_link_clipboard"),true);

		$this->ctrl->returnToParent($this);
		//ilUtil::redirect($this->getReturnLocation("link","adm_object.php?ref_id=".$_GET["ref_id"]));

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
			sendinfo($this->lng->txt("msg_clear_clipboard"),true);

			//ilUtil::redirect($this->getReturnLocation("clear","adm_object.php?ref_id=".$_GET["ref_id"]));
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
		global $rbacsystem, $rbacadmin, $rbacreview, $log;

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
		if (count($exists))
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

		// process COPY command
		if ($_SESSION["clipboard"]["cmd"] == "copy")
		{
			// CALL PRIVATE CLONE METHOD
			$this->cloneObject($ref_ids);
		}

		// process CUT command
		if ($_SESSION["clipboard"]["cmd"] == "cut")
		{
			// get subtrees
			foreach($ref_ids as $ref_id)
			{
				// get node data
				$top_node = $this->tree->getNodeData($ref_id);

				// get subnodes of top nodes
				$subnodes[$ref_id] = $this->tree->getSubtree($top_node);
			}

			// STEP 1: Move all subtrees to trash
			$log->write("ilObjectGUI::pasteObject(), (1/3) move subtrees to trash");

			foreach($ref_ids as $ref_id)
			{
				$tnodes = $this->tree->getSubtree($this->tree->getNodeData($ref_id));

				foreach ($tnodes as $tnode)
				{
					$rbacadmin->revokePermission($tnode["child"],0,false);
					
					// we don't remove the item from the personal desktop,
					// just due to moving it
					//$affected_users = ilUtil::removeItemFromDesktops($tnode["child"]);
				}

				$this->tree->saveSubTree($ref_id);
				$this->tree->deleteTree($this->tree->getNodeData($ref_id));
			}

			// STEP 2: Move all subtrees to new location
			$log->write("ilObjectGUI::pasteObject(), (2/3) move subtrees to new location");

			// TODO: this whole put in place again stuff needs revision. Permission settings get lost.
			foreach ($subnodes as $key => $subnode)
			{
$log->write("ilObjectGUI::pasteObject(), 0");
				// first paste top_node ...
				$rbacadmin->revokePermission($key,0,false);
$log->write("ilObjectGUI::pasteObject(), 1");
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($key);
$log->write("ilObjectGUI::pasteObject(), 2");
				$obj_data->putInTree($this->object->getRefId());
$log->write("ilObjectGUI::pasteObject(), 3");
				$obj_data->setPermissions($key);
$log->write("ilObjectGUI::pasteObject(), 4");
				// log entry
				$log->write("ilObjectGUI::pasteObject(), inserted top node. ref_id: $key,".
					" rgt: ".$subnode[0]["rgt"].", lft: ".$subnode[0]["lft"].", parent: ".$subnode[0]["parent"].",".
					" obj_id: ".$obj_data->getId().", type: ".$obj_data->getType().
					", title: ".$obj_data->getTitle());

				// ... remove top_node from list ...
				array_shift($subnode);

				// ... insert subtree of top_node if any subnodes exist
				if (count($subnode) > 0)
				{
					foreach ($subnode as $node)
					{
						$rbacadmin->revokePermission($node["child"],0,false);
						$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($node["child"]);
						$obj_data->putInTree($node["parent"]);
						$obj_data->setPermissions($node["parent"]);

						// log entry
						$log->write("ilObjectGUI::pasteObject(), inserted subnode. ref_id: ".$node["child"].",".
							" rgt: ".$node["rgt"].", lft: ".$node["lft"].", parent: ".$node["parent"].",".
							" obj_id: ".$obj_data->getId().", type: ".$obj_data->getType().
							", title: ".$obj_data->getTitle());
					}
				}
			}

			// STEP 3: Remove trashed objects from system
			$log->write("ilObjectGUI::pasteObject(), (3/3) remove trashed subtrees from system");

			foreach ($ref_ids as $ref_id)
			{
				// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
				$saved_tree = new ilTree(-(int)$ref_id);
				$node_data = $saved_tree->getNodeData($ref_id);
				$subtree_nodes = $saved_tree->getSubTree($node_data);

				// remember already checked deleted node_ids
				$checked[] = -(int) $ref_id;

				// dive in recursive manner in each already deleted subtrees and remove these objects too
				$this->removeDeletedNodes($ref_id, $checked, false);

				// delete save tree
				$this->tree->deleteTree($node_data);

				// write log entry
				$log->write("ilObjectGUI::pasteObject(), deleted tree, tree_id: ".$node_data["tree"].
					", child: ".$node_data["child"]);
			}


			$log->write("ilObjectGUI::pasteObject(), cut finished");

			// inform other objects in hierarchy about paste operation
			//$this->object->notify("paste",$this->object->getRefId(),$_SESSION["clipboard"]["parent_non_rbac_id"],$this->object->getRefId(),$subnodes);

			// inform other objects in hierarchy about cut operation
			// the parent object where cut occured
			$tmp_object = $this->ilias->obj_factory->getInstanceByRefId($_SESSION["clipboard"]["parent"]);
			//$tmp_object->notify("cut", $tmp_object->getRefId(),$_SESSION["clipboard"]["parent_non_rbac_id"],$tmp_object->getRefId(),$ref_ids);
			unset($tmp_object);
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
			sendInfo($this->lng->txt("msg_cut_copied"),true);
		}
		else
		{
			sendInfo($this->lng->txt("msg_linked"),true);
		}

		//ilUtil::redirect($this->getReturnLocation("paste",$this->ctrl->getLinkTarget($this)),get_class($this));
		$this->ctrl->returnToParent($this);
		//ilUtil::redirect($this->getReturnLocation("paste","adm_object.php?ref_id=".$_GET["ref_id"]));

	} // END PASTE



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
	function showCustomIconsEditing()
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
			$this->tpl->setCurrentBlock("container_icon_settings");
			$this->tpl->setVariable("ICON_SETTINGS", $this->lng->txt("icon_settings"));
			$this->tpl->setVariable("BIG_ICON", $this->lng->txt("big_icon"));
			$this->tpl->setVariable("SMALL_ICON", $this->lng->txt("small_icon"));
			$this->tpl->setVariable("BIG_SIZE", "(".
				$this->ilias->getSetting("custom_icon_big_width")."x".
				$this->ilias->getSetting("custom_icon_big_height").")");
			$this->tpl->setVariable("SMALL_SIZE", "(".
				$this->ilias->getSetting("custom_icon_small_width")."x".
				$this->ilias->getSetting("custom_icon_small_height").")");
			$this->tpl->setVariable("TXT_REMOVE", $this->lng->txt("remove"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function isActiveAdministrationPanel()
	{
		return $_SESSION["il_cont_admin_panel"];
	}

}
?>
