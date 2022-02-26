<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
 * Class ilObjCategoryGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Sascha Hofmann <saschahofmann@gmx.de>
 * @version      $Id$
 * @ilCtrl_Calls ilObjCategoryGUI: ilPermissionGUI, ilContainerPageGUI, ilContainerLinkListGUI, ilObjUserGUI, ilObjUserFolderGUI
 * @ilCtrl_Calls ilObjCategoryGUI: ilInfoScreenGUI, ilObjStyleSheetGUI, ilCommonActionDispatcherGUI, ilObjectTranslationGUI
 * @ilCtrl_Calls ilObjCategoryGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI, ilDidacticTemplateGUI, ilExportGUI
 * @ilCtrl_Calls ilObjCategoryGUI: ilObjTaxonomyGUI, ilObjectMetaDataGUI, ilContainerNewsSettingsGUI, ilContainerFilterAdminGUI
 * @ilCtrl_Calls ilObjCategoryGUI: ilRepUtilGUI
 * @ingroup      ModulesCategory
 */
class ilObjCategoryGUI extends ilContainerGUI
{
    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    public $ctrl;
    
    const CONTAINER_SETTING_TAXBLOCK = "tax_sblock_";

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
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
        //global $ilCtrl;

        // CONTROL OPTIONS
        //$this->ctrl =& $ilCtrl;
        //$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));
        $GLOBALS['lng']->loadLanguageModule('cat');
        $GLOBALS['lng']->loadLanguageModule('obj');

        $this->type = "cat";
        parent::__construct($a_data, (int) $a_id, $a_call_by_reference, false);
        
