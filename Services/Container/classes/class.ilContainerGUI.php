<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";
require_once "./Services/Container/classes/class.ilContainer.php";
include_once './Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';

/**
* Class ilContainerGUI
*
* This is a base GUI class for all container objects in ILIAS:
* root folder, course, group, category, folder
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version $Id$
*
* @extends ilObjectGUI
*/
class ilContainerGUI extends ilObjectGUI implements ilDesktopItemHandling
{
	var $bl_cnt = 1;		// block counter
	var $multi_download_enabled = false;	
	
	/**
	* Constructor
	* @access public
	*/
	function ilContainerGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $rbacsystem, $lng, $tree;

		$this->rbacsystem =& $rbacsystem;
		
		$lng->loadLanguageModule("cntr");

		//$this->ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		
        // Activate tree cache when rendering the container to improve performance
        //$tree->useCache(false);

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
			case "ilcontainerpagegui":
				if ($_GET["redirectSource"] != "ilinternallinkgui")
				{
					$ret = $this->forwardToPageObject();
					$tpl->setContent($ret);
				}
				else
				{
					return "";
				}
				break;
				
			case "ilobjstylesheetgui":
				$this->forwardToStyleSheet();
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
	 * Get values for edit form
	 *
	 * @return array
	 */
	protected function getEditFormValues()
	{
		$values = parent::getEditFormValues();

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
		$values['didactic_type'] =
			'dtpl_'.ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());

