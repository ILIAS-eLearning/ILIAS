<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ("./Services/COPage/classes/class.ilPageObjectGUI.php");

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
* @ilCtrl_Calls ilPageEditorGUI: ilPCMapGUI, ilPCPluggedGUI, ilPCTabsGUI, ilPCTabGUI, IlPCPlaceHolderGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCContentIncludeGUI, ilPCLoginPageElementGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCInteractiveImageGUI, ilPCProfileGUI, ilPCVerificationGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCBlogGUI, ilPCQuestionOverviewGUI, ilPCSkillsGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCConsultationHoursGUI, ilPCMyCoursesGUI, ilPCAMDPageListGUI
* @ilCtrl_Calls ilPageEditorGUI: ilPCGridGUI, ilPCGridCellGUI
*
* @ingroup ServicesCOPage
*/
class ilPageEditorGUI
{
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

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
	var $enable_keywords;
	var $enable_anchors;

	/**
	 * @var ilLogger
	 */
	protected $log;

	/**
	* Constructor
	*
	* @param	object		$a_page_object		page object
	* @access	public
	*/
	function __construct(&$a_page_object, &$a_page_object_gui)
	{
		global $DIC;

		$this->help = $DIC["ilHelp"];
		$this->user = $DIC->user();
		$this->access = $DIC->access();
		$tpl = $DIC["tpl"];
		$lng = $DIC->language();
		$objDefinition = $DIC["objDefinition"];
		$ilCtrl = $DIC->ctrl();
		$ilTabs = $DIC->tabs();

		$this->log = ilLoggerFactory::getLogger('copg');

		// initiate variables
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->objDefinition = $objDefinition;
		$this->tabs_gui = $ilTabs;
		$this->page = $a_page_object;
		$this->page_gui = $a_page_object_gui;

		$this->ctrl->saveParameter($this, array("hier_id", "pc_id"));
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
		$this->locator = $a_locator;
	}

