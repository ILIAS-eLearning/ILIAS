<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjExerciseGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @author Michael Jansen <mjansen@databay.de>
* $Id$
*
* @ilCtrl_Calls ilObjExerciseGUI: ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilObjectCopyGUI, ilExportGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilCommonActionDispatcherGUI, ilCertificateGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilExAssignmentEditorGUI, ilExSubmissionGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilExerciseManagementGUI, ilExcCriteriaCatalogueGUI
*
* @ingroup ModulesExercise
*/
class ilObjExerciseGUI extends ilObjectGUI
{
    /**
     * @var
     */
    private $certificateDownloadValidator;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilExAssignment
     */
    protected $ass = null;

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->help = $DIC["ilHelp"];
        $this->locator = $DIC["ilLocator"];
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $lng = $DIC->language();

        $this->lng->loadLanguageModule('cert');
        
        $this->type = "exc";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        
        $lng->loadLanguageModule("exercise");
        $lng->loadLanguageModule("exc");
        $this->ctrl->saveParameter($this, "ass_id");

        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        if ($_REQUEST["ass_id"] > 0 && is_object($this->object) && ilExAssignment::lookupExerciseId($_REQUEST["ass_id"]) == $this->object->getId()) {
            $this->ass = new ilExAssignment((int) $_REQUEST["ass_id"]);
        } elseif ($_REQUEST["ass_id"] > 0) {
            throw new ilExerciseException("Assignment ID does not match Exercise.");
        }


