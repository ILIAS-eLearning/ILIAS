<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjFolderGUI: ilPermissionGUI
* @ilCtrl_Calls ilObjFolderGUI: ilCourseContentGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjFolderGUI: ilInfoScreenGUI, ilContainerPageGUI, ilColumnGUI
* @ilCtrl_Calls ilObjFolderGUI: ilObjectCopyGUI, ilObjStyleSheetGUI
* @ilCtrl_Calls ilObjFolderGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilDidacticTemplateGUI
* @ilCtrl_Calls ilObjFolderGUI: ilBackgroundTaskHub, ilObjectTranslationGUI
*
* @extends ilObjectGUI
*/

require_once "./Services/Container/classes/class.ilContainerGUI.php";

class ilObjFolderGUI extends ilContainerGUI
{
    /**
     * @var ilHelpGUI
     */
    protected $help;

    public $folder_tree;		// folder tree

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = false)
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->help = $DIC["ilHelp"];
        $this->error = $DIC["ilErr"];
        $this->tpl = $DIC["tpl"];
        $this->settings = $DIC->settings();
        $this->type = "fold";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output, false);

        $this->lng->loadLanguageModule("obj");
    }


    /**
    * View folder
    */
    public function viewObject()
    {
        $tree = $this->tree;
        
        $this->checkPermission('read');

        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            parent::viewObject();
            return true;
        }
        
        // Trac access - see ilObjCourseGUI
        include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
        ilLearningProgress::_tracProgress(
            $GLOBALS["ilUser"]->getId(),
            $this->object->getId(),
            $this->object->getRefId(),
            'fold'
        );
        
        $this->renderObject();
        $this->tabs_gui->setTabActive('view_content');
        return true;
    }
        
    /**
    * Render folder
    */
    public function renderObject()
    {
        $ilTabs = $this->tabs;
        
        $this->checkPermission('read');

        $ilTabs->activateTab("view_content");
        $ret = parent::renderObject();
        return $ret;
    }

    public function executeCommand()
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        // show repository tree
        $this->showRepTree();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;


            case 'ilcoursecontentgui':
                $this->prepareOutput();
                include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
                $course_content_obj = new ilCourseContentGUI($this);
                $this->ctrl->forwardCommand($course_content_obj);
                break;
            
            case "illearningprogressgui":
                $this->prepareOutput();
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
                
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
                break;

            // container page editing
            case "ilcontainerpagegui":
                $this->prepareOutput(false);
                $ret = $this->forwardToPageObject();
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                break;

            case 'ilinfoscreengui':
                $this->prepareOutput();
                $this->infoScreen();
                break;

            case 'ilobjectcopygui':
                $this->prepareOutput();

                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('fold');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilobjstylesheetgui":
                $this->forwardToStyleSheet();
                break;
                
            case 'ilexportgui':
                $this->prepareOutput();
                    
                $this->tabs_gui->setTabActive('export');
                include_once './Services/Export/classes/class.ilExportGUI.php';
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;
            
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ildidactictemplategui':
                $this->ctrl->setReturn($this, 'edit');
                include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
                $did = new ilDidacticTemplateGUI($this);
                $this->ctrl->forwardCommand($did);
                break;
            case 'ilcolumngui':
                $this->tabs_gui->setTabActive('none');
                $this->checkPermission("read");
                $this->viewObject();
                break;
            
            case 'ilbackgroundtaskhub':
                include_once './Services/BackgroundTask/classes/class.ilBackgroundTaskHub.php';
                $bggui = new ilBackgroundTaskHub();
                $this->ctrl->forwardCommand($bggui);
                break;

            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                $this->setSubTabs("settings_trans");
                include_once("./Services/Object/classes/class.ilObjectTranslationGUI.php");
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;

            default:

                $this->prepareOutput();
                // cognos-blu-patch: begin
                // removed timings forward
                // cognos-blu-patch: end

                if (empty($cmd)) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
        
        $this->addHeaderAction();
    }

    /**
    * set tree
    */
    public function setFolderTree($a_tree)
    {
        $this->folder_tree = &$a_tree;
    }

    /**
     * Import file object
     * @global type $lng
     * @param type $parent_id
     * @param type $a_catch_errors
     */
    public function importFileObject($parent_id = null, $a_catch_errors = true)
    {
        $lng = $this->lng;
        
        if (parent::importFileObject($parent_id, $a_catch_errors)) {
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $this->ctrl->returnToParent($this);
        }
    }

    /**
     * Init object edit form
     *
     * @return ilPropertyFormGUI
     */
    protected function initEditForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $obj_service = $this->getObjectService();

        $lng->loadLanguageModule($this->object->getType());

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "update"));
        $form->setTitle($this->lng->txt($this->object->getType() . "_edit"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        // Show didactic template type
        $this->initDidacticTemplate($form);

        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('fold_presentation'));
        $form->addItem($pres);

        // title and icon visibility
        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTitleIconVisibility();

        // top actions visibility
        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTopActionsVisibility();

        // custom icon
        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addIcon();

        // tile image
        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

        // list presentation
        $form = $this->initListPresentationForm($form);

        $this->initSortingForm(
            $form,
            array(
                ilContainer::SORT_INHERIT,
                ilContainer::SORT_TITLE,
                ilContainer::SORT_CREATION,
                ilContainer::SORT_MANUAL
            )
        );

        $form->addCommandButton("update", $this->lng->txt("save"));
        //$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));

        return $form;
    }

    protected function getEditFormCustomValues(array &$a_values)
    {
        // we cannot use $this->object->getOrderType()
        // if set to inherit it will be translated to parent setting
        include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
        $sort = new ilContainerSortingSettings($this->object->getId());
        $a_values["sor"] = $sort->getSortMode();
    }

    protected function updateCustom(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->getObjectService();

        // title icon visibility
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveTitleIconVisibility();

        // top actions visibility
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveTopActionsVisibility();

        // custom icon
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveIcon();

        // tile image
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveTileImage();

        // list presentation
        $this->saveListPresentation($a_form);

        $this->saveSortingSettings($a_form);
    }
    
    // BEGIN ChangeEvent show info screen on folder object
    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function showSummaryObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }
    
    protected function afterSave(ilObject $a_new_object)
    {
        include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
        $sort = new ilContainerSortingSettings($a_new_object->getId());
        $sort->setSortMode(ilContainer::SORT_INHERIT);
        $sort->update();
        
        // always send a message
        ilUtil::sendSuccess($this->lng->txt("fold_added"), true);
        $this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
        $this->redirectToRefId($a_new_object->getRefId(), "");
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
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("visible", "", $this->ref_id)) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->MESSAGE);
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        
        $GLOBALS['ilTabs']->activateTab('info_short');

        $info->enablePrivateNotes();
        
        if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
                $info->setBlockProperty("news", "public_notifications_option", true);
            }
        }

        
        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        // forward the command
        $this->ctrl->forwardCommand($info);
    }
    // END ChangeEvent show info screen on folder object

    /**
    * Get tabs
    */
    public function getTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilHelp = $this->help;

        $this->ctrl->setParameter($this, "ref_id", $this->ref_id);
        
        $ilHelp->setScreenIdComponent("fold");

        $this->tabs_gui->setTabActive("");
        if ($rbacsystem->checkAccess('read', $this->ref_id)) {
            $this->tabs_gui->addTab(
                "view_content",
                $lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "")
            );

            //BEGIN ChangeEvent add info tab to category object
            $force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
                || strtolower($_GET["cmdClass"]) == "ilnotegui")
                ? true
                : false;
            $this->tabs_gui->addTarget(
                "info_short",
                $this->ctrl->getLinkTargetByClass(
                    array("ilobjfoldergui", "ilinfoscreengui"),
                    "showSummary"
                ),
                array("showSummary","", "infoScreen"),
                "",
                "",
                $force_active
            );
            //END ChangeEvent add info tab to category object
        }
        
        if ($rbacsystem->checkAccess('write', $this->ref_id)) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                "edit",
                "",
                "",
                ($ilCtrl->getCmd() == "edit")
            );
        }

        // learning progress
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(array('ilobjfoldergui','illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }
        
        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass('ilexportgui', ''),
                'export',
                'ilexportgui'
            );
        }
        

        if ($rbacsystem->checkAccess('edit_permission', $this->ref_id)) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }

        // show clipboard in repository
        if ($_GET["baseClass"] == "ilRepositoryGUI" and !empty($_SESSION['il_rep_clipboard'])) {
            $this->tabs_gui->addTarget(
                "clipboard",
                $this->ctrl->getLinkTarget($this, "clipboard"),
                "clipboard",
                get_class($this)
            );
        }
    }

    /**
    * goto target group
    */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target);
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }


    public function downloadFolderObject()
    {
        $ilAccess = $this->access;
        $ilErr = $this->error;
        $lng = $this->lng;
            
        if (!$ilAccess->checkAccess("read", "", $this->ref_id)) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->MESSAGE);
        }
        $filename = $this->object->downloadFolder();
        ilUtil::deliverFile($filename, ilUtil::getASCIIFilename($this->object->getTitle() . ".zip"));
    }
    
    /**
     * Modify Item ListGUI for presentation in container
     * @global type $tree
     * @param type $a_item_list_gui
     * @param type $a_item_data
     * @param type $a_show_path
     */
    public function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
    {
        $tree = $this->tree;

        // if folder is in a course, modify item list gui according to course requirements
        if ($course_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs')) {
            include_once("./Modules/Course/classes/class.ilObjCourse.php");
            include_once("./Modules/Course/classes/class.ilObjCourseGUI.php");
            $course_obj_id = ilObject::_lookupObjId($course_ref_id);
            ilObjCourseGUI::_modifyItemGUI(
                $a_item_list_gui,
                'ilcoursecontentgui',
                $a_item_data,
                $a_show_path,
                ilObjCourse::_lookupAboStatus($course_obj_id),
                $course_ref_id,
                $course_obj_id,
                $this->object->getRefId()
            );
        }
    }
    
    protected function forwardToTimingsView()
    {
        $tree = $this->tree;
        
        if (!$crs_ref = $tree->checkForParentType($this->ref_id, 'crs')) {
            return false;
        }
        include_once './Modules/Course/classes/class.ilObjCourse.php';
        if (!$this->ctrl->getCmd() and ilObjCourse::_lookupViewMode(ilObject::_lookupObjId($crs_ref)) == ilContainer::VIEW_TIMING) {
            if (!isset($_SESSION['crs_timings'])) {
                $_SESSION['crs_timings'] = true;
            }
            
            if ($_SESSION['crs_timings'] == true) {
                include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
                $course_content_obj = new ilCourseContentGUI($this);
                $this->ctrl->setCmdClass(get_class($course_content_obj));
                $this->ctrl->setCmd('editUserTimings');
                $this->ctrl->forwardCommand($course_content_obj);
                return true;
            }
        }
        $_SESSION['crs_timings'] = false;
        return false;
    }
    
    /**
     * Edit
     *
     * @param
     * @return
     */
    public function editObject()
    {
        $ilTabs = $this->tabs;
        $ilErr = $this->error;
        
        $this->setSubTabs("settings");
        $ilTabs->activateTab("settings");

        if (!$this->checkPermissionBool("write")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        }

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values, true);
        }
        $GLOBALS['tpl']->setContent($form->getHTML());
    }
    
    
    /**
     * Set sub tabs
     */
    public function setSubTabs($a_tab)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        
        $ilTabs->addSubTab(
            "settings",
            $lng->txt("fold_settings"),
            $this->ctrl->getLinkTarget($this, 'edit')
        );

        $this->tabs_gui->addSubTab(
            "settings_trans",
            $this->lng->txt("obj_multilinguality"),
            $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "")
        );

        $ilTabs->activateSubTab($a_tab);
        $ilTabs->activateTab("settings");
    }
} // END class.ilObjFolderGUI