		return $values;
	}

	/**
	 *
	 */
	protected function afterUpdate()
	{
		// check if template is changed
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
		$current_tpl_id = (int) ilDidacticTemplateObjSettings::lookupTemplateId(
			$this->object->getRefId()
		);
		$new_tpl_id = (int) $this->getDidacticTemplateVar('dtpl');

		if($new_tpl_id != $current_tpl_id)
		{
			$_REQUEST['tplid'] = $new_tpl_id;
			
			// redirect to didactic template confirmation
			include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
			$this->ctrl->setReturn($this,'edit');
			$this->ctrl->setCmdClass('ildidactictemplategui');
			$this->ctrl->setCmd('confirmTemplateSwitch');
			$dtpl_gui = new ilDidacticTemplateGUI($this);
			return $this->ctrl->forwardCommand($dtpl_gui);
		}
		parent::afterUpdate();
	}


	/**
	* Forward to style object
	*/
	function forwardToStyleSheet()
	{
		global $ilCtrl, $ilTabs;
		
		$ilTabs->clearTargets();
		
		$cmd = $ilCtrl->getCmd();
		include_once ("./Services/Style/classes/class.ilObjStyleSheetGUI.php");
		$this->ctrl->setReturn($this, "editStyleProperties");
		$style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
		$style_gui->omitLocator();
		if ($cmd == "create" || $_GET["new_type"]=="sty")
		{
			$style_gui->setCreationMode(true);
		}

		if ($cmd == "confirmedDelete")
		{
			$this->object->setStyleSheetId(0);
			$this->object->update();
		}

		$ret = $this->ctrl->forwardCommand($style_gui);

		if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle")
		{
			$style_id = $ret;
			$this->object->setStyleSheetId($style_id);
			$this->object->update();
			$this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
		}
	}
	
	
	/**
	* forward command to page object
	*/
	function &forwardToPageObject()
	{
		global $lng, $ilTabs, $ilCtrl;

		$cmd = $ilCtrl->getCmd();

		if (in_array($cmd, array("displayMediaFullscreen", "downloadFile", "displayMedia")))
		{
			$this->checkPermission("read");
		}
		else
		{
			$this->checkPermission("write");
		}
		
		$ilTabs->clearTargets();

		if ($_GET["redirectSource"] == "ilinternallinkgui")
		{
			exit;
		}

		$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
			"xhtml_page");
		if ($xpage_id > 0)
		{
			$ilTabs->setBackTarget($lng->txt("cntr_back_to_old_editor"),
				$ilCtrl->getLinkTarget($this, "switchToOldEditor"), "_top");
		}
		else
		{
			$ilTabs->setBackTarget($lng->txt("back"), "./goto.php?target=".$this->object->getType()."_".
				$this->object->getRefId(), "_top");
		}

		// page object
		include_once("./Services/Container/classes/class.ilContainerPage.php");
		include_once("./Services/Container/classes/class.ilContainerPageGUI.php");

		$lng->loadLanguageModule("content");
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		if (!ilContainerPage::_exists("cont",
			$this->object->getId()))
		{
			// doesn't exist -> create new one
			$new_page_object = new ilContainerPage();
			$new_page_object->setParentId($this->object->getId());
			$new_page_object->setId($this->object->getId());
			$new_page_object->createFromXML();
		}
		
		// get page object
		$this->ctrl->setReturnByClass("ilcontainerpagegui", "edit");
		$page_gui = new ilContainerPageGUI($this->object->getId());
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
			$this->object->getStyleSheetId(), $this->object->getType()));

		$page_gui->setTemplateTargetVar("ADM_CONTENT");
		$page_gui->setLinkXML($link_xml);
		$page_gui->setFileDownloadLink("");
		$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "showMediaFullscreen"));
		//$page_gui->setLinkParams($this->ctrl->getUrlParameterString()); // todo
		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);

		// old editor information text
		$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
			"xhtml_page");
		if ($xpage_id > 0)
		{
			$wtpl = new ilTemplate("tpl.cntr_old_editor_message.html", true,
				true, "Services/Container");
			$wtpl->setVariable("ALT_WARNING", $lng->txt("warning"));
			$wtpl->setVariable("IMG_WARNING",
				ilUtil::getImagePath("icon_alert.svg"));
			$wtpl->setVariable("TXT_MIGRATION_INFO", $lng->txt("cntr_switch_to_new_editor_message"));
			$wtpl->setVariable("TXT_MIGRATION_INFO", $lng->txt("cntr_switch_to_new_editor_message"));
			$wtpl->setVariable("HREF_SWITCH_TO_NEW_EDITOR",
				$ilCtrl->getLinkTarget($this, "useNewEditor"));
			$wtpl->setVariable("TXT_MIGRATION_SWITCH",
				$lng->txt("cntr_switch_to_new_editor_cmd"));
			$page_gui->setPrependingHtml($wtpl->get());
		}
		
		// style tab
		$page_gui->setTabHook($this, "addPageTabs");
		
		$ret = $this->ctrl->forwardCommand($page_gui);

		//$ret =& $page_gui->executeCommand();
		return $ret;
	}
	
	/**
	* Add page tabs
	*/
	function addPageTabs()
	{
		global $ilTabs, $ilCtrl;
		
		$ilTabs->addTarget("obj_sty",
			 $ilCtrl->getLinkTarget($this, 'editStyleProperties'), "editStyleProperties");
	}

	/**
	* Get container page HTML
	*/
	function getContainerPageHTML()
	{
		global $ilSetting, $ilUser;
		
		if (!$ilSetting->get("enable_cat_page_edit"))
		{
			return;
		}
		
		// old page editor content
		$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
			"xhtml_page");
		if ($xpage_id > 0)
		{
			include_once("Services/XHTMLPage/classes/class.ilXHTMLPage.php");
			$xpage = new ilXHTMLPage($xpage_id);
			return $xpage->getContent();
		}

		
		// page object
		

		// if page does not exist, return nothing
		include_once("./Services/COPage/classes/class.ilPageUtil.php");
		if (!ilPageUtil::_existsAndNotEmpty("cont",
			$this->object->getId()))
		{
			return "";
		}
		include_once("./Services/Container/classes/class.ilContainerPage.php");
		include_once("./Services/Container/classes/class.ilContainerPageGUI.php");
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		// get page object
		include_once("./Services/Object/classes/class.ilObjectTranslation.php");
		$ot = ilObjectTranslation::getInstance($this->object->getId());
		$lang = $ot->getEffectiveContentLang($ilUser->getCurrentLanguage(), "cont");
		$page_gui = new ilContainerPageGUI($this->object->getId(), 0, $lang);
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$page_gui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(
			$this->object->getStyleSheetId(), $this->object->getType()));

		$page_gui->setPresentationTitle("");
		$page_gui->setTemplateOutput(false);
		$page_gui->setHeader("");
		$ret = $page_gui->showPage();

		//$ret = "<div style='background-color: white; padding:5px; margin-bottom: 30px;'>".$ret."</div>";

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
				
				// Member view
				include_once './Services/Container/classes/class.ilMemberViewGUI.php';
				ilMemberViewGUI::showMemberViewSwitch($this->object->getRefId());
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
		// hide for member view
		include_once './Services/Container/classes/class.ilMemberViewSettings.php';
		if(ilMemberViewSettings::getInstance()->isActive())
		{
			return;
		}
		
		$mode = ($_SESSION["il_rep_mode"] == "flat")
			? "tree"
			: "flat";
		$link = "ilias.php?baseClass=ilRepositoryGUI&amp;cmd=frameset&amp;set_mode=".$mode."&amp;ref_id=".$this->object->getRefId();
		$tpl->setTreeFlatIcon($link, $mode);
	}
	
	/**
	* called by prepare output 
	*/
	function setTitleAndDescription()
	{
		global $ilias;

		if (!ilContainer::_lookupContainerSetting($this->object->getId(), "hide_header_icon_and_title"))
		{
			$this->tpl->setTitle($this->object->getTitle());
			$this->tpl->setDescription($this->object->getLongDescription());
	
			// set tile icon
			$icon = ilObject::_getIcon($this->object->getId(), "big", $this->object->getType());
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
						
			include_once './Services/Object/classes/class.ilObjectListGUIFactory.php';
			$lgui = ilObjectListGUIFactory::_getListGUIByType($this->object->getType());
			$lgui->initItem($this->object->getRefId(), $this->object->getId());
			$this->tpl->setAlertProperties($lgui->getAlertProperties());			
		}
	}
		
	/**
	* show possible sub objects selection list
	*/
	function showPossibleSubObjects()
	{							
		include_once "Services/Object/classes/class.ilObjectAddNewItemGUI.php";
		$gui = new ilObjectAddNewItemGUI($this->object->getRefId());
		$gui->render();
	}
	
	/**
	 * Get content gui object
	 *
	 * @param
	 * @return
	 */
	function getContentGUI()
	{
		switch ($this->object->getViewMode())
		{
			// all items in one block
			case ilContainer::VIEW_SIMPLE:
				include_once("./Services/Container/classes/class.ilContainerSimpleContentGUI.php");
				$container_view = new ilContainerSimpleContentGUI($this);
				break;
				
			case ilContainer::VIEW_OBJECTIVE:
				include_once('./Services/Container/classes/class.ilContainerObjectiveGUI.php');
				$container_view = new ilContainerObjectiveGUI($this);
				break;

			// all items in one block
			case ilContainer::VIEW_SESSIONS:
			case IL_CRS_VIEW_TIMING:			// not nice this workaround
				include_once("./Services/Container/classes/class.ilContainerSessionsContentGUI.php");
				$container_view = new ilContainerSessionsContentGUI($this);
				break;
				
			// all items in one block
			case ilContainer::VIEW_BY_TYPE:
			default:
				include_once("./Services/Container/classes/class.ilContainerByTypeContentGUI.php");
				$container_view = new ilContainerByTypeContentGUI($this);
				break;
		}

		return $container_view;
	}
	
	
	
	/**
	* render the object
	*/
	function renderObject()
	{
		global $ilDB, $tpl, $ilTabs, $ilCtrl, $ilSetting;
		
		$container_view = $this->getContentGUI();
		
		$this->setContentSubTabs();
		if ($this->isActiveAdministrationPanel())
		{
			$ilTabs->activateSubTab("manage");
		}
		else
		{
			$ilTabs->activateSubTab("view_content");
		}
		
		$container_view->setOutput();

		$this->adminCommands = $container_view->adminCommands;
		
		// it is important not to show the subobjects/admin panel here, since
		// we will create nested forms in case, e.g. a news/calendar item is added
		if ($ilCtrl->getNextClass() != "ilcolumngui")
		{
			$this->showAdministrationPanel($tpl);
			$this->showPossibleSubObjects();
		}
		
		$this->showPermanentLink($tpl);

		// add tree updater javascript
		if ((int) $_GET["ref_id"] > 1 && $ilSetting->get("rep_tree_synchronize"))
		{
			$ilCtrl->setParameter($this, "active_node", (int) $_GET["ref_id"]);
			/*$tpl->addOnloadCode("
				if (parent && parent.tree && parent.tree.updater)
				{
					parent.tree.updater('tree_div', '".
					$ilCtrl->getLinkTarget($this, "showTree", "", true, false)
					."');
				}");*/
		}
	}

	/**
	* Set content sub tabs
	*/
	function setContentSubTabs()
	{
		$this->addStandardContainerSubTabs();
	}

	/**
	* show administration panel
	*/
	function showAdministrationPanel(&$tpl)
	{
		global $ilAccess, $lng;

		$lng->loadLanguageModule('cntr');

		if ($this->isActiveAdministrationPanel())
		{			
			// #11545
			$GLOBALS['tpl']->setPageFormAction($this->ctrl->getFormAction($this));
			
			include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
			$toolbar = new ilToolbarGUI();
			$this->ctrl->setParameter($this, "type", "");
			$this->ctrl->setParameter($this, "item_ref_id", "");

			if (!$_SESSION["clipboard"])
			{
				if ($this->object->gotItems())
				{
					$toolbar->setLeadingImage(
						ilUtil::getImagePath("arrow_upright.svg"),
						$lng->txt("actions")
					);
					$toolbar->addFormButton(
						$this->lng->txt('delete_selected_items'),
						'delete'
					);
					$toolbar->addFormButton(
						$this->lng->txt('move_selected_items'),
						'cut'
					);
					$toolbar->addFormButton(
						$this->lng->txt('copy_selected_items'),
						'copy'
					);
					$toolbar->addFormButton(
						$this->lng->txt('link_selected_items'),
						'link'
					);
					// add download button if multi download enabled
					$folder_set = new ilSetting("fold");
					if ($folder_set->get("enable_multi_download") == true)
					{
						$toolbar->addSeparator();
						$toolbar->addFormButton(
							$this->lng->txt('download_selected_items'), 
							'download'
						);
					}
				}
				if($this->object->getType() == 'crs' or $this->object->getType() == 'grp')
				{
					if($this->object->gotItems())
					{
						$toolbar->addSeparator();
					}
					
					$toolbar->addButton(
						$this->lng->txt('cntr_adopt_content'),
						$this->ctrl->getLinkTargetByClass(
							'ilObjectCopyGUI',
							'adoptContent')
					);
				}
			}
			else
			{				
				//$GLOBALS["tpl"]->addAdminPanelCommand("paste",
                //    $this->lng->txt("paste_clipboard_items"));

				$toolbar->addFormButton(
					$this->lng->txt('paste_clipboard_items'),
					'paste'
				);

				if($_SESSION["clipboard"]["cmd"] == "link")
				{
					//$GLOBALS["tpl"]->addAdminPanelCommand("initAndDisplayLinkIntoMultipleObjects",
					//	$this->lng->txt("paste_clipboard_items_into_multiple_objects"));
					/*$toolbar->addFormButton(
						$this->lng->txt('paste_clipboard_items_into_multiple_objects'),
						'initAndDisplayLinkIntoMultipleObjects'
					);*/
				}

				$toolbar->addFormButton(
					$this->lng->txt('clear_clipboard'),
					'clear'
				);

				if ($this->isMultiDownloadEnabled())
				{
					$toolbar->addSeparator();
					$toolbar->addFormButton(
						$this->lng->txt('download_selected_items'),
						'download'
					);
				}
			}

			$GLOBALS['tpl']->addAdminPanelToolbar(
				$toolbar,
				($this->object->gotItems() && !$_SESSION["clipboard"]) ? true : false,
				($this->object->gotItems() && !$_SESSION["clipboard"]) ? true : false
			);

			// form action needed, see http://www.ilias.de/mantis/view.php?id=9630
			if ($this->object->gotItems())
			{
				$GLOBALS['tpl']->setPageFormAction($this->ctrl->getFormAction($this));
			}
		}
		else
		{
			if ($this->edit_order)
			{			
				if($this->object->gotItems() and $ilAccess->checkAccess("write", "", $this->object->getRefId()))
				{
					include_once('./Services/Container/classes/class.ilContainer.php');

					if ($this->isActiveOrdering())
					{
						// #11843
						$GLOBALS['tpl']->setPageFormAction($this->ctrl->getFormAction($this));
						
						include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
						$toolbar = new ilToolbarGUI();
						$this->ctrl->setParameter($this, "type", "");
						$this->ctrl->setParameter($this, "item_ref_id", "");

						$toolbar->addFormButton(
							$this->lng->txt('sorting_save'),
							'saveSorting'
						);

						$GLOBALS['tpl']->addAdminPanelToolbar($toolbar, true, false);

						/*																																			
						$GLOBALS["tpl"]->addAdminPanelCommand("saveSorting",
							$this->lng->txt('sorting_save'));

						// button should appear at bottom, too
						$GLOBALS["tpl"]->admin_panel_bottom = true;					 
						*/
					}
				}
			}
			else if ($this->isMultiDownloadEnabled())
			{
				// #11843
				$GLOBALS['tpl']->setPageFormAction($this->ctrl->getFormAction($this));						

				include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
				$toolbar = new ilToolbarGUI();
				$this->ctrl->setParameter($this, "type", "");
				$this->ctrl->setParameter($this, "item_ref_id", "");

				$toolbar->addFormButton(
					$this->lng->txt('download_selected_items'),
					'download'
				);

				$GLOBALS['tpl']->addAdminPanelToolbar(
					$toolbar,
					$this->object->gotItems() ? true : false,
					$this->object->gotItems() ? true : false
				);
			}		
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
		$GLOBALS["tpl"]->setPermanentLink($this->object->getType(),
			$this->object->getRefId(), "", "_top");
	}

	/**
	* Switch to standard page editor
	*/
	function switchToStdEditorObject()
	{
		global $ilCtrl;
		
		$_SESSION["il_cntr_editor"] = "std";
		$ilCtrl->redirect($this, "editPageFrame");
	}
	
	/**
	* Switch to old page editor
	*/
	function switchToOldEditorObject()
	{
		global $ilCtrl;
		
		$_SESSION["il_cntr_editor"] = "old";
		$ilCtrl->redirect($this, "editPageFrame");
	}

	/**
	* Use new editor (-> delete xhtml content page)
	*/
	function useNewEditorObject()
	{
		global $ilCtrl, $ilAccess, $lng, $ilCtrl;
		
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			include_once("./Services/XHTMLPage/classes/class.ilXHTMLPage.php");

			/* keep old page content for now...
			$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
				"xhtml_page");
			if ($xpage_id)
			{
				$xpage = new ilXHTMLPage($xpage_id);
			}
			*/

			ilContainer::_writeContainerSetting($this->object->getId(),
				"xhtml_page", 0);

			ilUtil::sendSuccess($lng->txt("cntr_switched_editor"), true);
		}
		
		$ilCtrl->redirect($this, "editPageFrame");
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
		$fs_gui->setSideFrameName("tree");

		// old tiny stuff
		$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
			"xhtml_page");
		if ($xpage_id > 0 && $_SESSION["il_cntr_editor"] != "std")
		{
			$fs_gui->setMainFrameSource(
				$this->ctrl->getLinkTarget(
					$this, "editPageContent"));
			$fs_gui->setSideFrameSource(
				$this->ctrl->getLinkTarget($this, "showLinkList"));
		}
		else
		{
			$this->ctrl->redirectByClass(array("ilcontainerpagegui"), "edit");
			exit;
		}
				
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
		global $rbacsystem, $tpl, $lng, $ilCtrl;

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
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.container_edit_page_content.html",
			"Services/Container");
		$tpl->setVariable("VAL_CONTENT", ilUtil::prepareFormOutput($content));
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_EDIT_PAGE_CONTENT",
			$this->lng->txt("edit_page_content"));
		$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$tpl->setVariable("TXT_MIGRATION_INFO", $lng->txt("cntr_old_editor_warning"));
		$tpl->setVariable("TXT_MIGRATION_OPEN_STD_EDITOR", $lng->txt("cntr_old_editor_open_standard_editor"));
		$tpl->setVariable("IMG_WARNING", ilUtil::getImagePath("icon_alert.svg"));
		$tpl->setVariable("HREF_OPEN_STD_EDITOR", $ilCtrl->getLinkTarget($this, "switchToStdEditor"));
		$tpl->setVariable("ALT_WARNING", $lng->txt("warning"));
		
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
		include_once "./Services/Object/classes/class.ilObject.php";
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

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "");
	}
	
	function cancelPageContentObject()
	{
		$this->ctrl->redirect($this, "");
	}

	function showLinkListObject()
	{
		global $lng, $tree;
		
		$tpl = new ilTemplate("tpl.container_link_help.html", true, true,
			"Services/Container");
		
		$type_ordering = array(
			"cat", "fold", "crs", "grp", "chat", "frm", "lres",
			"glo", "webr", "file", "exc",
			"tst", "svy", "mep", "qpl", "spl");
			
		$childs = $tree->getChilds($_GET["ref_id"]);
		foreach($childs as $child)
		{
			if (in_array($child["type"], array("lm", "dbk", "sahs", "htlm")))
			{
				$cnt["lres"]++;
			}
			else
			{
				$cnt[$child["type"]]++;
			}
		}
			
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$tpl->setVariable("TXT_HELP_HEADER", $lng->txt("help"));
		foreach($type_ordering as $type)
		{
			$tpl->setCurrentBlock("row");
			$tpl->setVariable("ROWCOL", "tblrow".((($i++)%2)+1));
			if ($type != "lres")
			{
				$tpl->setVariable("TYPE", $lng->txt("objs_".$type).
					" (".((int)$cnt[$type]).")");
			}
			else
			{
				$tpl->setVariable("TYPE", $lng->txt("learning_resources").
					" (".((int)$cnt["lres"]).")");
			}
			$tpl->setVariable("TXT_LINK", "[list-".$type."]");
			$tpl->parseCurrentBlock();
		}
		$tpl->show();
		exit;

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
/*	function determineAdminCommands($a_ref_id, $a_admin_com_included_in_list = false)
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
	}*/

	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	function &newBlockTemplate()
	{
		$tpl = new ilTemplate("tpl.container_list_block.html", true, true,
			"Services/Container");
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
		$icon = ilUtil::getImagePath("icon_".$a_type.".svg");
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
			$icon = ilUtil::getImagePath("icon_".$a_image_type.".svg");
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
			ilContainerSortingSettings::_lookupSortMode($this->object->getId()) == ilContainer::SORT_MANUAL)
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

	
	/**
	* Add page editor tabs
	*/
	function setPageEditorTabs()
	{
		global $lng;
		
		if (!$this->isActiveAdministrationPanel()
			|| strtolower($this->ctrl->getCmdClass()) != "ilcontainerpagegui")
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

		$this->tabs_gui->addTarget("edit", $this->ctrl->getLinkTargetByClass("ilcontainerpagegui", "view")
			, array("", "view"), "ilcontainerpagegui");

		//$this->tpl->setTabs($tabs_gui->getHTML());
	}

	/**
	* Add standar container subtabs for view, manage, oderdering and text/media editor link
	*/
	function addStandardContainerSubTabs($a_include_view = true)
	{
		global $ilTabs, $ilAccess, $lng, $ilCtrl, $ilUser, $ilSetting;

		if (!is_object($this->object))
		{
			return;
		}
		
		if ($a_include_view && $ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			if (!$this->isActiveAdministrationPanel())
			{
				$ilTabs->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTarget($this, ""));
			}
			else
			{
				$ilTabs->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTarget($this, "disableAdministrationPanel"));
			}
		}
		
		if ( $ilUser->getId() != ANONYMOUS_USER_ID &&
				($this->adminCommands ||
				(is_object($this->object) && 
				($ilAccess->checkAccess("write", "", $this->object->getRefId())))
										||
				(is_object($this->object) && 
				($this->object->getHiddenFilesFound())) ||
				$_SESSION["clipboard"]
				)
			)
		{
			if ($this->isActiveAdministrationPanel())
			{
				$ilTabs->addSubTab("manage", $lng->txt("cntr_manage"), $ilCtrl->getLinkTarget($this, ""));
			}
			else
			{
				$ilTabs->addSubTab("manage", $lng->txt("cntr_manage"), $ilCtrl->getLinkTarget($this, "enableAdministrationPanel"));
			}
		}
		if ($ilUser->getId() != ANONYMOUS_USER_ID &&
			is_object($this->object) && 
			$ilAccess->checkAccess("write", "", $this->object->getRefId()) /* &&
			$this->object->getOrderType() == ilContainer::SORT_MANUAL */ // always on because of custom block order 
			)
		{
			$ilTabs->addSubTab("ordering", $lng->txt("cntr_ordering"), $ilCtrl->getLinkTarget($this, "editOrder"));
		}
		if ($ilUser->getId() != ANONYMOUS_USER_ID &&
			is_object($this->object) && 
			$ilAccess->checkAccess("write", "", $this->object->getRefId())
			)
		{
			if ($ilSetting->get("enable_cat_page_edit"))
			{
				$ilTabs->addSubTab("page_editor", $lng->txt("cntr_text_media_editor"), $ilCtrl->getLinkTarget($this, "editPageFrame"),
					ilFrameTargetInfo::_getFrame("MainContent"));
			}
		}
	}
	

	/**
	* common tabs for all container objects (should be called
	* at the end of child getTabs() method
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $ilCtrl;

		// edit permissions
		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
				array("perm","info","owner"), 'ilpermissiongui');
			if ($ilCtrl->getNextClass() == "ilpermissiongui")
			{
				$tabs_gui->activateTab("perm_settings");
			}
		}

		// show clipboard
		if (strtolower($_GET["baseClass"]) == "ilrepositorygui" && !empty($_SESSION["clipboard"]))
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
	* Edit order 
	*/
	function editOrderObject()
	{
		global $ilTabs;
		
		$this->edit_order = true;
		$_SESSION["il_cont_admin_panel"] = false;
		$this->renderObject();
		
		$ilTabs->activateSubTab("ordering");	
	}
	
	/**
	 * Check if ordering is enabled 
	 * @return  bool
	 */
	public function isActiveOrdering()
	{
		return $this->edit_order ? true : false;
	}
	
	/**
	 * Check if item ordering is enabled
	 * @return bool
	 */
	public function isActiveItemOrdering()
	{
		if($this->isActiveOrdering())
		{
			return (ilContainerSortingSettings::_lookupSortMode($this->object->getId()) == ilContainer::SORT_MANUAL);			
		}
		return false;
	}
			
    /**
     * @see ilDesktopItemHandling::addToDesk()
     */
    public function addToDeskObject()
    {
    	global $ilSetting, $lng;
		
    	if((int)$ilSetting->get('disable_my_offers'))
		{
			return $this->renderObject();
		}
		
	 	include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::addToDesktop();
	 	ilUtil::sendSuccess($lng->txt("added_to_desktop"));
		$this->renderObject();
    }
    
    /**
     * @see ilDesktopItemHandling::removeFromDesk()
     */
    public function removeFromDeskObject()
    {
    	global $ilSetting, $lng;
		
    	if((int)$ilSetting->get('disable_my_offers'))
		{
			return $this->renderObject();
		}
		
	 	include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::removeFromDesktop();
	 	ilUtil::sendSuccess($lng->txt("removed_from_desktop"));
		$this->renderObject();
    }
	
	function enableMultiDownloadObject()
	{
		$this->multi_download_enabled = true;
		$this->renderObject();
	}
	
	function isMultiDownloadEnabled()
	{
		return $this->multi_download_enabled;
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
	* cut object(s) out from a container and write the information to clipboard
	*
	*
	* @access	public
	*/
	function cutObject()
	{
		global $rbacsystem, $ilCtrl;

		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		//$this->ilias->raiseError("move operation does not work at the moment and is disabled",$this->ilias->error_obj->MESSAGE);

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
		$_SESSION["clipboard"]["parent"] = $_GET["ref_id"];
		$_SESSION["clipboard"]["cmd"] = $ilCtrl->getCmd();
		$_SESSION["clipboard"]["ref_ids"] = $_POST["id"];

		ilUtil::sendInfo($this->lng->txt("msg_cut_clipboard"),true);

		return $this->initAndDisplayMoveIntoObjectObject();
	} // END CUT

	/**
	 * Copy object(s) out from a container and write the information to clipboard
	 * It is not possible to copy multiple objects at once.
	 *
	 *
	 * @access	public
	 */
	function copyObject()
	{
		global $rbacsystem, $ilCtrl, $objDefinition;

		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// FOR ALL OBJECTS THAT SHOULD BE COPIED
		$containers = 0;
		foreach ($_POST["id"] as $ref_id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $this->tree->getNodeData($ref_id);

			// count containers
			if ($objDefinition->isContainer($node_data["type"]))
			{
				$containers++;
			}

			$subtree_nodes = $this->tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK COPY PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
			foreach ($subtree_nodes as $node)
			{
				if($node['type'] == 'rolf')
				{
					continue;
				}
				
				if (!$rbacsystem->checkAccess('visible,read,copy',$node["ref_id"]))
				{
					$no_copy[] = $node["ref_id"];
				}
			}
		}

		if ($containers > 0 && count($_POST["id"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cntr_container_only_on_their_own"), $this->ilias->error_obj->MESSAGE);
		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'delete'
		if (count($no_copy))
		{
			$this->ilias->raiseError(
				$this->lng->txt("msg_no_perm_copy") . " " . implode(',',$this->getTitlesByRefId($no_copy)),
				$this->ilias->error_obj->MESSAGE);
		}

		// if we have a single container, set it as source id and redirect to ilObjectCopyGUI
		if (count($_POST["id"]) == 1)
		{
			$ilCtrl->setParameterByClass("ilobjectcopygui", "source_id", $_POST["id"][0]);
			$ilCtrl->redirectByClass("ilobjectcopygui", "initTargetSelection");
		}
		else
		{
			$ilCtrl->setParameterByClass("ilobjectcopygui", "source_ids", implode($_POST["id"],"_"));
			$ilCtrl->redirectByClass("ilobjectcopygui", "initTargetSelection");
		}

		$_SESSION["clipboard"]["parent"] = $_GET["ref_id"];
		$_SESSION["clipboard"]["cmd"] = $ilCtrl->getCmd();
		$_SESSION["clipboard"]["ref_ids"] = $_POST["id"];

		ilUtil::sendInfo($this->lng->txt("msg_copy_clipboard"), true);

		return $this->initAndDisplayCopyIntoMultipleObjectsObject();
	} // END COPY
	
	function downloadObject()
	{
		global $rbacsystem, $ilCtrl;
		
		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// FOR ALL OBJECTS THAT SHOULD BE DOWNLOADED
		foreach ($_POST["id"] as $ref_id)
		{
			$object =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);
			$obj_type = $object->getType();
			if (!in_array($obj_type, array("fold", "file")))
			{
				$no_download[] = $object->getType();
			}
			else if (!$rbacsystem->checkAccess('read', $ref_id))
			{
				$no_perm[] = $ref_id;
			}
		}
		
		// IF THERE IS ANY OBJECT THAT CANNOT BE DOWNLOADED
		if (count($no_download))
		{
			$no_download = array_unique($no_download);
			foreach ($no_download as $type)
			{
				$txt_objs[] = $this->lng->txt("objs_".$type);
			}
			$this->ilias->raiseError(implode(', ',$txt_objs)." ".$this->lng->txt("msg_obj_no_download"),$this->ilias->error_obj->MESSAGE);
		}
		
		// NO ACCESS
		if (count($no_perm))
		{
			$this->ilias->raiseError(
				$this->lng->txt("msg_obj_perm_download")." ".implode(',',$no_perm),
				$this->ilias->error_obj->MESSAGE);
		}
		
		// download the objects
		$this->downloadMultipleObjects($_POST["id"]);
	}	
	
	private function downloadMultipleObjects($a_ref_ids)
	{
		global $lng, $rbacsystem, $ilAccess;
		
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		include_once 'Modules/Folder/classes/class.ilObjFolder.php';
		include_once 'Modules/File/classes/class.ilObjFile.php';
		include_once 'Modules/File/classes/class.ilFileException.php';
		
		// create temporary file to download
		$zip = PATH_TO_ZIP;
		$tmpdir = ilUtil::ilTempnam();		
		ilUtil::makeDir($tmpdir);
		
		try 
		{
			// copy each selected object
			foreach ($a_ref_ids as $ref_id)
			{
				if (!$ilAccess->checkAccess("read", "", $ref_id))
					continue;
				
				if (ilObject::_isInTrash($ref_id))
					continue;
				
				// get object
				$object =& $this->ilias->obj_factory->getInstanceByRefId($ref_id);
				$obj_type = $object->getType();
				if ($obj_type == "fold")
				{
					// copy folder to temp directory
					self::recurseFolder($ref_id, $object->getTitle(), $tmpdir);
				}
				else if ($obj_type == "file")
				{
					// copy file to temp directory
					self::copyFile($object->getId(), $object->getTitle(), $tmpdir);
				}
			}

			// compress the folder
			$deliverFilename = ilUtil::getAsciiFilename($this->object->getTitle()) . ".zip";
			$tmpzipfile = ilUtil::ilTempnam() . ".zip";
			ilUtil::zip($tmpdir, $tmpzipfile, true);
			ilUtil::delDir($tmpdir);
			ilUtil::deliverFile($tmpzipfile, $deliverFilename, '', false, true, true);
		}
		catch (ilFileException $e) 
		{
			ilUtil::sendInfo($e->getMessage(), true);
		}
	}
	
	/**
	 * private functions which iterates through all folders and files 
	 * and create an according file structure in a temporary directory. This function works recursive. 
	 *
	 * @param integer $refid reference it
	 * @param tmpdictory $tmpdir
	 * @return returns first created directory
	 */
	private static function recurseFolder($refid, $title, $tmpdir) 
	{
		global $rbacsystem, $tree, $ilAccess;
		
		$tmpdir = $tmpdir . DIRECTORY_SEPARATOR . ilUtil::getASCIIFilename($title);
		ilUtil::makeDir($tmpdir);
		
		$subtree = $tree->getChildsByTypeFilter($refid, array("fold","file"));
		
		foreach ($subtree as $child) 
		{
			if (!$ilAccess->checkAccess("read", "", $child["ref_id"]))
				continue;			

			if (ilObject::_isInTrash($child["ref_id"]))
				continue;

			if ($child["type"] == "fold")
				self::recurseFolder($child["ref_id"], $child["title"], $tmpdir);
			else 
				self::copyFile($child["obj_id"], $child["title"], $tmpdir);
		}
	}
	
	private static function copyFile($obj_id, $title, $tmpdir)
	{
		$newFilename = $tmpdir . DIRECTORY_SEPARATOR . ilUtil::getASCIIFilename($title);
		
		// copy to temporary directory
		$oldFilename = ilObjFile::_lookupAbsolutePath($obj_id);
		if (!copy($oldFilename, $newFilename))
			throw new ilFileException("Could not copy ".$oldFilename." to ".$newFilename);
		
		touch($newFilename, filectime($oldFilename));								
	}

	/**
	* create an new reference of an object in tree
	* it's like a hard link of unix
	*
	* @access	public
	*/
	function linkObject()
	{
		global $clipboard, $rbacsystem, $rbacadmin, $ilCtrl;

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
			//#12203
			$this->ilias->raiseError($this->lng->txt("msg_obj_no_link"),$this->ilias->error_obj->MESSAGE);

			//$this->ilias->raiseError($this->lng->txt("msg_not_possible_link")." ".
			//						 implode(',',$no_link),$this->ilias->error_obj->MESSAGE);
		}

		// WRITE TO CLIPBOARD
		$clipboard["parent"] = $_GET["ref_id"];
		$clipboard["cmd"] = $ilCtrl->getCmd();

		foreach ($_POST["id"] as $ref_id)
		{
			$clipboard["ref_ids"][] = $ref_id;
		}

		$_SESSION["clipboard"] = $clipboard;

		$suffix = 'p';
		if(count($clipboard["ref_ids"]) == 1)
		{
			$suffix = 's';
		}
		ilUtil::sendInfo($this->lng->txt("msg_link_clipboard_" . $suffix),true);

		return $this->initAndDisplayLinkIntoMultipleObjectsObject();

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
			ilUtil::sendSuccess($this->lng->txt("msg_clear_clipboard"),true);

			//$this->ctrl->returnToParent($this);
			//ilUtil::redirect($this->getReturnLocation("clear",$this->ctrl->getLinkTarget($this)),get_class($this));
			$this->disableAdministrationPanelObject();
		}
	}
	
	public function performPasteIntoMultipleObjectsObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview, $log, $tree, $ilObjDataCache, $ilUser;

		$command = $_SESSION['clipboard']['cmd'];
		if(!in_array($command, array('cut', 'link', 'copy')))
		{
			$message = __METHOD__.": cmd was neither 'cut', 'link' nor 'copy'; may be a hack attempt!";
			$this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
		}
		
		if($command == 'cut')
		{
			if(isset($_POST['node']) && (int)$_POST['node'])
				$_POST['nodes'] = array($_POST['node']);
		}

		if(!is_array($_POST['nodes']) || !count($_POST['nodes']))
		{
			ilUtil::sendFailure($this->lng->txt('select_at_least_one_object'));
			switch ($command)
			{
				case 'cut':
					$this->showPasteTreeObject();
					break;
				case 'copy':
					$this->showPasteTreeObject();
					break;
				case 'link':
					$this->showPasteTreeObject();
					break;
			}
			return;
		}	

		// this loop does all checks
		$folder_objects_cache = array();
		foreach($_SESSION['clipboard']['ref_ids'] as $ref_id)
		{
			$obj_data = ilObjectFactory::getInstanceByRefId($ref_id);
			$current_parent_id = $tree->getParentId($obj_data->getRefId());
			
			foreach($_POST['nodes'] as $folder_ref_id)
			{
				if(!array_key_exists($folder_ref_id, $folder_objects_cache))
				{
					$folder_objects_cache[$folder_ref_id] = ilObjectFactory::getInstanceByRefId($folder_ref_id);
				}

				// CHECK ACCESS
				if(!$rbacsystem->checkAccess('create', $folder_ref_id, $obj_data->getType()))
				{
					$no_paste[] = sprintf($this->lng->txt('msg_no_perm_paste_object_in_folder'), $obj_data->getTitle().' ['.$obj_data->getRefId().']', $folder_objects_cache[$folder_ref_id]->getTitle().' ['.$folder_objects_cache[$folder_ref_id]->getRefId().']');
				}
				
				// CHECK IF REFERENCE ALREADY EXISTS
				if($folder_ref_id == $current_parent_id)
				{
					$exists[] = sprintf($this->lng->txt('msg_obj_exists_in_folder'), $obj_data->getTitle().' ['.$obj_data->getRefId().']', $folder_objects_cache[$folder_ref_id]->getTitle().' ['.$folder_objects_cache[$folder_ref_id]->getRefId().']');
				}
	
				// CHECK IF PASTE OBJECT SHALL BE CHILD OF ITSELF
				if ($tree->isGrandChild($ref_id, $folder_ref_id) ||
					$ref_id == $folder_ref_id)
				{
					$is_child[] = sprintf($this->lng->txt('msg_paste_object_not_in_itself'), $obj_data->getTitle().' ['.$obj_data->getRefId().']');
				}
	
				// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT	
				if(!in_array($obj_data->getType(), array_keys($folder_objects_cache[$folder_ref_id]->getPossibleSubObjects())))
				{
					$not_allowed_subobject[] = sprintf($this->lng->txt('msg_obj_may_not_contain_objects_of_type'), $folder_objects_cache[$folder_ref_id]->getTitle().' ['.$folder_objects_cache[$folder_ref_id]->getRefId().']', 
							$GLOBALS['lng']->txt('obj_'.$obj_data->getType()));
				}				
			}		
		}		
		
		////////////////////////////
		// process checking results
		if(count($exists) && $command != "copy")
		{
			$error .= implode('<br />', $exists);
		}

		if(count($is_child))
		{
			$error .= $error != '' ? '<br />' : '';
			$error .= implode('<br />', $is_child);
		}

		if(count($not_allowed_subobject))
		{
			$error .= $error != '' ? '<br />' : '';
			$error .= implode('<br />', $not_allowed_subobject);
		}

		if(count($no_paste))
		{
			$error .= $error != '' ? '<br />' : '';
			$error .= implode('<br />', $no_paste);
		}
		
		if($error != '')
		{
			ilUtil::sendFailure($error);
			switch ($command)
			{
				case 'cut':
					$this->showPasteTreeObject();
					break;
				case 'copy':
					$this->showPasteTreeObject();
					break;
				case 'link':
					$this->showPasteTreeObject();
					break;
			}
			return;
		}

		// log pasteObject call
		$log->write(__METHOD__.", cmd: ".$command);

		////////////////////////////////////////////////////////
		// everything ok: now paste the objects to new location

		// to prevent multiple actions via back/reload button
		$ref_ids = $_SESSION['clipboard']['ref_ids'];
		unset($_SESSION['clipboard']['ref_ids']);
		
		// BEGIN ChangeEvent: Record paste event.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		// END ChangeEvent: Record paste event.
		
		// process COPY command
		if($command == 'copy')
		{			
			foreach($_POST['nodes'] as $folder_ref_id)
			{
				foreach($ref_ids as $ref_id)
				{
					$revIdMapping = array(); 
					
					$oldNode_data = $tree->getNodeData($ref_id);
					if ($oldNode_data['parent'] == $folder_ref_id)
					{
						require_once 'Modules/File/classes/class.ilObjFileAccess.php';
						$newTitle = ilObjFileAccess::_appendNumberOfCopyToFilename($oldNode_data['title'],null);
						$newRef = $this->cloneNodes($ref_id, $folder_ref_id, $refIdMapping, $newTitle);
					}
					else
					{
						$newRef = $this->cloneNodes($ref_id, $folder_ref_id, $refIdMapping, null);
					}
					
					// BEGIN ChangeEvent: Record copy event.
					$old_parent_data = $tree->getParentNodeData($ref_id);
					$newNode_data = $tree->getNodeData($newRef);
					ilChangeEvent::_recordReadEvent($oldNode_data['type'], $ref_id,
						$oldNode_data['obj_id'], $ilUser->getId());
					ilChangeEvent::_recordWriteEvent($newNode_data['obj_id'], $ilUser->getId(), 'add', 
						$ilObjDataCache->lookupObjId($folder_ref_id));
					ilChangeEvent::_catchupWriteEvents($newNode_data['obj_id'], $ilUser->getId());				
					// END PATCH ChangeEvent: Record cut event.
				}
			}
			
			ilUtil::sendSuccess($this->lng->txt('msg_cloned'), true);
		} // END COPY	
		
		// process CUT command
		if($command == 'cut')
		{			
			foreach($_POST['nodes'] as $folder_ref_id)
			{
				foreach($ref_ids as $ref_id)
				{
					// Store old parent
					$old_parent = $tree->getParentId($ref_id);
					$tree->moveTree($ref_id, $folder_ref_id);
					$rbacadmin->adjustMovedObjectPermissions($ref_id, $old_parent);
					
					include_once('./Services/AccessControl/classes/class.ilConditionHandler.php');
					ilConditionHandler::_adjustMovedObjectConditions($ref_id);
	
					// BEGIN ChangeEvent: Record cut event.
					$node_data = $tree->getNodeData($ref_id);
					$old_parent_data = $tree->getNodeData($old_parent);
					ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'remove', 
						$old_parent_data['obj_id']);
					ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'add', 
						$ilObjDataCache->lookupObjId($folder_ref_id));
					ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());					
					// END PATCH ChangeEvent: Record cut event.
				}
				
				// prevent multiple iterations for cut cmommand
				break;
			}
			
			ilUtil::sendSuccess($this->lng->txt('msg_cut_copied'), true);
		} // END CUT	
		
		// process LINK command
		if($command == 'link')
		{
			$linked_to_folders = array();

			include_once "Services/AccessControl/classes/class.ilRbacLog.php";
			$rbac_log_active = ilRbacLog::isActive();
			
			foreach($_POST['nodes'] as $folder_ref_id)
			{		
				$linked_to_folders[$folder_ref_id] = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($folder_ref_id));
						
				foreach($ref_ids as $ref_id)
				{
					// get node data
					$top_node = $tree->getNodeData($ref_id);
	
					// get subnodes of top nodes
					$subnodes[$ref_id] = $tree->getSubtree($top_node);
				}
	
				// now move all subtrees to new location
				foreach($subnodes as $key => $subnode)
				{
					// first paste top_node....
					$obj_data = ilObjectFactory::getInstanceByRefId($key);
					$new_ref_id = $obj_data->createReference();
					$obj_data->putInTree($folder_ref_id);
					$obj_data->setPermissions($folder_ref_id);
					
					// rbac log
					if($rbac_log_active)
					{
						$rbac_log_roles = $rbacreview->getParentRoleIds($new_ref_id, false);
						$rbac_log = ilRbacLog::gatherFaPa($new_ref_id, array_keys($rbac_log_roles), true);
						ilRbacLog::add(ilRbacLog::LINK_OBJECT, $new_ref_id, $rbac_log, $key);
					}
	
					// BEGIN ChangeEvent: Record link event.
					$node_data = $tree->getNodeData($new_ref_id);
					ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'add', 
						$ilObjDataCache->lookupObjId($folder_ref_id));
					ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());					
					// END PATCH ChangeEvent: Record link event.
				}
	
				$log->write(__METHOD__.', link finished');
			}

			$linked_targets = array();
			if(count($linked_to_folders))
			{
				require_once 'Services/Link/classes/class.ilLink.php';
				foreach($linked_to_folders as $ref_id => $title)
				{
					$linked_targets[] = '<a href="' . ilLink::_getLink($ref_id) . '">' . $title . '</a>';
				}
			}

			$suffix = 'p';
			if(count($ref_ids) == 1)
			{
				$suffix = 's';
			}
			ilUtil::sendSuccess(sprintf($this->lng->txt('mgs_objects_linked_to_the_following_folders_' . $suffix), implode(', ', $linked_targets)), true);
		} // END LINK

		// clear clipboard
		$this->clearObject();	

		$this->ctrl->returnToParent($this);
	}
	
	public function initAndDisplayLinkIntoMultipleObjectsObject()
	{
		global $tree;
		
		// empty session on init
		$_SESSION['paste_linked_repexpand'] = array();
		
		// copy opend nodes from repository explorer		
		$_SESSION['paste_linked_repexpand'] = is_array($_SESSION['repexpand']) ? $_SESSION['repexpand'] : array();
		
		// open current position
		$path = $tree->getPathId((int)$_GET['ref_id']);
		foreach((array)$path as $node_id)
		{
			if(!in_array($node_id, $_SESSION['paste_linked_repexpand']))
				$_SESSION['paste_linked_repexpand'][] = $node_id;
		}
		
		return $this->showPasteTreeObject();
	}

	/**
	 * Show paste tree
	 */
	public function showPasteTreeObject()
	{
		global $ilTabs, $ilToolbar;

		$ilTabs->setTabActive('view_content');

		if(!in_array($_SESSION['clipboard']['cmd'], array('link', 'copy', 'cut')))
		{
			$message = __METHOD__.": Unknown action.";
			$this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
		}
		$cmd = $_SESSION['clipboard']['cmd'];

		//
		$exp = $this->getTreeSelectorGUI($cmd);
		if ($exp->handleCommand())
		{
			return;
		}
		$output = $exp->getHTML();

		$txt_var = ($cmd == "copy")
			? "copy"
			: "paste";

		// toolbars
		$t = new ilToolbarGUI();
		$t->setFormAction($this->ctrl->getFormAction($this, "performPasteIntoMultipleObjects"));

		include_once("./Services/UIComponent/Button/classes/class.ilSubmitButton.php");
		$b = ilSubmitButton::getInstance();
		$b->setCaption($txt_var);
		$b->setCommand("performPasteIntoMultipleObjects");

		//$t->addFormButton($this->lng->txt($txt_var), "performPasteIntoMultipleObjects");
		$t->addStickyItem($b);

			$t->addSeparator();
		$t->addFormButton($this->lng->txt("obj_insert_into_clipboard"), "keepObjectsInClipboard");
		$t->addFormButton($this->lng->txt("cancel"), "cancelMoveLink");
		$t->setCloseFormTag(false);
		$t->setLeadingImage(ilUtil::getImagePath("arrow_upright.svg"), " ");
		$output = $t->getHTML().$output;
		$t->setLeadingImage(ilUtil::getImagePath("arrow_downright.svg"), " ");
		$t->setCloseFormTag(true);
		$t->setOpenFormTag(false);
		$output.= "<br />".$t->getHTML();

		$this->tpl->setContent($output);
	}



