<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjUserTrackingGUI
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
* @ilCtrl_Calls ilObjUserTrackingGUI: ilLearningProgressGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjUserTrackingGUI: ilLPObjectStatisticsGUI, ilSessionStatisticsGUI
*/
class ilObjUserTrackingGUI extends ilObjectGUI
{
    public $tpl = null;
    public $lng = null;
    public $ctrl = null;

    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->type = "trac";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('trac');

        $this->ctrl = $ilCtrl;
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        $this->ctrl->setReturn($this, "show");
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret =&$this->ctrl->forwardCommand($perm_gui);
                break;

            case 'illearningprogressgui':
                $this->tabs_gui->setTabActive('learning_progress');
                include_once("./Services/Tracking/classes/class.ilLearningProgressGUI.php");
                $lp_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_ADMINISTRATION);
                $ret =&$this->ctrl->forwardCommand($lp_gui);
                break;
            
            case 'illpobjectstatisticsgui':
                $this->tabs_gui->setTabActive('statistics');
                include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsGUI.php");
                $os_gui = new ilLPObjectStatisticsGUI(ilLPObjectStatisticsGUI::LP_CONTEXT_ADMINISTRATION);
                $ret =&$this->ctrl->forwardCommand($os_gui);
                break;
            
            case 'ilsessionstatisticsgui':
                $this->tabs_gui->setTabActive('session_statistics');
                include_once("./Services/Authentication/classes/class.ilSessionStatisticsGUI.php");
                $sess_gui = new ilSessionStatisticsGUI();
                $ret =&$this->ctrl->forwardCommand($sess_gui);
                break;
                
            default:
                $cmd = $this->ctrl->getCmd();
                if ($cmd == "view" || $cmd == "") {
                    $cmd = "settings";
                }
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
        
        return true;
    }
    
    public function getAdminTabs()
    {
        $this->getTabs();
    }

    public function getTabs()
    {
        $this->ctrl->setParameter($this, "ref_id", $this->ref_id);
        
        $this->tabs_gui->addTarget(
            "settings",
            $this->ctrl->getLinkTarget($this, "settings"),
            "settings",
            get_class($this)
        );

        if ($this->checkPermissionBool("read")) {
            if (ilObjUserTracking::_enabledObjectStatistics()) {
                $this->tabs_gui->addTarget(
                    "statistics",
                    $this->ctrl->getLinkTargetByClass(
                        "illpobjectstatisticsgui",
                        "access"
                    ),
                    "",
                    "illpobjectstatisticsgui"
                );
            }

            if (ilObjUserTracking::_enabledLearningProgress()) {
                $this->tabs_gui->addTarget(
                    "learning_progress",
                    $this->ctrl->getLinkTargetByClass(
                        "illearningprogressgui",
                        "show"
                    ),
                    "",
                    "illearningprogressgui"
                );
            }
            
            // session statistics
            if (ilObjUserTracking::_enabledSessionStatistics()) {
                $this->tabs_gui->addTarget(
                    "session_statistics",
                    $this->ctrl->getLinkTargetByClass(
                        "ilsessionstatisticsgui",
                        ""
                    ),
                    "",
                    "ilsessionstatisticsgui"
                );
            }
        }
        
        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }


    /**
    * display tracking settings form
    */
    public function settingsObject($a_form = null)
    {
        $this->checkPermission('read');

        $this->tabs_gui->addSubTab(
            'lp_settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTarget($this, 'settings')
        );

        if (!ilObjUserTracking::_enabledLearningProgress()) {
            $this->tabs_gui->addSubTab(
                'lpdef',
                $this->lng->txt('trac_defaults'),
                $this->ctrl->getLinkTarget($this, 'editLPDefaults')
            );
        }
        
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('lp_settings');
        
        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }

        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function initSettingsForm()
    {
        include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('tracking_settings'));

        $activate = new ilCheckboxGroupInputGUI($this->lng->txt('activate_tracking'));
        $form->addItem($activate);
        
        // learning progress
        $lp = new ilCheckboxInputGUI($this->lng->txt('trac_learning_progress'), 'learning_progress_tracking');
        if ($this->object->enabledLearningProgress()) {
            $lp->setChecked(true);
        }
        $activate->addSubItem($lp);
        
        
        // lp settings

        /*
        $desktop = new ilCheckboxInputGUI($this->lng->txt('trac_lp_on_personal_desktop'), 'lp_desktop');
        $desktop->setInfo($this->lng->txt('trac_lp_on_personal_desktop_info'));
        $desktop->setChecked($this->object->hasLearningProgressDesktop());
        $lp->addSubItem($desktop);
        */
        
        $learner = new ilCheckboxInputGUI($this->lng->txt('trac_lp_learner_access'), 'lp_learner');
        $learner->setInfo($this->lng->txt('trac_lp_learner_access_info'));
        $learner->setChecked($this->object->hasLearningProgressLearner());
        $lp->addSubItem($learner);
        
        
        // extended data

        $extdata = new ilCheckboxGroupInputGUI($this->lng->txt('trac_learning_progress_settings_info'), 'lp_extdata');
        $extdata->addOption(new ilCheckboxOption($this->lng->txt('trac_first_and_last_access'), 'lp_access'));
        $extdata->addOption(new ilCheckboxOption($this->lng->txt('trac_read_count'), 'lp_count'));
        $extdata->addOption(new ilCheckboxOption($this->lng->txt('trac_spent_seconds'), 'lp_spent'));
        $lp->addSubItem($extdata);
        
        $ext_value = array();
        if ($this->object->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
            $ext_value[] = 'lp_access';
        }
        if ($this->object->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_READ_COUNT)) {
            $ext_value[] = 'lp_count';
        }
        if ($this->object->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS)) {
            $ext_value[] = 'lp_spent';
        }
        $extdata->setValue($ext_value);
        
        $listgui = new ilCheckboxInputGUI($this->lng->txt('trac_lp_list_gui'), 'lp_list');
        $listgui->setInfo($this->lng->txt('trac_lp_list_gui_info'));
        $listgui->setChecked($this->object->hasLearningProgressListGUI());
        $lp->addSubItem($listgui);
        
        /* => REPOSITORY
        // change event
        $event = new ilCheckboxInputGUI($this->lng->txt('trac_repository_changes'), 'change_event_tracking');
        if($this->object->enabledChangeEventTracking())
        {
            $event->setChecked(true);
        }
        $activate->addSubItem($event);
        */
        
        // object statistics
        $objstat = new ilCheckboxInputGUI($this->lng->txt('trac_object_statistics'), 'object_statistics');
        if ($this->object->enabledObjectStatistics()) {
            $objstat->setChecked(true);
        }
        $activate->addSubItem($objstat);
        
        // session statistics
        $sessstat = new ilCheckboxInputGUI($this->lng->txt('session_statistics'), 'session_statistics');
        if ($this->object->enabledSessionStatistics()) {
            $sessstat->setChecked(true);
        }
        $activate->addSubItem($sessstat);
            
        // Anonymized
        $user = new ilCheckboxInputGUI($this->lng->txt('trac_anonymized'), 'user_related');
        $user->setInfo($this->lng->txt('trac_anonymized_info'));
        $user->setChecked(!$this->object->enabledUserRelatedData());
        $form->addItem($user);

        // Max time gap
        $valid = new ilNumberInputGUI($this->lng->txt('trac_valid_request'), 'valid_request');
        $valid->setMaxLength(4);
        $valid->setSize(4);
        $valid->setSuffix($this->lng->txt('seconds'));
        $valid->setInfo($this->lng->txt('info_valid_request'));
        $valid->setValue($this->object->getValidTimeSpan());
        $valid->setMinValue(1);
        $valid->setMaxValue(9999);
        $valid->setRequired(true);
        $form->addItem($valid);
        
        include_once "Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php";
        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_LP,
            $form,
            $this
        );
        
        // #12259
        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        } else {
            $lp->setDisabled(true);
            $learner->setDisabled(true);
            $extdata->setDisabled(true);
            $listgui->setDisabled(true);
            $objstat->setDisabled(true);
            $user->setDisabled(true);
            $valid->setDisabled(true);
        }
                
        return $form;
    }

    /**
    * save user tracking settings
    */
    public function saveSettingsObject()
    {
        $this->checkPermission('write');
        
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $lp_active = $form->getInput('learning_progress_tracking');
            
            $this->object->enableLearningProgress($lp_active);
            
            if ($lp_active) {
                $ext_data = (array) $form->getInput("lp_extdata");
                $code = 0;
                if (in_array('lp_access', $ext_data)) {
                    $code += ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS;
                }
                if (in_array('lp_count', $ext_data)) {
                    $code += ilObjUserTracking::EXTENDED_DATA_READ_COUNT;
                }
                if (in_array('lp_spent', $ext_data)) {
                    $code += ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS;
                }
                $this->object->setExtendedData($code);
            }

            $this->object->enableChangeEventTracking($form->getInput('change_event_tracking'));
            $this->object->enableObjectStatistics($form->getInput('object_statistics'));
            $this->object->enableUserRelatedData(!$form->getInput('user_related'));
            $this->object->setValidTimeSpan($form->getInput('valid_request'));
            // $this->object->setLearningProgressDesktop($form->getInput('lp_desktop'));
            $this->object->setLearningProgressLearner($form->getInput('lp_learner'));
            $this->object->enableSessionStatistics($form->getInput('session_statistics'));
            $this->object->setLearningProgressListGUI($form->getInput('lp_list'));
            $this->object->updateSettings();
            
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "settings");
        }
    
        $form->setValuesByPost();
        $this->settingsObject($form);
        return false;
    }
    
    
    //
    // LP DEFAULTS
    //
    
    protected function editLPDefaultsObject($a_form = null)
    {
        $this->checkPermission('read');

        $this->tabs_gui->addSubTab(
            'lp_settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTarget($this, 'settings')
        );

        $this->tabs_gui->addSubTab(
            'lpdef',
            $this->lng->txt('trac_defaults'),
            $this->ctrl->getLinkTarget($this, 'editLPDefaults')
        );

        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('lpdef');
        
        if (!$a_form) {
            $a_form = $this->initLPDefaultsForm();
        }

        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function initLPDefaultsForm()
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        
        include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('trac_defaults'));
        $form->setDescription($this->lng->txt('trac_defaults_info'));
        
        include_once "Services/Object/classes/class.ilObjectLP.php";
        include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
        
        $types = array();
        foreach ($objDefinition->getAllRepositoryTypes() as $type) {
            if (ilObjectLP::isSupportedObjectType($type)) {
                $types[$type] = array(
                    "type" => $type,
                    "caption" => $this->lng->txt("obj_" . $type)
                );
            }
        }
        $types = ilUtil::sortArray($types, "caption", "asc");
        foreach ($types as $item) {
            $class = ilObjectLP::getTypeClass($item["type"]);
            $modes = $class::getDefaultModes(ilObjUserTracking::_enabledLearningProgress());
            if (sizeof($modes) > 1) {
                $def_type = new ilSelectInputGUI($item["caption"], "def_" . $item["type"]);
                $form->addItem($def_type);
                
                $def_type->setRequired(true);
                $def_type->setValue(ilObjectLP::getTypeDefault($item["type"]));
                
                $options = array();
                foreach ($modes as $mode) {
                    $caption = ($mode == ilLPObjSettings::LP_MODE_DEACTIVATED)
                        ? $this->lng->txt("trac_defaults_inactive")
                        : ilLPObjSettings::_mode2Text($mode);
                    $options[$mode] = $caption;
                }
                $def_type->setOptions($options);
            }
        }
        
        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton('saveLPDefaults', $this->lng->txt('save'));
        } else {
            foreach ($types as $item) {
                $form->getItemByPostVar("def_" . $item["type"])->setDisabled(true);
            }
        }
                
        return $form;
    }
    
    protected function saveLPDefaultsObject()
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        
        $this->checkPermission('write');
        
        $form = $this->initLPDefaultsForm();
        if ($form->checkInput()) {
            include_once "Services/Object/classes/class.ilObjectLP.php";
            include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
            
            $res = array();
            foreach ($objDefinition->getAllRepositoryTypes() as $type) {
                if (ilObjectLP::isSupportedObjectType($type)) {
                    $mode =  $form->getInput("def_" . $type);
                    $res[$type] = $mode
                        ? $mode
                        : ilLPObjSettings::LP_MODE_DEACTIVATED;
                }
            }
            
            ilObjectLP::saveTypeDefaults($res);
            
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editLPDefaults");
        }
    
        $form->setValuesByPost();
        $this->editLPDefaultsObject($form);
        return false;
    }

    /**
     * @param string $a_form_id
     * @return array
     */
    public function addToExternalSettingsForm($a_form_id)
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_CERTIFICATE:
                $fields = array();

                return array('obj_trac' => array('editLPDefaults', $fields));
        }
    }
}