        if (is_object($this->object)) {
            include_once("./Services/Container/classes/class.ilContainer.php");
            include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
            $this->info_screen_enabled = ilContainer::_lookupContainerSetting(
                $this->object->getId(),
                ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                true
            );
        }
    }

    public function executeCommand()
    {
        $rbacsystem = $this->rbacsystem;
        $ilNavigationHistory = $this->nav_history;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $header_action = true;
        switch ($next_class) {

            case strtolower(ilRepUtilGUI::class):
                $ru = new \ilRepUtilGUI($this);
                $this->ctrl->setReturn($this, 'trash');
                $this->ctrl->forwardCommand($ru);
                break;

            case "ilobjusergui":
                include_once('./Services/User/classes/class.ilObjUserGUI.php');
                
                $this->tabs_gui->setTabActive('administrate_users');
                if (!$_GET['obj_id']) {
                    $this->gui_obj = new ilObjUserGUI("", $_GET['ref_id'], true, false);
                    $this->gui_obj->setCreationMode($this->creation_mode);
                    $ret = &$this->ctrl->forwardCommand($this->gui_obj);
                } else {
                    $this->gui_obj = new ilObjUserGUI("", $_GET['obj_id'], false, false);
                    $this->gui_obj->setCreationMode($this->creation_mode);
                    $ret = &$this->ctrl->forwardCommand($this->gui_obj);
                }
                
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTarget($this, 'listUsers'));
        $ilHelp = $this->help;
                $ilHelp->setScreenIdComponent("cat");
                $ilHelp->setScreenId("administrate_user");
                $ilHelp->setSubScreenId($ilCtrl->getCmd());
                break;

            case "ilobjuserfoldergui":
                include_once('./Services/User/classes/class.ilObjUserFolderGUI.php');

                $this->gui_obj = new ilObjUserFolderGUI("", (int) $_GET['ref_id'], true, false);
                $this->gui_obj->setUserOwnerId((int) $_GET['ref_id']);
                $this->gui_obj->setCreationMode($this->creation_mode);
                $ret = &$this->ctrl->forwardCommand($this->gui_obj);

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
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $this->tpl->setVariable(
                    "LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId())
                );
                $this->renderObject();
                break;

            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;
                
            case 'ilinfoscreengui':
                if ($this->info_screen_enabled) {
                    $this->prepareOutput();
                    $this->infoScreen();
                }
                break;
                
            case 'ilcontainerlinklistgui':
                include_once("Services/Container/classes/class.ilContainerLinkListGUI.php");
                $link_list_gui = new ilContainerLinkListGUI();
                $ret = &$this->ctrl->forwardCommand($link_list_gui);
                break;

            // container page editing
            case "ilcontainerpagegui":
                $this->prepareOutput(false);
                $ret = $this->forwardToPageObject();
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                $header_action = false;
                break;
                
            case 'ilobjectcopygui':
                $this->prepareOutput();

                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('cat');
                $this->ctrl->forwardCommand($cp);
                break;
                
            case "ilobjstylesheetgui":
                $this->forwardToStyleSheet();
                break;
                
            case 'ilusertablegui':
                include_once './Services/User/classes/class.ilUserTableGUI.php';
                $u_table = new ilUserTableGUI($this, "listUsers");
                $u_table->initFilter();
                $this->ctrl->setReturn($this, 'listUsers');
                $this->ctrl->forwardCommand($u_table);
                break;
            
            case "ilcommonactiondispatchergui":
                $this->prepareOutput();
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

            case 'ilexportgui':
                $this->prepareOutput();
                $this->tabs_gui->setTabActive('export');
                include_once './Services/Export/classes/class.ilExportGUI.php';
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                //$this->tabs_gui->setTabActive('export');
                $this->setEditTabs("settings_trans");
                include_once("./Services/Object/classes/class.ilObjectTranslationGUI.php");
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;
            
            case 'ilobjtaxonomygui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                $this->initTaxSubTabs();
                include_once("./Services/Taxonomy/classes/class.ilObjTaxonomyGUI.php");
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
                if ($cmd == "infoScreen") {
                    $this->checkPermission("visible");
                } else {
                    $this->checkPermission("read");
                }

                // add entry to navigation history
                if (!$this->getCreationMode() &&
                    $ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
                    include_once("./Services/Link/classes/class.ilLink.php");
                    $ilNavigationHistory->addItem(
                        $_GET["ref_id"],
                        ilLink::_getLink($_GET["ref_id"], "cat"),
                        "cat"
                    );
                }

                $this->prepareOutput();
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                if (is_object($this->object)) {
                    $this->tpl->setVariable(
                        "LOCATION_CONTENT_STYLESHEET",
                        ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId())
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

        return true;
    }


    /**
     * @inheritDoc
     */
    protected function addHeaderAction()
    {
        ilPreviewGUI::initPreview();
        parent::addHeaderAction();
    }


    /**
     * Get object metadata gui
     *
     * @param
     * @return
     */
    public function getObjectMetadataGUI()
    {
        include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
        $md_gui = new ilObjectMetaDataGUI($this->object);
        include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
        if (ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            ilObjectServiceSettingsGUI::TAXONOMIES,
            false
        )) {
            $md_gui->enableTaxonomyDefinition(true);
            $tax = $md_gui->getTaxonomyObjGUI();
            $tax->setMultiple(true);
            $tax->setListInfo($this->lng->txt("cntr_tax_list_info"));
            $taxonomies = $this->getTaxonomiesForRefId();
            if (sizeof($taxonomies)) {
                $md_gui->setTaxonomySettings(function ($form) {
                    $tax = $this->getTaxonomiesForRefId();
                    $block = new ilCheckboxGroupInputGUI($this->lng->txt("cntr_taxonomy_show_sideblock"), "sblock");
                    $form->addItem($block);

                    $current = $this->getActiveBlocks();

                    foreach ($tax as $tax_id => $tax_item) {
                        $option = new ilCheckboxOption(
                            $tax_item["title"],
                            $tax_id,
                            ilObject::_lookupDescription($tax_id)
                        );

                        if ($tax_item["source"] != $this->object->getRefId()) {
                            $loc = new ilLocatorGUI();
                            $loc->setTextOnly(true);
                            $loc->addRepositoryItems($tax_item["source"]);
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
                    if (sizeof($taxonomies)) {
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
                                    1
                                );
                            }
                        }
                    }
                });
            }
        }
        return $md_gui;
    }


    /**
    * Get tabs
    */
    public function getTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $ilHelp = $this->help;
        $ilAccess = $this->access;

        if ($this->ctrl->getCmd() == "editPageContent") {
            return;
        }
        #$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

        $ilHelp->setScreenIdComponent("cat");
        
        if ($rbacsystem->checkAccess('read', $this->ref_id)) {
            $force_active = ($_GET["cmd"] == "" || $_GET["cmd"] == "render")
                ? true
                : false;
            $this->tabs_gui->addTab(
                "view_content",
                $lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "")
            );

            //BEGIN ChangeEvent add info tab to category object
            if ($this->info_screen_enabled) {
                $force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
                    || strtolower($_GET["cmdClass"]) == "ilnotegui")
                    ? true
                    : false;
                $this->tabs_gui->addTarget(
                    "info_short",
                    $this->ctrl->getLinkTargetByClass(
                        array("ilobjcategorygui", "ilinfoscreengui"),
                        "showSummary"
                    ),
                    array("showSummary","", "infoScreen"),
                    "",
                    "",
                    $force_active
                );
            }
            //END ChangeEvent add info tab to category object
        }
        
        if ($rbacsystem->checkAccess('write', $this->ref_id)) {
            $force_active = ($_GET["cmd"] == "edit")
                ? true
                : false;
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                "edit",
                get_class($this),
                "",
                $force_active
            );



            // metadata / taxonomies
            include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
            $mdgui = new ilObjectMetaDataGUI($this->object);
            if (ilContainer::_lookupContainerSetting(
                $this->object->getId(),
                ilObjectServiceSettingsGUI::TAXONOMIES,
                false
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

        include_once './Services/User/classes/class.ilUserAccountSettings.php';
        if (
            ilUserAccountSettings::getInstance()->isLocalUserAdministrationEnabled() and
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
    public function renderObject()
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("view_content");
        $ret = parent::renderObject();

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );

        return $ret;
    }

    public function viewObject()
    {
        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            parent::viewObject();
            return true;
        }
        return $this->renderObject();
    }

    protected function initCreationForms($a_new_type)
    {
        $forms = parent::initCreationForms($a_new_type);
        //unset($forms[self::CFORM_IMPORT]);
        return $forms;
    }

    protected function afterSave(ilObject $a_new_object)
    {
        $tree = $this->tree;

        // default: sort by title
        include_once('Services/Container/classes/class.ilContainerSortingSettings.php');
        $settings = new ilContainerSortingSettings($a_new_object->getId());
        $settings->setSortMode(ilContainer::SORT_TITLE);
        $settings->save();
        
        // inherit parents content style, if not individual
        $parent_ref_id = $tree->getParentId($a_new_object->getRefId());
        $parent_id = ilObject::_lookupObjId($parent_ref_id);
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $style_id = ilObjStyleSheet::lookupObjectStyle($parent_id);
        if ($style_id > 0) {
            if (ilObjStyleSheet::_lookupStandard($style_id)) {
                ilObjStyleSheet::writeStyleUsage($a_new_object->getId(), $style_id);
            }
        }

        // always send a message
        ilUtil::sendSuccess($this->lng->txt("cat_added"), true);
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
        $ilCtrl = $this->ctrl;
        $ilErr = $this->error;

        if (!$ilAccess->checkAccess("visible", "", $this->ref_id)) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
        }
        
        if (!$this->info_screen_enabled) {
            return;
        }
        
        // #10986
        $this->tabs_gui->setTabActive('info_short');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);

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
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'cat', $this->object->getId());
        $record_gui->setInfoObject($info);
        $record_gui->parse();
        

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        // forward the command
        if ($ilCtrl->getNextClass() == "ilinfoscreengui") {
            $ilCtrl->forwardCommand($info);
        } else {
            return $ilCtrl->getHTML($info);
        }
    }
    
    /**
     * Edit extended category settings
     *
     * @access protected
     */
    protected function editInfoObject()
    {
        $this->checkPermission("write");
        $this->setEditTabs();
        $this->tabs_gui->activateTab('settings');
        $this->tabs_gui->setSubTabActive('edit_cat_settings');
        
        $this->initExtendedSettings();
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Update info (extended meta data)
     *
     * @access protected
     */
    protected function updateInfoObject()
    {
        $this->checkPermission("write");
    
        // init form
        $this->initExtendedSettings();
        
        // still needed for date conversion and so on
        $this->form->checkInput();
        
        if ($this->record_gui->importEditFormPostValues()) {
            $this->record_gui->writeEditForm();
                        
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "editInfo");
        }

        $this->editInfoObject();
    }
    
    
    /**
     * build property form for extended category settings
     *
     * @access protected
     */
    protected function initExtendedSettings()
    {
        if (is_object($this->form)) {
            return true;
        }
        
        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTitle($this->lng->txt('ext_cat_settings'));
        $this->form->addCommandButton('updateInfo', $this->lng->txt('save'));
        $this->form->addCommandButton('editInfo', $this->lng->txt('cancel'));

        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'cat', $this->object->getId());
        $this->record_gui->setPropertyForm($this->form);
        $this->record_gui->parse();
        
        return true;
    }

    protected function setEditTabs($active_tab = "settings_misc")
    {
        $ilSetting = $this->settings;
        $ilTabs = $this->tabs;
        
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
            true
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

    public function initEditForm()
    {
        $obj_service = $this->getObjectService();

        $this->lng->loadLanguageModule($this->object->getType());
        $this->setEditTabs();

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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
            array(
                    ilContainer::SORT_TITLE,
                    ilContainer::SORT_CREATION,
                    ilContainer::SORT_MANUAL
                )
        );

        // block limit
        $bl = new ilNumberInputGUI($this->lng->txt("cont_block_limit"), "block_limit");
        $bl->setInfo($this->lng->txt("cont_block_limit_info"));
        $bl->setValue(ilContainer::_lookupContainerSetting($this->object->getId(), "block_limit"));
        $form->addItem($bl);
                
        // icon settings

        // Edit ecs export settings
        include_once 'Modules/Category/classes/class.ilECSCategorySettings.php';
        $ecs = new ilECSCategorySettings($this->object);
        $ecs->addSettingsToForm($form, 'cat');
        
        // services
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt('obj_features'));
        $form->addItem($sh);

        include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $form,
            array(
                    ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                    ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
                    ilObjectServiceSettingsGUI::TAXONOMIES,
                    ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                    ilObjectServiceSettingsGUI::TAG_CLOUD,
                    ilObjectServiceSettingsGUI::FILTER
                )
        );

        $form->addCommandButton("update", $this->lng->txt("save"));

        return $form;
    }

    public function getEditFormValues()
    {
        // values are set in initEditForm()
    }
    
    /**
    * updates object entry in object_data
    *
    * @access	public
    */
    public function updateObject()
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
                require_once('Services/Tracking/classes/class.ilChangeEvent.php');
                ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
                ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
                // END ChangeEvent: Record update
                
                // services
                include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
                ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                    $this->object->getId(),
                    $form,
                    array(
                        ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                        ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
                        ilObjectServiceSettingsGUI::TAXONOMIES,
                        ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                        ilObjectServiceSettingsGUI::TAG_CLOUD,
                        ilObjectServiceSettingsGUI::FILTER
                    )
                );

                // block limit
                if ((int) $form->getInput("block_limit") > 0) {
                    ilContainer::_writeContainerSetting($this->object->getId(), "block_limit", (int) $form->getInput("block_limit"));
                } else {
                    ilContainer::_deleteContainerSettings($this->object->getId(), "block_limit");
                }
                // Update ecs export settings
                include_once 'Modules/Category/classes/class.ilECSCategorySettings.php';
                $ecs = new ilECSCategorySettings($this->object);
                if ($ecs->handleSettingsUpdate()) {
                    return $this->afterUpdate();
                }
            }

            // display form to correct errors
            $this->setEditTabs();
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }

    /**
    * display form for category import
    */
    public function importCategoriesFormObject()
    {
        ilObjCategoryGUI::_importCategoriesForm($this->ref_id, $this->tpl);
    }

    /**
    * display form for category import (static, also called by RootFolderGUI)
    */
    public static function _importCategoriesForm($a_ref_id, &$a_tpl)
    {
        global $DIC;

        $lng = $DIC->language();
        $rbacreview = $DIC->rbac()->review();
        $ilCtrl = $DIC->ctrl();

        $a_tpl->addBlockfile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.cat_import_form.html",
            "Modules/Category"
        );

        $a_tpl->setVariable("FORMACTION", $ilCtrl->getFormActionByClass('ilObjCategoryGUI'));

        $a_tpl->setVariable("TXT_IMPORT_CATEGORIES", $lng->txt("import_categories"));
        $a_tpl->setVariable("TXT_HIERARCHY_OPTION", $lng->txt("import_cat_localrol"));
        $a_tpl->setVariable("TXT_IMPORT_FILE", $lng->txt("import_file"));
        $a_tpl->setVariable("TXT_IMPORT_TABLE", $lng->txt("import_cat_table"));

        $a_tpl->setVariable("BTN_IMPORT", $lng->txt("import"));
        $a_tpl->setVariable("BTN_CANCEL", $lng->txt("cancel"));

        // NEED TO FILL ADOPT_PERMISSIONS HTML FORM....
        $parent_role_ids = $rbacreview->getParentRoleIds($a_ref_id, true);
        
        // sort output for correct color changing
        ksort($parent_role_ids);
        
        foreach ($parent_role_ids as $key => $par) {
            if ($par["obj_id"] != SYSTEM_ROLE_ID) {
                $check = ilUtil::formCheckbox(0, "adopt[]", $par["obj_id"], 1);
                $output["adopt"][$key]["css_row_adopt"] = ilUtil::switchColor($key, "tblrow1", "tblrow2");
                $output["adopt"][$key]["check_adopt"] = $check;
                $output["adopt"][$key]["role_id"] = $par["obj_id"];
                $output["adopt"][$key]["type"] = ($par["type"] == 'role' ? 'Role' : 'Template');
                $output["adopt"][$key]["role_name"] = $par["title"];
            }
        }
        
        //var_dump($output);

        // BEGIN ADOPT PERMISSIONS
        foreach ($output["adopt"] as $key => $value) {
            $a_tpl->setCurrentBlock("ADOPT_PERM_ROW");
            $a_tpl->setVariable("CSS_ROW_ADOPT", $value["css_row_adopt"]);
            $a_tpl->setVariable("CHECK_ADOPT", $value["check_adopt"]);
            $a_tpl->setVariable("LABEL_ID", $value["role_id"]);
            $a_tpl->setVariable("TYPE", $value["type"]);
            $a_tpl->setVariable("ROLE_NAME", $value["role_name"]);
            $a_tpl->parseCurrentBlock();
        }
    }


    /**
    * import cancelled
    *
    * @access private
    */
    public function importCancelledObject()
    {
        $this->ctrl->redirect($this);
    }

    /**
    * get user import directory name
    */
    public static function _getImportDir()
    {
        return ilUtil::getDataDir() . "/cat_import";
    }

    /**
    * import categories
    */
    public function importCategoriesObject()
    {
        ilObjCategoryGUI::_importCategories($_GET["ref_id"]);
        // call to importCategories with $withrol = 0
        ilObjCategoryGUI::_importCategories($_GET["ref_id"], 0);
    }
    
    /**
     * import categories with local rol
     */
    public function importCategoriesWithRolObject()
    {
    
      //echo "entra aqui";
        // call to importCategories with $withrol = 1
        ilObjCategoryGUI::_importCategories($_GET["ref_id"], 1);
    }

    /**
    * import categories (static, also called by RootFolderGUI)
    */
    
    public static function _importCategories($a_ref_id, $withrol_tmp)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        require_once("./Modules/Category/classes/class.ilCategoryImportParser.php");

        $import_dir = ilObjCategoryGUI::_getImportDir();

        // create user import directory if necessary
        if (!@is_dir($import_dir)) {
            ilUtil::createDirectory($import_dir);
        }

        // move uploaded file to user import directory

        $file_name = $_FILES["importFile"]["name"];

        // added to prevent empty file names
        if (!strcmp($file_name, "")) {
            ilUtil::sendFailure($lng->txt("no_import_file_found"), true);
            $ilCtrl->redirectByClass('ilObjCategoryGUI');
        }

        $parts = pathinfo($file_name);
        $full_path = $import_dir . "/" . $file_name;
        ilUtil::moveUploadedFile($_FILES["importFile"]["tmp_name"], $file_name, $full_path);

        // unzip file
        ilUtil::unzip($full_path);

        $subdir = basename($parts["basename"], "." . $parts["extension"]);
        $xml_file = $import_dir . "/" . $subdir . "/" . $subdir . ".xml";
        // CategoryImportParser
        //var_dump($_POST);
        $importParser = new ilCategoryImportParser($xml_file, $a_ref_id, $withrol_tmp);
        $importParser->startParsing();

        ilUtil::sendSuccess($lng->txt("categories_imported"), true);
        $ilCtrl->redirectByClass('ilObjCategoryGUI');
    }
    
    /**
    * Reset filter
    * (note: this function existed before data table filter has been introduced
    */
    protected function resetFilterObject()
    {
        include_once("./Services/User/classes/class.ilUserTableGUI.php");
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
    protected function applyFilterObject()
    {
        $ilTabs = $this->tabs;
        
        include_once("./Services/User/classes/class.ilUserTableGUI.php");
        $utab = new ilUserTableGUI($this, "listUsers", ilUserTableGUI::MODE_LOCAL_USER);
        $utab->resetOffset();
        $utab->writeFilterToSession();
        $this->listUsersObject();
    }

    // METHODS for local user administration
    public function listUsersObject($show_delete = false)
    {
        $ilUser = $this->user;
        $ilErr = $this->error;
        $ilToolbar = $this->toolbar;

        include_once './Services/User/classes/class.ilLocalUser.php';
        include_once './Services/User/classes/class.ilObjUserGUI.php';

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

        if (count($rbacreview->getGlobalAssignableRoles()) or in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
            $ilToolbar->addButton(
                $this->lng->txt('add_user'),
                $this->ctrl->getLinkTargetByClass('ilobjusergui', 'create')
            );
    
            $ilToolbar->addButton(
                $this->lng->txt('import_users'),
                $this->ctrl->getLinkTargetByClass('ilobjuserfoldergui', 'importUserForm')
            );
        } else {
            ilUtil::sendInfo($this->lng->txt('no_roles_user_can_be_assigned_to'));
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
        
        include_once("./Services/User/classes/class.ilUserTableGUI.php");
        $utab = new ilUserTableGUI($this, 'listUsers', ilUserTableGUI::MODE_LOCAL_USER);
        $this->tpl->setVariable('USERS_TABLE', $utab->getHTML());

        return true;
    }

    /**
     * Show auto complete results
     */
    protected function addUserAutoCompleteObject()
    {
        include_once './Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array('login','firstname','lastname','email'));
        $auto->enableFieldSearchableCheck(true);
        $auto->isMoreLinkAvailable(true);

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($_REQUEST['term']);
        exit();
    }


    public function performDeleteUsersObject()
    {
        include_once './Services/User/classes/class.ilLocalUser.php';
        $this->checkPermission("cat_administrate_users");

        foreach ($_POST['user_ids'] as $user_id) {
            if (!in_array($user_id, ilLocalUser::_getAllUserIds($this->object->getRefId()))) {
                die('user id not valid');
            }
            if (!$tmp_obj = &ilObjectFactory::getInstanceByObjId($user_id, false)) {
                continue;
            }
            $tmp_obj->delete();
        }
        ilUtil::sendSuccess($this->lng->txt('deleted_users'));
        $this->listUsersObject();

        return true;
    }
            
    public function deleteUsersObject()
    {
        $this->checkPermission("cat_administrate_users");
        if (!count($_POST['id'])) {
            ilUtil::sendFailure($this->lng->txt('no_users_selected'));
            $this->listUsersObject();
            
            return true;
        }
        
        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('sure_delete_selected_users'));
        $confirm->setConfirm($this->lng->txt('delete'), 'performDeleteUsers');
        $confirm->setCancel($this->lng->txt('cancel'), 'listUsers');
        
        foreach ($_POST['id'] as $user) {
            $name = ilObjUser::_lookupName($user);
            
            $confirm->addItem(
                'user_ids[]',
                $user,
                $name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']'
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    public function assignRolesObject()
    {
        $rbacreview = $this->rbacreview;
        $ilTabs = $this->tabs;
        
        $this->checkPermission("cat_administrate_users");

        include_once './Services/User/classes/class.ilLocalUser.php';

        if (!isset($_GET['obj_id'])) {
            ilUtil::sendFailure('no_user_selected');
            $this->listUsersObject();

            return true;
        }

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTarget($this, 'listUsers'));
        $ilHelp = $this->help;
        $ilHelp->setScreenIdComponent("cat");
        $ilHelp->setScreenId("administrate_user");
        $ilHelp->setSubScreenId("assign_roles");


        $roles = $this->__getAssignableRoles();
        
        if (!count($roles)) {
            #ilUtil::sendInfo($this->lng->txt('no_roles_user_can_be_assigned_to'));
            #$this->listUsersObject();

            #return true;
        }
        
        $ass_roles = $rbacreview->assignedRoles($_GET['obj_id']);

        $counter = 0;
        $f_result = array();
        
        foreach ($roles as $role) {
            $role_obj = &ilObjectFactory::getInstanceByObjId($role['obj_id']);
            
            $disabled = false;
            $f_result[$counter]['checkbox'] = ilUtil::formCheckbox(
                in_array($role['obj_id'], $ass_roles) ? 1 : 0,
                'role_ids[]',
                $role['obj_id'],
                $disabled
            );
            $f_result[$counter]['title'] = $role_obj->getTitle() ? $role_obj->getTitle() : "";
            $f_result[$counter]['desc'] = $role_obj->getDescription() ? $role_obj->getDescription() : "";
            $f_result[$counter]['type'] = $role['role_type'] == 'global' ?
                $this->lng->txt('global') :
                $this->lng->txt('local');
            
            unset($role_obj);
            ++$counter;
        }

        include_once('./Modules/Category/classes/class.ilCategoryAssignRoleTableGUI.php');
        $table = new ilCategoryAssignRoleTableGUI($this, "assignRoles");
        $tmp_obj = &ilObjectFactory::getInstanceByObjId($_GET['obj_id']);
        $title = $this->lng->txt('role_assignment') . ' (' . $tmp_obj->getFullname() . ')';
        $table->setTitle($title, "icon_role.svg", $this->lng->txt("role_assignment"));
        $table->setData($f_result);
        $this->tpl->setContent($table->getHTML());
    }

    public function assignSaveObject()
    {
        $rbacreview = $this->rbacreview;
        $rbacadmin = $this->rbacadmin;
        $this->checkPermission("cat_administrate_users");

        include_once './Services/User/classes/class.ilLocalUser.php';
        // check hack
        if (!isset($_GET['obj_id']) or !in_array($_REQUEST['obj_id'], ilLocalUser::_getAllUserIds())) {
            ilUtil::sendFailure('no_user_selected');
            $this->listUsersObject();

            return true;
        }
        $roles = $this->__getAssignableRoles();

        // check minimum one global role
        if (!$this->__checkGlobalRoles($_POST['role_ids'])) {
            ilUtil::sendFailure($this->lng->txt('no_global_role_left'));
            $this->assignRolesObject();

            return false;
        }
        
        $new_role_ids = $_POST['role_ids'] ? $_POST['role_ids'] : array();
        $assigned_roles = $rbacreview->assignedRoles((int) $_REQUEST['obj_id']);
        foreach ($roles as $role) {
            if (in_array($role['obj_id'], $new_role_ids) and !in_array($role['obj_id'], $assigned_roles)) {
                $rbacadmin->assignUser($role['obj_id'], (int) $_REQUEST['obj_id']);
            }
            if (in_array($role['obj_id'], $assigned_roles) and !in_array($role['obj_id'], $new_role_ids)) {
                $rbacadmin->deassignUser($role['obj_id'], (int) $_REQUEST['obj_id']);
            }
        }
        ilUtil::sendSuccess($this->lng->txt('role_assignment_updated'));
        $this->assignRolesObject();
        
        return true;
    }

    // PRIVATE
    public function __getAssignableRoles()
    {
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;

        // check local user
        $tmp_obj = &ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
        // Admin => all roles
        if (in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
            $global_roles = $rbacreview->getGlobalRolesArray();
        } elseif ($tmp_obj->getTimeLimitOwner() == $this->object->getRefId()) {
            $global_roles = $rbacreview->getGlobalAssignableRoles();
        } else {
            $global_roles = array();
        }
        return $roles = array_merge(
            $global_roles,
            $rbacreview->getAssignableChildRoles($this->object->getRefId())
        );
    }

    public function __checkGlobalRoles($new_assigned)
    {
        $rbacreview = $this->rbacreview;
        $ilUser = $this->user;

        $this->checkPermission("cat_administrate_users");

        // return true if it's not a local user
        $tmp_obj = &ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
        if ($tmp_obj->getTimeLimitOwner() != $this->object->getRefId() and
           !in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
            return true;
        }

        // new assignment by form
        $new_assigned = $new_assigned ? $new_assigned : array();
        $assigned = $rbacreview->assignedRoles((int) $_GET['obj_id']);

        // all assignable globals
        if (!in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
            $ga = $rbacreview->getGlobalAssignableRoles();
        } else {
            $ga = $rbacreview->getGlobalRolesArray();
        }
        $global_assignable = array();
        foreach ($ga as $role) {
            $global_assignable[] = $role['obj_id'];
        }

        $new_visible_assigned_roles = array_intersect($new_assigned, $global_assignable);
        $all_assigned_roles = array_intersect($assigned, $rbacreview->getGlobalRoles());
        $main_assigned_roles = array_diff($all_assigned_roles, $global_assignable);

        if (!count($new_visible_assigned_roles) and !count($main_assigned_roles)) {
            return false;
        }
        return true;
    }
    
    public static function _goto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target);
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    //
    // taxonomy
    //
    
    protected function initTaxSubTabs($a_active = "tax_list")
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
    
    protected function getTaxonomiesForRefId()
    {
        $tree = $this->tree;
        
        include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
        include_once "Services/Taxonomy/classes/class.ilObjTaxonomy.php";
        
        // see ilTaxMDGUI::getSelectableTaxonomies()
        
        $res = array();
        foreach ($tree->getPathFull($this->object->getRefId()) as $node) {
            //if ($node["ref_id"] != $this->object->getRefId())
            {
                // find all defined taxes for parent node, activation is not relevant
                $node_taxes = ilObjTaxonomy::getUsageOfObject($node["obj_id"], true);
                if (sizeof($node_taxes)) {
                    foreach ($node_taxes as $node_tax) {
                        $res[$node_tax["tax_id"]] = array(
                            "title" => $node_tax["title"]
                        , "source" => $node["child"]
                        );
                    }
                }
            }
        }
        
        asort($res);
        return $res;
    }


    protected function getActiveBlocks()
    {
        $res = array();
        
        $prefix = self::CONTAINER_SETTING_TAXBLOCK;
        
        foreach (ilContainer::_getContainerSettings($this->object->getId()) as $keyword => $value) {
            if (substr($keyword, 0, strlen($prefix)) == $prefix && (bool) $value) {
                $res[] = substr($keyword, strlen($prefix));
            }
        }
        
        return $res;
    }
} // END class.ilObjCategoryGUI