        $this->certificateDownloadValidator = new ilCertificateDownloadValidator();
    }

    public function executeCommand()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
  
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();
  
        //echo "-".$next_class."-".$cmd."-"; exit;
        switch ($next_class) {
            case "ilinfoscreengui":
                $ilTabs->activateTab("info");
                $this->infoScreen();	// forwards command
                break;

            case 'ilpermissiongui':
                $ilTabs->activateTab("permissions");
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
            break;
    
            case "illearningprogressgui":
                $ilTabs->activateTab("learning_progress");
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
    
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
            break;
            
            case 'ilobjectcopygui':
                $ilCtrl->saveParameter($this, 'new_type');
                $ilCtrl->setReturnByClass(get_class($this), 'create');

                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('exc');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilexportgui":
                $ilTabs->activateTab("export");
                include_once("./Services/Export/classes/class.ilExportGUI.php");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
                break;
            
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "ilcertificategui":
                $this->setSettingsSubTabs();
                $this->tabs_gui->activateTab("settings");
                $this->tabs_gui->activateSubTab("certificate");

                $guiFactory = new ilCertificateGUIFactory();
                $output_gui = $guiFactory->create($this->object);

                $this->ctrl->forwardCommand($output_gui);
                break;
            
            case "ilexassignmenteditorgui":
                $this->checkPermission("write");
                $ilTabs->activateTab("content");
                $this->addContentSubTabs("list_assignments");
                include_once("./Modules/Exercise/classes/class.ilExAssignmentEditorGUI.php");
                $ass_gui = new ilExAssignmentEditorGUI($this->object->getId(), $this->object->isCompletionBySubmissionEnabled(), $this->ass);
                $this->ctrl->forwardCommand($ass_gui);
                break;
            
            case "ilexsubmissiongui":
                $this->checkPermission("read");
                $ilTabs->activateTab("content");
                $this->addContentSubTabs("content");
                $this->ctrl->setReturn($this, "showOverview");
                include_once("./Modules/Exercise/classes/class.ilExSubmissionGUI.php");
                $sub_gui = new ilExSubmissionGUI($this->object, $this->ass, (int) $_REQUEST["member_id"]);
                $this->ctrl->forwardCommand($sub_gui);
                break;
            
            case "ilexercisemanagementgui":
                // rbac or position access
                if ($GLOBALS['DIC']->access()->checkRbacOrPositionPermissionAccess(
                    'edit_submissions_grades',
                    'edit_submissions_grades',
                    $this->object->getRefId()
                )) {
                    $ilTabs->activateTab("grades");
                    include_once("./Modules/Exercise/classes/class.ilExerciseManagementGUI.php");
                    $mgmt_gui = new ilExerciseManagementGUI($this->object, $this->ass);
                    $this->ctrl->forwardCommand($mgmt_gui);
                } else {
                    $this->checkPermission("edit_submissions_grades");	// throw error by standard procedure
                }
                break;
            
            case "ilexccriteriacataloguegui":
                $this->checkPermission("write");
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs();
                $ilTabs->activateSubTab("crit");
                include_once("./Modules/Exercise/classes/class.ilExcCriteriaCatalogueGUI.php");
                $crit_gui = new ilExcCriteriaCatalogueGUI($this->object);
                $this->ctrl->forwardCommand($crit_gui);
                break;
                
            default:
                if (!$cmd) {
                    $cmd = "infoScreen";
                }
    
                $cmd .= "Object";
    
                $this->$cmd();
    
            break;
        }
        
        $this->addHeaderAction();
  
        return true;
    }

    public function viewObject()
    {
        $this->infoScreenObject();
    }
    
    protected function afterSave(ilObject $a_new_object)
    {
        $ilCtrl = $this->ctrl;
        
        $a_new_object->saveData();
        
        ilUtil::sendSuccess($this->lng->txt("exc_added"), true);
        
        $ilCtrl->setParameterByClass("ilExAssignmentEditorGUI", "ref_id", $a_new_object->getRefId());
        $ilCtrl->redirectByClass("ilExAssignmentEditorGUI", "addAssignment");
    }

    protected function listAssignmentsObject()
    {
        $ilCtrl = $this->ctrl;
        
        $this->checkPermissionBool("write");
        
        // #16587
        $ilCtrl->redirectByClass("ilExAssignmentEditorGUI", "listAssignments");
    }
    
    /**
    * Init properties form.
    */
    protected function initEditCustomForm(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->getObjectService();

        $a_form->setTitle($this->lng->txt("exc_edit_exercise"));

        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('obj_presentation'));
        $a_form->addItem($pres);

        // tile image
        $a_form = $obj_service->commonSettings()->legacyForm($a_form, $this->object)->addTileImage();

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('exc_passing_exc'));
        $a_form->addItem($section);

        // pass mode
        $radg = new ilRadioGroupInputGUI($this->lng->txt("exc_pass_mode"), "pass_mode");
    
        $op1 = new ilRadioOption(
            $this->lng->txt("exc_pass_all"),
            "all",
            $this->lng->txt("exc_pass_all_info")
        );
        $radg->addOption($op1);
        $op2 = new ilRadioOption(
            $this->lng->txt("exc_pass_minimum_nr"),
            "nr",
            $this->lng->txt("exc_pass_minimum_nr_info")
        );
        $radg->addOption($op2);

        // minimum number of assignments to pass
        $ni = new ilNumberInputGUI($this->lng->txt("exc_min_nr"), "pass_nr");
        $ni->setSize(4);
        $ni->setMaxLength(4);
        $ni->setRequired(true);
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        $mand = ilExAssignment::countMandatory($this->object->getId());
        $min = max($mand, 1);
        $ni->setMinValue($min, true);
        $ni->setInfo($this->lng->txt("exc_min_nr_info"));
        $op2->addSubItem($ni);

        $a_form->addItem($radg);

        // completion by submission
        $subcompl = new ilRadioGroupInputGUI($this->lng->txt("exc_passed_status_determination"), "completion_by_submission");
        $op1 = new ilRadioOption($this->lng->txt("exc_completion_by_tutor"), 0, "");
        $subcompl->addOption($op1);
        $op2 = new ilRadioOption($this->lng->txt("exc_completion_by_submission"), 1, $this->lng->txt("exc_completion_by_submission_info"));
        $subcompl->addOption($op2);
        $a_form->addItem($subcompl);

        /*$subcompl = new ilCheckboxInputGUI($this->lng->txt('exc_completion_by_submission'), 'completion_by_submission');
        $subcompl->setInfo($this->lng->txt('exc_completion_by_submission_info'));
        $subcompl->setValue(1);
        $a_form->addItem($subcompl);*/

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('exc_publishing'));
        $a_form->addItem($section);

        // show submissions
        $cb = new ilCheckboxInputGUI($this->lng->txt("exc_show_submissions"), "show_submissions");
        $cb->setInfo($this->lng->txt("exc_show_submissions_info"));
        $a_form->addItem($cb);
        
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('exc_notification'));
        $a_form->addItem($section);

        // submission notifications
        $cbox = new ilCheckboxInputGUI($this->lng->txt("exc_submission_notification"), "notification");
        $cbox->setInfo($this->lng->txt("exc_submission_notification_info"));
        $a_form->addItem($cbox);
        
        
        // feedback settings
        
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('exc_feedback'));
        $a_form->addItem($section);
        
        $fdb = new ilCheckboxGroupInputGUI($this->lng->txt("exc_settings_feedback"), "tfeedback");
        $a_form->addItem($fdb);
        
        $option = new ilCheckboxOption($this->lng->txt("exc_settings_feedback_mail"), ilObjExercise::TUTOR_FEEDBACK_MAIL);
        $option->setInfo($this->lng->txt("exc_settings_feedback_mail_info"));
        $fdb->addOption($option);
        $option = new ilCheckboxOption($this->lng->txt("exc_settings_feedback_file"), ilObjExercise::TUTOR_FEEDBACK_FILE);
        $option->setInfo($this->lng->txt("exc_settings_feedback_file_info"));
        $fdb->addOption($option);
        $option = new ilCheckboxOption($this->lng->txt("exc_settings_feedback_text"), ilObjExercise::TUTOR_FEEDBACK_TEXT);
        $option->setInfo($this->lng->txt("exc_settings_feedback_text_info"));
        $fdb->addOption($option);
        
        $position_settings = ilOrgUnitGlobalSettings::getInstance()
            ->getObjectPositionSettingsByType($this->object->getType());

        if ($position_settings->isActive()) {
            // add additional feature section
            $feat = new ilFormSectionHeaderGUI();
            $feat->setTitle($this->lng->txt('obj_features'));
            $a_form->addItem($feat);

            // add orgunit settings
            ilObjectServiceSettingsGUI::initServiceSettingsForm(
                $this->object->getId(),
                $a_form,
                array(
                        ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS
                    )
            );
        }
    }
    
    /**
    * Get values for properties form
    */
    protected function getEditFormCustomValues(array &$a_values)
    {
        $ilUser = $this->user;

        $a_values["desc"] = $this->object->getLongDescription();
        $a_values["show_submissions"] = $this->object->getShowSubmissions();
        $a_values["pass_mode"] = $this->object->getPassMode();
        if ($a_values["pass_mode"] == "nr") {
            $a_values["pass_nr"] = $this->object->getPassNr();
        }
        
        include_once "./Services/Notification/classes/class.ilNotification.php";
        $a_values["notification"] = ilNotification::hasNotification(
            ilNotification::TYPE_EXERCISE_SUBMISSION,
            $ilUser->getId(),
            $this->object->getId()
        );
                
        $a_values['completion_by_submission'] = (int) $this->object->isCompletionBySubmissionEnabled();
        
        $tfeedback = array();
        if ($this->object->hasTutorFeedbackMail()) {
            $tfeedback[] = ilObjExercise::TUTOR_FEEDBACK_MAIL;
        }
        if ($this->object->hasTutorFeedbackText()) {
            $tfeedback[] = ilObjExercise::TUTOR_FEEDBACK_TEXT;
        }
        if ($this->object->hasTutorFeedbackFile()) {
            $tfeedback[] = ilObjExercise::TUTOR_FEEDBACK_FILE;
        }
        $a_values['tfeedback'] = $tfeedback;

        // orgunit position setting enabled
        $a_values['obj_orgunit_positions'] = (bool) ilOrgUnitGlobalSettings::getInstance()
            ->isPositionAccessActiveForObject($this->object->getId());
    }

    protected function updateCustom(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->getObjectService();

        $ilUser = $this->user;
        $this->object->setShowSubmissions($a_form->getInput("show_submissions"));
        $this->object->setPassMode($a_form->getInput("pass_mode"));
        if ($this->object->getPassMode() == "nr") {
            $this->object->setPassNr($a_form->getInput("pass_nr"));
        }
        
        $this->object->setCompletionBySubmission($a_form->getInput('completion_by_submission') == 1 ? true : false);
        
        $feedback = $a_form->getInput("tfeedback");
        $this->object->setTutorFeedback(is_array($feedback)
            ? array_sum($feedback)
            : null);
        
        include_once "./Services/Notification/classes/class.ilNotification.php";
        ilNotification::setNotification(
            ilNotification::TYPE_EXERCISE_SUBMISSION,
            $ilUser->getId(),
            $this->object->getId(),
            (bool) $a_form->getInput("notification")
        );

        // tile image
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveTileImage();

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $a_form,
            array(
                ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS
            )
        );
    }
  
    /**
     * Add subtabs of content view
     *
     * @param	object		$tabs_gui		ilTabsGUI object
     */
    public function addContentSubTabs($a_activate)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $ilTabs->addSubTab(
            "content",
            $lng->txt("view"),
            $ilCtrl->getLinkTarget($this, "showOverview")
        );
        if ($this->checkPermissionBool("write")) {
            $ilTabs->addSubTab(
                "list_assignments",
                $lng->txt("edit"),
                $ilCtrl->getLinkTargetByClass("ilExAssignmentEditorGUI", "listAssignments")
            );
        }
        $ilTabs->activateSubTab($a_activate);
    }

    /**
    * adds tabs to tab gui object
    *
    * @param	object		$tabs_gui		ilTabsGUI object
    */
    public function getTabs()
    {
        $lng = $this->lng;
        $ilHelp = $this->help;
  
        $ilHelp->setScreenIdComponent("exc");
        
        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "content",
                $lng->txt("exc_assignments"),
                $this->ctrl->getLinkTarget($this, "showOverview")
            );
        }

        $next_class = strtolower($this->ctrl->getNextClass());
        if ($this->checkPermissionBool("visible") || $this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary")
            );
        }

        // edit properties
        if ($this->checkPermissionBool("write")) {
            /*$tabs_gui->addTab("assignments",
                $lng->txt("exc_edit_assignments"),
                $this->ctrl->getLinkTarget($this, 'listAssignments'));*/
            
            $this->tabs_gui->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, 'edit')
            );
        }
        if ($this->access->checkRbacOrPositionPermissionAccess(
            'edit_submissions_grades',
            'edit_submissions_grades',
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTab(
                "grades",
                $lng->txt("exc_submissions_and_grades"),
                $this->ctrl->getLinkTargetByClass("ilexercisemanagementgui", "members")
            );
        }

        // learning progress
        $save_sort_order = $_GET["sort_order"];		// hack, because exercise sort parameters
        $save_sort_by = $_GET["sort_by"];			// must not be forwarded to learning progress
        $save_offset = $_GET["offset"];
        $_GET["offset"] = $_GET["sort_by"] = $_GET["sort_order"] = "";
        
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'learning_progress',
                $lng->txt('learning_progress'),
                $this->ctrl->getLinkTargetByClass(array('ilobjexercisegui','illearningprogressgui'), '')
            );
        }

        $_GET["sort_order"] = $save_sort_order;		// hack, part ii
        $_GET["sort_by"] = $save_sort_by;
        $_GET["offset"] = $save_offset;

        // export
        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "export",
                $lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }


        // permissions
        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab(
                'permissions',
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
            );
        }
    }
    
    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    /**
    * show information screen
    */
    public function infoScreen()
    {
        $ilUser = $this->user;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        $ilTabs->activateTab("info");

        if (!$this->checkPermissionBool("read")) {
            $this->checkPermission("visible");
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        
        $info->enablePrivateNotes();
        
        $info->enableNews();
        if ($this->checkPermissionBool("write")) {
            $info->enableNewsEditing();
            $info->setBlockProperty("news", "settings", true);
        }
        
        // standard meta data
        //$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());

        // instructions
        $info->addSection($this->lng->txt("exc_overview"));
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        $ass = ilExAssignment::getAssignmentDataOfExercise($this->object->getId());
        $cnt = 0;
        $mcnt = 0;
        foreach ($ass as $a) {
            $cnt++;
            if ($a["mandatory"]) {
                $mcnt++;
            }
        }
        $info->addProperty($lng->txt("exc_assignments"), $cnt);
        $info->addProperty($lng->txt("exc_mandatory"), $mcnt);
        if ($this->object->getPassMode() != "nr") {
            $info->addProperty(
                $lng->txt("exc_pass_mode"),
                $lng->txt("exc_msg_all_mandatory_ass")
            );
        } else {
            $info->addProperty(
                $lng->txt("exc_pass_mode"),
                sprintf($lng->txt("exc_msg_min_number_ass"), $this->object->getPassNr())
            );
        }

        // feedback from tutor
        include_once("Services/Tracking/classes/class.ilLPMarks.php");
        if ($this->checkPermissionBool("read")) {
            $lpcomment = ilLPMarks::_lookupComment($ilUser->getId(), $this->object->getId());
            $mark = ilLPMarks::_lookupMark($ilUser->getId(), $this->object->getId());
            //$status = ilExerciseMembers::_lookupStatus($this->object->getId(), $ilUser->getId());
            $st = $this->object->determinStatusOfUser($ilUser->getId());
            $status = $st["overall_status"];
            if ($lpcomment != "" || $mark != "" || $status != "notgraded") {
                $info->addSection($this->lng->txt("exc_feedback_from_tutor"));
                if ($lpcomment != "") {
                    $info->addProperty(
                        $this->lng->txt("exc_comment"),
                        $lpcomment
                    );
                }
                if ($mark != "") {
                    $info->addProperty(
                        $this->lng->txt("exc_mark"),
                        $mark
                    );
                }

                //if ($status == "")
                //{
                //  $info->addProperty($this->lng->txt("status"),
                //		$this->lng->txt("message_no_delivered_files"));
                //}
                //else
                if ($status != "notgraded") {
                    $img = '<img src="' . ilUtil::getImagePath("scorm/" . $status . ".svg") . '" ' .
                        ' alt="' . $lng->txt("exc_" . $status) . '" title="' . $lng->txt("exc_" . $status) .
                        '" />';

                    $add = "";
                    if ($st["failed_a_mandatory"]) {
                        $add = " (" . $lng->txt("exc_msg_failed_mandatory") . ")";
                    } elseif ($status == "failed") {
                        $add = " (" . $lng->txt("exc_msg_missed_minimum_number") . ")";
                    }
                    $info->addProperty(
                        $this->lng->txt("status"),
                        $img . " " . $this->lng->txt("exc_" . $status) . $add
                    );
                }
            }
        }
        
        // forward the command
        $this->ctrl->forwardCommand($info);
    }
    
    public function editObject()
    {
        $this->setSettingsSubTabs();
        $this->tabs_gui->activateSubTab("edit");
        return parent::editObject();
    }
    
    protected function setSettingsSubTabs()
    {
        $this->tabs_gui->addSubTab(
            "edit",
            $this->lng->txt("general_settings"),
            $this->ctrl->getLinkTarget($this, "edit")
        );
        
        $this->tabs_gui->addSubTab(
            "crit",
            $this->lng->txt("exc_criteria_catalogues"),
            $this->ctrl->getLinkTargetByClass("ilexccriteriacataloguegui", "")
        );
        
        include_once "Services/Certificate/classes/class.ilCertificate.php";
        if (ilCertificate::isActive()) {
            $this->tabs_gui->addSubTab(
                "certificate",
                $this->lng->txt("certificate"),
                $this->ctrl->getLinkTarget($this, "certificate")
            );
        }
    }

    /**
    * redirect script
    *
    * @param	string		$a_target
    */
    public static function _goto($a_target, $a_raw)
    {
        global $DIC;

        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ilCtrl = $DIC->ctrl();

        //we don't have baseClass here...
        $ilCtrl->setTargetScript("ilias.php");
        $ilCtrl->initBaseClass("ilRepositoryGUI");

        //ilExerciseMailNotification has links to:
        // "Assignments", "Submission and Grades" and Downnoad the NEW files if the assignment type is "File Upload".
        $ass_id = $_GET['ass_id'];
        $parts = explode("_", $a_raw);
        if (!$ass_id) {
            $ass_id = null;
            $action = null;

            switch (end($parts)) {
                case "download":
                    $action = $parts[3];
                    $member = $parts[2];
                    $ass_id = $parts[1];
                    break;

                case "setdownload":
                    $action = $parts[3];
                    $member = $parts[2];
                    $ass_id = $parts[1];
                    break;

                case "grades":
                    $action = $parts[2];
                    $ass_id = $parts[1];
                    break;
            }
        }

        $ilCtrl->setParameterByClass("ilExerciseHandlerGUI", "ref_id", $a_target);

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            $ilCtrl->setParameterByClass("ilExerciseHandlerGUI", "target", $a_raw);

            if ($ass_id) {
                $ilCtrl->setParameterByClass("ilExerciseManagementGUI", "ass_id", $ass_id);
            }

            switch ($action) {
                case "grades":
                    $ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilExerciseHandlerGUI", "ilObjExerciseGUI", "ilExerciseManagementGUI"), "members");
                    break;

                /*case "download":
                    $ilCtrl->setParameterByClass("ilExerciseHandlerGUI", "member_id", $member);
                    $ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilExerciseHandlerGUI", "ilObjExerciseGUI", "ilExerciseManagementGUI", "ilExSubmissionFileGUI"),"downloadNewReturned");
                    break;*/

                case "setdownload":
                    $ilCtrl->setParameterByClass("ilExerciseHandlerGUI", "member_id", $member);
                    $ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilExerciseHandlerGUI", "ilObjExerciseGUI", "ilExerciseManagementGUI"), "waitingDownload");
                    break;

                default:
                    if ($parts[1] != "") {
                        $ilCtrl->setParameterByClass("ilExerciseHandlerGUI", "ass_id", $parts[1]);
                        $ilCtrl->setParameterByClass("ilExerciseHandlerGUI", "ass_id_goto", $parts[1]);
                    }
                    $ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilExerciseHandlerGUI", "ilObjExerciseGUI"), "showOverview");
                    break;

            }
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            $ilCtrl->redirectByClass(array("ilRepositoryGUI", "ilExerciseHandlerGUI", "ilObjExerciseGUI"), "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }

    /**
    * Add locator item
    */
    public function addLocatorItems()
    {
        $ilLocator = $this->locator;
        
        if (is_object($this->object)) {
            // #17955
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "showOverview"), "", $_GET["ref_id"]);
        }
    }
    
    
    ////
    //// Assignments, Learner's View
    ////

    /**
     * Show overview of assignments
     */
    public function showOverviewObject()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilUser = $this->user;
        $ilToolbar = $this->toolbar;
        
        $this->checkPermission("read");

        $tpl->addJavaScript("./Modules/Exercise/js/ilExcPresentation.js");
        
        include_once("./Services/Tracking/classes/class.ilLearningProgress.php");
        ilLearningProgress::_tracProgress(
            $ilUser->getId(),
            $this->object->getId(),
            $this->object->getRefId(),
            'exc'
        );
        
        $ilTabs->activateTab("content");
        $this->addContentSubTabs("content");

        if ($this->certificateDownloadValidator->isCertificateDownloadable((int) $ilUser->getId(), (int) $this->object->getId())) {
            $ilToolbar->addButton(
                $this->lng->txt("certificate"),
                $this->ctrl->getLinkTarget($this, "outCertificate")
            );
        }

        include_once("./Modules/Exercise/classes/class.ilExAssignmentGUI.php");
        $ass_gui = new ilExAssignmentGUI($this->object);
                
        include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
        $acc = new ilAccordionGUI();
        $acc->setId("exc_ow_" . $this->object->getId());

        $ass_data = ilExAssignment::getInstancesByExercise($this->object->getId());
        foreach ($ass_data as $ass) {
            // incoming assignment deeplink
            $force_open = false;
            if (isset($_GET["ass_id_goto"]) &&
                (int) $_GET["ass_id_goto"] == $ass->getId()) {
                $force_open = true;
            }
            
            $acc->addItem(
                $ass_gui->getOverviewHeader($ass),
                $ass_gui->getOverviewBody($ass),
                $force_open
            );
        }
        
        if (count($ass_data) < 2) {
            $acc->setBehaviour("FirstOpen");
        } else {
            $acc->setUseSessionStorage(true);
        }

        $mtpl = new ilTemplate("tpl.exc_ass_overview.html", true, true, "Modules/Exercise");
        $mtpl->setVariable("CONTENT", $acc->getHTML());

        $tpl->setContent($mtpl->get());
    }
    
    public function certificateObject()
    {
        $this->setSettingsSubTabs();
        $this->tabs_gui->activateTab("settings");
        $this->tabs_gui->activateSubTab("certificate");

        $guiFactory = new ilCertificateGUIFactory();
        $output_gui = $guiFactory->create($this->object);

        $output_gui->certificateEditor();
    }
    
    public function outCertificateObject()
    {
        global $DIC;

        $database = $DIC->database();
        $logger = $DIC->logger()->root();

        $ilUser = $this->user;

        $objectId = (int) $this->object->getId();

        if (false === $this->certificateDownloadValidator->isCertificateDownloadable($ilUser->getId(), $objectId)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }

        $ilUserCertificateRepository = new ilUserCertificateRepository($database, $logger);
        $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository, $logger);

        $pdfAction = new ilCertificatePdfAction(
            $logger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->lng->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf((int) $ilUser->getId(), (int) $objectId);
    }

    /**
     * Start assignment with relative deadline
     */
    public function startAssignmentObject()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();

        if ($this->ass) {
            include_once("./Modules/Exercise/classes/class.ilExcAssMemberState.php");
            $state = ilExcAssMemberState::getInstanceByIds($this->ass->getId(), $ilUser->getId());
            if (!$state->getCommonDeadline() && $state->getRelativeDeadline()) {
                $idl = $state->getIndividualDeadlineObject();
                $idl->setStartingTimestamp(time());
                $idl->save();
            }
        }

        $ilCtrl->redirect($this, "showOverview");
    }
}
