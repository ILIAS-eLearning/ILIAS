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

use ILIAS\Category\StandardGUIRequest;

/**
 * Class ilObjCategoryGUI
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Sascha Hofmann <saschahofmann@gmx.de>
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjCategoryGUI: ilPermissionGUI, ilContainerPageGUI, ilObjUserGUI, ilObjUserFolderGUI
 * @ilCtrl_Calls ilObjCategoryGUI: ilInfoScreenGUI, ilObjStyleSheetGUI, ilCommonActionDispatcherGUI, ilObjectTranslationGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjCategoryGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI, ilDidacticTemplateGUI, ilExportGUI
 * @ilCtrl_Calls ilObjCategoryGUI: ilObjTaxonomyGUI, ilObjectMetaDataGUI, ilContainerNewsSettingsGUI, ilContainerFilterAdminGUI
 * @ilCtrl_Calls ilObjCategoryGUI: ilRepositoryTrashGUI
 * @ingroup      ModulesCategory
 */
class ilObjCategoryGUI extends ilContainerGUI
{
    public const CONTAINER_SETTING_TAXBLOCK = "tax_sblock_";

    protected ilNavigationHistory $nav_history;
    protected ilHelpGUI $help;
    protected bool $info_screen_enabled = false;
    protected ilObjectGUI $gui_obj;
    protected bool $creation_mode;
    protected ilAdvancedMDRecordGUI $record_gui;
    protected StandardGUIRequest $cat_request;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->help = $DIC["ilHelp"];
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $this->error = $DIC["ilErr"];
        $this->settings = $DIC->settings();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacadmin = $DIC->rbac()->admin();

        $this->lng->loadLanguageModule('cat');
        $this->lng->loadLanguageModule('obj');

        $this->type = "cat";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        
        if (is_object($this->object)) {
            $this->info_screen_enabled = (bool) ilContainer::_lookupContainerSetting(
                $this->object->getId(),
                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                '1'
            );
        }
        $this->cat_request = $DIC
            ->category()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function executeCommand() : void
    {
        $ilNavigationHistory = $this->nav_history;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $header_action = true;
        switch ($next_class) {

            case strtolower(ilRepositoryTrashGUI::class):
                $ru = new ilRepositoryTrashGUI($this);
                $this->ctrl->setReturn($this, 'trash');
                $this->ctrl->forwardCommand($ru);
                break;

            case "ilobjusergui":
                $this->tabs_gui->setTabActive('administrate_users');
                if ($this->cat_request->getObjId() === 0) {
                    $this->gui_obj = new ilObjUserGUI(
                        "",
                        $this->cat_request->getRefId(),
                        true,
                        false
                    );
                } else {
                    $this->gui_obj = new ilObjUserGUI(
                        "",
                        $this->cat_request->getObjId(),
                        false,
                        false
                    );
                }
                $this->gui_obj->setCreationMode($this->creation_mode);
                $this->ctrl->forwardCommand($this->gui_obj);

                $ilTabs->clearTargets();
                $ilTabs->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTarget($this, 'listUsers'));
                $ilHelp = $this->help;
                $ilHelp->setScreenIdComponent("cat");
                $ilHelp->setScreenId("administrate_user");
                $ilHelp->setSubScreenId($ilCtrl->getCmd());
                break;

            case "ilobjuserfoldergui":
                $this->gui_obj = new ilObjUserFolderGUI(
                    "",
                    $this->cat_request->getRefId(),
                    true
                );
                $this->gui_obj->setUserOwnerId($this->cat_request->getRefId());
                $this->gui_obj->setCreationMode($this->creation_mode);
                $this->ctrl->forwardCommand($this->gui_obj);

                $ilTabs->clearTargets();
                $ilTabs->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTarget($this, 'listUsers'));
                $ilHelp = $this->help;
                $ilHelp->setScreenIdComponent("cat");
                $ilHelp->setScreenId("administrate_user");
                $ilHelp->setSubScreenId($ilCtrl->getCmd());
                break;
                