	/**
	* redirect to parent context
	*/
	function returnToContext()
	{
		$this->ctrl->returnToParent($this);
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
	function executeCommand()
	{
		$ilCtrl = $this->ctrl;
		$ilHelp = $this->help;

		$this->log->debug("begin ============");

		// Step BC (basic command determination)
		// determine cmd, cmdClass, hier_id and pc_id
		$cmd = $this->ctrl->getCmd("displayPage");
		$cmdClass = strtolower($this->ctrl->getCmdClass());

		$hier_id = $_GET["hier_id"];
		$pc_id = $_GET["pc_id"];
		if(isset($_POST["new_hier_id"]))
		{
			$hier_id = $_POST["new_hier_id"];
		}

		$new_type = (isset($_GET["new_type"]))
			? $_GET["new_type"]
			: $_POST["new_type"];

		$this->log->debug("step BC: cmd:$cmd, cmdClass:$cmdClass, hier_id: $hier_id, pc_id: $pc_id");

		// Step EC (exec_ command handling)
		// handle special exec_ commands, modify pc, hier_id
		if (substr($cmd, 0, 5) == "exec_")
		{
			// check whether pc id is given
			$pca = explode(":", key($_POST["cmd"]));
			$pc_id = $pca[1];
			$cmd = explode("_", $pca[0]);
			unset($cmd[0]);
			$hier_id = implode($cmd, "_");
			$cmd = $_POST["command".$hier_id];
		}
		$this->log->debug("step EC: cmd:$cmd, hier_id: $hier_id, pc_id: $pc_id");

		// Step CC (handle table container (and similar) commands
		// ... strip "c" "r" of table ids from hierarchical id
		$first_hier_character = substr($hier_id, 0, 1);
		if ($first_hier_character == "c" ||
			$first_hier_character == "r" ||
			$first_hier_character == "g" ||
			$first_hier_character == "i")
		{
			$hier_id = substr($hier_id, 1);
		}
		$this->log->debug("step CC: cmd:$cmd, hier_id: $hier_id, pc_id: $pc_id");

		// Step B (build dom, and ids in XML)
		$this->page->buildDom();
		$this->page->addHierIDs();


		// Step CS (strip base command)
		if ($cmdClass != "ilfilesystemgui")
		{
			$com = explode("_", $cmd);
			$cmd = $com[0];
		}
		$this->log->debug("step CS: cmd:$cmd");


		// Step NC (determine next class)
		$next_class = $this->ctrl->getNextClass($this);
		$this->log->debug("step NC: next class: ".$next_class);


		// Step PH (placeholder handling, placeholders from preview mode come without hier_id)
		if ($next_class == "ilpcplaceholdergui" && $hier_id == "" && $_GET["pl_pc_id"] != "")
		{
			$hid = $this->page->getHierIdsForPCIds(array($_GET["pl_pc_id"]));
			$hier_id = $hid[$_GET["pl_pc_id"]];
		}
		$this->log->debug("step PH: next class: ".$next_class);

		if ($com[0] == "insert" || $com[0] == "create")
		{
			// Step CM (creation mode handling)
			$cmd = $com[0];
			$ctype = $com[1];				// note ctype holds type if cmdclass is empty, but also subcommands if not (e.g. applyFilter in ilpcmediaobjectgui)
			$add_type = $com[2];
			if ($ctype == "mob") $ctype = "media";

			$this->log->debug("step CM: cmd: ".$cmd.", ctype: ".$ctype.", add_type: ".$add_type);
		}
		else
		{
			// Step LM (setting cmd and cmdclass for editing of linked media)
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
				if ($cmdClass != "ilinternallinkgui" && $cmdClass != "ilmdeditorgui"
					&& $cmdClass != "ilimagemapeditorgui" && $cmdClass != "ilfilesystemgui")
				{
					$this->ctrl->setCmdClass("ilobjmediaobjectgui");
					$cmdClass = "ilobjmediaobjectgui";
				}
			}
			$this->log->debug("step LM: cmd: ".$cmd.", cmdClass: ".$cmdClass);


			// Step PR (get content object and return to parent)
			$this->log->debug("before PR: cmdClass: $cmdClass, nextClass: $next_class".
				", hier_id: ".$hier_id.", pc_id: ".$pc_id.")");
			// note: ilinternallinkgui for page: no cont_obj is received
			// ilinternallinkgui for mob: cont_obj is received
			if ($cmd != "insertFromClipboard" && $cmd != "pasteFromClipboard" &&
				$cmd != "setMediaMode" && $cmd != "copyLinkedMediaToClipboard" &&
				$cmd != "activatePage" && $cmd != "deactivatePage" &&
				$cmd != "copyLinkedMediaToMediaPool" && $cmd != "showSnippetInfo" &&
				$cmd != "deleteSelected" && $cmd != "paste" &&
				$cmd != "cancelDeleteSelected" && $cmd != "confirmedDeleteSelected" &&
				$cmd != "copySelected" && $cmd != "cutSelected" &&
				($cmd != "displayPage" || $_POST["editImagemapForward_x"] != "" || $_POST["imagemap_x"] != "") &&
				($cmd != "displayPage" || $_POST["editImagemapForward_x"] != "") &&
				$cmd != "activateSelected" && $cmd != "assignCharacteristicForm" &&
				$cmd != "assignCharacteristic" &&
				$cmdClass != "ilrepositoryselector2inputgui" &&
				$cmd != "cancelCreate" && $cmd != "popup" &&
				$cmdClass != "ileditclipboardgui" && $cmd != "addChangeComment" &&
				($cmdClass != "ilinternallinkgui" || ($next_class == "ilpcmediaobjectgui")))
			{
				if ($_GET["pgEdMediaMode"] != "editLinkedMedia")
				{
					$cont_obj = $this->page->getContentObject($hier_id, $pc_id);
					if (!is_object($cont_obj))
					{
						$this->log->debug("returnToParent");
						$ilCtrl->returnToParent($this);
					}
					$ctype = $cont_obj->getType();
				}
			}
			$this->log->debug("step PR: ctype: $ctype");
		}


		if ($ctype != "media" || !is_object ($cont_obj))
		{
			if ($this->getHeader() != "")
			{
				$this->tpl->setTitle($this->getHeader());
			}
			$this->displayLocator();
		}

		$this->cont_obj = $cont_obj;


		// Step NC (handle empty next class)
		$this->ctrl->setParameter($this, "hier_id", $hier_id);
		$this->ctrl->setParameter($this, "pc_id", $pc_id);
		$this->ctrl->setCmd($cmd);
		if ($next_class == "")
		{
			include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
			$pc_def = ilCOPagePCDef::getPCDefinitionByType($ctype);
			if (is_array($pc_def))
			{
				$this->ctrl->setCmdClass($pc_def["pc_gui_class"]);
			}
			$next_class = $this->ctrl->getNextClass($this);
		}
		$this->log->debug("step NC: next_class: $next_class");

		// ... do not do this while imagemap editing is ongoing
		// Step IM (handle image map editing)
		if ($cmd == "displayPage" && $_POST["editImagemapForward_x"] == "" && $_POST["imagemap_x"] == "")
		{
			$next_class = "";
		}
		$this->log->debug("step IM: next_class: $next_class");


		// Step FC (forward command)
		$this->log->debug("before FC: next_class:".$next_class.", pc_id:".$pc_id.
				", hier_id:".$hier_id.", ctype:".$ctype.", cmd:".$cmd.", _GET[cmd]: ".$_GET["cmd"]);
		switch($next_class)
		{
			case "ilinternallinkgui":
				$link_gui = new ilInternalLinkGUI(
					$this->page_gui->getPageConfig()->getIntLinkHelpDefaultType(),
					$this->page_gui->getPageConfig()->getIntLinkHelpDefaultId(),
					$this->page_gui->getPageConfig()->getIntLinkHelpDefaultIdIsRef());
				$link_gui->setFilterWhiteList(
					$this->page_gui->getPageConfig()->getIntLinkFilterWhiteList());
				foreach ($this->page_gui->getPageConfig()->getIntLinkFilters() as $filter)
				{
					$link_gui->filterLinkType($filter);
				}
				$link_gui->setReturn($this->int_link_return);

				$ret = $this->ctrl->forwardCommand($link_gui);
				break;

			// PC Media Object
			case "ilpcmediaobjectgui":
				include_once ("./Services/COPage/classes/class.ilPCMediaObjectGUI.php");

				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->page_gui->page_back_title,
					$ilCtrl->getLinkTarget($this->page_gui, "edit"));
				$pcmob_gui = new ilPCMediaObjectGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$pcmob_gui->setStyleId($this->page_gui->getStyleId());
				$pcmob_gui->setSubCmd($ctype);
				$pcmob_gui->setEnabledMapAreas($this->page_gui->getPageConfig()->getEnableInternalLinks());
				$ret = $this->ctrl->forwardCommand($pcmob_gui);
				$ilHelp->setScreenIdComponent("copg_media");
				break;

			// only for "linked" media
			case "ilobjmediaobjectgui":
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->lng->txt("back"),
					$ilCtrl->getParentReturn($this));
				$mob_gui = new ilObjMediaObjectGUI("", $_GET["mob_id"],false, false);
				$mob_gui->getTabs();
				$mob_gui->setEnabledMapAreas($this->page_gui->getPageConfig()->getEnableInternalLinks());
				$this->tpl->setTitle($this->lng->txt("mob").": ".
					ilObject::_lookupTitle($_GET["mob_id"]));
				$ret = $this->ctrl->forwardCommand($mob_gui);
				break;

