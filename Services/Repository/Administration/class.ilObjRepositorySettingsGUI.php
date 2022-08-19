<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Repository\Administration\AdministrationGUIRequest;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as RefFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Refinery\Constraint;

/**
 * Repository settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjRepositorySettingsGUI: ilPermissionGUI
 */
class ilObjRepositorySettingsGUI extends ilObjectGUI
{
    protected ilRbacSystem $rbacsystem;
    protected AdministrationGUIRequest $admin_gui_request;
    protected ilErrorHandling $error;
    protected ilSetting $folder_settings;
    protected GlobalHttpState $http;
    protected UIFactory $factory;
    protected UIRenderer $renderer;
    protected RefFactory $refinery;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->settings = $DIC->settings();
        $this->folder_settings = new ilSetting('fold');
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->type = 'reps';
        $this->lng->loadLanguageModule('rep');
        $this->lng->loadLanguageModule('cmps');

        $this->admin_gui_request = $DIC
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
    
    public function getAdminTabs() : void
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
    
    public function view(StandardForm $a_form = null) : void
    {
        $this->tabs_gui->activateTab("settings");
        
        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }
        
        $this->tpl->setContent($this->renderer->render($a_form));
    }
    
    protected function initSettingsForm() : StandardForm
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;

        $read_only = !$ilAccess->checkAccess('write', '', $this->object->getRefId());

        $f = $this->factory->input()->field();

        // repository tree
        $op1 = $f->group(
            [],
            $this->lng->txt("adm_rep_tree_only_cntr")
        )->withByline($this->lng->txt("adm_rep_tree_only_cntr_info"));

        // limit tree in courses and groups
        $cb = $f->checkbox(
            $this->lng->txt("adm_rep_tree_limit_grp_crs"),
            $this->lng->txt("adm_rep_tree_limit_grp_crs_info")
        )->withValue((bool) $ilSetting->get("rep_tree_limit_grp_crs"));

        $op2 = $f->group(
            [
                'rep_tree_limit_grp_crs' => $cb
            ],
            $this->lng->txt("adm_rep_tree_all_types")
        )->withByline($this->lng->txt("adm_rep_tree_all_types_info"));

        $tree_pres = $f->switchableGroup(
            [
                '' => $op1,
                'all_types' => $op2
            ],
            $this->lng->txt("adm_rep_tree_presentation")
        )->withValue($ilSetting->get("repository_tree_pres") ?? "");

        // limit items in tree (number)
        $tree_limit_number = $f->numeric(
            $this->lng->txt("rep_tree_limit_number"),
            $this->lng->txt("rep_tree_limit_number_info")
        )->withValue($ilSetting->get("rep_tree_limit_number"))
         ->withAdditionalTransformation($this->getMaxLengthConstraint(3))
         ->withAdditionalTransformation($this->getPositiveConstraint());

        // limit items in tree
        $tree_limit = $f->optionalGroup(
            [
                'rep_tree_limit_number' => $tree_limit_number
            ],
            $this->lng->txt("rep_tree_limit"),
            $this->lng->txt("rep_tree_limit_info")
        );
        if ($ilSetting->get("rep_tree_limit_number") <= 0) {
            $tree_limit = $tree_limit->withValue(null);
        }

        // breadcrumbs start with courses
        //TODO this needs cleaning up, was previously made up of nested inputs two layers deep
        $change_mode = $f->radio(
            $this->lng->txt("rep_breadcr_crs")
        )->withOption(
            '1',
            $this->lng->txt("rep_breadcr_crs_overwrite")
        )->withOption(
            'rep_breadcr_crs_default', //this was previously the postvar
            $this->lng->txt("rep_breadcr_crs_overwrite") .
            ' (' . $this->lng->txt("rep_default") . ')' //TODO introduce new langvar for this
        )->withOption(
            '0',
            $this->lng->txt("rep_breadcr_crs_overwrite_not")
        )->withValue((string) ((int) $ilSetting->get("rep_breadcr_crs_overwrite")));
        if ($ilSetting->get("rep_breadcr_crs_default")) {
            $change_mode = $change_mode->withValue('rep_breadcr_crs_default');
        }

        $breadcrumbs = $f->optionalGroup(
            [
                'rep_breadcr_crs_overwrite' => $change_mode
            ],
            $this->lng->txt("rep_breadcr_crs")
        );
        if (!$ilSetting->get("rep_breadcr_crs")) {
            $breadcrumbs = $breadcrumbs->withValue(null);
        }

        // trash
        $enable_trash = $f->checkbox(
            $this->lng->txt("enable_trash"),
            $this->lng->txt("enable_trash_info")
        )->withValue((bool) $ilSetting->get("enable_trash"));
    
        // change event
        $this->lng->loadLanguageModule("trac");
        $event = $f->checkbox(
            $this->lng->txt('trac_show_repository_views'),
            $this->lng->txt("trac_show_repository_views_info")
        )->withValue(ilChangeEvent::_isActive());

        // export limitations
        $exp_disabled = $f->group(
            [],
            $this->lng->txt("rep_export_limitation_disabled")
        );

        // limit items in tree (number)
        $exp_limit_num = $f->numeric(
            $this->lng->txt("rep_export_limit_number")
        )->withAdditionalTransformation($this->getMaxLengthConstraint(6))
         ->withAdditionalTransformation($this->getPositiveConstraint())
         ->withValue($ilSetting->get("rep_export_limit_number"));

        $exp_limited = $f->group(
            [
                'rep_export_limit_number' => $exp_limit_num
            ],
            $this->lng->txt("rep_export_limitation_limited")
        );

        $limiter = new ilExportLimitation();
        $exp_limit = $f->switchableGroup(
            [
                (string) ilExportLimitation::SET_EXPORT_DISABLED => $exp_disabled,
                (string) ilExportLimitation::SET_EXPORT_LIMITED => $exp_limited
            ],
            $this->lng->txt("rep_export_limitation"),
            $this->lng->txt("rep_export_limitation_info")
        )->withValue((string) $limiter->getLimitationMode());

        // Show download action for folder
        $dl_prob = $f->checkbox(
            $this->lng->txt("enable_download_folder"),
            $this->lng->txt('enable_download_folder_info')
        )->withValue(
            (int) $this->folder_settings->get(
                "enable_download_folder",
                '0'
            ) === 1
        ); // default value should reflect previous behaviour (-> 0)

        // multi download
        $dl_prop = $f->checkbox(
            $this->lng->txt("enable_multi_download"),
            $this->lng->txt('enable_multi_download_info')
        )->withValue(
            (int) $this->folder_settings->get(
                "enable_multi_download",
                '1'
            ) === 1
        ); // default value should reflect previous behaviour (-> 0)

        // favourites
        $fav = $f->checkbox(
            $this->lng->txt("rep_favourites"),
            $this->lng->txt("rep_favourites_info")
        )->withValue((bool) $ilSetting->get("rep_favourites"));

        //TODO split this up into two sections
        $settings = $f->section(
            [
                'tree_pres' => $tree_pres,
                'rep_tree_limit' => $tree_limit,
                'rep_breadcr_crs' => $breadcrumbs,
                'enable_trash' => $enable_trash,
                'change_event_tracking' => $event,
                'rep_export_limitation' => $exp_limit,
                'enable_download_folder' => $dl_prob,
                'enable_multi_download' => $dl_prop,
                'rep_favourites' => $fav
            ],
            $this->lng->txt("settings")
        )->withDisabled($read_only);

        // object lists
        //shorten description
        $sdesclen = $f->numeric(
            $this->lng->txt("adm_rep_shorten_description_length")
        )->withValue($ilSetting->get("rep_shorten_description_length"))
         ->withAdditionalTransformation($this->getMaxLengthConstraint(3))
         ->withAdditionalTransformation($this->getPositiveConstraint());

        $sdesc = $f->optionalGroup(
            [
                'rep_shorten_description_length' => $sdesclen
            ],
            $this->lng->txt("adm_rep_shorten_description"),
            $this->lng->txt("adm_rep_shorten_description_info")
        );
        if (!$ilSetting->get("rep_shorten_description")) {
            $sdesc = $sdesc->withValue(null);
        }

        // load action commands asynchronously
        $async = $f->checkbox(
            $this->lng->txt("adm_item_cmd_asynch"),
            $this->lng->txt("adm_item_cmd_asynch_info")
        )->withValue((bool) $ilSetting->get("item_cmd_asynch"));
        
        // notes/comments/tagging
        $pltags = $f->checkbox(
            $this->lng->txt('adm_show_comments_tagging_in_lists_tags')
        )->withValue((bool) $ilSetting->get('comments_tagging_in_lists_tags'));

        $pl = $f->optionalGroup(
            [
                'comments_tagging_in_lists_tags' => $pltags
            ],
            $this->lng->txt('adm_show_comments_tagging_in_lists')
        );
        if (!$ilSetting->get('comments_tagging_in_lists')) {
            $pl = $pl->withValue(null);
        }

        $obj_lists = $f->section(
            [
                'rep_shorten_description' => $sdesc,
                'item_cmd_asynch' => $async,
                'comments_tagging_in_lists' => $pl
            ],
            $this->lng->txt("rep_object_lists")
        )->withDisabled($read_only);

        $form = $this->factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveSettings'),
            [
                'settings' => $settings,
                'obj_lists' => $obj_lists
            ]
        );

        return $form;
    }
    
    public function saveSettings() : void
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('permission_denied'),
                true
            );
            $this->ctrl->redirect($this, "view");
        }
    
        $form = $this->initSettingsForm()
                     ->withRequest($this->http->request());
        if ($form->getData()) {
            $data = $form->getData()['settings'];
            $ilSetting->set(
                "repository_tree_pres",
                $data["tree_pres"][0]
            );
            if ($data['tree_pres'][0] === 'all_types') {
                $ilSetting->set(
                    "rep_tree_limit_grp_crs",
                    (string) $data['tree_pres'][1]["rep_tree_limit_grp_crs"] ?? ''
                );
            }

            $ilSetting->set(
                "rep_favourites",
                (string) $data["rep_favourites"]
            );

            $ilSetting->set(
                "rep_export_limitation",
                (string) $data["rep_export_limitation"][0]
            );
            if ($data["rep_export_limitation"][0] === (string) ilExportLimitation::SET_EXPORT_LIMITED) {
                $ilSetting->set(
                    "rep_export_limit_number",
                    (string) $data["rep_export_limitation"][1]["rep_export_limit_number"] ?? ''
                );
            }
            $ilSetting->set(
                "enable_trash",
                (string) $data["enable_trash"]
            );

            $ilSetting->set(
                "rep_breadcr_crs",
                (string) ((int) $data["rep_breadcr_crs"])
            );
            if (isset($data["rep_breadcr_crs"])) {
                $overwrite = $data["rep_breadcr_crs"]["rep_breadcr_crs_overwrite"];
                $ilSetting->set(
                    "rep_breadcr_crs_default",
                    (string) ((int) ($overwrite === 'rep_breadcr_crs_default'))
                );
                if ($overwrite === 'rep_breadcr_crs_default') {
                    $overwrite = '1';
                }
                $ilSetting->set(
                    "rep_breadcr_crs_overwrite",
                    (string) ((int) $overwrite)
                );
            }

            // repository tree limit of children
            $limit_number = ($data['rep_tree_limit'] &&
                $data['rep_tree_limit']['rep_tree_limit_number'] > 0)
                ? (int) $data['rep_tree_limit']['rep_tree_limit_number']
                : 0;
            $ilSetting->set('rep_tree_limit_number', (string) $limit_number);

            $this->folder_settings->set(
                "enable_download_folder",
                (string) ((int) $data["enable_download_folder"] === 1)
            );
            $this->folder_settings->set(
                "enable_multi_download",
                (string) ((int) $data["enable_multi_download"] === 1)
            );
            if ($data['change_event_tracking']) {
                ilChangeEvent::_activate();
            } else {
                ilChangeEvent::_deactivate();
            }

            //object lists
            $data = $form->getData()['obj_lists'];
            $ilSetting->set(
                "rep_shorten_description",
                (string) ((int) $data['rep_shorten_description'])
            );
            if (isset($data['rep_shorten_description'])) {
                $ilSetting->set(
                    "rep_shorten_description_length",
                    (string) ((int) $data['rep_shorten_description']['rep_shorten_description_length'])
                );
            }
            $ilSetting->set(
                'item_cmd_asynch',
                (string) ((int) $data['item_cmd_asynch'])
            );
            $ilSetting->set(
                'comments_tagging_in_lists',
                (string) ((int) $data['comments_tagging_in_lists'])
            );
            if (isset($data['comments_tagging_in_lists'])) {
                $ilSetting->set(
                    'comments_tagging_in_lists_tags',
                    (string) $data['comments_tagging_in_lists']['comments_tagging_in_lists_tags']
                );
            }
                        
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "view");
        }

        $this->view($form);
    }
    
    public function customIcons(StandardForm $a_form = null) : void
    {
        $this->tabs_gui->activateTab("icons");
        
        if (!$a_form) {
            $a_form = $this->initCustomIconsForm();
        }
        
        $this->tpl->setContent($this->renderer->render($a_form));
    }
    
    protected function initCustomIconsForm() : StandardForm
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;

        $cb = $this->factory->input()->field()->checkbox(
            $this->lng->txt("enable_custom_icons"),
            $this->lng->txt("enable_custom_icons_info")
        )->withValue((bool) $ilSetting->get("custom_icons"));

        $section = $this->factory->input()->field()->section(
            ['custom_icons' => $cb],
            $this->lng->txt("rep_custom_icons")
        )->withDisabled(
            !$ilAccess->checkAccess('write', '', $this->object->getRefId())
        );

        $form = $this->factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveCustomIcons'),
            ['section' => $section]
        );
        
        return $form;
    }
    
    public function saveCustomIcons() : void
    {
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        
        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('permission_denied'),
                true
            );
            $this->ctrl->redirect($this, "customIcons");
        }
    
        $form = $this->initCustomIconsForm()
                     ->withRequest($this->http->request());
        if ($form->getData()) {
            $ilSetting->set(
                "custom_icons",
                (string) ((int) $form->getData()['section']['custom_icons'])
            );
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt("msg_obj_modified"),
                true
            );
            $this->ctrl->redirect($this, "customIcons");
        }

        $this->customIcons($form);
    }
    
    protected function setModuleSubTabs(string $a_active) : void
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

        $item_groups = $this->admin_gui_request->getNewItemGroups();
        $item_positions = $this->admin_gui_request->getNewItemPositions();

        if (count($item_groups) === 0 || count($item_positions) === 0 ||
            !$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $ilCtrl->redirect($this, "listModules");
        }
        
        $grp_pos_map = [0 => 9999];
        foreach (ilObjRepositorySettings::getNewItemGroups() as $item) {
            $grp_pos_map[$item["id"]] = $item["pos"];
        }
        
        $type_pos_map = [];
        $item_enablings = $this->admin_gui_request->getNewItemEnablings();
        foreach ($item_positions as $obj_type => $pos) {
            $grp_id = ($item_groups[$obj_type] ?? 0);
            $type_pos_map[$grp_id][$obj_type] = $pos;
            
            // enable creation?
            $ilSetting->set(
                "obj_dis_creation_" . $obj_type,
                (string) ((int) (!($item_enablings[$obj_type] ?? false)))
            );
        }
        
        foreach ($type_pos_map as $grp_id => $obj_types) {
            $grp_pos = str_pad($grp_pos_map[$grp_id], 4, "0", STR_PAD_LEFT);
        
            asort($obj_types);
            $pos = 0;
            foreach (array_keys($obj_types) as $obj_type) {
                $pos += 10;
                $type_pos = $grp_pos . str_pad((string) $pos, 4, "0", STR_PAD_LEFT);
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
    
    protected function initNewItemGroupForm(int $a_grp_id = 0) : StandardForm
    {
        $this->setModuleSubTabs("new_item_groups");
        
        $this->lng->loadLanguageModule("meta");
        $def_lng = $this->lng->getDefaultLanguage();
    
        $title = new ilTextInputGUI($this->lng->txt("title"), "title_" . $def_lng);
        $title->setInfo($this->lng->txt("meta_l_" . $def_lng) .
            " (" . $this->lng->txt("default_language") . ")");
        $title->setRequired(true);
        $form->addItem($title);
        
        foreach ($this->lng->getInstalledLanguages() as $lang_id) {
            if ($lang_id !== $def_lng) {
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
            $titles = [];
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
    
    protected function editNewItemGroup(StandardForm $a_form = null) : void
    {
        $grp_id = $this->admin_gui_request->getNewItemGroupId();
        if (!$grp_id) {
            $this->ctrl->redirect($this, "listNewItemGroups");
        }
        
        if (!$a_form) {
            $this->ctrl->setParameter($this, "grp_id", $grp_id);
            $a_form = $this->initNewItemGroupForm($grp_id);
        }
        
        $this->tpl->setContent($this->renderer->render($a_form));
    }
    
    protected function updateNewItemGroup() : void
    {
        $grp_id = $this->admin_gui_request->getNewItemGroupId();
        if (!$grp_id) {
            $this->ctrl->redirect($this, "listNewItemGroups");
        }
        
        $this->ctrl->setParameter($this, "grp_id", $grp_id);
        
        $form = $this->initNewItemGroupForm($grp_id);
        if ($form->checkInput()) {
            $titles = [];
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

        $group_order = $this->admin_gui_request->getNewItemGroupOrder();
        if (count($group_order) > 0) {
            ilObjRepositorySettings::updateNewItemGroupOrder($group_order);
                                    
            $grp_pos_map = [];
            foreach (ilObjRepositorySettings::getNewItemGroups() as $item) {
                $grp_pos_map[$item["id"]] = str_pad($item["pos"], 4, "0", STR_PAD_LEFT);
            }
        
            // update order of assigned objects
            foreach (ilObjRepositorySettings::getNewItemGroupSubItems() as $grp_id => $subitems) {
                // unassigned objects will always be last
                if ($grp_id) {
                    foreach ($subitems as $obj_type) {
                        $old_pos = $ilSetting->get("obj_add_new_pos_" . $obj_type);
                        if (strlen($old_pos) === 8) {
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
        $group_ids = $this->admin_gui_request->getNewItemGroupIds();
        if (count($group_ids) === 0) {
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
            $cgui->addItem("grp_ids[]", (string) $grp_id, $groups[$grp_id]["title"]);
        }
        
        $this->tpl->setContent($cgui->getHTML());
    }
    
    protected function deleteNewItemGroup() : void
    {
        $group_ids = $this->admin_gui_request->getNewItemGroupIds();
        if (count($group_ids) === 0) {
            $this->listNewItemGroups();
            return;
        }
        
        foreach ($group_ids as $grp_id) {
            ilObjRepositorySettings::deleteNewItemGroup($grp_id);
        }
        
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "listNewItemGroups");
    }
    
    public function addToExternalSettingsForm(int $a_form_id) : ?array
    {
        $ilSetting = $this->settings;
        
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_LP:
                
                $fields = ['trac_show_repository_views' => [ilChangeEvent::_isActive(), ilAdministrationSettingsFormHandler::VALUE_BOOL]];
                                                
                return [["view", $fields]];
                
                
            case ilAdministrationSettingsFormHandler::FORM_TAGGING:
                
                $fields = [
                    'adm_show_comments_tagging_in_lists' => [
                        $ilSetting->get('comments_tagging_in_lists'), ilAdministrationSettingsFormHandler::VALUE_BOOL,
                        ['adm_show_comments_tagging_in_lists_tags' => [$ilSetting->get('comments_tagging_in_lists_tags'), ilAdministrationSettingsFormHandler::VALUE_BOOL]]
                    ]
                ];
                
                return [["view", $fields]];
        }
        return null;
    }

    protected function getMaxLengthConstraint(int $max_length) : Constraint
    {
        //This gives max_length many 9's in a row (and 0 for non-positive max_length)
        //The int cast is necessary for negative max_length
        $bound = (int) (10 ** $max_length - 1);

        return $this->refinery->int()->isLessThanOrEqual($bound);
    }

    protected function getPositiveConstraint() : Constraint
    {
        return $this->refinery->int()->isGreaterThanOrEqual(0);
    }
}