/*	public function showLinkIntoMultipleObjectsTreeObject()
	{
		global $ilTabs, $ilToolbar;
	
		$ilTabs->setTabActive('view_content');
		
		if(!in_array($_SESSION['clipboard']['cmd'], array('link')))
		{
			$message = __METHOD__.": cmd was not 'link'; may be a hack attempt!";
			$this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
		}

		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.paste_into_multiple_objects.html',
			"Services/Object");	
		
		require_once './Services/Object/classes/class.ilPasteIntoMultipleItemsExplorer.php';
		$exp = new ilPasteIntoMultipleItemsExplorer(ilPasteIntoMultipleItemsExplorer::SEL_TYPE_CHECK,
			'ilias.php?baseClass=ilRepositoryGUI&cmd=goto', 'paste_linked_repexpand');	
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showLinkIntoMultipleObjectsTree'));
		$exp->setTargetGet('ref_id');				
		$exp->setPostVar('nodes[]');
		$exp->highlightNode($_GET['ref_id']);
		is_array($_POST['nodes']) ? $exp->setCheckedItems((array)$_POST['nodes']) : $exp->setCheckedItems(array());

		if($_GET['paste_linked_repexpand'] == '')
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET['paste_linked_repexpand'];
		}
		
		$this->tpl->setVariable('FORM_TARGET', '_top');
		$this->tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performPasteIntoMultipleObjects'));

		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable('OBJECT_TREE', $output);
		
		$this->tpl->setVariable('CMD_SUBMIT', 'performPasteIntoMultipleObjects');
		$this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('paste'));
		
		$ilToolbar->addButton($this->lng->txt('cancel'), $this->ctrl->getLinkTarget($this,'cancelMoveLink'));
	}*/

	/**
	 * Cancel move|link
	 * empty clipboard and return to parent
	 */
	public function cancelMoveLinkObject()
	{
		unset($_SESSION['clipboard']);
		$GLOBALS['ilCtrl']->returnToParent($this);
	}

	/**
	 * Keep objects in the clipboard
	 */
	function keepObjectsInClipboardObject()
	{
		ilUtil::sendSuccess($this->lng->txt("obj_inserted_clipboard"), true);
		$GLOBALS['ilCtrl']->returnToParent($this);
	}

	
	public function initAndDisplayCopyIntoMultipleObjectsObject()
	{
		global $tree;

		// empty session on init
		$_SESSION['paste_copy_repexpand'] = array();
		
		// copy opend nodes from repository explorer		
		$_SESSION['paste_copy_repexpand'] = is_array($_SESSION['repexpand']) ? $_SESSION['repexpand'] : array();
		
		// open current position
		$path = $tree->getPathId((int)$_GET['ref_id']);
		foreach((array)$path as $node_id)
		{
			if(!in_array($node_id, $_SESSION['paste_copy_repexpand']))
				$_SESSION['paste_copy_repexpand'][] = $node_id;
		}
		
		return $this->showPasteTreeObject();
	}
	
	/*public function showCopyIntoMultipleObjectsTreeObject()
	{
		global $ilTabs, $ilToolbar;
		
		$ilTabs->setTabActive('view_content');
		
		if(!in_array($_SESSION['clipboard']['cmd'], array('copy')))
		{
			$message = __METHOD__.": cmd was not 'copy'; may be a hack attempt!";
			$this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
		}

		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.paste_into_multiple_objects.html', "Services/Object");	
		
		require_once './Services/Object/classes/class.ilPasteIntoMultipleItemsExplorer.php';
		$exp = new ilPasteIntoMultipleItemsExplorer(ilPasteIntoMultipleItemsExplorer::SEL_TYPE_CHECK, 
			'ilias.php?baseClass=ilRepositoryGUI&cmd=goto', 'paste_copy_repexpand');	
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showCopyIntoMultipleObjectsTree'));
		$exp->setTargetGet('ref_id');				
		$exp->setPostVar('nodes[]');
		$exp->highlightNode($_GET['ref_id']);
		is_array($_POST['nodes']) ? $exp->setCheckedItems((array)$_POST['nodes']) : $exp->setCheckedItems(array());

		if($_GET['paste_copy_repexpand'] == '')
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET['paste_copy_repexpand'];
		}
		
		$this->tpl->setVariable('FORM_TARGET', '_top');
		$this->tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performPasteIntoMultipleObjects'));

		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable('OBJECT_TREE', $output);
		
		$this->tpl->setVariable('CMD_SUBMIT', 'performPasteIntoMultipleObjects');
		$this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('copy'));
		
		$ilToolbar->addButton($this->lng->txt('cancel'), $this->ctrl->getLinkTarget($this,'cancelMoveLink'));
	}*/
	
	public function initAndDisplayMoveIntoObjectObject()
	{
		global $tree;
		
		// empty session on init
		$_SESSION['paste_cut_repexpand'] = array();
		
		// copy opend nodes from repository explorer		
		$_SESSION['paste_cut_repexpand'] = is_array($_SESSION['repexpand']) ? $_SESSION['repexpand'] : array();
		
		// open current position
		$path = $tree->getPathId((int)$_GET['ref_id']);
		foreach((array)$path as $node_id)
		{
			if(!in_array($node_id, $_SESSION['paste_cut_repexpand']))
				$_SESSION['paste_cut_repexpand'][] = $node_id;
		}
		
		return $this->showPasteTreeObject();
	}
	
	/*public function showMoveIntoObjectTreeObject()
	{
		global $ilTabs, $ilToolbar;
	
		$ilTabs->setTabActive('view_content');
		
		if(!in_array($_SESSION['clipboard']['cmd'], array('cut')))
		{
			$message = __METHOD__.": cmd was not 'cut'; may be a hack attempt!";
			$this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
		}

		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.paste_into_multiple_objects.html',
			"Services/Object");	
		
		require_once './Services/Object/classes/class.ilPasteIntoMultipleItemsExplorer.php';
		$exp = new ilPasteIntoMultipleItemsExplorer(ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO,
			'ilias.php?baseClass=ilRepositoryGUI&cmd=goto', 'paste_cut_repexpand');	
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showMoveIntoObjectTree'));
		$exp->setTargetGet('ref_id');				
		$exp->setPostVar('node');
		$exp->setCheckedItems(array((int)$_POST['node']));
		$exp->highlightNode($_GET['ref_id']);
		
		if($_GET['paste_cut_repexpand'] == '')
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET['paste_cut_repexpand'];
		}
		
		$this->tpl->setVariable('FORM_TARGET', '_top');
		$this->tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this, 'performPasteIntoMultipleObjects'));

		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable('OBJECT_TREE', $output);
		
		$this->tpl->setVariable('CMD_SUBMIT', 'performPasteIntoMultipleObjects');
		$this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('paste'));
		
		$ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this,'cancelMoveLink'));
	}*/

	/**
	* paste object from clipboard to current place
	* Depending on the chosen command the object(s) are linked, copied or moved
	*
	* @access	public
 	*/
	function pasteObject()
	{
		global $rbacsystem, $rbacadmin, $rbacreview, $log,$tree;
		global $ilUser, $lng, $ilCtrl;

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
				$no_paste_titles[] = $obj_data->getTitle();
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
				$is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
			}

			if ($ref_id == $this->object->getRefId())
			{
				$is_child[] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
			}

			// CHECK IF OBJECT IS ALLOWED TO CONTAIN PASTED OBJECT AS SUBOBJECT
			$obj_type = $obj_data->getType();

			if (!in_array($obj_type, array_keys($this->object->getPossibleSubObjects())))
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
			unset($_SESSION["clipboard"]["cmd"]);

			// new implementation, redirects to ilObjectCopyGUI
			if (count($ref_ids) == 1)
			{
				$ilCtrl->setParameterByClass("ilobjectcopygui", "target", $this->object->getRefId());
				$ilCtrl->setParameterByClass("ilobjectcopygui", "source_id", $ref_ids[0]);
				$ilCtrl->redirectByClass("ilobjectcopygui", "saveTarget");
			}
			else
			{
				$ilCtrl->setParameterByClass("ilobjectcopygui", "target", $this->object->getRefId());
				$ilCtrl->setParameterByClass("ilobjectcopygui", "source_ids", implode($ref_ids, "_"));
				$ilCtrl->redirectByClass("ilobjectcopygui", "saveTarget");
			}



			/* old implementation

			foreach($ref_ids as $ref_id)
			{
				$revIdMapping = array(); 
                                
				$oldNode_data = $tree->getNodeData($ref_id);
				if ($oldNode_data['parent'] == $this->object->getRefId())
				{
					require_once 'Modules/File/classes/class.ilObjFileAccess.php';
					$newTitle = ilObjFileAccess::_appendNumberOfCopyToFilename($oldNode_data['title'],null);
					$newRef = $this->cloneNodes($ref_id, $this->object->getRefId(), $refIdMapping, $newTitle);
				}
				else
				{
					$newRef = $this->cloneNodes($ref_id, $this->object->getRefId(), $refIdMapping, null);
				}

				// BEGIN ChangeEvent: Record copy event.
				$old_parent_data = $tree->getParentNodeData($ref_id);
				$newNode_data = $tree->getNodeData($newRef);
				ilChangeEvent::_recordReadEvent($oldNode_data['type'], $ref_id,
					$oldNode_data['obj_id'], $ilUser->getId());
				ilChangeEvent::_recordWriteEvent($newNode_data['obj_id'], $ilUser->getId(), 'add', 
					$this->object->getId());
				ilChangeEvent::_catchupWriteEvents($newNode_data['obj_id'], $ilUser->getId());				
				// END ChangeEvent: Record copy event.
			}*/

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
				
				include_once('./Services/AccessControl/classes/class.ilConditionHandler.php');
				ilConditionHandler::_adjustMovedObjectConditions($ref_id);

				// BEGIN ChangeEvent: Record cut event.
				$node_data = $tree->getNodeData($ref_id);
				$old_parent_data = $tree->getNodeData($old_parent);
				ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'remove', 
					$old_parent_data['obj_id']);
				ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'add', 
					$this->object->getId());
				ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());				
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

				// BEGIN ChangeEvent: Record link event.
				$node_data = $tree->getNodeData($new_ref_id);
				ilChangeEvent::_recordWriteEvent($node_data['obj_id'], $ilUser->getId(), 'add', 
					$this->object->getId());
				ilChangeEvent::_catchupWriteEvents($node_data['obj_id'], $ilUser->getId());				
				// END PATCH ChangeEvent: Record link event.
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
			ilUtil::sendSuccess($this->lng->txt("msg_cut_copied"),true);
		}
		// BEGIN WebDAV: Support a copy command in repository
		else if ($last_cmd == "copy")
		{
			ilUtil::sendSuccess($this->lng->txt("msg_cloned"),true);
		}
		else if ($last_cmd == 'link')
		// END WebDAV: Support copy command in repository
		{
			ilUtil::sendSuccess($this->lng->txt("msg_linked"),true);
		}

		$this->ctrl->returnToParent($this);

	} // END PASTE
	

	// BEGIN WebDAV: Support a copy command in repository
	
	/**
	* Copy object(s) out from a container and write the information to clipboard
	* It is not possible to copy multiple objects at once.
	*
	*
	* @access	public
	*/
	// stefan.born@phzh.ch (01.07.2013): 
	// UNCOMMENTED DUE NEW copyObject FUNCTION AND BECAUSE IT SEEMS THIS FUNCTION IS NOT USED ANYWHERE
	/*function copyObject()
	{
		global $ilAccess,$ilObjDefinition;
		
		if(!$ilAccess->checkAccess('copy','',$_GET['item_ref_id']))
		{
			$title = ilObject::_lookupTitle(ilObject::_lookupObjId($_GET['item_ref_id']));
			
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_copy').' '.$title,true);
			$this->ctrl->returnToParent($this);
		}
		
		$_SESSION["clipboard"]["parent"] = $_GET["ref_id"];
		$_SESSION["clipboard"]["cmd"] = 'copy';

		ilUtil::sendInfo($this->lng->txt("msg_copy_clipboard"),true);
		
		// THIS FUNCTION DOES NOT EXIST!
		return $this->initAndDisplayCopyIntoObjectObject();
		
		
		

		

		// FOR ALL OBJECTS THAT SHOULD BE COPIED
		foreach ($_POST["id"] as $ref_id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $this->tree->getNodeData($ref_id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK VIEW, READ AND COPY PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
			foreach ($subtree_nodes as $node)
			{
				if($node['type'] == 'rolf')
				{
					continue;
				}
				
				if (!$rbacsystem->checkAccess('visible,read,copy',$node["ref_id"]))
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
	*/
	// BEGIN WebDAV: Support copy command in repository


	/**
	* show clipboard
	*/
	function clipboardObject()
	{
		global $ilErr, $ilLog, $ilTabs, $tpl, $ilToolbar, $ilCtrl, $lng;

		$ilTabs->activateTab("clipboard");

		// function should not be called if clipboard is empty
		if (empty($_SESSION['clipboard']) or !is_array($_SESSION['clipboard']))
		{
			$message = sprintf('%s::clipboardObject(): Illegal access. Clipboard variable is empty!', get_class($this));
			$ilLog->write($message,$ilLog->FATAL);
			$ilErr->raiseError($this->lng->txt("permission_denied"),$ilErr->WARNING);
		}

		$data = array();
		foreach($_SESSION["clipboard"]["ref_ids"] as $ref_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id,false))
			{
				continue;
			}

			$data[] = array(
				"type" => $tmp_obj->getType(),
				"type_txt" => $this->lng->txt("obj_".$tmp_obj->getType()),
				"title" => $tmp_obj->getTitle(),
				"cmd" => ($_SESSION["clipboard"]["cmd"] == "cut") ? $this->lng->txt("move") :$this->lng->txt($_SESSION["clipboard"]["cmd"]),
				"ref_id" => $ref_id,
				"obj_id" => $tmp_obj->getId()
			);

			unset($tmp_obj);
		}

		include_once("./Services/Object/classes/class.ilObjClipboardTableGUI.php");
		$tab = new ilObjClipboardTableGUI($this, "clipboard");
		$tab->setData($data);
		$tpl->setContent($tab->getHTML());

		if (count($data) > 0)
		{
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
			$ilToolbar->addFormButton($lng->txt("insert_object_here"),
				"paste");
			$ilToolbar->addFormButton($lng->txt("clear_clipboard"),
				"clear");
		}

		return true;
	}

	
	/**
	* show edit section of custom icons for container
	* 
	*/
	function showCustomIconsEditing($a_input_colspan = 1, ilPropertyFormGUI $a_form = null, $a_as_section = true)
	{
		if ($this->ilias->getSetting("custom_icons"))
		{
			if(!$a_form)
			{
				/*
				$this->tpl->addBlockFile("CONTAINER_ICONS", "container_icon_settings",
					"tpl.container_icon_settings.html", "Services/Container");

				if (($big_icon = $this->object->getBigIconPath()) != "")
				{
					$this->tpl->setCurrentBlock("big_icon");
					$this->tpl->setVariable("SRC_BIG_ICON", $big_icon);
					$this->tpl->parseCurrentBlock();
				}
				if ($this->object->getType() != "root")
				{
					if (($small_icon = $this->object->getSmallIconPath()) != "")
					{
						$this->tpl->setCurrentBlock("small_icon");
						$this->tpl->setVariable("SRC_SMALL_ICON", $small_icon);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("small_icon_row");
					$this->tpl->setVariable("SMALL_ICON", $this->lng->txt("standard_icon"));
					$this->tpl->setVariable("SMALL_SIZE", "(".
						$this->ilias->getSetting("custom_icon_small_width")."x".
						$this->ilias->getSetting("custom_icon_small_height").")");
					$this->tpl->setVariable("TXT_REMOVE_S", $this->lng->txt("remove"));
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
				$this->tpl->setVariable("TINY_ICON", $this->lng->txt("tiny_icon"));
				$this->tpl->setVariable("BIG_SIZE", "(".
					$this->ilias->getSetting("custom_icon_big_width")."x".
					$this->ilias->getSetting("custom_icon_big_height").")");
				$this->tpl->setVariable("TINY_SIZE", "(".
					$this->ilias->getSetting("custom_icon_tiny_width")."x".
					$this->ilias->getSetting("custom_icon_tiny_height").")");
				$this->tpl->setVariable("TXT_REMOVE", $this->lng->txt("remove"));
				$this->tpl->parseCurrentBlock();
				*/
			}
			else
			{
				//$big_icon = $this->object->getBigIconPath();
				$custom_icon = $this->object->getCustomIconPath();
				//$tiny_icon = $this->object->getTinyIconPath();

				if($a_as_section)
				{					
					$title = new ilFormSectionHeaderGUI();
					$title->setTitle($this->lng->txt("icon_settings"));
				}
				else
				{
					$title = new ilCustomInputGUI($this->lng->txt("icon_settings"), "");
				}
				$a_form->addItem($title);

				// big
				/*
				$caption = $this->lng->txt("big_icon")." (".
					$this->ilias->getSetting("custom_icon_big_width")."x".
					$this->ilias->getSetting("custom_icon_big_height").")";
				$icon = new ilImageFileInputGUI($caption, "cont_big_icon");
				$icon->setImage($big_icon);
				if($a_as_section)
				{
					$a_form->addItem($icon);
				}
				else
				{
					$title->addSubItem($icon);
				}*/
				
				// small/standard
				//if ($this->object->getType() != "root")
				//{
					/*$caption = $this->lng->txt("standard_icon")." (".
						$this->ilias->getSetting("custom_icon_small_width")."x".
						$this->ilias->getSetting("custom_icon_small_height").")";*/
					$caption = $this->lng->txt("cont_custom_icon");
					$icon = new ilImageFileInputGUI($caption, "cont_icon");
					$icon->setSuffixes(array("svg"));
					$icon->setImage($custom_icon);
					if($a_as_section)
					{
						$a_form->addItem($icon);
					}
					else
					{
						$title->addSubItem($icon);
					}
				//}

				// tiny
				/*
				$caption = $this->lng->txt("tiny_icon")." (".
					$this->ilias->getSetting("custom_icon_tiny_width")."x".
					$this->ilias->getSetting("custom_icon_tiny_height").")";
				$icon = new ilImageFileInputGUI($caption, "cont_tiny_icon");
				$icon->setImage($tiny_icon);
				if($a_as_section)
				{
					$a_form->addItem($icon);
				}
				else
				{
					$title->addSubItem($icon);
				}
				*/
			}
		}
	}

	function isActiveAdministrationPanel()
	{
		global $ilAccess;
		
		// #10081
		if($_SESSION["il_cont_admin_panel"] &&
			$this->object->getRefId() && 
			!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			return false;
		}
		
		return $_SESSION["il_cont_admin_panel"];
	}
	
	/**
	* May be overwritten in subclasses.
	*/
	function setColumnSettings($column_gui)
	{
		global $ilAccess;
		parent::setColumnSettings($column_gui);

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()) &&
			$this->isActiveAdministrationPanel() &&
			$this->allowBlocksMoving())
		{
			$column_gui->setEnableMovement(true);
		}
		
		$column_gui->setRepositoryItems(
			$this->object->getSubItems($this->isActiveAdministrationPanel(), true));
		
		//if ($ilAccess->checkAccess("write", "", $this->object->getRefId())
		//	&& $this->allowBlocksConfigure())
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
	* Standard is to allow blocks moving
	*/
	function allowBlocksMoving()
	{
		true;
	}

	/**
	* Standard is to allow blocks configuration
	*/
	function allowBlocksConfigure()
	{
		true;
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
			ilUtil::sendFailure($this->lng->txt('select_one'));
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

		
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.container_wizard_page.html',
	 		"Services/Container");
	 	$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
	 	$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$new_type.'.svg'));
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
				$this->tpl->setVariable('TREE_IMG',ilUtil::getImagePath('icon_'.$node['type'].'.svg'));
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
		global $ilLog, $ilCtrl;
		
		include_once('./Services/Link/classes/class.ilLink.php');
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
			ilUtil::sendFailure($this->lng->txt('select_one'));
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
		
		include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
		if(ilCopyWizardOptions::_isFinished($result['copy_id']))
		{
			ilUtil::sendSuccess($this->lng->txt("object_duplicated"),true);			
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $result['ref_id']);
			$ilCtrl->redirectByClass("ilrepositorygui", "");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("object_copy_in_progress"),true);
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
			$ilCtrl->redirectByClass("ilrepositorygui", "");
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
		$sorting = ilContainerSorting::_getInstance($this->object->getId());

		// Allow comma
		$positions = str_replace(',','.',$_POST['position']);

		$sorting->savePost($positions);
		ilUtil::sendSuccess($this->lng->txt('cntr_saved_sorting'), true);
		$this->ctrl->redirect($this, "editOrder");
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
			$newObj->update();
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

	/**
	* Modify Item ListGUI for presentation in container
	*/
	function modifyItemGUI(&$a_item_list_gui, $a_item_data, $a_show_path)
	{
		global $lng;
		
		if($a_show_path)
		{
			$a_item_list_gui->addCustomProperty($lng->txt('path'),
				ilContainer::buildPath($a_item_data['ref_id'], $this->object->getRefId()),
				false, true);
		}
	}
	
	/**
	* build path
	*/
	static function _buildPath($a_ref_id, $a_course_ref_id)
	{
		global $tree;

		$path_arr = $tree->getPathFull($a_ref_id, $a_course_ref_id);
		$counter = 0;
		foreach($path_arr as $data)
		{
			if($counter++)
			{
				$path .= " > ";
			}
			$path .= $data['title'];
		}

		return $path;
	}

	//
	// Style editing
	//
	
	/**
	* Edit style properties
	*/
	function editStylePropertiesObject()
	{
		global $ilTabs, $tpl;
		
		$this->checkPermission("write");
		
		$this->initStylePropertiesForm();
		$tpl->setContent($this->form->getHTML());
		
		$ilTabs->activateTab("obj_sty");
	}
	
	/**
	* Init style properties form
	*/
	function initStylePropertiesForm()
	{
		global $ilCtrl, $lng, $ilTabs, $ilSetting, $tpl;
		
		$tpl->setTreeFlatIcon("", "");
		$ilTabs->clearTargets();
		$xpage_id = ilContainer::_lookupContainerSetting($this->object->getId(),
			"xhtml_page");
		if ($xpage_id > 0)
		{
			$ilTabs->setBackTarget($lng->txt("cntr_back_to_old_editor"),
				$ilCtrl->getLinkTarget($this, "switchToOldEditor"), "_top");
		}
		else
		{
			$ilTabs->setBackTarget($lng->txt("back"), "./goto.php?target=".$this->object->getType()."_".
				$this->object->getRefId(), "_top");
		}

		include_once("./Services/Container/classes/class.ilContainerPageGUI.php");
		$page_gui = new ilContainerPageGUI($this->object->getId());
		$style_id = $this->object->getStyleSheetId();
		if (ilObject::_lookupType($style_id) == "sty")
		{
			$page_gui->setStyleId($style_id);
		}
		else
		{
			$style_id = 0;
		}
		$page_gui->setTabHook($this, "addPageTabs");
		$ilCtrl->getHTML($page_gui);
		$ilTabs->setTabActive("obj_sty");
		
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$lng->loadLanguageModule("style");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$fixed_style = $ilSetting->get("fixed_content_style_id");
//		$style_id = $this->object->getStyleSheetId();

		if ($fixed_style > 0)
		{
			$st = new ilNonEditableValueGUI($lng->txt("wiki_current_style"));
			$st->setValue(ilObject::_lookupTitle($fixed_style)." (".
				$this->lng->txt("global_fixed").")");
			$this->form->addItem($st);
		}
		else
		{
			$st_styles = ilObjStyleSheet::_getStandardStyles(true, false,
				$_GET["ref_id"]);

			$st_styles[0] = $this->lng->txt("default");
			ksort($st_styles);

			if ($style_id > 0)
			{
				// individual style
				if (!ilObjStyleSheet::_lookupStandard($style_id))
				{
					$st = new ilNonEditableValueGUI($lng->txt("style_current_style"));
					$st->setValue(ilObject::_lookupTitle($style_id));
					$this->form->addItem($st);

//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "edit"));

					// delete command
					$this->form->addCommandButton("editStyle",
						$lng->txt("style_edit_style"));
					$this->form->addCommandButton("deleteStyle",
						$lng->txt("style_delete_style"));
//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "delete"));
				}
			}

			if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id))
			{
				$style_sel = ilUtil::formSelect ($style_id, "style_id",
					$st_styles, false, true);
				$style_sel = new ilSelectInputGUI($lng->txt("style_current_style"), "style_id");
				$style_sel->setOptions($st_styles);
				$style_sel->setValue($style_id);
				$this->form->addItem($style_sel);
//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "create"));
				$this->form->addCommandButton("saveStyleSettings",
						$lng->txt("save"));
				$this->form->addCommandButton("createStyle",
					$lng->txt("sty_create_ind_style"));
			}
		}
		$this->form->setTitle($lng->txt("obj_sty"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Create Style
	*/
	function createStyleObject()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "create");
	}
	
	/**
	* Edit Style
	*/
	function editStyleObject()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "edit");
	}

	/**
	* Delete Style
	*/
	function deleteStyleObject()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "delete");
	}

	/**
	* Save style settings
	*/
	function saveStyleSettingsObject()
	{
		global $ilSetting;
	
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		if ($ilSetting->get("fixed_content_style_id") <= 0 &&
			(ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
			|| $this->object->getStyleSheetId() == 0))
		{
			$this->object->setStyleSheetId(ilUtil::stripSlashes($_POST["style_id"]));
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$this->ctrl->redirect($this, "editStyleProperties");
	}

	/**
	* Get item list command drop down asynchronously
	*/
	function getAsynchItemListObject()
	{
		global $ilCtrl;
		
		$ref_id = $_GET["cmdrefid"];
		$obj_id = ilObject::_lookupObjId($ref_id);
		$type = ilObject::_lookupType($obj_id);
		
		// this should be done via container-object->getSubItem in the future
		$data = array("child" => $ref_id, "ref_id" => $ref_id, "obj_id" => $obj_id,
			"type" => $type);
		include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';
		$item_list_gui = ilObjectListGUIFactory::_getListGUIByType($type);
		$item_list_gui->setContainerObject($this);
		
		$item_list_gui->enableComments(true);
		$item_list_gui->enableNotes(true);
		$item_list_gui->enableTags(true);
		
		$this->modifyItemGUI($item_list_gui, $data, false);
		$html = $item_list_gui->getListItemHTML($ref_id,
			$obj_id, "", "", true, true);

		// include plugin slot for async item list
		global $ilPluginAdmin;
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		foreach ($pl_names as $pl)
		{
			$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();
			$resp = $gui_class->getHTML("Services/Container", "async_item_list", array("html" => $html));
			if ($resp["mode"] != ilUIHookPluginGUI::KEEP)
			{
				$html = $gui_class->modifyHTML($html, $resp);
			}
		}
		
		echo $html;
		exit;
	}

	/**
	 * Show webdav password instruction
	 * @return 
	 */
	protected function showPasswordInstructionObject($a_init = true)
	{
		global $tpl,$ilToolbar;
		
		if($a_init)
		{
			ilUtil::sendInfo($this->lng->txt('webdav_pwd_instruction'));
			$this->initFormPasswordInstruction();
		}
		
		include_once ('Services/WebDAV/classes/class.ilDAVServer.php');
		$davServer = ilDAVServer::getInstance();
		$ilToolbar->addButton(
			$this->lng->txt('mount_webfolder'),
			$davServer->getMountURI($this->object->getRefId()),
			'_blank',
			'',
			$davServer->getFolderURI($this->object->getRefId())
		);

		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Init password form
	 * @return 
	 */
	protected function initFormPasswordInstruction()
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	
		// new password
		$ipass = new ilPasswordInputGUI($this->lng->txt("desired_password"), "new_password");
		$ipass->setRequired(true);

		$this->form->addItem($ipass);
		$this->form->addCommandButton("savePassword", $this->lng->txt("save"));
		$this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
		
		$this->form->setTitle($this->lng->txt("chg_ilias_and_webfolder_password"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		return $this->form;
	}
	
	/**
	 * Save password
	 * @return 
	 */
	protected function savePasswordObject()
	{
		global $ilUser;
		
		$form = $this->initFormPasswordInstruction();
		if($form->checkInput())
		{
			$ilUser->resetPassword($this->form->getInput('new_password'),$this->form->getInput('new_password'));
			ilUtil::sendSuccess($this->lng->txt('webdav_pwd_instruction_success'),true);
			$this->showPasswordInstructionObject(false);
			return true;
		}
		$form->setValuesByPost();
		$this->showPasswordInstructionObject();
	}
	
	/**
	 * Redraw a list item (ajax)
	 *
	 * @param
	 * @return
	 */
	function redrawListItemObject()
	{
		global $tpl;
		
		$html = null;
		
		$item_data = $this->object->getSubItems(false, false, (int) $_GET["child_ref_id"]);
		$container_view = $this->getContentGUI();
		
		// list item is session material (not part of "_all"-items - see below)
		include_once './Modules/Session/classes/class.ilEventItems.php';
		$event_items = ilEventItems::_getItemsOfContainer($this->object->getRefId());
		if(in_array((int)$_GET["child_ref_id"], $event_items))
		{			
			include_once('./Services/Object/classes/class.ilObjectActivation.php');				
			foreach ($this->object->items["sess"] as $id)
			{					
				$items = ilObjectActivation::getItemsByEvent($id['obj_id']);
				foreach($items as $event_item)
				{
					if ($event_item["child"] == (int)$_GET["child_ref_id"])
					{
						// sessions
						if((int)$_GET["parent_ref_id"])
						{
							$event_item["parent"] = (int)$_GET["parent_ref_id"];
						}
						$html = $container_view->renderItem($event_item);
					}
				}
			}
		}
			
		// "normal" list item
		if(!$html)
		{
			foreach ($this->object->items["_all"] as $id)
			{
				if ($id["child"] == (int) $_GET["child_ref_id"])
				{
					$html = $container_view->renderItem($id);				
				}
			}
		}
		
		if($html)
		{
			echo $html;
			
			// we need to add onload code manually (rating, comments, etc.)
			echo $tpl->getOnLoadCodeForAsynch();	
		}
						
		exit;
	}

	// begin-patch fm
	/**
	 * Add file manager link
	 * @param <type> $a_sub_type
	 * @param <type> $a_sub_id
	 *
	 */
	protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
	{
		$lg = parent::initHeaderAction($a_sub_type, $a_sub_id);

		// begin-patch fm
		include_once './Services/WebServices/FileManager/classes/class.ilFMSettings.php';
		if(ilFMSettings::getInstance()->isEnabled())
		{
			if($lg instanceof ilObjectListGUI)
			{
				$lg->addCustomCommand($this->ctrl->getLinkTarget($this,'fileManagerLaunch'), 'fm_start','_blank');
			}
		}
		// end-patch fm
		return $lg;
	}

	/**
	 * Launch jnlp
	 */
	protected function fileManagerLaunchObject()
	{
		global $ilUser;
		
		$tpl = new ilTemplate('tpl.fm_launch_ws.html',false,false,'Services/WebServices/FileManager');
		$tpl->setVariable('JNLP_URL',ILIAS_HTTP_PATH.'/Services/WebServices/FileManager/lib/dist/FileManager.jnlp');
		$tpl->setVariable('SESSION_ID', $_COOKIE['PHPSESSID'].'::'.CLIENT_ID);
		$tpl->setVariable('UID',$ilUser->getId());
		$tpl->setVariable('REF_ID', $this->object->getRefId());
		$tpl->setVariable('WSDL_URI', ILIAS_HTTP_PATH.'/webservice/soap/server.php?wsdl');
		$tpl->setVariable('LOCAL_FRAME', ilFMSettings::getInstance()->isLocalFSEnabled() ? 1 : 0);
		$tpl->setVariable('REST_URI',ILIAS_HTTP_PATH.'/Services/WebServices/Rest/server.php');
		$tpl->setVariable('FILE_LOCKS',0);
		$tpl->setVariable('UPLOAD_FILESIZE',  ilFMSettings::getInstance()->getMaxFileSize());

		include_once("./Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
		$header_top_title = ilObjSystemFolder::_getHeaderTitle();
		$tpl->setVariable('HEADER_TITLE',$header_top_title ? $header_top_title : '');
		echo $tpl->get();
		exit;
	}
	// begin-patch fm
		
	/**
	 * Show tree
	 */
	function showRepTree()
	{
		global $tpl, $ilUser, $ilSetting, $ilCtrl;
		
		// set current repository view mode
		if (!empty($_GET["set_mode"]))
		{
			$_SESSION["il_rep_mode"] = $_GET["set_mode"];
			if ($ilUser->getId() != ANONYMOUS_USER_ID)
			{
				$ilUser->writePref("il_rep_mode", $_GET["set_mode"]);
			}
		}

		// get user setting
		if ($_SESSION["il_rep_mode"] == "")
		{
			if ($ilUser->getId() != ANONYMOUS_USER_ID)
			{
				$_SESSION["il_rep_mode"] = $ilUser->getPref("il_rep_mode");
			}
		}

		// if nothing set, get default view
		if ($_SESSION["il_rep_mode"] == "")
		{
			$_SESSION["il_rep_mode"] = $ilSetting->get("default_repository_view");
		}
		
		$mode = ($_SESSION["il_rep_mode"] != "")
			? $_SESSION["il_rep_mode"]
			: "flat";

		// check for administration context, see #0016312
		if ($mode == "tree" && (strtolower($_GET["baseClass"]) != "iladministrationgui"))
		{
			include_once("./Services/Repository/classes/class.ilRepositoryExplorerGUI.php");
			$exp = new ilRepositoryExplorerGUI($this, "showRepTree");
			if (!$exp->handleCommand())
			{
				$tpl->setLeftNavContent($exp->getHTML());
			}
		}
	}
	
	/**
	 * Append sorting settings to property form
	 * @param ilPropertyFormGUI $form
	 * @param type $a_sorting_settings
	 */
	protected function initSortingForm(ilPropertyFormGUI $form, array $a_sorting_settings)
	{
		include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
		include_once './Services/Container/classes/class.ilContainer.php';
		
		$settings = new ilContainerSortingSettings($this->object->getId());
		$sort = new ilRadioGroupInputGUI($this->lng->txt('sorting_header'), "sorting");
		
		if(in_array(ilContainer::SORT_INHERIT, $a_sorting_settings))
		{
			$sort_inherit = new ilRadioOption();
			$sort_inherit->setTitle(
				$this->lng->txt('sort_inherit_prefix').
					' ('.ilContainerSortingSettings::sortModeToString(
							ilContainerSortingSettings::lookupSortModeFromParentContainer(
									$this->object->getId())).') '
			);
			$sort_inherit->setValue(ilContainer::SORT_INHERIT);
			$sort_inherit->setInfo($this->lng->txt('sorting_info_inherit'));
			$sort->addOption($sort_inherit);
		}
		if(in_array(ilContainer::SORT_TITLE,$a_sorting_settings))
		{
			$sort_title = new ilRadioOption(
					$this->lng->txt('sorting_title_header'),
					ilContainer::SORT_TITLE
			);
			$sort_title->setInfo($this->lng->txt('sorting_info_title'));
			
			$this->initSortingDirectionForm($settings,$sort_title,'title');
			$sort->addOption($sort_title);
		}
		if(in_array(ilContainer::SORT_CREATION, $a_sorting_settings))
		{
			$sort_activation = new ilRadioOption($this->lng->txt('sorting_creation_header'),ilContainer::SORT_CREATION);
			$sort_activation->setInfo($this->lng->txt('sorting_creation_info'));
			$this->initSortingDirectionForm($settings,$sort_activation,'creation');
			$sort->addOption($sort_activation);
		}
		if(in_array(ilContainer::SORT_ACTIVATION, $a_sorting_settings))
		{
			$sort_activation = new ilRadioOption($this->lng->txt('crs_sort_activation'),ilContainer::SORT_ACTIVATION);
			$sort_activation->setInfo($this->lng->txt('crs_sort_timing_info'));
			$this->initSortingDirectionForm($settings,$sort_activation,'activation');
			$sort->addOption($sort_activation);
		}
		if(in_array(ilContainer::SORT_MANUAL, $a_sorting_settings))
		{
			$sort_manual = new ilRadioOption(
				$this->lng->txt('sorting_manual_header'),
				ilContainer::SORT_MANUAL
			);
			$sort_manual->setInfo($this->lng->txt('sorting_info_manual'));
			$this->initManualSortingOptionForm($settings, $sort_manual, "manual", $a_sorting_settings);
			$sort->addOption($sort_manual);
		}

		$sort->setValue($settings->getSortMode());
		$form->addItem($sort);
		
		return $form;
	}
	
	/**
	 * Add sorting direction
	 * @param ilFormPropertyGUI $element
	 */
	protected function initSortingDirectionForm(ilContainerSortingSettings $sorting_settings, $element, $a_prefix)
	{
		if($a_prefix == 'manual')
		{
			$txt = $this->lng->txt('sorting_new_items_direction');
		}
		else
		{
			$txt = $this->lng->txt('sorting_direction');
		}
		
		$direction = new ilRadioGroupInputGUI($txt,$a_prefix.'_sorting_direction');
		$direction->setValue($sorting_settings->getSortDirection());
		$direction->setRequired(TRUE);
		
		// asc
		$asc = new ilRadioOption(
				$this->lng->txt('sorting_asc'),
				ilContainer::SORT_DIRECTION_ASC
		);
		$direction->addOption($asc);

		// desc
		$desc = new ilRadioOption(
				$this->lng->txt('sorting_desc'),
				ilContainer::SORT_DIRECTION_DESC
		);
		$direction->addOption($desc);
		
		$element->addSubItem($direction);
		
		return $element;
	}

	/**
	 * Add manual sorting options
	 *
	 */
	protected function initManualSortingOptionForm(ilContainerSortingSettings $settings, $element, $a_prefix,
												   $a_sorting_settings)
	{
		$position = new ilRadioGroupInputGUI($this->lng->txt('sorting_new_items_position'),$a_prefix.'_new_items_position');
		$position->setValue($settings->getSortNewItemsPosition());
		$position->setRequired(TRUE);

		//new items insert on top
		$new_top = new ilRadioOption(
			$this->lng->txt('sorting_new_items_at_top'),
			ilContainer::SORT_NEW_ITEMS_POSITION_TOP
		);

		$position->addOption($new_top);

		//new items insert at bottom
		$new_bottom = new ilRadioOption(
			$this->lng->txt('sorting_new_items_at_bottom'),
			ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM
		);

		$position->addOption($new_bottom);

		$element->addSubItem($position);

		$order = new ilRadioGroupInputGUI($this->lng->txt('sorting_new_items_order'),$a_prefix.'_new_items_order');
		$order->setValue($settings->getSortNewItemsOrder());
		$order->setRequired(TRUE);

		if(in_array(ilContainer::SORT_TITLE, $a_sorting_settings))
		{
			//new items sort in alphabetical order
			$new_title = new ilRadioOption(
				$this->lng->txt('sorting_title_header'),
				ilContainer::SORT_NEW_ITEMS_ORDER_TITLE
			);

			$order->addOption($new_title);
		}

		if(in_array(ilContainer::SORT_CREATION, $a_sorting_settings))
		{
			//new items sort by creation date
			$new_creation = new ilRadioOption(
				$this->lng->txt('sorting_creation_header'),
				ilContainer::SORT_NEW_ITEMS_ORDER_CREATION
			);

			$order->addOption($new_creation);
		}


		if(in_array(ilContainer::SORT_ACTIVATION, $a_sorting_settings))
		{
			//new items by activation
			$new_activation = new ilRadioOption(
				$this->lng->txt('crs_sort_activation'),
				ilContainer::SORT_NEW_ITEMS_ORDER_ACTIVATION
			);

			$order->addOption($new_activation);
		}

		$element->addSubItem($order);

		$this->initSortingDirectionForm($settings,$element,'manual');

		return $element;
	}

	/**
	 * Save sorting settings
	 * @param ilPropertyFormGUI $form
	 */
	protected function saveSortingSettings(ilPropertyFormGUI $form)
	{
		include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
		$settings = new ilContainerSortingSettings($this->object->getId());
		$settings->setSortMode($form->getInput("sorting"));
		
		switch($form->getInput('sorting'))
		{
			case ilContainer::SORT_TITLE:
				$settings->setSortDirection($form->getInput('title_sorting_direction'));
				break;
			case ilContainer::SORT_ACTIVATION:
				$settings->setSortDirection($form->getInput('activation_sorting_direction'));
				break;
			case ilContainer::SORT_CREATION:
				$settings->setSortDirection($form->getInput('creation_sorting_direction'));
				break;
			case ilContainer::SORT_MANUAL:
				$settings->setSortNewItemsPosition($form->getInput('manual_new_items_position'));
				$settings->setSortNewItemsOrder($form->getInput('manual_new_items_order'));
				$settings->setSortDirection($form->getInput('manual_sorting_direction'));
				break;
		}
		
		$settings->update();
	}

	/**
	 * Show trash content of object
	 *
	 * @access	public
	 */
	public function trashObject()
	{
		global $tpl;

		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
		$ru = new ilRepUtilGUI($this);
		$ru->showTrashTable($_GET["ref_id"]);
	}

	/**
	 * remove objects from trash bin and all entries therefore every object needs a specific deleteObject() method
	 *
	 * @access	public
	 */
	public function removeFromSystemObject()
	{
		global $log, $ilAppEventHandler, $lng;

		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
		$ru = new ilRepUtilGUI($this);
		$ru->removeObjectsFromSystem($_POST["trash_id"]);
		$this->ctrl->redirect($this, "trash");
	}

	/**
	 * Get objects back from trash
	 */
	public function undeleteObject()
	{
		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
		$ru = new ilRepUtilGUI($this);
		$ru->restoreObjects($_GET["ref_id"], $_POST["trash_id"]);
		$this->ctrl->redirect($this, "trash");
	}

	/**
	 * confirmation screen remove from system
	 */
	public function confirmRemoveFromSystemObject()
	{
		global $lng;
		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");

		if(!isset($_POST["trash_id"]))
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "trash");
		}

		$ru = new ilRepUtilGUI($this);
		$ru->confirmRemoveFromSystemObject($_POST["trash_id"]);
	}

	/**
	 * @param $cmd string
	 * @return ilTreeExplorerGUI
	 */
	protected function getTreeSelectorGUI($cmd)
	{
		include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
		$exp = new ilRepositorySelectorExplorerGUI($this, "showPasteTree");
		// TODO: The study programme 'prg' is not included here, as the
		// ilRepositorySelectorExplorerGUI only handles static rules for
		// parent-child-relations and not the dynamic relationsships
		// required for the SP (see #16909).
		$exp->setTypeWhiteList(array("root", "cat", "grp", "crs", "fold"));
		if ($cmd == "link") {
			$exp->setSelectMode("nodes", true);
			return $exp;
		} else {
			$exp->setSelectMode("nodes[]", false);
			return $exp;
		}
	}
}
?>