			// Question
			case "ilpcquestiongui":
				include_once("./Services/COPage/classes/class.ilPCQuestionGUI.php");
				$pc_question_gui = new ilPCQuestionGUI($this->page, $cont_obj, $hier_id, $pc_id);
				$pc_question_gui->setSelfAssessmentMode($this->page_gui->getPageConfig()->getEnableSelfAssessment());
				$pc_question_gui->setPageConfig($this->page_gui->getPageConfig());

				if ($this->page_gui->getPageConfig()->getEnableSelfAssessment())
				{
					$this->tabs_gui->clearTargets();
					$ilHelp->setScreenIdComponent("copg_pcqst");
					$this->tabs_gui->setBackTarget($this->lng->txt("back"),
						$ilCtrl->getParentReturn($this));
					$ret = $this->ctrl->forwardCommand($pc_question_gui);
				}
				else
				{
					$cmd = $this->ctrl->getCmd();
					$pc_question_gui->$cmd();
					$this->ctrl->redirectByClass(array("ilobjquestionpoolgui", get_class($cont_obj)), "editQuestion");
				}
				break;
					
			// Plugged Component
			case "ilpcpluggedgui":
				$this->tabs_gui->clearTargets();
				include_once ("./Services/COPage/classes/class.ilPCPluggedGUI.php");
				$plugged_gui = new ilPCPluggedGUI($this->page, $cont_obj, $hier_id,
					$add_type, $pc_id);
				$ret = $this->ctrl->forwardCommand($plugged_gui);
				break;

			default:
				
