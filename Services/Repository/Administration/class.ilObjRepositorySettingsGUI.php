<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Repository settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjRepositorySettingsGUI: ilPermissionGUI
 */
class ilObjRepositorySettingsGUI extends ilObjectGUI
{
    protected ilErrorHandling $error;
    protected ilSetting $folder_settings;

    public function __construct(
        ?array $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->settings = $DIC->settings();
        $this->folder_settings = new ilSetting('fold');
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->type = 'reps';
        $this->lng->loadLanguageModule('rep');
        $this->lng->loadLanguageModule('cmps');

        $this->request = $DIC
            ->repository()
            ->internal()
            ->gui()
            ->administration()
            ->request();
    }
    
    public function executeCommand() : void
    {
        $ilErr = $this->error;
        $ilAccess = $this->access;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $this->$cmd();
                break;
        }
    }
    
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;
        
        $this->tabs_gui->addTab(
            "settings",
            $this->lng->txt("settings"),
            $this->ctrl->getLinkTarget($this, "view")
        );
        
        $this->tabs_gui->addTab(
            "icons",
            $this->lng->txt("rep_custom_icons"),
            $this->ctrl->getLinkTarget($this, "customIcons")
        );
        
        $this->tabs_gui->addTab(
            "modules",
            $this->lng->txt("cmps_repository_object_types"),
            $this->ctrl->getLinkTarget($this, "listModules")
        );
        
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }
    
    public function view(ilPropertyFormGUI $a_form = null) : void
    {
        $this->tabs_gui->activateTab("settings");
        
        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function initSettingsForm() : ilPropertyFormGUI
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("settings"));
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));
        
        // default repository view
        $options = array(
            "flat" => $this->lng->txt("flatview"),
            "tree" => $this->lng->txt("treeview")
            );
        $si = new ilSelectInputGUI($this->lng->txt("def_repository_view"), "default_rep_view");
        $si->setOptions($options);
        $si->setInfo($this->lng->txt(""));
        if ($ilSetting->get("default_repository_view") == "tree") {
            $si->setValue("tree");
        } else {
            $si->setValue("flat");
        }
        $form->addItem($si);

        //
        $options = array(
            "" => $this->lng->txt("adm_rep_tree_only_container"),
            "tree" => $this->lng->txt("adm_all_resource_types")
            );

        // repository tree
        $radg = new ilRadioGroupInputGUI($this->lng->txt("adm_rep_tree_presentation"), "tree_pres");
        $radg->setValue($ilSetting->get("repository_tree_pres"));
        $op1 = new ilRadioOption(
            $this->lng->txt("adm_rep_tree_only_cntr"),
            "",
            $this->lng->txt("adm_rep_tree_only_cntr_info")
        );
        $radg->addOption($op1);

        $op2 = new ilRadioOption(
            $this->lng->txt("adm_rep_tree_all_types"),
            "all_types",
            $this->lng->txt("adm_rep_tree_all_types_info")
        );

        // limit tree in courses and groups
        $cb = new ilCheckboxInputGUI($this->lng->txt("adm_rep_tree_limit_grp_crs"), "rep_tree_limit_grp_crs");
        $cb->setChecked($ilSetting->get("rep_tree_limit_grp_crs"));
        $cb->setInfo($this->lng->txt("adm_rep_tree_limit_grp_crs_info"));
        $op2->addSubItem($cb);

        $radg->addOption($op2);

        $form->addItem($radg);

        // limit items in tree
        $tree_limit = new ilCheckboxInputGUI($this->lng->txt("rep_tree_limit"), "rep_tree_limit");
        $tree_limit->setChecked($ilSetting->get("rep_tree_limit_number") > 0);
        $tree_limit->setInfo($this->lng->txt("rep_tree_limit_info"));
        $form->addItem($tree_limit);

        // limit items in tree (number)
        $tree_limit_number = new ilNumberInputGUI($this->lng->txt("rep_tree_limit_number"), "rep_tree_limit_number");
        $tree_limit_number->setMaxLength(3);
        $tree_limit_number->setSize(3);
        $tree_limit_number->setValue($ilSetting->get("rep_tree_limit_number"));
        $tree_limit_number->setInfo($this->lng->txt("rep_tree_limit_number_info"));
        $tree_limit->addSubItem($tree_limit_number);

        // breadcrumbs start with courses
        $cb = new ilCheckboxInputGUI($this->lng->txt("rep_breadcr_crs"), "rep_breadcr_crs");
        $cb->setChecked((int) $ilSetting->get("rep_breadcr_crs"));
        $form->addItem($cb);

        $radg = new ilRadioGroupInputGUI($this->lng->txt("rep_breadcr_crs"), "rep_breadcr_crs_overwrite");
        $radg->setValue((int) $ilSetting->get("rep_breadcr_crs_overwrite"));

        $op0 = new ilRadioOption($this->lng->txt("rep_breadcr_crs_overwrite"), 1);
        $cb0 = new ilCheckboxInputGUI($this->lng->txt("rep_default"), "rep_breadcr_crs_default");
        $cb0->setChecked((int) $ilSetting->get("rep_breadcr_crs_default"));
        $op0->addSubItem($cb0);
        $radg->addOption($op0);

        $op1 = new ilRadioOption($this->lng->txt("rep_breadcr_crs_overwrite_not"), 0);
        $radg->addOption($op1);


        $cb->addSubItem($radg);



        // trash
        $cb = new ilCheckboxInputGUI($this->lng->txt("enable_trash"), "enable_trash");
        $cb->setInfo($this->lng->txt("enable_trash_info"));
        if ($ilSetting->get("enable_trash")) {
            $cb->setChecked(true);
        }
        $form->addItem($cb);
    
        // change event
        $this->lng->loadLanguageModule("trac");
        $event = new ilCheckboxInputGUI($this->lng->txt('trac_show_repository_views'), 'change_event_tracking');
        $event->setInfo($this->lng->txt("trac_show_repository_views_info"));
        $event->setChecked(ilChangeEvent::_isActive());
        $form->addItem($event);
        
        
        ilAdministrationSettingsFormHandler::addFieldsToForm(
            ilAdministrationSettingsFormHandler::FORM_REPOSITORY,
            $form,
            $this
        );

        // export limitations
        $limiter = new ilExportLimitation();
        $exp_limit = new ilRadioGroupInputGUI($this->lng->txt("rep_export_limitation"), "rep_export_limitation");
        $exp_limit->setValue($limiter->getLimitationMode());
        $exp_limit->setInfo($this->lng->txt("rep_export_limitation_info"));

        $op_disabled = new ilRadioOption(
            $this->lng->txt("rep_export_limitation_disabled"),
            ilExportLimitation::SET_EXPORT_DISABLED
        );
        $exp_limit->addOption($op_disabled);

        $op_limited = new ilRadioOption(
            $this->lng->txt("rep_export_limitation_limited"),
            ilExportLimitation::SET_EXPORT_LIMITED
        );
        $exp_limit->addOption($op_limited);

        // limit items in tree (number)
        $exp_limit_num = new ilNumberInputGUI($this->lng->txt("rep_export_limit_number"), "rep_export_limit_number");
        $exp_limit_num->setMaxLength(6);
        $exp_limit_num->setSize(6);
        $exp_limit_num->setValue($ilSetting->get("rep_export_limit_number"));
        $op_limited->addSubItem($exp_limit_num);

        $form->addItem($exp_limit);

        // Show download action for folder
        $dl_prop = new ilCheckboxInputGUI($this->lng->txt("enable_download_folder"), "enable_download_folder");
        $dl_prop->setValue('1');
        // default value should reflect previous behaviour (-> 0)
        $dl_prop->setChecked($this->folder_settings->get("enable_download_folder", 0) == 1);
        $dl_prop->setInfo($this->lng->txt('enable_download_folder_info'));
        $form->addItem($dl_prop);

        // multi download
        $dl_prop = new ilCheckboxInputGUI($this->lng->txt("enable_multi_download"), "enable_multi_download");
        $dl_prop->setValue('1');
        // default value should reflect previous behaviour (-> 0)
        $dl_prop->setChecked($this->folder_settings->get("enable_multi_download", 0) == 1);
        $dl_prop->setInfo($this->lng->txt('enable_multi_download_info'));
        $form->addItem($dl_prop);

        // object lists
        
        $lists = new ilFormSectionHeaderGUI();
        $lists->setTitle($this->lng->txt("rep_object_lists"));
        $form->addItem($lists);
            
        $sdesc = new ilCheckboxInputGUI($this->lng->txt("adm_rep_shorten_description"), "rep_shorten_description");
        $sdesc->setInfo($this->lng->txt("adm_rep_shorten_description_info"));
        $sdesc->setChecked($ilSetting->get("rep_shorten_description"));
        $form->addItem($sdesc);
        
        $sdesclen = new ilNumberInputGUI($this->lng->txt("adm_rep_shorten_description_length"), "rep_shorten_description_length");
        $sdesclen->setValue($ilSetting->get("rep_shorten_description_length"));
        $sdesclen->setSize(3);
        $sdesc->addSubItem($sdesclen);
            
        // load action commands asynchronously
        $cb = new ilCheckboxInputGUI($this->lng->txt("adm_item_cmd_asynch"), "item_cmd_asynch");
        $cb->setInfo($this->lng->txt("adm_item_cmd_asynch_info"));
        $cb->setChecked($ilSetting->get("item_cmd_asynch"));
        $form->addItem($cb);
        
        // notes/comments/tagging
        $pl = new ilCheckboxInputGUI($this->lng->txt('adm_show_comments_tagging_in_lists'), 'comments_tagging_in_lists');
        $pl->setValue(1);
        $pl->setChecked($ilSetting->get('comments_tagging_in_lists'));
        $form->addItem($pl);
        
        $pltags = new ilCheckboxInputGUI($this->lng->txt('adm_show_comments_tagging_in_lists_tags'), 'comments_tagging_in_lists_tags');
        $pltags->setValue(1);
        $pltags->setChecked($ilSetting->get('comments_tagging_in_lists_tags'));
        $pl->addSubItem($pltags);
                
        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('saveSettings', $this->lng->txt('save'));
        }
        
        return $form;
    }
    
    public function saveSettings() : void
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->ctrl->redirect($this, "view");
        }
    
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $ilSetting->set(
                "default_repository_view",
                $form->getInput("default_rep_view")
            );
            $ilSetting->set(
                "repository_tree_pres",
                $form->getInput("tree_pres")
            );
            $rep_tree_limit_grp_crs = $form->getInput("rep_tree_limit_grp_crs");
            if ($form->getInput("tree_pres") == "") {
                $rep_tree_limit_grp_crs = "";
            }
            $ilSetting->set(
                "rep_tree_limit_grp_crs",
                $rep_tree_limit_grp_crs
            );

            $ilSetting->set(
                "rep_export_limitation",
                $form->getInput("rep_export_limitation")
            );
            $ilSetting->set(
                "rep_export_limit_number",
                $form->getInput("rep_export_limit_number")
            );
            $ilSetting->set(
                "enable_trash",
                $form->getInput("enable_trash")
            );
            $ilSetting->set(
                "rep_breadcr_crs_overwrite",
                (int) $form->getInput("rep_breadcr_crs_overwrite")
            );
            $ilSetting->set(
                "rep_breadcr_crs",
                (int) $form->getInput("rep_breadcr_crs")
            );
            $ilSetting->set(
                "rep_breadcr_crs_default",
                (int) $form->getInput("rep_breadcr_crs_default")
            );
            $ilSetting->set(
                "rep_shorten_description",
                $form->getInput('rep_shorten_description')
            );
            $ilSetting->set(
                "rep_shorten_description_length",
                (int) $form->getInput('rep_shorten_description_length')
            );
            $ilSetting->set(
                'item_cmd_asynch',
                (int) $form->getInput('item_cmd_asynch')
            );
            $ilSetting->set(
                'comments_tagging_in_lists',
                (int) $form->getInput('comments_tagging_in_lists')
            );
            $ilSetting->set(
                'comments_tagging_in_lists_tags',
                $form->getInput('comments_tagging_in_lists_tags')
            );

            // repository tree limit of children
            $limit_number = ($form->getInput('rep_tree_limit') &&
                $form->getInput('rep_tree_limit_number') > 0)
                ? (int) $form->getInput('rep_tree_limit_number')
                : 0;
            $ilSetting->set('rep_tree_limit_number', $limit_number);

            $this->folder_settings->set(
                "enable_download_folder",
                $form->getInput("enable_download_folder") == 1
            );
            $this->folder_settings->set(
                "enable_multi_download",
                $form->getInput("enable_multi_download") == 1
            );
            if ($form->getInput('change_event_tracking')) {
                ilChangeEvent::_activate();
            } else {
                ilChangeEvent::_deactivate();
            }
                        
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "view");
        }
        
        $form->setValuesByPost();
        $this->view($form);
    }
    
    public function customIcons(ilPropertyFormGUI $a_form = null) : void
    {
        $this->tabs_gui->activateTab("icons");
        
        if (!$a_form) {
            $a_form = $this->initCustomIconsForm();
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function initCustomIconsForm() : ilPropertyFormGUI
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("rep_custom_icons"));
        $form->setFormAction($this->ctrl->getFormAction($this, 'saveCustomIcons'));
                
        $cb = new ilCheckboxInputGUI($this->lng->txt("enable_custom_icons"), "custom_icons");
        $cb->setInfo($this->lng->txt("enable_custom_icons_info"));
        $cb->setChecked($ilSetting->get("custom_icons"));
        $form->addItem($cb);

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('saveCustomIcons', $this->lng->txt('save'));
        }
        
        return $form;
    }
    
    public function saveCustomIcons() : void
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->ctrl->redirect($this, "customIcons");
        }
    
        $form = $this->initCustomIconsForm();
        if ($form->checkInput()) {
            $ilSetting->set("custom_icons", (int) $form->getInput("custom_icons"));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "customIcons");
        }
        
        $form->setValuesByPost();
        $this->customIcons($form);
    }
    
    protected function setModuleSubTabs($a_active) : void
    {
        $this->tabs_gui->activateTab('modules');
        
        $this->tabs_gui->addSubTab(
            "list_mods",
            $this->lng->txt("rep_new_item_menu"),
            $this->ctrl->getLinkTarget($this, "listModules")
        );
        
        $this->tabs_gui->addSubTab(
            "new_item_groups",
            $this->lng->txt("rep_new_item_groups"),
            $this->ctrl->getLinkTarget($this, "listNewItemGroups")
        );
        
        $this->tabs_gui->activateSubTab($a_active);
    }
    
    protected function listModules() : void
    {
        $ilAccess = $this->access;
        
        $this->setModuleSubTabs("list_mods");
                
        $has_write = $ilAccess->checkAccess('write', '', $this->object->getRefId());
        
        $comp_table = new ilModulesTableGUI($this, "listModules", $has_write);
                
        $this->tpl->setContent($comp_table->getHTML());
    }
    
    protected function saveModules() : void
    {
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;

        $item_groups = $this->request->getNewItemGroups();
        $item_positions = $this->request->getNewItemPositions();

        if (count($item_groups) == 0 ||
            count($item_positions) == 0 ||
            !$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $ilCtrl->redirect($this, "listModules");
        }
        
        $grp_pos_map = array(0 => 9999);
        foreach (ilObjRepositorySettings::getNewItemGroups() as $item) {
            $grp_pos_map[$item["id"]] = $item["pos"];
        }
        
        $type_pos_map = array();
        $item_enablings = $this->request->getNewItemEnablings();
        foreach ($item_positions as $obj_type => $pos) {
            $grp_id = ($item_groups[$obj_type] ?? 0);
            $type_pos_map[$grp_id][$obj_type] = $pos;
            
            // enable creation?
            $ilSetting->set(
                "obj_dis_creation_" . $obj_type,
                !($item_enablings[$obj_type] ?? false)
            );
        }
        
        foreach ($type_pos_map as $grp_id => $obj_types) {
            $grp_pos = str_pad($grp_pos_map[$grp_id], 4, "0", STR_PAD_LEFT);
        
            asort($obj_types);
            $pos = 0;
            foreach (array_keys($obj_types) as $obj_type) {
                $pos += 10;
                $type_pos = $grp_pos . str_pad($pos, 4, "0", STR_PAD_LEFT);
                $ilSetting->set("obj_add_new_pos_" . $obj_type, $type_pos);
                $ilSetting->set("obj_add_new_pos_grp_" . $obj_type, $grp_id);
            }
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listModules");
    }
    
    protected function listNewItemGroups() : void
    {
        $ilToolbar = $this->toolbar;
        $ilAccess = $this->access;
        
        $this->setModuleSubTabs("new_item_groups");
        
        $has_write = $ilAccess->checkAccess('write', '', $this->object->getRefId());
        
        if ($has_write) {
            $ilToolbar->addButton(
                $this->lng->txt("rep_new_item_group_add"),
                $this->ctrl->getLinkTarget($this, "addNewItemGroup")
            );
        
            $ilToolbar->addButton(
                $this->lng->txt("rep_new_item_group_add_separator"),
                $this->ctrl->getLinkTarget($this, "addNewItemGroupSeparator")
            );
        }
        
        $grp_table = new ilNewItemGroupTableGUI($this, "listNewItemGroups", $has_write);
                
        $this->tpl->setContent($grp_table->getHTML());
    }
    
    protected function initNewItemGroupForm($a_grp_id = false) : ilPropertyFormGUI
    {
        $this->setModuleSubTabs("new_item_groups");
        
        $form = new ilPropertyFormGUI();
        
        $this->lng->loadLanguageModule("meta");
        $def_lng = $this->lng->getDefaultLanguage();
    
        $title = new ilTextInputGUI($this->lng->txt("title"), "title_" . $def_lng);
        $title->setInfo($this->lng->txt("meta_l_" . $def_lng) .
            " (" . $this->lng->txt("default_language") . ")");
        $title->setRequired(true);
        $form->addItem($title);
        
        foreach ($this->lng->getInstalledLanguages() as $lang_id) {
            if ($lang_id != $def_lng) {
                $title = new ilTextInputGUI($this->lng->txt("translation"), "title_" . $lang_id);
                $title->setInfo($this->lng->txt("meta_l_" . $lang_id));
                $form->addItem($title);
            }
        }
                                        
        if (!$a_grp_id) {
            $form->setTitle($this->lng->txt("rep_new_item_group_add"));
            $form->setFormAction($this->ctrl->getFormAction($this, "saveNewItemGroup"));
            
            $form->addCommandButton("saveNewItemGroup", $this->lng->txt("save"));
        } else {
            $form->setTitle($this->lng->txt("rep_new_item_group_edit"));
            $form->setFormAction($this->ctrl->getFormAction($this, "updateNewItemGroup"));
            
            $grp = ilObjRepositorySettings::getNewItemGroups();
            $grp = $grp[$a_grp_id];
            
            foreach ($grp["titles"] as $id => $value) {
                $field = $form->getItemByPostVar("title_" . $id);
                if ($field) {
                    $field->setValue($value);
                }
            }
            
            $form->addCommandButton("updateNewItemGroup", $this->lng->txt("save"));
        }
        $form->addCommandButton("listNewItemGroups", $this->lng->txt("cancel"));
        
        return $form;
    }
    
    protected function addNewItemGroup(ilPropertyFormGUI $a_form = null) : void
    {
        if (!$a_form) {
            $a_form = $this->initNewItemGroupForm();
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function saveNewItemGroup() : void
    {
        $form = $this->initNewItemGroupForm();
        if ($form->checkInput()) {
            $titles = array();
            foreach ($this->lng->getInstalledLanguages() as $lang_id) {
                $titles[$lang_id] = $form->getInput("title_" . $lang_id);
            }
            
            if (ilObjRepositorySettings::addNewItemGroup($titles)) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
                $this->ctrl->redirect($this, "listNewItemGroups");
            }
        }
        
        $form->setValuesByPost();
        $this->addNewItemGroup($form);
    }
    
    protected function editNewItemGroup(ilPropertyFormGUI $a_form = null) : void
    {
        $grp_id = $this->request->getNewItemGroupId();
        if (!$grp_id) {
            $this->ctrl->redirect($this, "listNewItemGroups");
        }
        
        if (!$a_form) {
            $this->ctrl->setParameter($this, "grp_id", $grp_id);
            $a_form = $this->initNewItemGroupForm($grp_id);
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }
    
    protected function updateNewItemGroup() : void
    {
        $grp_id = $this->request->getNewItemGroupId();
        if (!$grp_id) {
            $this->ctrl->redirect($this, "listNewItemGroups");
        }
        
        $this->ctrl->setParameter($this, "grp_id", $grp_id);
        
        $form = $this->initNewItemGroupForm($grp_id);
        if ($form->checkInput()) {
            $titles = array();
            foreach ($this->lng->getInstalledLanguages() as $lang_id) {
                $titles[$lang_id] = $form->getInput("title_" . $lang_id);
            }
            
            if (ilObjRepositorySettings::updateNewItemGroup($grp_id, $titles)) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
                $this->ctrl->redirect($this, "listNewItemGroups");
            }
        }
        
        $form->setValuesByPost();
        $this->addNewItemGroup($form);
    }
    
    protected function addNewItemGroupSeparator() : void
    {
        if (ilObjRepositorySettings::addNewItemGroupSeparator()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }
        $this->ctrl->redirect($this, "listNewItemGroups");
    }
    
    protected function saveNewItemGroupOrder() : void
    {
        $ilSetting = $this->settings;

        $group_order = $this->request->getNewItemGroupOrder();
        if (count($group_order) > 0) {
            ilObjRepositorySettings::updateNewItemGroupOrder($group_order);
                                    
            $grp_pos_map = array();
            foreach (ilObjRepositorySettings::getNewItemGroups() as $item) {
                $grp_pos_map[$item["id"]] = str_pad($item["pos"], 4, "0", STR_PAD_LEFT);
            }
        
            // update order of assigned objects
            foreach (ilObjRepositorySettings::getNewItemGroupSubItems() as $grp_id => $subitems) {
                // unassigned objects will always be last
                if ($grp_id) {
                    foreach ($subitems as $obj_type) {
                        $old_pos = $ilSetting->get("obj_add_new_pos_" . $obj_type);
                        if (strlen($old_pos) == 8) {
                            $new_pos = $grp_pos_map[$grp_id] . substr($old_pos, 4);
                            $ilSetting->set("obj_add_new_pos_" . $obj_type, $new_pos);
                        }
                    }
                }
            }
            
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }
        $this->ctrl->redirect($this, "listNewItemGroups");
    }
    
    protected function confirmDeleteNewItemGroup() : void
    {
        $group_ids = $this->request->getNewItemGroupIds();
        if (count($group_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("select_one"));
            $this->listNewItemGroups();
            return;
        }
        
        $this->setModuleSubTabs("new_item_groups");
        
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt("rep_new_item_group_delete_sure"));

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "listNewItemGroups");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteNewItemGroup");
        
        $groups = ilObjRepositorySettings::getNewItemGroups();

        foreach ($group_ids as $grp_id) {
            $cgui->addItem("grp_ids[]", $grp_id, $groups[$grp_id]["title"]);
        }
        
        $this->tpl->setContent($cgui->getHTML());
    }
    
    protected function deleteNewItemGroup() : void
    {
        $group_ids = $this->request->getNewItemGroupIds();
        if (count($group_ids) == 0) {
            $this->listNewItemGroups();
            return;
        }
        
        foreach ($group_ids as $grp_id) {
            ilObjRepositorySettings::deleteNewItemGroup($grp_id);
        }
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "listNewItemGroups");
    }
    
    public function addToExternalSettingsForm($a_form_id) : ?array
    {
        $ilSetting = $this->settings;
        
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_LP:
                
                $fields = array('trac_show_repository_views' => array(ilChangeEvent::_isActive(), ilAdministrationSettingsFormHandler::VALUE_BOOL));
                                                
                return array(array("view", $fields));
                
                
            case ilAdministrationSettingsFormHandler::FORM_TAGGING:
                
                $fields = array(
                    'adm_show_comments_tagging_in_lists' => array($ilSetting->get('comments_tagging_in_lists'), ilAdministrationSettingsFormHandler::VALUE_BOOL,
                        array('adm_show_comments_tagging_in_lists_tags' => array($ilSetting->get('comments_tagging_in_lists_tags'), ilAdministrationSettingsFormHandler::VALUE_BOOL))
                ));
                
                return array(array("view", $fields));
        }
        return null;
    }
}