            case "ilcolumngui":
                $this->checkPermission("read");
                $this->prepareOutput();
                $this->content_style_gui->addCss(
                    $this->tpl,
                    $this->object->getRefId()
                );
                $this->renderObject();
                break;

            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
                
            case 'ilinfoscreengui':
                if ($this->info_screen_enabled) {
                    $this->prepareOutput();
                    $this->infoScreen();
                }
                break;
                
            // container page editing
            case "ilcontainerpagegui":
                $this->prepareOutput(false);
                $ret = $this->forwardToPageObject();
                if ($ret !== "") {
                    $this->tpl->setContent($ret);
                }
                $header_action = false;
                break;
                
            case 'ilobjectcopygui':
                $this->prepareOutput();

                $cp = new ilObjectCopyGUI($this);
                $cp->setType('cat');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->setTitleAndDescription();
                $this->showContainerPageTabs();
                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case 'ilusertablegui':
                $u_table = new ilUserTableGUI($this, "listUsers");
                $u_table->initFilter();
                $this->ctrl->setReturn($this, 'listUsers');
                $this->ctrl->forwardCommand($u_table);
                break;
            
            case "ilcommonactiondispatchergui":
                $this->prepareOutput();
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ildidactictemplategui':
                $this->ctrl->setReturn($this, 'edit');
                $did = new ilDidacticTemplateGUI($this);
                $this->ctrl->forwardCommand($did);
                break;

            case 'ilexportgui':
                $this->prepareOutput();
                $this->tabs_gui->setTabActive('export');
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                //$this->tabs_gui->setTabActive('export');
                $this->setEditTabs("settings_trans");
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;
            
            case 'ilobjtaxonomygui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                $this->initTaxSubTabs();
                $tax = new ilObjTaxonomyGUI();
                $tax->setAssignedObject($this->object->getId());
                $tax->setMultiple(true);
                $tax->setListInfo($this->lng->txt("cntr_tax_list_info"));
                $this->ctrl->forwardCommand($tax);
                break;
            
            case 'ilobjectmetadatagui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                $this->tabs_gui->activateTab('meta_data');
                $this->ctrl->forwardCommand($this->getObjectMetadataGUI());
                break;

            case "ilcontainernewssettingsgui":
                $this->prepareOutput();
                $this->tabs_gui->setTabActive('settings');
                $this->setEditTabs();
                $this->tabs_gui->activateSubTab('obj_news_settings');
                $news_set_gui = new ilContainerNewsSettingsGUI($this);
                $news_set_gui->setHideByDate(true);
                $this->ctrl->forwardCommand($news_set_gui);
                break;

            case 'ilcontainerfilteradmingui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                $this->setEditTabs($active_tab = "settings_filter");
                $this->tabs_gui->activateTab('settings');
                $this->ctrl->forwardCommand(new ilContainerFilterAdminGUI($this));
                break;

            default:
                if ($cmd === "infoScreen") {
                    $this->checkPermission("visible");
                } else {
                    $this->checkPermission("read");
                }

                // add entry to navigation history
                if (!$this->getCreationMode() &&
                    $ilAccess->checkAccess("read", "", $this->cat_request->getRefId())) {
                    $ilNavigationHistory->addItem(
                        $this->cat_request->getRefId(),
                        ilLink::_getLink($this->cat_request->getRefId(), "cat"),
                        "cat"
                    );
                }

                $this->prepareOutput();
                if (is_object($this->object)) {
                    $this->content_style_gui->addCss(
                        $this->tpl,
                        $this->object->getRefId()
                    );
                }

                if (!$cmd) {
                    $cmd = "render";
                }
                $cmd .= "Object";
                $this->tabs_gui->activateTab("view_content");	// see #19868
                $this->$cmd();