				// generic calls to gui classes
				include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
				if (ilCOPagePCDef::isPCGUIClassName($next_class, true))
				{
					$this->log->debug("Generic Call");
					$pc_def = ilCOPagePCDef::getPCDefinitionByGUIClassName($next_class);
					$this->tabs_gui->clearTargets();
					$this->tabs_gui->setBackTarget($this->page_gui->page_back_title,
						$ilCtrl->getLinkTarget($this->page_gui, "edit"));
					$ilHelp->setScreenIdComponent("copg_".$pc_def["pc_type"]);
					ilCOPagePCDef::requirePCGUIClassByName($pc_def["name"]);
					$gui_class_name = $pc_def["pc_gui_class"];
					$pc_gui = new $gui_class_name($this->page, $cont_obj, $hier_id, $pc_id);
					if ($pc_def["style_classes"])
					{
						$pc_gui->setStyleId($this->page_gui->getStyleId());
					}
					$pc_gui->setPageConfig($this->page_gui->getPageConfig());
					$ret = $this->ctrl->forwardCommand($pc_gui);
				}
				else
				{
					$this->log->debug("Call ilPageEditorGUI command.");
					// cmd belongs to ilPageEditorGUI	
					
					if ($cmd == "pasteFromClipboard")
					{
						$ret = $this->pasteFromClipboard($hier_id);
					}
					else if ($cmd == "paste")
					{
						$ret = $this->paste($hier_id);
					}
					else
					{
						$ret = $this->$cmd();
					}
				}
				break;

		}

		$this->log->debug("end ---");

		return $ret;
	}
	
	/**
	* checks if current user has activated js editing and
	* if browser is js capable
	*/
	static function _doJSEditing()
	{
		global $DIC;

		$ilUser = $DIC->user();

		if ($ilUser->getPref("ilPageEditor_JavaScript") != "disable"
			&& ilPageEditorGUI::_isBrowserJSEditCapable())
		{
			return true;
		}
		return false;
	}

	/**
	* checks wether browser is javascript editing capable
	*/
	static function _isBrowserJSEditCapable()
	{
		return true;
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
		$ilUser = $this->user;

		$ilUser->writePref("ilPageEditor_MediaMode", $_POST["media_mode"]);
		$ilUser->writePref("ilPageEditor_HTMLMode", $_POST["html_mode"]);
		if ($ilUser->getPref("ilPageEditor_JavaScript") != $_POST["js_mode"])
		{
			// not nice, should be solved differently in the future
			if ($this->page->getParentType() == "lm")
			{
				$this->ctrl->setParameterByClass("illmpageobjectgui", "reloadTree", "y");
			}
		}
		$ilUser->writePref("ilPageEditor_JavaScript", $_POST["js_mode"]);
		
		// again not so nice...
		if ($this->page->getParentType() == "lm")
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
		$ilUser = $this->user;
		
		ilUtil::sendSuccess($this->lng->txt("copied_to_clipboard"), true);
		$ilUser->addObjectToClipboard($_POST["mob_id"], "mob", ilObject::_lookupTitle($_POST["mob_id"]));
		$this->ctrl->returnToParent($this);
	}

	/**
	* copy linked media object to media pool
	*/
	function copyLinkedMediaToMediaPool()
	{
		$ilUser = $this->user;
		
		$this->ctrl->setParameterByClass("ilmediapooltargetselector", "mob_id", $_POST["mob_id"]); 
		$this->ctrl->redirectByClass("ilmediapooltargetselector", "listPools");
	}
	
	/**
	* add change comment to history
	*/
	function addChangeComment()
	{
		include_once("./Services/History/classes/class.ilHistory.php");
		ilHistory::_createEntry($this->page->getId(), "update",
			"", $this->page->getParentType().":pg",
			ilUtil::stripSlashes($_POST["change_comment"]), true);
		ilUtil::sendSuccess($this->lng->txt("cont_added_comment"), true);
		$this->ctrl->returnToParent($this);
	}

	/**
	 * Confirm
	 */
	function deleteSelected()
	{
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		$lng = $this->lng;

		$targets = explode(";", $_POST["target"][0]);

		if (count($targets) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$this->ctrl->returnToParent($this);
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("copg_confirm_el_deletion"));
			$cgui->setCancel($lng->txt("cancel"), "cancelDeleteSelected");
			$cgui->setConfirm($lng->txt("confirm"), "confirmedDeleteSelected");
			$cgui->addHiddenItem("target", $_POST["target"][0]);

			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	 * Cancel deletion
	 *
	 * @param
	 * @return
	 */
	function cancelDeleteSelected()
	{
		$this->ctrl->returnToParent($this);
	}


	/**
	 * Delete selected items
	 */
	function confirmedDeleteSelected()
	{
		$targets = explode(";", $_POST["target"]);
		if (count($targets) > 0)
		{
			$updated = $this->page->deleteContents($targets, true,
				$this->page_gui->getPageConfig()->getEnableSelfAssessment());
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
	 * Copy selected items
	 */
	function copySelected()
	{
		$lng = $this->lng;
		
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$this->page->copyContents($_POST["target"]);
			ilUtil::sendSuccess($lng->txt("cont_sel_el_copied_use_paste"), true);
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	 * Cut selected items
	 */
	function cutSelected()
	{
		$lng = $this->lng;
		
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$updated = $this->page->cutContents($_POST["target"]);
			if($updated !== true)
			{
				$_SESSION["il_pg_error"] = $updated;
			}
			else
			{
				unset($_SESSION["il_pg_error"]);
			}
			ilUtil::sendSuccess($lng->txt("cont_sel_el_cut_use_paste"), true);
		}
		$this->ctrl->returnToParent($this);
	}

	/**
	 * paste from clipboard (redirects to clipboard)
	 */
	function paste($a_hier_id)
	{
		$ilCtrl = $this->ctrl;
		$this->page->pasteContents($a_hier_id, $this->page_gui->getPageConfig()->getEnableSelfAssessment());
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		//ilEditClipboard::setAction("");
		$this->ctrl->returnToParent($this);
	}

	/**
	* (de-)activate selected items
	*/
	function activateSelected()
	{
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$updated = $this->page->switchEnableMultiple($_POST["target"], true,
				$this->page_gui->getPageConfig()->getEnableSelfAssessment());
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
	* Assign characeristic to text blocks/sections
	*/
	function assignCharacteristicForm()
	{
		$tpl = $this->tpl;
		$lng = $this->lng;
		
		if (is_int(strpos($_POST["target"][0], ";")))
		{
			$_POST["target"] = explode(";", $_POST["target"][0]);
		}
		if (is_array($_POST["target"]))
		{
			$types = array();
			
			// check what content element types have been selected
			foreach ($_POST["target"] as $t)
			{
				$tarr = explode(":", $t);
				$cont_obj = $this->page->getContentObject($tarr[0], $tarr[1]);
				if (is_object($cont_obj) && $cont_obj->getType() == "par")
				{
					$types["par"] = "par";
				}
				if (is_object($cont_obj) && $cont_obj->getType() == "sec")
				{
					$types["sec"] = "sec";
				}
			}
		
			if (count($types) == 0)
			{
				ilUtil::sendFailure($lng->txt("cont_select_par_or_section"), true);
				$this->ctrl->returnToParent($this);
			}
			else
			{
				$this->initCharacteristicForm($_POST["target"], $types);
				$tpl->setContent($this->form->getHTML());
			}
		}
		else
		{
			$this->ctrl->returnToParent($this);
		}
	}

	/**
	 * Init map creation/update form
	 */
	function initCharacteristicForm($a_target, $a_types)
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		
		
		// edit form
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTitle($this->lng->txt("cont_choose_characteristic"));
		
		if ($a_types["par"] == "par")
		{
			$select_prop = new ilSelectInputGUI($this->lng->txt("cont_choose_characteristic_text"),
				"char_par");
			include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
			$options = ilPCParagraphGUI::_getCharacteristics($this->page_gui->getStyleId());
			$select_prop->setOptions($options);
			$this->form->addItem($select_prop);
		}
		if ($a_types["sec"] == "sec")
		{
			$select_prop = new ilSelectInputGUI($this->lng->txt("cont_choose_characteristic_section"),
				"char_sec");
			include_once("./Services/COPage/classes/class.ilPCSectionGUI.php");
			$options = ilPCSectionGUI::_getCharacteristics($this->page_gui->getStyleId());
			$select_prop->setOptions($options);
			$this->form->addItem($select_prop);
		}
		
		foreach ($a_target as $t)
		{
			$hidden = new ilHiddenInputGUI("target[]");
			$hidden->setValue($t);
			$this->form->addItem($hidden);
		}

		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$this->form->addCommandButton("assignCharacteristic", $lng->txt("save"));
		$this->form->addCommandButton("showPage", $lng->txt("cancel"));

	}

	/**
	* Assign characteristic
	*/
	function assignCharacteristic()
	{
		$char_par = ilUtil::stripSlashes($_POST["char_par"]);
		$char_sec = ilUtil::stripSlashes($_POST["char_sec"]);
		if (is_array($_POST["target"]))
		{
			foreach ($_POST["target"] as $t)
			{
				$tarr = explode(":", $t);
				$cont_obj = $this->page->getContentObject($tarr[0], $tarr[1]);
				if (is_object($cont_obj) && $cont_obj->getType() == "par")
				{
					$cont_obj->setCharacteristic($char_par);
				}
				if (is_object($cont_obj) && $cont_obj->getType() == "sec")
				{
					$cont_obj->setCharacteristic($char_sec);
				}
			}
			$updated = $this->page->update();
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
		$ilCtrl = $this->ctrl;
//var_dump($a_hier_id);
		$ilCtrl->setParameter($this, "hier_id", $a_hier_id);
		$ilCtrl->setParameterByClass("ilEditClipboardGUI", "returnCommand",
			rawurlencode($ilCtrl->getLinkTarget($this,
			"insertFromClipboard", "", false, false)));
//echo ":".$ilCtrl->getLinkTarget($this, "insertFromClipboard").":";
		$ilCtrl->redirectByClass("ilEditClipboardGUI", "getObject");
	}

	/**
	* insert object from clipboard
	*/
	function insertFromClipboard()
	{
		include_once("./Services/Clipboard/classes/class.ilEditClipboardGUI.php");
		$ids = ilEditClipboardGUI::_getSelectedIDs();
		include_once ("./Services/COPage/classes/class.ilPCMediaObject.php");
		if ($ids != "")
		{
			foreach ($ids as $id2)
			{
				$id = explode(":", $id2);
				$type = $id[0];
				$id = $id[1];
				if ($type == "mob")
				{
					$this->content_obj = new ilPCMediaObject($this->page);
					$this->content_obj->readMediaObject($id);
					$this->content_obj->createAlias($this->page, $_GET["hier_id"]);
					$this->updated = $this->page->update();
				}
				if ($type == "incl")
				{
					include_once("./Services/COPage/classes/class.ilPCContentInclude.php");
					$this->content_obj = new ilPCContentInclude($this->page);
					$this->content_obj->create($this->page, $_GET["hier_id"]);
					$this->content_obj->setContentType("mep");
					$this->content_obj->setContentId($id);
					$this->updated = $this->page->update();
				}
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
		/*if(is_object($this->locator))
		{
			$this->locator->display();
		}*/
	}

	/**
	* Show snippet info
	*/
	function showSnippetInfo()
	{
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ilAccess = $this->access;
		$ilCtrl = $this->ctrl;
		
		$stpl = new ilTemplate("tpl.snippet_info.html", true, true, "Services/COPage");
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
		$mep_pools = ilMediaPoolItem::getPoolForItemId($_POST["ci_id"]);
		foreach ($mep_pools as $mep_id)
		{
			$ref_ids = ilObject::_getAllReferences($mep_id);
			$edit_link = false;
			foreach ($ref_ids as $rid)
			{
				if (!$edit_link && $ilAccess->checkAccess("write", "", $rid))
				{
					$stpl->setCurrentBlock("edit_link");
					$stpl->setVariable("TXT_EDIT", $lng->txt("edit"));
					$stpl->setVariable("HREF_EDIT",
						"./goto.php?target=mep_".$rid);
					$stpl->parseCurrentBlock();
				}
			}
			$stpl->setCurrentBlock("pool");
			$stpl->setVariable("TXT_MEDIA_POOL", $lng->txt("obj_mep"));
			$stpl->setVariable("VAL_MEDIA_POOL", ilObject::_lookupTitle($mep_id));
			$stpl->parseCurrentBlock();
		}
		
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
		$stpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$stpl->setVariable("VAL_TITLE", ilMediaPoolPage::lookupTitle($_POST["ci_id"]));
		$stpl->setVariable("TXT_BACK", $lng->txt("back"));
		$stpl->setVariable("HREF_BACK",
			$ilCtrl->getLinkTarget($this->page_gui, "edit"));
		$tpl->setContent($stpl->get());
	}
	
}
?>
