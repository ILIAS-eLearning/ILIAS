<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModuleGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Export.php");
include_once("./Services/Style/classes/class.ilObjStyleSheetGUI.php");
include_once("./Services/Style/classes/class.ilPageLayout.php");

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
* $Id: class.ilObjSCORMLearningModuleGUI.php 13133 2007-01-30 11:13:06Z akill $
*
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilInfoScreenGUI, ilSCORM2004ChapterGUI, ilSCORM2004SeqChapterGUI, ilSCORM2004PageNodeGUI, ilSCORM2004ScoGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilCertificateGUI, ilObjStyleSheetGUI, ilNoteGUI, ilSCORM2004AssetGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilLicenseGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjSCORM2004LearningModuleGUI: ilSCORM2004TrackingItemsPerScoFilterGUI, ilSCORM2004TrackingItemsPerUserFilterGUI, ilSCORM2004TrackingItemsTableGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORM2004LearningModuleGUI extends ilObjSCORMLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjSCORM2004LearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$lng->loadLanguageModule("sahs");
		$lng->loadLanguageModule("search");
		$lng->loadLanguageModule("exp");
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		#$this->tabs_gui =& new ilTabsGUI();
	}

	/**
	 * execute command
	 */
	function executeCommand()
	{
		global $ilAccess, $ilCtrl, $tpl, $ilTabs, $lng;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		if ($this->object->getEditable() && $cmd != "showEditTree")	// show editing frameset
		{
			$this->showEditTree();
		}

		// update expander
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		$form_gui = new ilSCORM2004OrganizationHFormGUI();
		$form_gui->setTree($this->getEditTree());
		$form_gui->updateExpanded();
		switch($next_class)
		{
			// notes
			case "ilnotegui":
				$this->getTemplate();
				$this->setLocator();
				$this->setTabs();
				switch($_GET["notes_mode"])
				{
					default:
						$ilTabs->setTabActive("sahs_organization");
						return $this->showOrganization();
				}
				break;

			// chapters
			case "ilscorm2004chaptergui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004ChapterGUI.php");
				$chap_gui = new ilSCORM2004ChapterGUI($this->object, $_GET["obj_id"]);
				$chap_gui->setParentGUI($this);
				return $ilCtrl->forwardCommand($chap_gui);

				// sequencing chapters
			case "ilscorm2004seqchaptergui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004SeqChapterGUI.php");
				$chap_gui = new ilSCORM2004SeqChapterGUI($this->object, $_GET["obj_id"]);
				$chap_gui->setParentGUI($this);
				return $ilCtrl->forwardCommand($chap_gui);

				// scos
			case "ilscorm2004scogui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004ScoGUI.php");
				$sco_gui = new ilSCORM2004ScoGUI($this->object, $_GET["obj_id"]);
				$sco_gui->setParentGUI($this);
				return $ilCtrl->forwardCommand($sco_gui);

			// assets
			case "ilscorm2004assetgui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004AssetGUI.php");
				$ass_gui = new ilSCORM2004AssetGUI($this->object, $_GET["obj_id"]);
				$ass_gui->setParentGUI($this);
				return $ilCtrl->forwardCommand($ass_gui);

				// pages
			case "ilscorm2004pagenodegui":
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNodeGUI.php");
				$page_gui = new ilSCORM2004PageNodeGUI($this->object, $_GET["obj_id"]);
				$page_gui->setParentGUI($this);
				$ilCtrl->forwardCommand($page_gui);
				break;

			default:										
				parent::executeCommand();
				$this->addHeaderAction();
				break;
		}					
	}

	/**
	 * Show tree
	 *
	 * @param
	 * @return
	 */
	function showEditTree()
	{
		global $tpl;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004EditorExplorerGUI.php");
		$exp = new ilSCORM2004EditorExplorerGUI($this, "showEditTree", $this->object);
		if (!$exp->handleCommand())
		{
			$tpl->setLeftNavContent($exp->getHTML());
		}
	}
	
	
	/**
	 * Edit organization (called from listgui, must setup frameset)
	 *
	 * @param
	 * @return
	 */
	function editOrganization($a_to_organization = true)
	{
		if ($_GET["obj_id"] > 0)
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
			$type = ilSCORM2004Node::_lookupType($_GET["obj_id"]);
		}
		if (in_array($type, array("sco", "chap", "seqc", "page")))
		{
			$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
			$this->ctrl->redirect($this, "jumpToNode");
		}
		else
		{
			if ($a_to_organization)
			{
				$this->ctrl->redirect($this, "showOrganization");
			}
			else
			{
				$this->ctrl->redirect($this, "properties");
			}
		}

	}

	/**
	 * output main frameset of media pool
	 * left frame: explorer tree of folders
	 * right frame: media pool content
	 */
	function frameset($a_to_organization = false)
	{
		if ($this->object->getEditable())	// show editing frameset
		{
$this->ctrl->redirect($this, "properties");
			include_once("./Services/Frameset/classes/class.ilFramesetGUI.php");
			$fs_gui = new ilFramesetGUI();
			$fs_gui->setFramesetTitle($this->lng->txt("editor"));
			$fs_gui->setMainFrameName("content");
			$fs_gui->setSideFrameName("tree");
			$this->ctrl->setParameter($this, "active_node", $_GET["obj_id"]);
			$fs_gui->setSideFrameSource($this->ctrl->getLinkTarget($this, "showTree"));
			$this->ctrl->setParameter($this, "activeNode", "");
			if ($_GET["obj_id"] > 0)
			{
				include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
				$type = ilSCORM2004Node::_lookupType($_GET["obj_id"]);
			}
			if (in_array($type, array("sco", "chap", "seqc", "page")))
			{
				$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
				$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, "jumpToNode"));
			}
			else
			{
				if ($a_to_organization)
				{
					$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, "showOrganization"));
				}
				else
				{
					$fs_gui->setMainFrameSource($this->ctrl->getLinkTarget($this, "properties"));
				}
			}
			$fs_gui->show();
			exit;
		}
		else						// otherwise show standard frameset
		{
			$this->tpl = new ilTemplate("tpl.sahs_edit_frameset.html", false, false, "Modules/ScormAicc");
			$this->tpl->setVariable("SRC",
			$this->ctrl->getLinkTarget($this, "properties"));
			$this->tpl->show("DEFAULT", false);
		}
		exit;
	}

	function jumpToNode($a_anchor_node = "", $a_highlight_ids = "")
	{
		global $ilCtrl;
		
		$anchor = ($a_anchor_node != "")
			? "node_".$a_anchor_node
			: "";
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		$type = ilSCORM2004Node::_lookupType($_GET["obj_id"]);
		$ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		switch($type)
		{
			case "sco":
				$ilCtrl->setParameterByClass("ilscorm2004scogui", "highlight", $a_highlight_ids);
				$ilCtrl->redirectByClass("ilscorm2004scogui", "showOrganization", $anchor);
			case "ass":
				$ilCtrl->setParameterByClass("ilscorm2004assetgui", "highlight", $a_highlight_ids);
				$ilCtrl->redirectByClass("ilscorm2004assetgui", "showOrganization", $anchor);
			case "chap":
				$ilCtrl->setParameterByClass("ilscorm2004chaptergui", "highlight", $a_highlight_ids);
				$ilCtrl->redirectByClass("ilscorm2004chaptergui", "showOrganization", $anchor);
			case "seqc":
				$ilCtrl->setParameterByClass("ilscorm2004seqchaptergui", "highlight", $a_highlight_ids);
				$ilCtrl->redirectByClass("ilscorm2004seqchaptergui", "showOrganization", $anchor);
			case "page":
				$ilCtrl->redirectByClass("ilscorm2004pagenodegui", "edit");
		}
	}

	/**
	 * Scorm 2004 module properties
	 */
	function properties()
	{
		global $rbacsystem, $tree, $tpl, $lng, $ilToolbar, $ilCtrl, $ilSetting, $ilTabs;

		$this->setSubTabs("settings", "general_settings");
		
		$lng->loadLanguageModule("style");

		// not editable
		if ($this->object->editable != 1)
		{
			ilObjSAHSLearningModuleGUI::setSettingsSubTabs();
			$ilTabs->setSubTabActive('cont_settings');
			// view
			$ilToolbar->addButton($this->lng->txt("view"),
				"ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=".$this->object->getRefID(),
				"_blank");
		}
		else  	// editable
		{
			// glossary buttons to toolbar
			$sep = false;
			if (ilObject::_lookupType($this->object->getAssignedGlossary()) != "glo")
			{
				$parent_ref_id = $tree->getParentId((int) $_GET["ref_id"]);
				if ($rbacsystem->checkAccess("create", $parent_ref_id, "glo"))
				{
					$ilToolbar->addButton($this->lng->txt("cont_glo_create"),
						$ilCtrl->getLinkTarget($this, "createGlossary"));
				}
				$ilToolbar->addButton($this->lng->txt("cont_glo_assign"),
					$ilCtrl->getLinkTarget($this, "assignGlossary"));
			}
			else
			{
				$ilToolbar->addButton($this->lng->txt("cont_glo_detach"),
					$ilCtrl->getLinkTarget($this, "detachGlossary"));
			}

			// style buttons to toolbar
			$fixed_style = $ilSetting->get("fixed_content_style_id");
			$style_id = $this->object->getStyleSheetId();

			if ($fixed_style == 0)
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
						$ilToolbar->addSeparator();
						
						// delete command
						$ilToolbar->addButton($this->lng->txt("cont_edit_style"),
							$ilCtrl->getLinkTarget($this, "editStyle"));
						$ilToolbar->addButton($this->lng->txt("cont_delete_style"),
							$ilCtrl->getLinkTarget($this, "deleteStyle"));
					}
				}
	
				if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id))
				{
					$ilToolbar->addSeparator();
					
					$ilToolbar->addButton($this->lng->txt("sty_create_ind_style"),
						$ilCtrl->getLinkTarget($this, "createStyle"));
				}
			}
		}
		
		// output forms
		if ($this->object->editable != 1)
		{
			$this->initPropertiesForm();
			$tpl->setContent($this->form->getHTML());
		}
		else
		{
			$this->initPropertiesEditableForm();
			$this->getPropertiesEditableValues();
			$tpl->setContent($this->form->getHTML());
		}
	}
	
	/**
	 * Initialize properties form
	 *
	 * @param
	 * @return
	 */
	function initPropertiesForm()
	{
		global $lng, $ilCtrl;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$this->form->setTitle($this->lng->txt("cont_lm_properties"));

		// SCORM-type
		$ne = new ilNonEditableValueGUI($this->lng->txt("type"), "");
		$ne->setValue($this->lng->txt( "lm_type_" . ilObjSAHSLearningModule::_lookupSubType( $this->object->getID() ) ) );
		$this->form->addItem($ne);

		// version
		$ne = new ilNonEditableValueGUI($this->lng->txt("cont_sc_version"), "");
		$ne->setValue($this->object->getModuleVersion());
		$this->form->addItem($ne);
		
		// online
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_online"), "cobj_online");
		$cb->setValue("y");
		if ($this->object->getOnline())
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);

		// offline Mode
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_offline_mode_allow"), "cobj_offline_mode");
		$cb->setValue("y");
		$cb->setChecked($this->object->getOfflineMode());
		include_once("./Modules/ScormAicc/classes/class.ilSCORMOfflineMode.php");
		if ($this->object->getOfflineMode()== true && ilSCORMOfflineMode::checkIfAnyoneIsInOfflineMode($this->object->getID()) == true) {
			$cb->setDisabled(true);
			$cb->setInfo($this->lng->txt("cont_offline_mode_disable_not_allowed_info"));
		} else {
			$cb->setInfo($this->lng->txt("cont_offline_mode_allow_info"));
		}
		$this->form->addItem($cb);

		//
		// presentation
		//
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt("cont_presentation"));
		$this->form->addItem($sh);
		
		// display mode (open)
		$options = array(
			"0" => $this->lng->txt("cont_open_normal"),
			"1" => $this->lng->txt("cont_open_iframe_max"),
			"2" => $this->lng->txt("cont_open_iframe_defined"),
			"5" => $this->lng->txt("cont_open_window_undefined"),
			"6" => $this->lng->txt("cont_open_window_defined")
			);
		$si = new ilSelectInputGUI($this->lng->txt("cont_open"), "open_mode");
		$si->setOptions($options);
		$si->setValue($this->object->getOpenMode());
		$this->form->addItem($si);
		
		// width
		$ni = new ilNumberInputGUI($this->lng->txt("cont_width"), "width");
		$ni->setMaxLength(4);
		$ni->setSize(4);
		$ni->setValue($this->object->getWidth());
		$this->form->addItem($ni);
		
		// height
		$ni = new ilNumberInputGUI($this->lng->txt("cont_height"), "height");
		$ni->setMaxLength(4);
		$ni->setSize(4);
		$ni->setValue($this->object->getHeight());
		$this->form->addItem($ni);
		
		// disable top menu
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_nomenu"), "cobj_nomenu");
		$cb->setValue("y");
		$cb->setChecked($this->object->getNoMenu());
		$this->form->addItem($cb);
		
		// disable left-side navigation
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_hidenavig"), "cobj_hidenavig");
		$cb->setValue("y");
		$cb->setChecked($this->object->getHideNavig());
		$this->form->addItem($cb);
		
		// auto navigation to last visited item
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_last_visited"), "cobj_auto_last_visited");
		$cb->setValue("y");
		$cb->setChecked($this->object->getAuto_last_visited());
		$cb->setInfo($this->lng->txt("cont_auto_last_visited_info"));
		$this->form->addItem($cb);

		// set IE compatibility mode
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_ie_compatibility"), "cobj_ie_compatibility");
		$cb->setValue("y");
		$cb->setChecked($this->object->getIe_compatibility());
		$cb->setInfo($this->lng->txt("cont_ie_compatibility_info"));
		$this->form->addItem($cb);

		// force IE to render again
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_ie_force_render"), "cobj_ie_force_render");
		$cb->setValue("y");
		$cb->setChecked($this->object->getIe_force_render());
		$cb->setInfo($this->lng->txt("cont_ie_force_render_info"));
		$this->form->addItem($cb);

		//
		// scorm options
		//
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt("cont_scorm_options"));
		$this->form->addItem($sh);

		// max attempts
		$ni = new ilNumberInputGUI($this->lng->txt("cont_sc_max_attempt"), "max_attempt");
		$ni->setMaxLength(3);
		$ni->setSize(3);
		$ni->setValue($this->object->getMaxAttempt());
		$this->form->addItem($ni);
		
		// lesson mode
		$options = array("normal" => $this->lng->txt("cont_sc_less_mode_normal"),
				"browse" => $this->lng->txt("cont_sc_less_mode_browse"));
		$si = new ilSelectInputGUI($this->lng->txt("cont_def_lesson_mode"), "lesson_mode");
		$si->setOptions($options);
		$si->setValue($this->object->getDefaultLessonMode());
		$this->form->addItem($si);
		
		// credit mode
		$options = array("credit" => $this->lng->txt("cont_credit_on"),
			"no_credit" => $this->lng->txt("cont_credit_off"));
		$si = new ilSelectInputGUI($this->lng->txt("cont_credit_mode"), "credit_mode");
		$si->setOptions($options);
		$si->setValue($this->object->getCreditMode());
		$si->setInfo($this->lng->txt("cont_credit_mode_info"));
		$this->form->addItem($si);
		
		// set lesson mode review when completed
		$options = array(
			"n" => $this->lng->txt("cont_sc_auto_review_no"),
			"r" => $this->lng->txt("cont_sc_auto_review_completed_not_failed_or_passed"),
			"p" => $this->lng->txt("cont_sc_auto_review_passed"),
			"q" => $this->lng->txt("cont_sc_auto_review_passed_or_failed"),
			"c" => $this->lng->txt("cont_sc_auto_review_completed"),
			"d" => $this->lng->txt("cont_sc_auto_review_completed_and_passed"),
			"y" => $this->lng->txt("cont_sc_auto_review_completed_or_passed"),
			);
		$si = new ilSelectInputGUI($this->lng->txt("cont_sc_auto_review_2004"), "auto_review");
		$si->setOptions($options);
		$si->setValue($this->object->getAutoReviewChar());
		$si->setInfo($this->lng->txt("cont_sc_auto_review_info_2004"));
		$this->form->addItem($si);

		//
		// rte settings
		//
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt("cont_rte_settings"));
		$this->form->addItem($sh);
		
		// unlimited session timeout
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_sc_usession"), "cobj_session");
		$cb->setValue("y");
		$cb->setChecked($this->object->getSession());
		$cb->setInfo($this->lng->txt("cont_sc_usession_info"));
		$this->form->addItem($cb);
		
		// SCORM 2004 fourth edition features
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_fourth_edition"), "cobj_fourth_edition");
		$cb->setValue("y");
		$cb->setChecked($this->object->getFourth_edition());
		$cb->setInfo($this->lng->txt("cont_fourth_edition_info"));
		$this->form->addItem($cb);
		
		// sequencing
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_sequencing"), "cobj_sequencing");
		$cb->setValue("y");
		$cb->setChecked($this->object->getSequencing());
		$cb->setInfo($this->lng->txt("cont_sequencing_info"));
		$this->form->addItem($cb);
		
		// storage of interactions
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_interactions"), "cobj_interactions");
		$cb->setValue("y");
		$cb->setChecked($this->object->getInteractions());
		$this->form->addItem($cb);
		
		// objectives
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_objectives"), "cobj_objectives");
		$cb->setValue("y");
		$cb->setChecked($this->object->getObjectives());
		$this->form->addItem($cb);

		// comments
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_comments"), "cobj_comments");
		$cb->setValue("y");
		$cb->setChecked($this->object->getComments());
		$this->form->addItem($cb);

		// time from lms
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_time_from_lms"), "cobj_time_from_lms");
		$cb->setValue("y");
		$cb->setChecked($this->object->getTime_from_lms());
		$cb->setInfo($this->lng->txt("cont_time_from_lms_info"));
		$this->form->addItem($cb);

		// check values
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_check_values"), "cobj_check_values");
		$cb->setValue("y");
		$cb->setChecked($this->object->getCheck_values());
		$this->form->addItem($cb);

		// auto cmi.exit to suspend
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_auto_suspend"), "cobj_auto_suspend");
		$cb->setValue("y");
		$cb->setChecked($this->object->getAutoSuspend());
		$cb->setInfo($this->lng->txt("cont_auto_suspend_info"));
		$this->form->addItem($cb);

		//
		// debugging
		//
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt("cont_debugging"));
		$this->form->addItem($sh);

		// test tool
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_debug"), "cobj_debug");
		$cb->setValue("y");
		$cb->setChecked($this->object->getDebug());
		if ($this->object->getDebugActivated() == false)
		{
			$cb->setDisabled(true);
			$cb->setInfo($this->lng->txt("cont_debug_deactivated"));
		}
		else
		{
			$cb->setInfo($this->lng->txt("cont_debug_deactivate"));
		}
		$this->form->addItem($cb);
		$this->form->addCommandButton("saveProperties", $lng->txt("save"));
	}
	
	
	/**
	 * Init properties (editable) form.
	 */
	public function initPropertiesEditableForm()
	{
		global $lng, $ilCtrl, $tree, $rbacsystem, $ilSetting;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// localization
		$options = array(
			"" => $lng->txt("please_select"),
			);
		$langs = $lng->getInstalledLanguages();
		$lng->loadLanguageModule("meta");
		foreach ($langs as $l)
		{
			$options[$l] = $lng->txt("meta_l_".$l);
		}
		$loc = new ilSelectInputGUI($this->lng->txt("cont_localization"), "localization");
		$loc->setOptions($options);
		$loc->setInfo($this->lng->txt("cont_localization_info"));
		$this->form->addItem($loc);

		// glossary
		$ne = new ilNonEditableValueGUI($lng->txt("obj_glo"), "glossary");
		$this->form->addItem($ne);
		
		// style
		$lng->loadLanguageModule("style");
		$fixed_style = $ilSetting->get("fixed_content_style_id");
		$style_id = $this->object->getStyleSheetId();

		if ($fixed_style > 0)
		{
			$st = new ilNonEditableValueGUI($lng->txt("cont_current_style"));
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
					$st = new ilNonEditableValueGUI($lng->txt("cont_current_style"));
					$st->setValue(ilObject::_lookupTitle($style_id));
					$this->form->addItem($st);
				}
			}

			if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id))
			{
				$style_sel = ilUtil::formSelect ($style_id, "style_id",
					$st_styles, false, true);
				$style_sel = new ilSelectInputGUI($lng->txt("cont_current_style"), "style_id");
				$style_sel->setOptions($st_styles);
				$style_sel->setValue($style_id);
				$this->form->addItem($style_sel);
			}
		}
		
		// number of tries
		$ni = new ilNumberInputGUI($lng->txt("cont_qtries"), "q_tries");
		$ni->setInfo($lng->txt("cont_qtries_info")); // #15133
		$ni->setMaxLength(3);
		$ni->setSize(3);
		$this->form->addItem($ni);
		

		$this->form->addCommandButton("saveProperties", $lng->txt("save"));

		$this->form->setTitle($lng->txt("cont_scorm_ed_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Get current values for properties (editable) from 
	 */
	public function getPropertiesEditableValues()
	{
		$values = array();
	
		if (ilObject::_lookupType($this->object->getAssignedGlossary()) == "glo")
		{
			$values["glossary"] = ilObject::_lookupTitle($this->object->getAssignedGlossary());
		}
		else
		{
			$values["glossary"] = $this->lng->txt("cont_no_glossary");
		}
		$values["q_tries"] = $this->object->getTries();
		$values["localization"] = $this->object->getLocalization();
		$values["style_id"] = $this->object->getStyleSheetId();
	
		$this->form->setValuesByArray($values);
	}
	
	/**
	* save scorm 2004 module properties
	*/
	function saveProperties()
	{
		global $ilSetting;
		
		if ($this->object->editable != 1)
		{
			//check if OfflineMode-Zip has to be created
			$tmpOfflineMode= ilUtil::yn2tf($_POST["cobj_offline_mode"]);
			$tmpFourth_edition = ilUtil::yn2tf($_POST["cobj_fourth_edition"]);
			$tmpSequencing = ilUtil::yn2tf($_POST["cobj_sequencing"]);
			if ($tmpOfflineMode == true) {
//				$tmpSequencing = false; //actually no sequencing for offline_mode
				$tmpFourth_edition = false; //4th edition is not possible
				if ($this->object->getOfflineMode() == false) {
					$this->object->zipLmForOfflineMode();
				}
			}

			$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
			$this->object->setOpenMode($_POST["open_mode"]);
			$this->object->setWidth($_POST["width"]);
			$this->object->setHeight($_POST["height"]);
			$this->object->setCreditMode($_POST["credit_mode"]);
			$this->object->setMaxAttempt($_POST["max_attempt"]);
			$this->object->setAutoReviewChar($_POST["auto_review"]);
			$this->object->setDefaultLessonMode($_POST["lesson_mode"]);
			$this->object->setSession(ilUtil::yn2tf($_POST["cobj_session"]));
			$this->object->setNoMenu(ilUtil::yn2tf($_POST["cobj_nomenu"]));
			$this->object->setHideNavig(ilUtil::yn2tf($_POST["cobj_hidenavig"]));
			$this->object->setAuto_last_visited(ilUtil::yn2tf($_POST["cobj_auto_last_visited"]));
			$this->object->setIe_compatibility(ilUtil::yn2tf($_POST["cobj_ie_compatibility"]));
			$this->object->setIe_force_render(ilUtil::yn2tf($_POST["cobj_ie_force_render"]));
			$this->object->setFourth_edition($tmpFourth_edition);
			$this->object->setSequencing($tmpSequencing);
			$this->object->setInteractions(ilUtil::yn2tf($_POST["cobj_interactions"]));
			$this->object->setObjectives(ilUtil::yn2tf($_POST["cobj_objectives"]));
			$this->object->setComments(ilUtil::yn2tf($_POST["cobj_comments"]));
			$this->object->setTime_from_lms(ilUtil::yn2tf($_POST["cobj_time_from_lms"]));
			$this->object->setCheck_values(ilUtil::yn2tf($_POST["cobj_check_values"]));
			$this->object->setAutoSuspend(ilUtil::yn2tf($_POST["cobj_auto_suspend"]));
			$this->object->setOfflineMode($tmpOfflineMode);
			$this->object->setDebug(ilUtil::yn2tf($_POST["cobj_debug"]));
			//$this->object->setDebugPw($_POST["debug_pw"]);

		}
		else
		{
			$this->initPropertiesEditableForm();
			if ($this->form->checkInput())
			{
				$this->object->setTries($_POST["q_tries"]);
				$this->object->setLocalization($_POST["localization"]);
				
				if ($ilSetting->get("fixed_content_style_id") <= 0 &&
					(ilObjStyleSheet::_lookupStandard($this->object->getStyleSheetId())
					|| $this->object->getStyleSheetId() == 0))
				{
					$this->object->setStyleSheetId(ilUtil::stripSlashes($_POST["style_id"]));
				}
			}
		}
		$this->object->update();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "properties");
	}

	/**
	 * Detach glossary
	 */
	function detachGlossary()
	{
		global $ilCtrl;
		
		$this->object->setAssignedGlossary(0);
		$this->object->update();
		$ilCtrl->redirect($this, "properties");
	}
	
	/**
	 * Create glossary
	 */
	function createGlossary()
	{
		global $tpl;
	
		$this->initGlossaryCreationForm();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Init glossary creation form.
	 */
	public function initGlossaryCreationForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($lng->txt("desc"), "description");
		$this->form->addItem($ta);
		
		$this->form->addCommandButton("saveGlossary", $lng->txt("save"));
		$this->form->addCommandButton("properties", $lng->txt("cancel"));
	                
		$this->form->setTitle($lng->txt("cont_glo_create"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	 * Save glossary form
	 */
	public function saveGlossary()
	{
		global $tpl, $lng, $ilCtrl, $rbacsystem, $tree;
	
		$parent_ref_id = $tree->getParentId((int) $_GET["ref_id"]);
		if (!$rbacsystem->checkAccess("create", $parent_ref_id, "glo"))
		{
			ilUtil::sendFailure($lng->txt("no_permission"), true);
			$ilCtrl->redirect($this, "properties");
		}
		
		$this->initGlossaryCreationForm();
		if ($this->form->checkInput())
		{
			include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
			$newObj = new ilObjGlossary();
			$newObj->setType("glo");
			$newObj->setTitle($_POST["title"]);
			$newObj->setDescription($_POST["description"]);
			$newObj->setVirtualMode("none");
			$newObj->create();
			$newObj->createReference();
			$newObj->putInTree($parent_ref_id);
			$newObj->setPermissions($parent_ref_id);
			$newObj->notify("new",$parent_ref_id,$_GET["parent_non_rbac_id"],$parent_ref_id,$newObj->getRefId());
			
			// perform save
			$this->object->setAssignedGlossary($newObj->getId());
			$this->object->update();
			
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "properties");
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	/**
	 * Assign glossary
	 */
	function assignGlossary()
	{
		global $tpl, $ilCtrl, $tree;
		
		include_once("./Modules/Scorm2004/classes/class.ilGlossarySelectorGUI.php");
		$exp = new ilGlossarySelectorGUI(
			$ilCtrl->getLinkTarget($this, "selectGlossary"), "ilobjscorm2004learningmodulegui");
		$exp->setSelectableTypes(array("glo"));
		
		if ($_GET["expand"] == "")
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}
		$exp->setExpand($expanded);
		
		$exp->setTargetGet("glo_id");
		//$this->ctrl->setParameter($this, "target_type", $a_type);
		//$ilCtrl->setParameter($this, "subCmd", "insertFromRepository");
		$exp->setParamsGet($this->ctrl->getParameterArray($this, "assignGlossary"));
		
		// filter
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("fold");
		$exp->addFilter("crs");
		$exp->addFilter("glo");

		$exp->setOutput(0);

		$tpl->setContent($exp->getOutput());	
	}

	/**
	 * Select glossary
	 */
	function selectGlossary()
	{
		global $ilCtrl;
		
		$this->object->setAssignedGlossary(ilObject::_lookupObjId((int) $_GET["glo_ref_id"]));
		$this->object->update();
		$ilCtrl->redirect($this, "properties");
	}
	
	/**
	* assign scorm object to scorm gui object
	*/
	function assignObject()
	{
		if ($this->id != 0)
		{
			if ($this->call_by_reference)
			{
				$this->object =& new ilObjSCORM2004LearningModule($this->id, true);
			}
			else
			{
				$this->object =& new ilObjSCORM2004LearningModule($this->id, false);
			}
		}
	}

	/**
	 * Edit Stlye Properties
	 */
	function editStyleProperties()
	{
		global $tpl;
		
		$this->initStylePropertiesForm();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Init style properties form
	 */
	function initStylePropertiesForm()
	{
		global $ilCtrl, $lng, $ilTabs, $ilSetting;
		
		$lng->loadLanguageModule("style");
		$this->setSubTabs("settings", "style");
		$ilTabs->setTabActive("settings");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$fixed_style = $ilSetting->get("fixed_content_style_id");
		$style_id = $this->object->getStyleSheetId();

		if ($fixed_style > 0)
		{
			$st = new ilNonEditableValueGUI($lng->txt("cont_current_style"));
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
					$st = new ilNonEditableValueGUI($lng->txt("cont_current_style"));
					$st->setValue(ilObject::_lookupTitle($style_id));
					$this->form->addItem($st);

//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "edit"));

					// delete command
					$this->form->addCommandButton("editStyle",
						$lng->txt("cont_edit_style"));
					$this->form->addCommandButton("deleteStyle",
						$lng->txt("cont_delete_style"));
//$this->ctrl->getLinkTargetByClass("ilObjStyleSheetGUI", "delete"));
				}
			}

			if ($style_id <= 0 || ilObjStyleSheet::_lookupStandard($style_id))
			{
				$style_sel = ilUtil::formSelect ($style_id, "style_id",
					$st_styles, false, true);
				$style_sel = new ilSelectInputGUI($lng->txt("cont_current_style"), "style_id");
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
		$this->form->setTitle($lng->txt("cont_style"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	 * Create Style
	 */
	function createStyle()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "create");
	}
	
	/**
	 * Edit Style
	 */
	function editStyle()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "edit");
	}

	/**
	 * Delete Style
	 */
	function deleteStyle()
	{
		global $ilCtrl;

		$ilCtrl->redirectByClass("ilobjstylesheetgui", "delete");
	}
	
	/**
	 * Save style settings
	 */
	function saveStyleSettings()
	{
		global $ilSetting;
	
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
* show tracking data
*/
protected function showTrackingItemsBySco()
{
	global $ilTabs;


	ilObjSCORMLearningModuleGUI::setSubTabs();
	$ilTabs->setTabActive("cont_tracking_data");
	$ilTabs->setSubTabActive("cont_tracking_bysco");

	$reports = array('exportSelectedCore','exportSelectedInteractions','exportSelectedObjectives','tracInteractionItem','tracInteractionUser','tracInteractionUserAnswers');

	$scoSelected = "all";
	if (isset($_GET["scoSelected"])) $scoSelected = ilUtil::stripSlashes($_GET["scoSelected"]);
	if (isset($_POST["scoSelected"])) $scoSelected = ilUtil::stripSlashes($_POST["scoSelected"]);
	$this->ctrl->setParameter($this,'scoSelected',$scoSelected);

	$report = "choose";
	if (isset($_GET["report"])) $report = ilUtil::stripSlashes($_GET["report"]);
	if (isset($_POST["report"])) $report = ilUtil::stripSlashes($_POST["report"]);
	$this->ctrl->setParameter($this,'report',$report);

	include_once './Modules/Scorm2004/classes/class.ilSCORM2004TrackingItemsPerScoFilterGUI.php';
	$filter = new ilSCORM2004TrackingItemsPerScoFilterGUI($this, 'showTrackingItemsBySco');
	$filter->parse($scoSelected,$report,$reports);
	if($report == "choose") {
		$this->tpl->setContent($filter->form->getHTML());
	} else {
		$scosSelected = array();
		if ($scoSelected != "all") $scosSelected[] = $scoSelected;
		else {
			$tmpscos=$this->object->getTrackedItems();
			for ($i=0; $i<count($tmpscos); $i++) {
				$scosSelected[] = $tmpscos[$i]["id"];
			}
		}
		//with check for course ...
		include_once "Services/Tracking/classes/class.ilTrQuery.php";
		$a_users=ilTrQuery::getParticipantsForObject($this->ref_id);
//			var_dump($this->object->getTrackedUsers(""));
		include_once './Modules/Scorm2004/classes/class.ilSCORM2004TrackingItemsTableGUI.php';
		$tbl = new ilSCORM2004TrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItemsBySco', $a_users, $scosSelected, $report);
		$this->tpl->setContent($filter->form->getHTML().$tbl->getHTML());
	}
	return true;
}
function showTrackingItems()
{
	global $ilTabs;

	ilObjSCORMLearningModuleGUI::setSubTabs();
	$ilTabs->setTabActive('cont_tracking_data');
	$ilTabs->setSubTabActive('cont_tracking_byuser');

	$reports = array('exportSelectedSuccess','exportSelectedCore','exportSelectedInteractions','exportSelectedObjectives','exportObjGlobalToSystem');

	$userSelected = "all";
	if (isset($_GET["userSelected"])) $userSelected = ilUtil::stripSlashes($_GET["userSelected"]);
	if (isset($_POST["userSelected"])) $userSelected = ilUtil::stripSlashes($_POST["userSelected"]);
	$this->ctrl->setParameter($this,'userSelected',$userSelected);

	$report = "choose";
	if (isset($_GET["report"])) $report = ilUtil::stripSlashes($_GET["report"]);
	if (isset($_POST["report"])) $report = ilUtil::stripSlashes($_POST["report"]);
	$this->ctrl->setParameter($this,'report',$report);

	include_once './Modules/Scorm2004/classes/class.ilSCORM2004TrackingItemsPerUserFilterGUI.php';
	$filter = new ilSCORM2004TrackingItemsPerUserFilterGUI($this, 'showTrackingItems');
	$filter->parse($userSelected,$report,$reports);
	if($report == "choose") {
		$this->tpl->setContent($filter->form->getHTML());
	} else {
		$usersSelected = array();
		if ($userSelected != "all") $usersSelected[] = $userSelected;
		else {
			include_once "Services/Tracking/classes/class.ilTrQuery.php";
			$users=ilTrQuery::getParticipantsForObject($this->ref_id);
			foreach($users as $user) {
				if(ilObject::_exists($user)  && ilObject::_lookUpType($user) == 'usr') {
					$usersSelected[] = $user;
				}
			}
		}
		$scosSelected = array();
		$tmpscos=$this->object->getTrackedItems();
		for ($i=0; $i<count($tmpscos); $i++) {
			$scosSelected[] = $tmpscos[$i]["id"];
		}
		//with check for course ...
		// include_once "Services/Tracking/classes/class.ilTrQuery.php";
		// $a_users=ilTrQuery::getParticipantsForObject($this->ref_id);
//			var_dump($this->object->getTrackedUsers(""));
		include_once './Modules/Scorm2004/classes/class.ilSCORM2004TrackingItemsTableGUI.php';
		$tbl = new ilSCORM2004TrackingItemsTableGUI($this->object->getId(), $this, 'showTrackingItems', $usersSelected, $scosSelected, $report);
		$this->tpl->setContent($filter->form->getHTML().$tbl->getHTML());
	}
	return true;
}

function exportAll(){
	$this->object->exportSelected(1);
}

function exportSelected()
{
	if (!isset($_POST["user"]))
	{
		ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
		$this->ctrl->redirect($this, "showTrackingItems");
	} else {
		$this->object->exportSelected(0,$_POST["user"]);
	}
}

function export($a_export_all = 0)
{	
	if (!isset($_POST["export_type"])) {
		//show form
		$this->exportOptions($a_export_all,$_POST["user"]);
	} else {
		if (isset($_POST["cancel"])) {
			$this->ctrl->redirect($this, "showTrackingItems");
		} else {
			$a_export_all = $_POST["export_all"];
			if ($a_export_all == 0) {
				$export_type = $_POST["export_type"];
				$a_user = unserialize(stripslashes($_POST["user"]));
				if ($export_type == "core") $this->object->exportSelectedCore($a_user);
				else if ($export_type == "interactions") $this->object->exportSelectedInteractions($a_user);
				else if ($export_type == "objectives") $this->object->exportSelectedObjectives($a_user);
				else if ($export_type == "forImport") $this->object->exportSelected($a_user);
				else $this->object->exportSelectedSuccess($a_user);
			}
			else $this->object->exportAll($_POST["export_type"]);
		}
	}
}

function exportOptionsTMP($a_export_all=0, $a_users)
{
	$obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
	$type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

	// display import form
	$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scorm_tracking_data_export.html", "Modules/Scorm2004");

	$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_slm.gif'));
	$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_sahs"));

	$this->tpl->setVariable("TXT_EXPORT", $this->lng->txt("cont_export_options"));

	$this->ctrl->setParameter($this, "new_type", "sahs");
	$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

	$this->tpl->setVariable("BTN_NAME", "export");

	$this->tpl->setVariable("TARGET", ' target="'.
		ilFrameTargetInfo::_getFrame("MainContent").'" ');

	$this->tpl->setVariable("TXT_SELECT_TYPE", $this->lng->txt("cont_export_type"));
	$this->tpl->setVariable("TXT_EXPORT_FORIMPORT", $this->lng->txt("cont_export_for_import"));
	$this->tpl->setVariable("TXT_EXPORT_SUCCESS", $this->lng->txt("cont_export_success"));
	$this->tpl->setVariable("TXT_EXPORT_CORE", $this->lng->txt("exportSCOdataCore"));
	$this->tpl->setVariable("TXT_EXPORT_INTERACTIONS", $this->lng->txt("exportSCOdataInteractions"));
	$this->tpl->setVariable("TXT_EXPORT_OBJECTIVES", $this->lng->txt("exportSCOdataObjectives"));
	$this->tpl->setVariable("TXT_EXPORT_TRACKING", $this->lng->txt("cont_export_tracking"));

	$this->tpl->setVariable("TXT_EXPORT", $this->lng->txt("export"));
	$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
	$this->tpl->setVariable("VAL_USER", htmlentities(serialize($a_users)));
	$this->tpl->setVariable("VAL_EXPORTALL",$a_export_all);
}

/**
* show tracking data of item
*/
function showTrackingItem()
{

	include_once "./Services/Table/classes/class.ilTableGUI.php";

	// load template for table
	$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
	// load template for table content data
	$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.scorm2004_track_item.html", "Modules/Scorm2004");

	$num = 2;

	$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

	// create table
	$tbl = new ilTableGUI();

	include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
	$sc_item =& new ilSCORMItem($_GET["obj_id"]);

	// title & header columns
	$user = new ilObjUser( $_GET["user_id"]);
	$tbl->setTitle($user->getLastname().", ".$user->getFirstname());

	$tbl->setHeaderNames(array($this->lng->txt("title"),
		$this->lng->txt("cont_status"), $this->lng->txt("cont_time"),
		$this->lng->txt("cont_score")));

	$header_params = array("ref_id" => $this->ref_id, "cmd" => $_GET["cmd"],
		"cmdClass" => get_class($this), "obj_id" => $_GET["obj_id"], "baseClass"=>"ilSAHSEditGUI", 'user_id'=>$_GET["user_id"]);
	
	$cols = array("title", "status", "time", "score");
	$tbl->setHeaderVars($cols, $header_params);
	//$tbl->setColumnWidth(array("25%",));

	// control
	$tbl->setOrderColumn($_GET["sort_by"]);
	$tbl->setOrderDirection($_GET["sort_order"]);
	$tbl->setLimit($_GET["limit"]);
	$tbl->setOffset($_GET["offset"]);
	$tbl->setMaxCount($this->maxcount);

	//$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
	//$this->showActions(true);

	// footer
	$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
	#$tbl->disable("footer");

	$tr_data = $this->object->getTrackingDataAgg($_GET["user_id"]);

	//$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
	$tbl->setMaxCount(count($tr_data));
	$tr_data = array_slice($tr_data, $_GET["offset"], $_GET["limit"]);

	$tbl->render();

	if (count($tr_data) > 0)
	{
		foreach ($tr_data as $data)
		{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("VAL_TITLE", $data["title"]);
				$this->ctrl->setParameter($this, "user_id",  $_GET["user_id"]);
				$this->ctrl->setParameter($this, "obj_id",  $data["sco_id"]);
				
				$this->tpl->setVariable("LINK_SCO",
					$this->ctrl->getLinkTarget($this, "showTrackingItemPerUser"));
				$this->tpl->setVariable("VAL_TIME", $data["time"]);
				$this->tpl->setVariable("VAL_STATUS", $data["status"]);
				$this->tpl->setVariable("VAL_SCORE", $data["score"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
		
		}
	} //if is_array
	else
	{
		$this->tpl->setCurrentBlock("notfound");
		$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
		$this->tpl->setVariable("NUM_COLS", $num);
		$this->tpl->parseCurrentBlock();
	}
}


/**
	* display deletion confirmation screen
	*/
	function deleteTrackingForUser()
	{
		if(!isset($_POST["user"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setHeaderText($this->lng->txt("info_delete_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteTracking");
		$cgui->setConfirm($this->lng->txt("confirm"), "confirmedDeleteTracking");
		
		foreach($_POST["user"] as $id)
		{
			if (ilObject::_exists($id) && ilObject::_lookUpType($id)=="usr" )
			{	
				$user = new ilObjUser($id);
				
				$caption = ilUtil::getImageTagByType("sahs", $this->tpl->tplPath).
					" ".$this->lng->txt("cont_tracking_data").
					": ".$user->getLastname().", ".$user->getFirstname();
				
				
				$cgui->addItem("user[]", $id, $caption);
			}	
		}

		$this->tpl->setContent($cgui->getHTML());
	}
	
	function resetSearch() {
		unset($_SESSION["scorm_search_string"]);
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	/**
	* cancel deletion of export files
	*/
	function cancelDeleteTracking()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	/**
	* Confirmed tracking deletion
	*
	*/
	function confirmedDeleteTracking()
	{
	 	foreach ($_POST["user"] as $user)
	 	{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004DeleteData.php");
			ilSCORM2004DeleteData::removeCMIDataForUserAndPackage($user,$this->object->getId());

			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");	
			ilLPStatusWrapper::_updateStatus($this->object->getId(), $user);
	 	}

	 	$this->ctrl->redirect($this, "showTrackingItems");
	}
	
	
	function deleteTrackingData()
	{
		if (is_array($_POST["id"]))
		{
			$this->object->deleteTrackingDataOfUsers($_POST["id"]);
		}
		$this->showTrackingItems();
	}

	/**
	 * Show Editing Tree
	 */
	function showTree()
	{
		global $ilUser, $ilias, $ilCtrl, $lng;

		$mtree = new ilTree($this->object->getId());
		$mtree->setTableNames('sahs_sc13_tree','sahs_sc13_tree_node');
		$mtree->setTreeTablePK("slm_id");

		if ($_POST["expandAll"] != "")
		{
			$_GET["scexpand"] = "";
			$stree = $mtree->getSubTree($mtree->getNodeData($mtree->readRootId()));
			$n_arr = array();
			foreach ($stree as $n)
			{
				$n_arr[] = $n["child"];
			}
			$_SESSION["scexpand"] = $n_arr;
		}

		if ($_POST["collapseAll"] != "")
		{
			$_GET["scexpand"] = "";
			$_SESSION["scexpand"] = array($mtree->readRootId());
		}
		
		$this->tpl = new ilTemplate("tpl.main.html", true, true);
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$ilCtrl->setParameter($this, "active_node", $_GET["active_node"]);

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.png", false));
		
		$this->tpl->setCurrentBlock("exp2_button");
		$this->tpl->setVariable("CMD_EXP2_BTN", "expandAll");
		$this->tpl->setVariable("TXT_EXP2_BTN", $lng->txt("expand_all"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("exp2_button");
		$this->tpl->setVariable("CMD_EXP2_BTN", "collapseAll");
		$this->tpl->setVariable("TXT_EXP2_BTN", $lng->txt("collapse_all"));
		$this->tpl->parseCurrentBlock();

		require_once ("./Modules/Scorm2004/classes/class.ilSCORM2004EditorExplorer.php");
		$exp = new ilSCORM2004EditorExplorer($this->ctrl->getLinkTarget($this, "edit"),
		$this->object);
		$exp->setFrameUpdater("content", "ilHierarchyFormUpdater");
		$exp->setTargetGet("obj_id");
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this, "showTree"));
		
		if ($_GET["scexpand"] == "")
		{
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["scexpand"];
		}

//echo "-".$_GET["active_node"]."-";
		if ($_GET["active_node"] != "")
		{
			$path = $mtree->getPathId($_GET["active_node"]);
			$exp->setForceOpenPath($path);

			$exp->highlightNode($_GET["active_node"]);
		}
		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		// asynchronous output
		if ($ilCtrl->isAsynch())
		{
			echo $output; exit;
		}
		
		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("sahs_organization"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->ctrl->setParameter($this, "scexpand", $_GET["scexpand"]);
		$this->tpl->setVariable("ACTION", $this->ctrl->getLinkTarget($this, "showTree"));
		$this->tpl->parseCurrentBlock();

		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery($this->tpl);

		$this->tpl->show(false);
		
		
		exit;
	}

	/**
	 * Show Sequencing
	 */
	function showSequencing()
	{
		global $tpl, $lng, $ilTabs, $ilToolbar, $ilCtrl;
		
		$ilTabs->setTabActive("sahs_sequencing");
		
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");

		if (!$this->object->getSequencingExpertMode())
		{
			$ilToolbar->addButton($lng->txt("sahs_activate_expert_mode"),
				$ilCtrl->getLinkTarget($this, "confirmExpertMode"));
		}
		else
		{
			include_once("./Services/UIComponent/NestedList/classes/class.ilNestedList.php");
			$list = new ilNestedList();
			$t = $this->object->getTree();
			$root_node = $t->getNodeData($t->getRootId());
			$nodes = $this->object->getTree()->getSubtree($root_node);
			foreach ($nodes as $node)
			{
				if (in_array($node["type"], array("", "chap", "sco")))
				{
					$ntpl = new ilTemplate("tpl.seq_node.html", true, true, "Modules/Scorm2004");
					$ntpl->setVariable("NODE_ID", $node["child"]);
					if ($node["type"] == "")
					{
						$ntpl->setVariable("TITLE", $this->object->getTitle());
						$item = new ilSCORM2004Item($this->object->getId(), true);
					}
					else
					{
						$ntpl->setVariable("TITLE", $node["title"]);
						$item = new ilSCORM2004Item($node["child"]);
					}
					$ntpl->setVariable("SEQ_INFO",
						ilUtil::prepareFormOutput($item->exportAsXML(false)));
					$list->addListNode($ntpl->get(), $node["child"], $node["parent"]);
				}
			}
			
			$tb = new ilToolbarGUI();
			$tb->addFormButton($lng->txt("save"), "saveSequencing");
			$ftpl = new ilTemplate("tpl.sequencing.html", true, true, "Modules/Scorm2004");
			$ftpl->setVariable("CONTENT", $list->getHTML());
			$ftpl->setVariable("FORM_ACTION", $ilCtrl->getFormAction($this));
			$ftpl->setVariable("TB", $tb->getHTML());
			$tpl->setContent($ftpl->get());
		}
	}
	
	/**
	 * Confirm activation of expert mode
	 */
	function confirmExpertMode()
	{
		global $ilCtrl, $tpl, $lng, $ilTabs;
		
		$ilTabs->setTabActive("sahs_sequencing");
			
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setHeaderText($lng->txt("sahs_activate_expert_mode_info"));
		$cgui->setCancel($lng->txt("cancel"), "showSequencing");
		$cgui->setConfirm($lng->txt("sahs_activate_expert_mode"), "activateExpertMode");
		
		$tpl->setContent($cgui->getHTML());
	}
	
	/**
	 * Activate expert mode
	 *
	 * @param
	 * @return
	 */
	function activateExpertMode()
	{
		global $ilCtrl, $lng;
		
		$this->object->setSequencingExpertMode(true);
		$this->object->update();
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "showSequencing");
	}
	

	/**
	 * Save sequencing
	 */
	function saveSequencing()
	{
		global $tpl,$lng, $ilCtrl;
		
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
		$t = $this->object->getTree();
		$root_node = $t->getNodeData($t->getRootId());
		$nodes = $this->object->getTree()->getSubtree($root_node);
		foreach ($nodes as $node)
		{
			if (in_array($node["type"], array("", "chap", "sco")))
			{
				if ($node["type"] == "")
				{
					$item = new ilSCORM2004Item($this->object->getId(), true);
				}
				else
				{
					$item = new ilSCORM2004Item($node["child"]);
				}
				$xml = '<?xml version="1.0"?>'.ilUtil::stripSlashes($_POST["seq"][$node["child"]], false);
				
				$ob_texts = array();
				if ($node["type"] == "sco")
				{
					$sco = new ilSCORM2004Sco($this->object, $node["child"]);
					$objectives = $sco->getObjectives();
					foreach ($objectives as $o)
					{
						$ob_texts[$o->getId()] = $o->getObjectiveId();
					}
				}
				
				$item->setSeqXml($xml);
				$item->initDom();
				$item->update();

				if ($node["type"] == "sco")
				{
					foreach ($ob_texts as $id => $t)
					{
						$objective = new ilScorm2004Objective($node["child"], $id);
						$objective->setObjectiveId($t);
						$objective->updateObjective();
					}
				}
			}
		}

		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		
		$ilCtrl->redirect($this, "showSequencing");
	}

	/**
	 * Show Learning Objectives Alignment
	 */
	function showLearningObjectivesAlignment()
	{
		global $tpl, $lng, $ilCtrl, $ilToolbar;

		$chaps = $this->object->getTree()->getChilds(
			$this->object->getTree()->getRootId());
		$s_chaps = array();
		foreach($chaps as $chap)
		{
			if ($chap["type"] == "chap")
			{
				$s_chaps[$chap["child"]] = $chap["title"];
			}
		}
		$cur_chap = $_SESSION["sahs_cur_chap"]
			? $_SESSION["sahs_cur_chap"]
			: 0;

		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array(
			"0" => $lng->txt("all")
		);
		$options = $options + $s_chaps;
		$si = new ilSelectInputGUI($lng->txt("chapter"), "chapter");
		$si->setOptions($options);
		$si->setValue($cur_chap);
		$ilToolbar->addInputItem($si, true);
		$ilToolbar->addFormButton($lng->txt("change"), "selectLObjChapter");
		
		include_once("./Modules/Scorm2004/classes/class.ilObjectivesAlignmentTableGUI.php");
		$obj_table = new ilObjectivesAlignmentTableGUI($this, "showLearningObjectivesAlignment",
			$this->getEditTree(), $this->object, $cur_chap);
		$tpl->setContent($obj_table->getHTML());
	}

	function selectLObjChapter()
	{
		global $ilCtrl;

		$_SESSION["sahs_cur_chap"] = (int) $_POST["chapter"];
		$ilCtrl->redirect($this, "showLearningObjectivesAlignment");
	}
	
	/**
	* Select the export type of the SCORM 2004 module
	*/
	public function selectExport()
	{
		switch ($_POST['select_export'])
		{
			case "exportScorm12":
			case "exportScorm2004_3rd":
			case "exportScorm2004_4th":
			case "exportPDF":
			case "exportISO":
			case "exportHTML":
			case "exportHTMLOne":
				$this->ctrl->redirect($this, $_POST['select_export']);
				break;
			default:
				$this->ctrl->redirect($this, 'showExportList');
				break;
		}
	}
	
	/**
	 * Show Export List
	 */
	function showExportList()
	{
		global $tpl, $ilToolbar;

		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'selectExport'));
		$ilToolbar->setId("scorm2004export");

		//$template = new ilTemplate("tpl.scorm2004_export_buttons.html", true, true, 'Modules/Scorm2004');

/*		$buttons = array(
			"exportScorm2004_3rd" => $this->lng->txt("scorm_create_export_file_scrom2004"),
			"exportScorm2004_4th" => $this->lng->txt("scorm_create_export_file_scrom2004_4th"),
			"exportScorm12" => $this->lng->txt("scorm_create_export_file_scrom12"),
			"exportPDF" => $this->lng->txt("scorm_create_export_file_pdf"),
			"exportISO" => $this->lng->txt("scorm_create_export_file_iso"),
			"exportHTML" => $this->lng->txt("scorm_create_export_file_html"),
			"exportHTMLOne" => $this->lng->txt("scorm_create_export_file_html_one")
		);*/
		$buttons = array(
			"exportScorm2004_3rd" => $this->lng->txt("scorm_create_export_file_scrom2004"),
			"exportScorm2004_4th" => $this->lng->txt("scorm_create_export_file_scrom2004_4th"),
			"exportScorm12" => $this->lng->txt("scorm_create_export_file_scrom12"),
			"exportHTML" => $this->lng->txt("scorm_create_export_file_html"),
			"exportHTMLOne" => $this->lng->txt("scorm_create_export_file_html_one")
		);

		//
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($this->lng->txt('type'), "select_export");
		$si->setOptions($buttons);
		$ilToolbar->addInputItem($si, true);

		$ilToolbar->addFormButton($this->lng->txt('export'), "selectExport");

		$export_files = $this->object->getExportFiles();

		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004ExportTableGUI.php";
		$table_gui = new ilSCORM2004ExportTableGUI($this, 'showExportList');
		$data = array();
		foreach ($export_files as $exp_file)
		{
			$filetype = $exp_file['type'];
			$public_str = ($exp_file["file"] == $this->object->getPublicExportFile($filetype))
				? " <b>(".$this->lng->txt("public").")<b>"
				: "";
			$file_arr = explode("__", $exp_file["file"]);
			array_push($data, array('file' => $exp_file['file'], 'filetype' => $filetype, 'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)), 'size' => $exp_file['size'], 'type' => $exp_file['type'].$public_str));
		}
		$table_gui->setData($data);

		$this->tpl->setContent($table_gui->getHTML());
	}

	/**
	 * Adds tabs to tab gui object
	 *
	 * @param	object		$tabs_gui		ilTabsGUI object
	 */
	function getTabs(&$tabs_gui)
	{
		global $ilAccess, $ilHelp;

		if ($this->ctrl->getCmd() == "delete")
		{
			return;
		}

		if (!$this->object->getEditable())
		{
			return parent::getTabs($tabs_gui);
		}
		
		$ilHelp->setScreenIdComponent("sahsed");

		// organization
		$tabs_gui->addTarget("sahs_organization",
		$this->ctrl->getLinkTarget($this, "showOrganization"), "showOrganization",
		get_class($this));

		// info screen
		$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui")
		? true
		: false;
		$tabs_gui->addTarget("info_short",
		$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"), "",
			"ilinfoscreengui", "", $force_active);
			
		// settings
		$tabs_gui->addTarget("settings",
		$this->ctrl->getLinkTarget($this, "properties"), "properties",
		get_class($this));

		// tracking data
		/*	Later, only if tracking data exists
		 $tabs_gui->addTarget("cont_tracking_data",
			$this->ctrl->getLinkTarget($this, "showTrackingItems"), "showTrackingItems",
			get_class($this));
			*/
		
		// objective alignment
		$tabs_gui->addTarget("sahs_objectives_alignment",
		$this->ctrl->getLinkTarget($this, "showLearningObjectivesAlignment"), "showLearningObjectivesAlignment",
		get_class($this));

		// sequencing
		$tabs_gui->addTarget("sahs_sequencing",
		$this->ctrl->getLinkTarget($this, "showSequencing"), "showSequencing",
			get_class($this));

		// learning progress
		/*	Later, only if tracking data exists
		 include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		 if(ilObjUserTracking::_enabledLearningProgress())
		 {
			$tabs_gui->addTarget('learning_progress',
			$this->ctrl->getLinkTargetByClass(array('illearningprogressgui'),''),
			'',
			array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
			}
			*/

		// edit meta
		$tabs_gui->addTarget("meta_data",
		$this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
			 "", "ilmdeditorgui");

		// export
		$tabs_gui->addTarget("export",
		$this->ctrl->getLinkTarget($this, "showExportList"), array("showExportList", 'confirmDeleteExportFile'),
		get_class($this));

		// perm
		if ($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
			$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
		
		if ($this->object->editable==1)
		{
			// preview
			$tabs_gui->addNonTabbedLink("preview",
				$this->lng->txt("cont_sc_preview"),
				$this->ctrl->getLinkTarget($this, "preview"),
				"_blank");
		}
		
	}

	/**
	 * Set sub tabs
	 */
	function setSubTabs($a_main_tab, $a_active)
	{
		global $ilTabs, $ilCtrl, $lng;

		if ($a_main_tab == "settings" &&
			$this->object->editable == 1)
		{
/*			// general properties
			$ilTabs->addSubTab("general_settings",
				$lng->txt("general_settings"),
				$ilCtrl->getLinkTarget($this, 'properties'));

			// style properties
			$ilTabs->addSubTab("style",
				$lng->txt("cont_style"),
				$ilCtrl->getLinkTarget($this, 'editStyleProperties'));
*/
			$ilTabs->activateSubTab($a_active);
		}
	}
	
	
	/**
	* Get editing tree object
	*/
	function getEditTree()
	{
		$slm_tree = new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
		return $slm_tree;
	}
	
	/**
	 * Show subhiearchy of chapters, scos and pages
	 */
	function showOrganization($a_top_node = 0, $a_form_action = "",
		$a_title = "", $a_icon = "", $a_gui_obj = null, $a_gui_cmd = "")
	{
		global $lng, $ilCtrl, $tpl;

		if ($a_form_action == "")
		{
			$a_form_action = $ilCtrl->getFormAction($this);
		}

		if ($a_icon == "")
		{
			$a_title = $this->object->getTitle();
			$a_icon = ilUtil::getImagePath("icon_lm.svg");
		}

		$slm_tree = $this->getEditTree();

		if ($a_top_node == 0)
		{
			$a_top_node = $slm_tree->getRootId();
		}
		
		if (is_null($a_gui_obj))
		{
			$a_gui_obj = $this;
			$a_gui_cmd = "showOrganization";
		}

		$ilCtrl->setParameter($this, "backcmd", "showOrganization");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		$form_gui = new ilSCORM2004OrganizationHFormGUI();
		$form_gui->setParentCommand($a_gui_obj, $a_gui_cmd);
		$form_gui->setFormAction($a_form_action);
//		$form_gui->setTitle($a_title);
//		$form_gui->setIcon($a_icon);
		$form_gui->setTree($slm_tree);
		$form_gui->setCurrentTopNodeId($a_top_node);
		$form_gui->addMultiCommand($lng->txt("delete"), "deleteNodes");
		$form_gui->addMultiCommand($lng->txt("cut"), "cutItems");
		$form_gui->addMultiCommand($lng->txt("copy"), "copyItems");
		$form_gui->addCommand($lng->txt("cont_save_all_titles"), "saveAllTitles");
		$form_gui->addCommand($lng->txt("expand_all"), "expandAll");
		$form_gui->addCommand($lng->txt("collapse_all"), "collapseAll");
		$form_gui->setTriggeredUpdateCommand("saveAllTitles");
		
		// highlighted nodes
		if ($_GET["highlight"] != "")
		{
			$hl = explode(":", $_GET["highlight"]);
			$form_gui->setHighlightedNodes($hl);
			$form_gui->setFocusId($hl[0]);
		}

		$ilCtrl->setParameter($this, "active_node", $_GET["obj_id"]);
//		$form_gui->setExplorerUpdater("tree", "tree_div",
//			$ilCtrl->getLinkTarget($this, "showTree", "", true));
		$sc_tpl = new ilTemplate("tpl.scormeditor_orga_screen.html", true, true, "Modules/Scorm2004");
		$sc_tpl->setVariable("ORGANIZATION", $form_gui->getHTML());
		$sc_tpl->setVariable("NOTES", $this->getNotesHTML());
		
		$tpl->setContent($sc_tpl->get());
	}

	/**
	* Get notes HTML
	*/
	function getNotesHTML($a_mode = "")
	{
		global $ilCtrl;
		
		// notes
		$ilCtrl->setParameter($this, "nodes_mode", $a_mode);
		include_once("Services/Notes/classes/class.ilNoteGUI.php");
		$node_id = $_GET["obj_id"];
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		$node_type = ($node_id > 0)
			? ilSCORM2004Node::_lookupType($node_id)
			: "sahs";

		$notes_gui = new ilNoteGUI($this->object->getId(),
			(int) $node_id, $node_type);
//		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
//		{
//			$notes_gui->enablePublicNotesDeletion(true);
//		}
		$notes_gui->enablePrivateNotes();
		$notes_gui->enablePublicNotes();
		
		$next_class = $ilCtrl->getNextClass($this);
		if ($next_class == "ilnotegui")
		{
			$html = $this->ctrl->forwardCommand($notes_gui);
		}
		else
		{	
			$html = $notes_gui->getNotesHTML();
		}
		return $html;
	}

	/**
	 * Insert (multiple) chapters at node
	 */
	function insertChapter($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = ilSCORM2004OrganizationHFormGUI::getPostMulti();
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();

		if (!ilSCORM2004OrganizationHFormGUI::getPostFirstChild())	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		
		$chap_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$chap = new ilSCORM2004Chapter($this->object);
			$chap->setTitle($lng->txt("sahs_new_chapter"));
			$chap->setSLMId($this->object->getId());
			$chap->create();
			ilSCORM2004Node::putInTree($chap, $parent_id, $target);
			$chap_ids[] = $chap->getId();
		}
		$chap_ids = array_reverse($chap_ids);
		$chap_ids = implode($chap_ids, ":");

		if ($a_redirect)
		{
			$ilCtrl->setParameter($this, "highlight", $chap_ids);
			$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
		}
		return array("node_id" => $node_id, "items" => $chap_ids);
	}

	/**
	 * Insert (multiple) scos at node
	 */
	function insertSco($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = ilSCORM2004OrganizationHFormGUI::getPostMulti();
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

		if (!ilSCORM2004OrganizationHFormGUI::getPostFirstChild())	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$sco_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$sco = new ilSCORM2004Sco($this->object);
			$sco->setTitle($lng->txt("sahs_new_sco"));
			$sco->setSLMId($this->object->getId());
			$sco->create();
			ilSCORM2004Node::putInTree($sco, $parent_id, $target);
			$sco_ids[] = $sco->getId();
		}
		$sco_ids = array_reverse($sco_ids);
		$sco_ids = implode($sco_ids, ":");

		if ($a_redirect)
		{
			$ilCtrl->setParameter($this, "highlight", $sco_ids);
			$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
		}
		return array("node_id" => $node_id, "items" => $sco_ids);
	}

	/**
	 * Insert (multiple) assets at node
	 */
	function insertAsset($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = ilSCORM2004OrganizationHFormGUI::getPostMulti();
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Asset.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

		if (!ilSCORM2004OrganizationHFormGUI::getPostFirstChild())	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$ass_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$ass = new ilSCORM2004Asset($this->object);
			$ass->setTitle($lng->txt("sahs_new_asset"));
			$ass->setSLMId($this->object->getId());
			$ass->create();
			ilSCORM2004Node::putInTree($ass, $parent_id, $target);
			$ass_ids[] = $ass->getId();
		}
		$ass_ids = array_reverse($ass_ids);
		$ass_ids = implode($ass_ids, ":");

		if ($a_redirect)
		{
			$ilCtrl->setParameter($this, "highlight", $ass_ids);
			$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
		}
		return array("node_id" => $node_id, "items" => $ass_ids);
	}

	/**
	 * Insert (multiple) pages at node
	 */
	function insertPage($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = ilSCORM2004OrganizationHFormGUI::getPostMulti();
		$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		if (!ilSCORM2004OrganizationHFormGUI::getPostFirstChild())	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else													// insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$page_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$page = new ilSCORM2004PageNode($this->object);
			$page->setTitle($lng->txt("sahs_new_page"));
			$page->setSLMId($this->object->getId());
			$page->create();
			ilSCORM2004Node::putInTree($page, $parent_id, $target);
			$page_ids[] = $page->getId();
		}
		$page_ids = array_reverse($page_ids);
		$page_ids = implode($page_ids, ":");

		if ($a_redirect)
		{
			$ilCtrl->setParameter($this, "highlight", $page_ids);
			$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
		}
		return array("node_id" => $node_id, "items" => $page_ids);
	}


	/**
	 * Insert sequencing scenario at node
	 */
	function insertScenarioGUI()
	{

		global $ilCtrl,$lng, $tpl;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004SeqTemplate.php");

		$templates = array();
		$description = null;
		$image = null;

		$default_identifier = $_POST["identifier"];

		//get available templates
		$arr_templates = ilSCORM2004SeqTemplate::availableTemplates();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scormeditor_seq_chooser.html", "Modules/Scorm2004");

		$this->tpl->setCurrentBlock("option_item");

		$active = null;
		foreach ($arr_templates as $templ)
		{
			$sel= "";
			$item_data = $templ->getMetadataProperties();
			$item_data['identifier'] = $templ->getIdentifier();
			array_push($templates,$item_data);
			if ($default_identifier == $item_data['identifier']) {$sel = 'selected'; $active =  $item_data;}
			$this->tpl->setVariable("VAL_SELECTED",$sel );
			$this->tpl->setVariable("VAL_IDENTIFIER",$item_data['identifier'] );
			$this->tpl->setVariable("VAL_TITLE",$item_data['title'] );
			$this->tpl->parseCurrentBlock();
		}

		//default
		if ($active == null )
		{
			$this->saveAllTitles(false);
			$description = $templates[0]['description'];
			$image = $templates[0]['thumbnail'];
		} else {
			$description = $active['description'];
			$image = $active['thumbnail'];
		}
			
		$this->tpl->setVariable("VAL_DESCRIPTION",$description);
		$this->tpl->setVariable("VAL_IMAGE",ilSCORM2004SeqTemplate::SEQ_TEMPLATE_DIR."/images/".$image);

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_NAME", "insertScenario");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_INSERT", $this->lng->txt("insert"));
		$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("change"));

		$this->tpl->setVariable("TXT_TITLE", "Choose Sequencing Template");

		$node_id = $_POST["node_id"];
		$first_child = $_POST["first_child"];

		if (!$node_id) {$node_id = ilSCORM2004OrganizationHFormGUI::getPostNodeId();}
		if (!$first_child) {$first_child = ilSCORM2004OrganizationHFormGUI::getPostFirstChild();}

		$this->tpl->setVariable("VAL_NODE_ID", $node_id);
		$this->tpl->setVariable("VAL_FIRST_CHILD", $first_child);

	}


	/**
	 * Insert sequencing scenario at node
	 */
	function insertScenario()
	{
		global $ilCtrl;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$node_id = $_POST["node_id"];

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		include_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004SeqTemplate.php");

		if (!$_POST["first_child"])	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else     // insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$template = new ilSCORM2004SeqTemplate($_POST["identifier"]);
		$id = $template->insertTemplateForObjectAtParent($this->object,$parent_id,$target);
		$ilCtrl->setParameter($this, "highlight", $id);
		$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);

	}

	/**
	 * Insert special page
	 */
	function insertSpecialPage($a_redirect = true)
	{
		$this->insertTemplateGUI($a_redirect, true);
	}
	
	
	/**
	 * Displays GUI to select template for page
	 */
	function insertTemplateGUI($a_redirect = true, $a_special_page = false)
	{
		global $ilCtrl,$lng, $tpl;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");
		
		$arr_templates = ilPageLayout::activeLayouts($a_special_page, ilPageLayout::MODULE_SCORM);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.scormeditor_page_layout_chooser.html", "Modules/Scorm2004");

		$this->tpl->setCurrentBlock("option_item");

		$count = 0;
		foreach ($arr_templates as $templ)
		{
			$count++;
			$sel= "";
			$templ->readObject();
			$this->tpl->setVariable("VAL_LAYOUT_TITLE",$templ->getTitle());
			$this->tpl->setVariable("VAL_LAYOUT_IMAGE",$templ->getPreview());
			$this->tpl->setVariable("VAL_LAYOUT_ID",$templ->getId());
			$this->tpl->setVariable("VAL_DISPLAY","inline");
			if ($count==1) {
				$this->tpl->setVariable("VAL_CHECKED","checked");
			}
			if ($count%4 == 0) {
				$this->tpl->setVariable("END_ROW","</tr>");
			}
			if ($count == 1 || ($count-1)%4 == 0) {
				$this->tpl->setVariable("BEGIN_ROW","<tr>");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		//matrix table
		if ($count%4!=0) {
			$rest = 4-($count%4);
		} else {
			$rest=0;
		}
		
		for ($i=1;$i<=$rest;$i++) {
			$this->tpl->setVariable("VAL_DISPLAY","none");			
			$this->tpl->setVariable("VAL_LAYOUT_ID",$templ->getId());
			
			if ($i == $rest) {
				$this->tpl->setVariable("END_ROW","</tr>");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		//empty cells and closing <tr>
		
		$this->tpl->setVariable("VAL_NODE_ID",ilSCORM2004OrganizationHFormGUI::getPostNodeId());
		$this->tpl->setVariable("VAL_MULTI", ilSCORM2004OrganizationHFormGUI::getPostMulti());
		$this->tpl->setVariable("VAL_FIRST_CHILD", ilSCORM2004OrganizationHFormGUI::getPostFirstChild());
		$this->tpl->setVariable("VAL_OBJ_ID", ilSCORM2004OrganizationHFormGUI::getPostFirstChild());
	
		$ilCtrl->saveParameter($this,"obj_id");
	
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("BTN_NAME", "insertTemplate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_INSERT", $this->lng->txt("create"));
		$this->tpl->setVariable("CMD_CANCEL", "showOrganization");

		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_INSERT", $this->lng->txt("insert"));
		$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("change"));
		if ($a_special_page)
		{
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("sahs_choose_special_page"));
		}
		else
		{
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("sahs_choose_page_template"));
		}
	}
	
	
	
	/**
	 * Insert (multiple) pages at node
	 */
	function insertTemplate($a_redirect = true)
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004OrganizationHFormGUI.php");

		$slm_tree =& new ilTree($this->object->getId());
		$slm_tree->setTreeTablePK("slm_id");
		$slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');

		$num = $_POST["multi"];
		$node_id = $_POST["node_id"];
		$layout_id = $_POST["layout_id"];
		

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php");
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");

		if (!$_POST["first_child"])	// insert after node id
		{
			$parent_id = $slm_tree->getParentId($node_id);
			$target = $node_id;
		}
		else           // insert as first child
		{
			$parent_id = $node_id;
			$target = IL_FIRST_NODE;
		}

		$page_ids = array();
		for ($i = 1; $i <= $num; $i++)
		{
			$page = new ilSCORM2004PageNode($this->object);
			$page->setTitle($lng->txt("sahs_new_page"));
			$page->setSLMId($this->object->getId());
			$page->create(false,$layout_id);
			ilSCORM2004Node::putInTree($page, $parent_id, $target);
			$page_ids[] = $page->getId();
		}
		$page_ids = array_reverse($page_ids);
		$page_ids = implode($page_ids, ":");

		if ($a_redirect)
		{	
			if ($_GET["obj_id"] != "")
			{
				$this->jumpToNode($node_id, $page_ids);
			}
			else
			{
				$ilCtrl->setParameter($this, "highlight", $page_ids);
				$ilCtrl->redirect($this, "showOrganization", "node_".$node_id);
			}
		}
	}
	
	/**
	* Expand all
	*/
	function expandAll($a_redirect = true)
	{
		$_GET["scexpand"] = "";
		$mtree = $this->object->getTree();
		$n_id = ($_GET["obj_id"] > 0)
			? $_GET["obj_id"]
			: $mtree->readRootId();
		$stree = $mtree->getSubTree($mtree->getNodeData($n_id));
		$n_arr = array();
		foreach ($stree as $n)
		{
			$n_arr[] = $n["child"];
			$_SESSION["scexpand"] = $n_arr;
		}
		$this->saveAllTitles($a_redirect);
	}
	
	/**
	* Collapse all
	*/
	function collapseAll($a_redirect = true)
	{
		$_GET["scexpand"] = "";
		$mtree = $this->object->getTree();
		$n_id = ($_GET["obj_id"] > 0)
			? $_GET["obj_id"]
			: $mtree->readRootId();
		$stree = $mtree->getSubTree($mtree->getNodeData($n_id));
		$old = $_SESSION["scexpand"];
		foreach ($stree as $n)
		{
			if (in_array($n["child"], $old) && $n["child"] != $n_id)
			{
				$k = array_search($n["child"], $old);
				unset($old[$k]);
			}
		}
		$_SESSION["scexpand"] = $old;
		$this->saveAllTitles($a_redirect);
	}
	
	/**
	 * Save all titles of chapters/scos/pages
	 */
	function saveAllTitles($a_redirect = true)
	{
		global $ilCtrl;

		if (is_array($_POST["title"]))
		{
			include_once("./Services/MetaData/classes/class.ilMD.php");
			include_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
			foreach($_POST["title"] as $id => $title)
			{
				$node_obj = ilSCORM2004NodeFactory::getInstance($this->object, $id, false);
				if (is_object($node_obj))
				{
					// Update Title and description
					$md = new ilMD($this->object->getId(), $id, $node_obj->getType());
					$md_gen = $md->getGeneral();
					$md_gen->setTitle(ilUtil::stripSlashes($title));
					$md_gen->update();
					$md->update();
					ilSCORM2004Node::_writeTitle($id, ilUtil::stripSlashes($title));
				}
			}
		}
		if ($a_redirect)
		{
			$ilCtrl->redirect($this, "showOrganization");
		}
	}

	/**
	 * confirm deletion screen of chapters/scos/pages
	 *
	 * @param	string		form action
	 */
	function deleteNodes($a_form_action = "")
	{
		global $lng, $tpl;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$confirmation_gui = new ilConfirmationGUI();

		if ($a_form_action == "")
		{
			$cmd = ($_GET["backcmd"] == "")
				? "showOrganization"
				: $_GET["backcmd"];
			$this->ctrl->setParameter($this, "backcmd", $cmd);
			$a_form_action = $this->ctrl->getFormAction($this);
		}
		$confirmation_gui->setFormAction($a_form_action);
		$confirmation_gui->setHeaderText($this->lng->txt("info_delete_sure"));

		// Add items to delete
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
		foreach($_POST["id"] as $id)
		{
			if ($id != IL_FIRST_NODE)
			{
				$node_obj = ilSCORM2004NodeFactory::getInstance($this->object, $id, false);
				$confirmation_gui->addItem("id[]", $node_obj->getId(),
				$node_obj->getTitle(), ilUtil::getImagePath("icon_".$node_obj->getType().".svg"));
			}
		}

		$confirmation_gui->setCancel($lng->txt("cancel"), "cancelDelete");
		$confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDelete");

		$tpl->setContent($confirmation_gui->getHTML());
	}

	/**
	 * cancel delete
	 */
	function cancelDelete()
	{
		$this->ctrl->redirect($this, $_GET["backcmd"]);
	}

	/**
	 * Delete chapters/scos/pages
	 */
	function confirmedDelete($a_redirect = true)
	{
		global $ilCtrl;

		$tree = new ilTree($this->object->getId());
		$tree->setTableNames('sahs_sc13_tree','sahs_sc13_tree_node');
		$tree->setTreeTablePK("slm_id");

		// delete all selected objects
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004NodeFactory.php");
		foreach ($_POST["id"] as $id)
		{
			if ($id != IL_FIRST_NODE)
			{
				$obj = ilSCORM2004NodeFactory::getInstance($this->object, $id, false);
				$node_data = $tree->getNodeData($id);
				if (is_object($obj))
				{
					$obj->setSLMId($this->object->getId());

					/*include_once("./Services/History/classes/class.ilHistory.php");
					 ilHistory::_createEntry($this->object->getId(), "delete_".$obj->getType(),
						array(ilLMObject::_lookupTitle($id), $id),
						$this->object->getType());*/

					$obj->delete();
				}
				if($tree->isInTree($id))
				{
					$tree->deleteTree($node_data);
				}
			}
		}

		// check the tree
		//		$this->object->checkTree();

		// feedback
		ilUtil::sendInfo($this->lng->txt("info_deleted"),true);

		if ($a_redirect)
		{
			$ilCtrl->redirect($this, "showOrganization");
		}
	}
	
	/**
	* Perform drag and drop action
	*/
	function proceedDragDrop()
	{
		global $ilCtrl;

		$this->object->executeDragDrop($_POST["il_hform_source_id"], $_POST["il_hform_target_id"],
			$_POST["il_hform_fc"], $_POST["il_hform_as_subitem"]);
		$ilCtrl->redirect($this, "showOrganization");
	}

	/**
	* Copy items to clipboard
	*/
	function copyItems($a_return = "showOrganization")
	{
		global $ilCtrl, $lng;

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		
		$items = ilUtil::stripSlashesArray($_POST["id"]);
		$todel = array();				// delete IDs < 0 (needed for non-js editing)
		foreach($items as $k => $item)
		{
			if ($item < 0)
			{
				$todel[] = $k;
			}
		}
		foreach($todel as $k)
		{
			unset($items[$k]);
		}
		if (!ilSCORM2004Node::uniqueTypesCheck($items))
		{
			ilUtil::sendFailure($lng->txt("sahs_choose_pages_chap_scos_ass_only"), true);
			$ilCtrl->redirect($this, $a_return);
		}
		ilSCORM2004Node::clipboardCopy($this->object->getId(), $items);

		// @todo: move this to a service since it can be used here, too
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("copy");
		ilUtil::sendInfo($lng->txt("cont_selected_items_have_been_copied"), true);

		$ilCtrl->redirect($this, $a_return);
	}

	/**
	* Copy items to clipboard, then cut them from the current tree
	*/
	function cutItems($a_return = "showOrganization")
	{
		global $ilCtrl, $lng;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		
		$items = ilUtil::stripSlashesArray($_POST["id"]);
		$todel = array();			// delete IDs < 0 (needed for non-js editing)
		foreach($items as $k => $item)
		{
			if ($item < 0)
			{
				$todel[] = $k;
			}
		}
		foreach($todel as $k)
		{
			unset($items[$k]);
		}
		
		if (!ilSCORM2004Node::uniqueTypesCheck($items))
		{
			ilUtil::sendFailure($lng->txt("sahs_choose_pages_chap_scos_ass_only"), true);
			$ilCtrl->redirect($this, $a_return);
		}

		ilSCORM2004Node::clipboardCut($this->object->getId(), $items);
		
		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		ilEditClipboard::setAction("cut");

		ilUtil::sendInfo($lng->txt("cont_selected_items_have_been_cut"), true);

		$ilCtrl->redirect($this, $a_return);
	}

	/**
	* Insert pages from clipboard
	*/
	function insertPageClip()
	{
		global $ilCtrl, $ilUser;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		ilSCORM2004Node::insertPageClip($this->object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	/**
	 * Insert scos from clipboard
	 */
	function insertScoClip()
	{
		global $ilCtrl, $ilUser;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		ilSCORM2004Node::insertScoClip($this->object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	/**
	 * Insert assets from clipboard
	 */
	function insertAssetClip()
	{
		global $ilCtrl, $ilUser;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		ilSCORM2004Node::insertAssetClip($this->object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}

	/**
	* Insert chapter from clipboard
	*/
	function insertChapterClip()
	{
		global $ilCtrl, $ilUser;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
		ilSCORM2004Node::insertChapterClip($this->object);
		
		$ilCtrl->redirect($this, "showOrganization",
			"node_".ilSCORM2004OrganizationHFormGUI::getPostNodeId());
	}


	function exportScorm2004_4th()
	{
		$export = new ilScorm2004Export($this->object,'SCORM 2004 4th');
		$export->buildExportFile();
		ilUtil::sendSuccess($this->lng->txt("exp_file_created"), true);
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportScorm2004_3rd()
	{
		$export = new ilScorm2004Export($this->object,'SCORM 2004 3rd');
		$export->buildExportFile();
		ilUtil::sendSuccess($this->lng->txt("exp_file_created"), true);
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportScorm12()
	{
		$export = new ilScorm2004Export($this->object,'SCORM 1.2');
		$export->buildExportFile();
		ilUtil::sendSuccess($this->lng->txt("exp_file_created"), true);
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportHTML()
	{
		$export = new ilScorm2004Export($this->object,'HTML');
		$export->buildExportFile();
		ilUtil::sendSuccess($this->lng->txt("exp_file_created"), true);
		$this->ctrl->redirect($this, "showExportList");
	}

	function exportHTMLOne()
	{
		$export = new ilScorm2004Export($this->object,'HTMLOne');
		$export->buildExportFile();
		ilUtil::sendSuccess($this->lng->txt("exp_file_created"), true);
		$this->ctrl->redirect($this, "showExportList");
	}

	function exportISO()
	{
		$export = new ilScorm2004Export($this->object,'ISO');
		if(!$export->buildExportFile())
		{
			if(!PATH_TO_MKISOFS)
				$this->ilias->raiseError($this->lng->txt("no_mkisofs_configured"),$this->ilias->error_obj->MESSAGE);
		}
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function exportPDF()
	{
		$export = new ilScorm2004Export($this->object,'PDF');
		$export->buildExportFile();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	function downloadExportFile()
	{
		$export = new ilSCORM2004Export($this->object);

		$export_dir = $export->getExportDirectoryForType($_GET['type']);
		ilUtil::deliverFile($export_dir."/".$_GET['file'], $_GET['file']);
	}
	
	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFile()
	{
		if(!isset($_POST["file"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"),true);
			$this->ctrl->redirect($this, "showExportList");
		}

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));
		$export_files = $this->object->getExportFiles();

		include_once "./Modules/Scorm2004/classes/class.ilSCORM2004ExportTableGUI.php";
		$table_gui = new ilSCORM2004ExportTableGUI($this, 'showExportList', true);
		$data = array();
		foreach ($export_files as $exp_file)
		{
			foreach ($_POST['file'] as $delete_file)
			{
				if (strcmp($delete_file, $exp_file['file']) == 0)
				{
					$public_str = ($exp_file["file"] == $this->object->getPublicExportFile($exp_file["type"]))
						? " <b>(".$this->lng->txt("public").")<b>"
						: "";
					$file_arr = explode("__", $exp_file["file"]);
					array_push($data, array('file' => $exp_file['file'], 'date' => ilDatePresentation::formatDate(new ilDateTime($file_arr[0], IL_CAL_UNIX)), 'size' => $exp_file['size'], 'type' => $exp_file['type'].$public_str));
				}
			}
		}
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFile()
	{
		ilSession::clear("ilExportFiles");
		$this->ctrl->redirect($this, "showExportList");
	}


	/**
	* delete export files
	*/
	function deleteExportFile()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$export = new ilSCORM2004Export($this->object);
		foreach($_POST['file'] as $idx => $file)
		{
			$export_dir = $export->getExportDirectoryForType($_POST['type'][$idx]);
			$exp_file = $export_dir."/".$file;
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('msg_deleted_export_files'), true);
		$this->ctrl->redirect($this, "showExportList");
	}
	
	/*
	* download export file
	*/
	function publishExportFile()
	{
		if(!isset($_POST["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		if (count($_POST["file"]) > 1)
		{
			$this->ilias->raiseError($this->lng->txt("cont_select_max_one_item"),$this->ilias->error_obj->MESSAGE);
		}

		$export = new ilSCORM2004Export($this->object);
		$file = $_POST['file'][0];
		$type = $_POST['type'][$_POST['file'][0]];

		if ($this->object->getPublicExportFile($type) == $file)
		{
			$this->object->setPublicExportFile($type, "");
		}
		else
		{
			$this->object->setPublicExportFile($type, $file);
		}
		$this->object->update();
		$this->ctrl->redirect($this, "showExportList");
	}
	
	/*
	 * perform silent scorm 2004 export and import for view player
	 */
	function preview()
	{
		global $ilias;
		
		$export = new ilScorm2004Export($this->object,'SCORM 2004 3rd');
		$zipfile = $export->buildExportFile();
		$zipPathinfo = pathinfo($zipfile);
		$file_path = $this->object->getDataDirectory()."/".($zipPathinfo["basename"]);
		copy($zipfile,$file_path);
		unlink($zipfile);
		
		ilUtil::unzip($file_path,true);
		ilUtil::renameExecutables($this->object->getDataDirectory());
		unlink($file_path);
		
		include_once ("./Modules/Scorm2004/classes/ilSCORM13Package.php");
		$rte_pkg = new ilSCORM13Package();
		$rte_pkg->il_import($this->object->getDataDirectory(),$this->object->getId(),$ilias,false,true);

		//increase module version is it necessary?
		//$this->object->setModuleVersion($module_version+1);
		//$this->object->update();
			
		//redirect to view player
		ilUtil::redirect("ilias.php?baseClass=ilSAHSPresentationGUI&ref_id=".$this->object->getRefID()."&envEditor=1");
	}


}
?>