                break;
        }

        if ($header_action) {
            $this->addHeaderAction();
        }
    }


    protected function addHeaderAction() : void
    {
        ilPreviewGUI::initPreview();
        parent::addHeaderAction();
    }

    public function getObjectMetadataGUI() : ilObjectMetaDataGUI
    {
        $md_gui = new ilObjectMetaDataGUI($this->object);
        if (ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            ilObjectServiceSettingsGUI::TAXONOMIES,
            '0'
        )) {
            $md_gui->enableTaxonomyDefinition(true);
            $tax = $md_gui->getTaxonomyObjGUI();
            $tax->setMultiple(true);
            $tax->setListInfo($this->lng->txt("cntr_tax_list_info"));
            $taxonomies = $this->getTaxonomiesForRefId();
            if (count($taxonomies)) {
                $md_gui->setTaxonomySettings(function ($form) {
                    $tax = $this->getTaxonomiesForRefId();
                    $block = new ilCheckboxGroupInputGUI($this->lng->txt("cntr_taxonomy_show_sideblock"), "sblock");
                    $form->addItem($block);

                    $current = $this->getActiveBlocks();

                    $value = null;
                    foreach ($tax as $tax_id => $tax_item) {
                        $option = new ilCheckboxOption(
                            $tax_item["title"],
                            $tax_id,
                            ilObject::_lookupDescription($tax_id)
                        );

                        if ((int) $tax_item["source"] !== $this->object->getRefId()) {
                            $loc = new ilLocatorGUI();
                            $loc->setTextOnly(true);
                            $loc->addRepositoryItems((int) $tax_item["source"]);
                            $option->setInfo($loc->getHTML());
                        }

                        $block->addOption($option);

                        if (in_array($tax_id, $current)) {
                            $value[] = $tax_id;
                        }
                    }

                    $block->setValue($value);
                }, function ($form) {
                    $taxonomies = $this->getTaxonomiesForRefId();
                    if (count($taxonomies)) {
                        $sblock = $form->getInput("sblock");

                        $prefix = self::CONTAINER_SETTING_TAXBLOCK;

                        ilContainer::_deleteContainerSettings(
                            $this->object->getId(),
                            $prefix . "%",
                            true
                        );

                        if (is_array($sblock)) {
                            foreach ($sblock as $tax_id) {
                                ilContainer::_writeContainerSetting(
                                    $this->object->getId(),
                                    $prefix . $tax_id,
                                    '1'
                                );
                            }
                        }
                    }
                });
            }
        }
        return $md_gui;
    }

    protected function getTabs() : void
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $ilHelp = $this->help;
        $ilAccess = $this->access;

        if ($this->ctrl->getCmd() === "editPageContent") {
            return;
        }

        $ilHelp->setScreenIdComponent("cat");
        
        if ($rbacsystem->checkAccess('read', $this->ref_id)) {
            $this->tabs_gui->addTab(
                "view_content",
                $lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "")
            );

            //BEGIN ChangeEvent add info tab to category object
            if ($this->info_screen_enabled) {
                $force_active = $this->ctrl->getNextClass() === "ilinfoscreengui"
                    || strtolower($this->cat_request->getCmdClass()) === "ilnotegui";
                $this->tabs_gui->addTarget(
                    "info_short",
                    $this->ctrl->getLinkTargetByClass(
                        ["ilobjcategorygui", "ilinfoscreengui"],
                        "showSummary"
                    ),
                    ["showSummary", "", "infoScreen"],
                    "",
                    "",
                    $force_active
                );
            }
            //END ChangeEvent add info tab to category object
        }
        
        if ($rbacsystem->checkAccess('write', $this->ref_id)) {
            $force_active = ($this->ctrl->getCmd() === "edit");
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                "edit",
                get_class($this),
                "",
                $force_active
            );



            // metadata / taxonomies
            $mdgui = new ilObjectMetaDataGUI($this->object);
            if (ilContainer::_lookupContainerSetting(
                $this->object->getId(),
                ilObjectServiceSettingsGUI::TAXONOMIES,
                '0'
            )) {
                $mdgui->enableTaxonomyDefinition(true);
            }
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    "meta_data",
                    $this->lng->txt("meta_data"),
                    $mdtab
                );
            }
        }

        if (ilUserAccountSettings::getInstance()->isLocalUserAdministrationEnabled() &&
            $rbacsystem->checkAccess('cat_administrate_users', $this->ref_id)) {
            $this->tabs_gui->addTarget(
                "administrate_users",
                $this->ctrl->getLinkTarget($this, "listUsers"),
                "listUsers",
                get_class($this)
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
        
        // parent tabs (all container: edit_permission, clipboard, trash
        parent::getTabs();
    }

    /**
    * Render category
    */
    public function renderObject() : void
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("view_content");
        parent::renderObject();

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );
    }

    public function viewObject() : void
    {
        if (strtolower($this->cat_request->getBaseClass()) === "iladministrationgui") {
            parent::viewObject();
            return;
        }
        $this->renderObject();
    }

    protected function initCreationForms(string $new_type) : array
    {
        $forms = parent::initCreationForms($new_type);
        //unset($forms[self::CFORM_IMPORT]);
        return $forms;
    }

    protected function afterSave(ilObject $new_object) : void
    {
        $tree = $this->tree;

        // default: sort by title
        $settings = new ilContainerSortingSettings($new_object->getId());
        $settings->setSortMode(ilContainer::SORT_TITLE);
        $settings->save();
        
        // inherit parents content style, if not individual
        $this->content_style_domain
            ->styleForRefId($new_object->getRefId())
            ->inheritFromParent();

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("cat_added"), true);
        $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
        $this->redirectToRefId($new_object->getRefId(), "");
    }
    
    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function infoScreenObject() : void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    /**
     * show information screen
     */
    public function infoScreen() : string
    {
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        $ilErr = $this->error;

        if (!$ilAccess->checkAccess("visible", "", $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
        }
        
        if (!$this->info_screen_enabled) {
            return "";
        }
        
        // #10986
        $this->tabs_gui->setTabActive('info_short');

        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();
        
        if ($ilAccess->checkAccess("read", "", $this->cat_request->getRefId())) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($ilAccess->checkAccess("write", "", $this->cat_request->getRefId())) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", '1');
                $info->setBlockProperty("news", "public_notifications_option", '1');
            }
        }
        
        $record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'cat', $this->object->getId());
        $record_gui->setInfoObject($info);
        $record_gui->parse();
        

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        // forward the command
        if ($ilCtrl->getNextClass() === "ilinfoscreengui") {
            $ilCtrl->forwardCommand($info);
        } else {
            return $ilCtrl->getHTML($info);
        }
        return "";
    }
    
    protected function editInfoObject() : void
    {
        $this->checkPermission("write");
        $this->setEditTabs();
        $this->tabs_gui->activateTab('settings');
        $this->tabs_gui->setSubTabActive('edit_cat_settings');
        
        $this->initExtendedSettings();
        $this->tpl->setContent($this->form->getHTML());
    }
    
    // Update info (extended meta data)
    protected function updateInfoObject() : void
    {
        $this->checkPermission("write");
    
        // init form
        $this->initExtendedSettings();
        
        // still needed for date conversion and so on
        $this->form->checkInput();
        
        if ($this->record_gui->importEditFormPostValues()) {
            $this->record_gui->writeEditForm();
                        
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "editInfo");
        }

        $this->editInfoObject();
    }
    
    
    // build property form for extended category settings
    protected function initExtendedSettings() : bool
    {
        if (is_object($this->form)) {
            return true;
        }
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTitle($this->lng->txt('ext_cat_settings'));
        $this->form->addCommandButton('updateInfo', $this->lng->txt('save'));
        $this->form->addCommandButton('editInfo', $this->lng->txt('cancel'));

        $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'cat', $this->object->getId());
        $this->record_gui->setPropertyForm($this->form);
        $this->record_gui->parse();
        
        return true;
    }

    protected function setEditTabs($active_tab = "settings_misc") : void
    {
        $this->tabs_gui->addSubTab(
            "settings_misc",
            $this->lng->txt("settings"),
            $this->ctrl->getLinkTarget($this, "edit")
        );

        $this->tabs_gui->addSubTab(
            "settings_trans",
            $this->lng->txt("obj_multilinguality"),
            $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "")
        );

        //news tab
        $news_active = ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
            '1'
        );

        if ($news_active) {
            $this->tabs_gui->addSubTab(
                'obj_news_settings',
                $this->lng->txt("cont_news_settings"),
                $this->ctrl->getLinkTargetByClass('ilcontainernewssettingsgui')
            );
        }

        $this->tabs_gui->addSubTab(
            "settings_filter",
            $this->lng->txt("cont_filter"),
            $this->ctrl->getLinkTargetByClass("ilcontainerfilteradmingui", "")
        );

        $this->tabs_gui->activateTab("settings");
        $this->tabs_gui->activateSubTab($active_tab);
    }

    protected function initEditForm() : ilPropertyFormGUI
    {
        $obj_service = $this->getObjectService();

        $this->lng->loadLanguageModule($this->object->getType());
        $this->setEditTabs();

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt($this->object->getType() . "_edit"));
        
        // title/description
        $this->initFormTitleDescription($form);

        // Show didactic template type
        $this->initDidacticTemplate($form);

        // presentation
        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('obj_presentation'));
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

        // sorting
        $form = $this->initSortingForm(
            $form,
            [
                    ilContainer::SORT_TITLE,
                    ilContainer::SORT_CREATION,
                    ilContainer::SORT_MANUAL
            ]
        );

        // block limit
        $bl = new ilNumberInputGUI($this->lng->txt("cont_block_limit"), "block_limit");
        $bl->setInfo($this->lng->txt("cont_block_limit_info"));
        $bl->setValue(ilContainer::_lookupContainerSetting($this->object->getId(), "block_limit"));
        $form->addItem($bl);
                
        // icon settings

        // Edit ecs export settings
        $ecs = new ilECSCategorySettings($this->object);
        $ecs->addSettingsToForm($form, 'cat');
        
        // services
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt('obj_features'));
        $form->addItem($sh);

        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $form,
            [
                    ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                    ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
                    ilObjectServiceSettingsGUI::TAXONOMIES,
                    ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                    ilObjectServiceSettingsGUI::TAG_CLOUD,
                    ilObjectServiceSettingsGUI::FILTER
            ]
        );

        $form->addCommandButton("update", $this->lng->txt("save"));

        return $form;
    }

    protected function getEditFormValues() : array
    {
        return [];
    }
    
    public function updateObject() : void
    {
        $ilErr = $this->error;
        $ilUser = $this->user;
        $obj_service = $this->getObjectService();

        if (!$this->checkPermissionBool("write")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_write"), $ilErr->MESSAGE);
        } else {
            $form = $this->initEditForm();
            if ($form->checkInput()) {
                $title = $form->getInput("title");
                $desc = $form->getInput("desc");

                $this->object->setTitle($title);
                $this->object->setDescription($desc);
                $this->object->update();

                $this->saveSortingSettings($form);

                // title icon visibility
                $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTitleIconVisibility();

                // top actions visibility
                $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTopActionsVisibility();

                // custom icon
                $obj_service->commonSettings()->legacyForm($form, $this->object)->saveIcon();

                // tile image
                $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

                // list presentation
                $this->saveListPresentation($form);

                // BEGIN ChangeEvent: Record update
                ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
                ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
                // END ChangeEvent: Record update
                
                // services
                ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                    $this->object->getId(),
                    $form,
                    [
                        ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                        ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
                        ilObjectServiceSettingsGUI::TAXONOMIES,
                        ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                        ilObjectServiceSettingsGUI::TAG_CLOUD,
                        ilObjectServiceSettingsGUI::FILTER
                    ]
                );

                // block limit
                if ((int) $form->getInput("block_limit") > 0) {
                    ilContainer::_writeContainerSetting(
                        $this->object->getId(),
                        "block_limit",
                        (string) ((int) $form->getInput("block_limit"))
                    );
                } else {
                    ilContainer::_deleteContainerSettings($this->object->getId(), "block_limit");
                }
                // Update ecs export settings
                $ecs = new ilECSCategorySettings($this->object);
                if ($ecs->handleSettingsUpdate()) {
                    $this->afterUpdate();
                    return;
                }
            }

            // display form to correct errors
            $this->setEditTabs();
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }


    public static function _getImportDir() : string
    {
        return ilFileUtils::getDataDir() . "/cat_import";
    }

    /**
    * import categories (static, also called by RootFolderGUI)
    */
    
    /**
    * Reset filter
    * (note: this function existed before data table filter has been introduced
    */
    protected function resetFilterObject() : void
    {
        $utab = new ilUserTableGUI($this, "listUsers", ilUserTableGUI::MODE_LOCAL_USER);
        $utab->resetOffset();
        $utab->resetFilter();

        // from "old" implementation
        $this->listUsersObject();
    }
    
    /**
     * Apply filter
     * @return
     */
    protected function applyFilterObject() : void
    {
        $ilTabs = $this->tabs;
        
        $utab = new ilUserTableGUI($this, "listUsers", ilUserTableGUI::MODE_LOCAL_USER);
        $utab->resetOffset();
        $utab->writeFilterToSession();
        $this->listUsersObject();
    }

    // METHODS for local user administration
    public function listUsersObject(bool $show_delete = false) : void
    {
        $ilUser = $this->user;
        $ilErr = $this->error;
        $ilToolbar = $this->toolbar;

        $rbacsystem = $this->rbacsystem;
        $rbacreview = $this->rbacreview;

        if (!$rbacsystem->checkAccess("cat_administrate_users", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_admin_users"), $ilErr->MESSAGE);
        }
        $this->tabs_gui->setTabActive('administrate_users');



        $this->tpl->addBlockfile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.cat_admin_users.html',
            "Modules/Category"
        );

        if (count($rbacreview->getGlobalAssignableRoles()) ||
            in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()), true)) {
            $ilToolbar->addButton(
                $this->lng->txt('add_user'),
                $this->ctrl->getLinkTargetByClass('ilobjusergui', 'create')
            );
    
            $ilToolbar->addButton(
                $this->lng->txt('import_users'),
                $this->ctrl->getLinkTargetByClass('ilobjuserfoldergui', 'importUserForm')
            );
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_roles_user_can_be_assigned_to'));
        }

        if ($show_delete) {
            $this->tpl->setCurrentBlock("confirm_delete");
            $this->tpl->setVariable("CONFIRM_FORMACTION", $this->ctrl->getFormAction($this));
            $this->tpl->setVariable("TXT_CANCEL", $this->lng->txt('cancel'));
            $this->tpl->setVariable("CONFIRM_CMD", 'performDeleteUsers');
            $this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt('delete'));
            $this->tpl->parseCurrentBlock();
        }
        
        $this->lng->loadLanguageModule('user');
        
        $utab = new ilUserTableGUI($this, 'listUsers', ilUserTableGUI::MODE_LOCAL_USER);
        $this->tpl->setVariable('USERS_TABLE', $utab->getHTML());
    }

    /**
     * Show auto complete results
     */
    protected function addUserAutoCompleteObject() : void
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(['login', 'firstname', 'lastname', 'email']);
        $auto->enableFieldSearchableCheck(true);
        //$auto->isMoreLinkAvailable(true);

        if (($this->cat_request->getFetchAll())) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($this->cat_request->getTerm());
        exit();
    }

    public function performDeleteUsersObject() : void
    {
        $this->checkPermission("cat_administrate_users");

        foreach ($this->cat_request->getUserIds() as $user_id) {
            if (!in_array($user_id, ilLocalUser::_getAllUserIds($this->object->getRefId()), true)) {
                throw new ilException('user id not valid');
            }
            if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id, false)) {
                continue;
            }
            $tmp_obj->delete();
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('deleted_users'));
        $this->listUsersObject();
    }
            
    public function deleteUsersObject() : void
    {
        $this->checkPermission("cat_administrate_users");
        if ($this->cat_request->getIds() === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_users_selected'));
            $this->listUsersObject();
            return;
        }
        
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('sure_delete_selected_users'));
        $confirm->setConfirm($this->lng->txt('delete'), 'performDeleteUsers');
        $confirm->setCancel($this->lng->txt('cancel'), 'listUsers');
        
        foreach ($this->cat_request->getIds() as $user) {
            $name = ilObjUser::_lookupName($user);
            
            $confirm->addItem(
                'user_ids[]',
                (string) $user,
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']'
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    public function assignRolesObject() : void
    {
        $rbacreview = $this->rbacreview;
        $ilTabs = $this->tabs;
        
        $this->checkPermission("cat_administrate_users");

        if ($this->cat_request->getObjId() === 0) {
            $this->tpl->setOnScreenMessage('failure', 'no_user_selected');
            $this->listUsersObject();
            return;
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTarget($this, 'listUsers'));
        $ilHelp = $this->help;
        $ilHelp->setScreenIdComponent("cat");
        $ilHelp->setScreenId("administrate_user");
        $ilHelp->setSubScreenId("assign_roles");


        $roles = $this->getAssignableRoles();
        
        $ass_roles = $rbacreview->assignedRoles($this->cat_request->getObjId());

        $counter = 0;
        $f_result = [];
        
        foreach ($roles as $role) {
            $role_obj = ilObjectFactory::getInstanceByObjId((int) $role['obj_id']);
            
            $disabled = false;
            $f_result[$counter]['checkbox'] = ilLegacyFormElementsUtil::formCheckbox(
                in_array((int) $role['obj_id'], $ass_roles, true),
                'role_ids[]',
                $role['obj_id'],
                $disabled
            );
            $f_result[$counter]['title'] = $role_obj->getTitle() ?: "";
            $f_result[$counter]['desc'] = $role_obj->getDescription() ?: "";
            $f_result[$counter]['type'] = $role['role_type'] === 'global' ?
                $this->lng->txt('global') :
                $this->lng->txt('local');
            
            unset($role_obj);
            ++$counter;
        }

        $table = new ilCategoryAssignRoleTableGUI($this, "assignRoles");
        $tmp_obj = ilObjectFactory::getInstanceByObjId($this->cat_request->getObjId());
        $title = $this->lng->txt('role_assignment') . ' (' . $tmp_obj->getFullname() . ')';
        $table->setTitle($title, "icon_role.svg", $this->lng->txt("role_assignment"));
        $table->setData($f_result);
        $this->tpl->setContent($table->getHTML());
    }

    public function assignSaveObject() : void
    {
        $rbacreview = $this->rbacreview;
        $rbacadmin = $this->rbacadmin;
        $this->checkPermission("cat_administrate_users");

        // check hack
        if ($this->cat_request->getObjId() === 0 ||
            !in_array($this->cat_request->getObjId(), ilLocalUser::_getAllUserIds(), true)) {
            $this->tpl->setOnScreenMessage('failure', 'no_user_selected');
            $this->listUsersObject();
            return;
        }
        $roles = $this->getAssignableRoles();

        // check minimum one global role
        if (!$this->checkGlobalRoles($this->cat_request->getRoleIds())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_global_role_left'));
            $this->assignRolesObject();
            return;
        }
        
        $new_role_ids = $this->cat_request->getRoleIds();
        $assigned_roles = $rbacreview->assignedRoles($this->cat_request->getObjId());
        foreach ($roles as $role) {
            if (in_array((int) $role['obj_id'], $new_role_ids, true) && !in_array((int) $role['obj_id'], $assigned_roles, true)) {
                $rbacadmin->assignUser((int) $role['obj_id'], $this->cat_request->getObjId());
            }
            if (in_array((int) $role['obj_id'], $assigned_roles, true) && !in_array((int) $role['obj_id'], $new_role_ids, true)) {
                $rbacadmin->deassignUser((int) $role['obj_id'], $this->cat_request->getObjId());
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('role_assignment_updated'));
        $this->assignRolesObject();
    }

    // PRIVATE
    private function getAssignableRoles() : array
    {
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;

        // check local user
        $tmp_obj = ilObjectFactory::getInstanceByObjId($this->cat_request->getObjId());
        // Admin => all roles
        if (in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()), true)) {
            $global_roles = $rbacreview->getGlobalRolesArray();
        } elseif ($tmp_obj->getTimeLimitOwner() === $this->object->getRefId()) {
            $global_roles = $rbacreview->getGlobalAssignableRoles();
        } else {
            $global_roles = [];
        }
        return array_merge(
            $global_roles,
            $rbacreview->getAssignableChildRoles($this->object->getRefId())
        );
    }

    private function checkGlobalRoles($new_assigned) : bool
    {
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;

        $this->checkPermission("cat_administrate_users");

        // return true if it's not a local user
        $tmp_obj = ilObjectFactory::getInstanceByObjId($this->cat_request->getObjId());
        if ($tmp_obj->getTimeLimitOwner() !== $this->object->getRefId() &&
           !in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()), true)) {
            return true;
        }

        // new assignment by form
        $new_assigned = $new_assigned ?: [];
        $assigned = $rbacreview->assignedRoles($this->cat_request->getObjId());

        // all assignable globals
        if (!in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()), true)) {
            $ga = $rbacreview->getGlobalAssignableRoles();
        } else {
            $ga = $rbacreview->getGlobalRolesArray();
        }
        $global_assignable = [];
        foreach ($ga as $role) {
            $global_assignable[] = $role['obj_id'];
        }

        $new_visible_assigned_roles = array_intersect($new_assigned, $global_assignable);
        $all_assigned_roles = array_intersect($assigned, $rbacreview->getGlobalRoles());
        $main_assigned_roles = array_diff($all_assigned_roles, $global_assignable);

        if (!count($new_visible_assigned_roles) && !count($main_assigned_roles)) {
            return false;
        }
        return true;
    }
    
    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        if ($ilAccess->checkAccess("read", "", (int) $a_target)) {
            ilObjectGUI::_gotoRepositoryNode((int) $a_target);
        } elseif ($ilAccess->checkAccess("visible", "", (int) $a_target)) {
            ilObjectGUI::_gotoRepositoryNode((int) $a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId((int) $a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    //
    // taxonomy
    //
    
    protected function initTaxSubTabs($a_active = "tax_list") : void
    {
        $this->tabs_gui->setTabActive("obj_tool_setting_taxonomies");
        $this->tabs_gui->addSubTab(
            "tax_settings",
            $this->lng->txt("cntr_taxonomy_sideblock_settings"),
            $this->ctrl->getLinkTarget($this, "editTaxonomySettings")
        );
        $this->tabs_gui->addSubTab(
            "tax_list",
            $this->lng->txt("cntr_taxonomy_definitions"),
            $this->ctrl->getLinkTargetByClass("ilobjtaxonomygui", "")
        );
        $this->tabs_gui->activateSubTab($a_active);
    }
    
    protected function getTaxonomiesForRefId() : array
    {
        $tree = $this->tree;
        
        // see ilTaxMDGUI::getSelectableTaxonomies()
        
        $res = [];
        foreach ($tree->getPathFull($this->object->getRefId()) as $node) {
            //if ($node["ref_id"] != $this->object->getRefId())
            {
                // find all defined taxes for parent node, activation is not relevant
                $node_taxes = ilObjTaxonomy::getUsageOfObject((int) $node["obj_id"], true);
                if (count($node_taxes)) {
                    foreach ($node_taxes as $node_tax) {
                        $res[$node_tax["tax_id"]] = [
                            "title" => $node_tax["title"]
                        , "source" => $node["child"]
                        ];
                    }
                }
            }
        }
        
        asort($res);
        return $res;
    }

    protected function getActiveBlocks() : array
    {
        $res = [];
        
        $prefix = self::CONTAINER_SETTING_TAXBLOCK;
        
        foreach (ilContainer::_getContainerSettings($this->object->getId()) as $keyword => $value) {
            if ($value && strpos($keyword, $prefix) === 0) {
                $res[] = substr($keyword, strlen($prefix));
            }
        }
        
        return $res;
    }
}
