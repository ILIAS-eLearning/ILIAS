<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjOrgUnit GUI class
 *
 * @author            : Oskar Truffer <ot@studer-raimann.ch>
 * @author            : Martin Studer <ms@studer-raimann.ch>
 * @author            : Stefan Wanzenried <sw@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilObjOrgUnitGUI: ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilPermissionGUI, ilPageObjectGUI, ilContainerLinkListGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilObjUserGUI, ilObjUserFolderGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilInfoScreenGUI, ilObjStyleSheetGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilDidacticTemplateGUI, illearningprogressgui
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilTranslationGUI, ilLocalUserGUI, ilOrgUnitExportGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilOrgUnitStaffGUI, ilExtIdGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilOrgUnitSimpleImportGUI, ilOrgUnitSimpleUserImportGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilOrgUnitTypeGUI, ilOrgUnitPositionGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilOrgUnitUserAssignmentGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilOrgUnitTypeGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilPropertyFormGUI
 */
class ilObjOrgUnitGUI extends ilContainerGUI
{
    const TAB_POSITIONS = 'positions';
    const TAB_ORGU_TYPES = 'orgu_types';
    const TAB_SETTINGS = "settings";
    const TAB_STAFF = 'orgu_staff';
    const TAB_GLOBAL_SETTINGS = 'global_settings';
    const TAB_EXPORT = 'export';
    /**
     * @var ilCtrl
     */
    public $ctrl;
    /**
     * @var ilTemplate
     */
    public $tpl;
    /**
     * @var ilTabsGUI
     */
    public $tabs_gui;
    /**
     * @var ilAccessHandler
     */
    protected $ilAccess;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilLocatorGUI
     */
    protected $ilLocator;
    /**
     * @var ilTree
     */
    public $tree;
    /**
     * @var ilObjOrgUnit
     */
    public $object;
    /**
     * @var ilLog
     */
    protected $ilLog;
    /**
     * @var Ilias
     */
    public $ilias;
    const CMD_EDIT_SETTINGS = 'editSettings';


    public function __construct()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilLocator = $DIC['ilLocator'];
        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilLog = $DIC['ilLog'];
        $ilias = $DIC['ilias'];
        parent::__construct(array(), $_GET["ref_id"], true, false);

        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->ilAccess = $ilAccess;
        $this->ilLocator = $ilLocator;
        $this->tree = $tree;
        $this->toolbar = $ilToolbar;
        $this->ilLog = $ilLog;
        $this->ilias = $ilias;
        $this->type = 'orgu';

        $lng->loadLanguageModule("orgu");
        $this->tpl->addCss('./Modules/OrgUnit/templates/default/orgu.css');
    }


    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        parent::prepareOutput();

        //Otherwise move-Objects would not work
        if ($cmd != "cut") {
            $this->showTree();
        }

        switch ($next_class) {
            case 'ilorgunitglobalsettingsgui':
                $this->tabs_gui->activateTab(self::TAB_GLOBAL_SETTINGS);
                $global_settings = new ilOrgUnitGlobalSettingsGUI();
                $this->ctrl->forwardCommand($global_settings);
                break;
            case "illocalusergui":
                if (!ilObjOrgUnitAccess::_checkAccessAdministrateUsers((int) $_GET['ref_id'])) {
                    ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
                    $this->ctrl->redirect($this);
                }
                $this->tabs_gui->activateTab('administrate_users');
                $ilLocalUserGUI = new ilLocalUserGUI($this);
                $this->ctrl->forwardCommand($ilLocalUserGUI);
                break;
            case "ilorgunitsimpleimportgui":
                $this->tabs_gui->activateTab("view");
                $this->setContentSubTabs();
                $this->tabs_gui->setSubTabActive('import');
                $ilOrgUnitSimpleImportGUI = new ilOrgUnitSimpleImportGUI($this);
                $this->ctrl->forwardCommand($ilOrgUnitSimpleImportGUI);
                break;
            case "ilorgunitsimpleuserimportgui":
                $ilOrgUnitSimpleUserImportGUI = new ilOrgUnitSimpleUserImportGUI($this);
                $this->ctrl->forwardCommand($ilOrgUnitSimpleUserImportGUI);
                break;
            case "ilorgunitstaffgui":
            case "ilrepositorysearchgui":
                $this->tabs_gui->activateTab(self::TAB_STAFF);
                $ilOrgUnitStaffGUI = new ilOrgUnitStaffGUI($this);
                $this->ctrl->forwardCommand($ilOrgUnitStaffGUI);
                break;
            case "ilobjusergui":
                switch ($cmd) {
                    case "create":
                        $ilObjUserGUI = new ilObjUserGUI("", (int) $_GET['ref_id'], true, false);
                        $ilObjUserGUI->setCreationMode(true);
                        $this->ctrl->forwardCommand($ilObjUserGUI);
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
                        break;
                    case "save":
                        $ilObjUserGUI = new ilObjUserGUI("", $_GET['ref_id'], true, false);
                        $ilObjUserGUI->setCreationMode(true);
                        $this->ctrl->forwardCommand($ilObjUserGUI);
                        $this->tabs_gui->clearTargets();
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
                        break;
                    case "view":
                    case "update":
                        $ilObjUserGUI = new ilObjUserGUI("", (int) $_GET['obj_id'], false, false);
                        $ilObjUserGUI->setCreationMode(false);
                        $this->ctrl->forwardCommand($ilObjUserGUI);
                        $this->tabs_gui->clearTargets();
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
                        break;
                    case "cancel":
                        $this->ctrl->redirectByClass("illocalusergui", "index");
                        break;
                }
                break;
            case "ilobjuserfoldergui":
                switch ($cmd) {
                    case "view":
                        $this->ctrl->redirectByClass("illocalusergui", "index");
                        break;
                    default:
                        $ilObjUserFolderGUI = new ilObjUserFolderGUI("", (int) $_GET['ref_id'], true, false);
                        $ilObjUserFolderGUI->setUserOwnerId((int) $_GET['ref_id']);
                        $ilObjUserFolderGUI->setCreationMode(true);
                        $this->ctrl->forwardCommand($ilObjUserFolderGUI);
                        $this->tabs_gui->clearTargets();
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
                        break;
                }
                break;
            case "ilinfoscreengui":
                $this->tabs_gui->activateTab("info_short");
                if (!$this->ilAccess->checkAccess("read", "", $this->ref_id) and !$this->ilAccess->checkAccess("visible", "", $this->ref_id)) {
                    $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->MESSAGE);
                }
                $info = new ilInfoScreenGUI($this);
                $amd_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
                $amd_gui->setInfoObject($info);
                $amd_gui->parse();
                $this->ctrl->forwardCommand($info);
                break;
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                $ilPermissionGUI = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($ilPermissionGUI);
                break;
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            case 'illearningprogressgui':
            case 'illplistofprogressgui':
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget($this->lng->txt('backto_staff'), $this->ctrl->getLinkTargetByClass(ilOrgUnitUserAssignmentGUI::class, ilOrgUnitUserAssignmentGUI::CMD_INDEX));
                if (!ilObjOrgUnitAccess::_checkAccessToUserLearningProgress($this->object->getRefid(), $_GET['obj_id'])) {
                    ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
                    $this->ctrl->redirectByClass("ilOrgUnitStaffGUI", "showStaff");
                }
                $this->ctrl->saveParameterByClass("illearningprogressgui", "obj_id");
                $this->ctrl->saveParameterByClass("illearningprogressgui", "recursive");
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_ORG_UNIT, $_GET["ref_id"], $_GET['obj_id']);
                $this->ctrl->forwardCommand($new_gui);
                break;
            case 'ilorgunitexportgui':
                if (!ilObjOrgUnitAccess::_checkAccessExport((int) $_GET['ref_id'])) {
                    ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
                    $this->ctrl->redirect($this);
                }
                $this->tabs_gui->activateTab(self::TAB_EXPORT);;
                $ilOrgUnitExportGUI = new ilOrgUnitExportGUI($this);
                $ilOrgUnitExportGUI->addFormat('xml');
                $this->ctrl->forwardCommand($ilOrgUnitExportGUI);
                break;
            case strtolower(ilTranslationGUI::class):
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $this->setSubTabsSettings('edit_translations');
                $ilTranslationGui = new ilTranslationGUI($this);
                $this->ctrl->forwardCommand($ilTranslationGui);
                break;
            case strtolower(ilOrgUnitTypeGUI::class):
                $this->tabs_gui->activateTab(self::TAB_ORGU_TYPES);
                $types_gui = new ilOrgUnitTypeGUI($this);
                $this->ctrl->forwardCommand($types_gui);
                break;
            case strtolower(ilOrgUnitPositionGUI::class):
                $this->tabs_gui->activateTab(self::TAB_POSITIONS);
                $types_gui = new ilOrgUnitPositionGUI($this);
                $this->ctrl->forwardCommand($types_gui);
                break;
            case strtolower(ilOrgUnitUserAssignmentGUI::class):
                $this->tabs_gui->activateTab(self::TAB_STAFF);
                $ilOrgUnitUserAssignmentGUI = new ilOrgUnitUserAssignmentGUI();
                $this->ctrl->forwardCommand($ilOrgUnitUserAssignmentGUI);
                break;
            default:
                switch ($cmd) {
                    case '':
                    case 'view':
                    case 'render':
                    case 'cancel':
                    case 'cancelDelete':
                        $this->view();
                        break;
                    case 'performPaste':
                    case 'paste':
                        $this->performPaste();
                        break;
                    case 'performPasteIntoMultipleObjects':
                        $this->performPasteIntoMultipleObjectsObject();
                        break;
                    case 'keepObjectsInClipboard':
                        $this->keepObjectsInClipboardObject();
                        break;
                    case 'create':
                        parent::createObject();
                        break;
                    case 'save':
                        parent::saveObject();
                        break;
                    case 'delete':
                        $this->tabs_gui->clearTargets();
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
                        $this->deleteObject();
                        break;
                    case 'confirmedDelete':
                        $this->confirmedDeleteObject();
                        break;
                    case 'cut':
                        $this->tabs_gui->clearTargets();
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
                        parent::cutObject();
                        break;
                    case 'clear':
                        parent::clearObject();
                        break;
                    case 'enableAdministrationPanel':
                        parent::enableAdministrationPanelObject();
                        break;
                    case 'disableAdministrationPanel':
                        parent::disableAdministrationPanelObject();
                        break;
                    case 'getAsynchItemList':
                        parent::getAsynchItemListObject();
                        break;
                    case self::CMD_EDIT_SETTINGS:
                        $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                        $this->setSubTabsSettings('edit_settings');
                        $this->editSettings();
                        break;
                    case 'updateSettings':
                        $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                        $this->setSubTabsSettings('edit_settings');
                        $this->updateSettings();
                        break;
                    case 'editAdvancedSettings':
                        $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                        $this->setSubTabsSettings('edit_advanced_settings');
                        $this->editAdvancedSettings();
                        break;
                    case 'updateAdvancedSettings':
                        $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                        $this->setSubTabsSettings('edit_advanced_settings');
                        $this->updateAdvancedSettings();
                        break;
                    case 'importFile':
                        $this->importFileObject();
                        break;
                    case 'cancelMoveLink':
                        $this->cancelMoveLinkObject();
                        break;
                }
                break;
        }
    }


    /**
     * @inheritDoc
     */
    protected function afterSave(ilObject $a_new_object)
    {
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
        ilUtil::redirect($this->getReturnLocation("save", $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS, "", false, false)));
    }


    public function view()
    {
        if (!$this->rbacsystem->checkAccess("read", $_GET["ref_id"])) {
            if ($this->rbacsystem->checkAccess("visible", $_GET["ref_id"])) {
                ilUtil::sendFailure($this->lng->txt("msg_no_perm_read"));
                $this->ctrl->redirectByClass('ilinfoscreengui', '');
            }

            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->WARNING);
        }

        parent::renderObject();
        $this->tabs_gui->activateTab("view_content");
        $this->tabs_gui->removeSubTab("page_editor");
        $this->tabs_gui->removeSubTab("ordering"); // Mantis 0014728
    }


    /**
     * initCreationForms
     *
     * We override the method of class.ilObjectGUI because we have no copy functionality
     * at the moment
     *
     * @param string $a_new_type
     *
     * @return array
     */
    protected function initCreationForms($a_new_type)
    {
        $forms = array(
            self::CFORM_NEW => $this->initCreateForm($a_new_type),
            self::CFORM_IMPORT => $this->initImportForm($a_new_type),
        );

        return $forms;
    }


    public function showPossibleSubObjects()
    {
        $gui = new ilObjectAddNewItemGUI($this->object->getRefId());
        $gui->setMode(ilObjectDefinition::MODE_ADMINISTRATION);
        $gui->setCreationUrl("ilias.php?ref_id=" . $_GET["ref_id"] . "&admin_mode=settings&cmd=create&baseClass=ilAdministrationGUI");
        $gui->render();
    }


    public function showTree()
    {
        $tree = new ilOrgUnitExplorerGUI("orgu_explorer", "ilObjOrgUnitGUI", "showTree", new ilTree(1));
        $tree->setTypeWhiteList($this->getTreeWhiteList());
        $tree->setNodeOpen(ilObjOrgUnit::getRootOrgRefId());

        if (!$tree->handleCommand()) {
            $this->tpl->setLeftNavContent($tree->getHTML());
        }
        $this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
    }


    protected function getTreeWhiteList()
    {
        $whiteList = array( "orgu" );
        $pls = ilOrgUnitExtension::getActivePluginIdsForTree();

        return array_merge($whiteList, $pls);
    }


    /**
     * called by prepare output
     */
    public function setTitleAndDescription()
    {
        # all possible create permissions
        parent::setTitleAndDescription();
        if ($this->object->getTitle() == "__OrgUnitAdministration") {
            $this->tpl->setTitle($this->lng->txt("objs_orgu"));
            $this->tpl->setDescription($this->lng->txt("objs_orgu"));
        }

        // Check for custom icon of type
        if ($this->ilias->getSetting('custom_icons')) {
            $icons_cache = ilObjOrgUnit::getIconsCache();
            $icon_file = (isset($icons_cache[$this->object->getId()])) ? $icons_cache[$this->object->getId()] : '';
            if ($icon_file) {
                $this->tpl->setTitleIcon($icon_file, $this->lng->txt("obj_" . $this->object->getType()));
            }
        }
    }


    /**
     * @param bool $a_do_not_add_object
     */
    protected function addAdminLocatorItems($a_do_not_add_object = false)
    {
        $path = $this->tree->getPathFull($_GET["ref_id"], ilObjOrgUnit::getRootOrgRefId());
        // add item for each node on path
        foreach ((array) $path as $key => $row) {
            if ($row["title"] == "__OrgUnitAdministration") {
                $row["title"] = $this->lng->txt("objs_orgu");
            }
            $this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $row["child"]);
            $this->ilLocator->addItem($row["title"], $this->ctrl->getLinkTargetByClass("ilobjorgunitgui", "view"), ilFrameTargetInfo::_getFrame("MainContent"), $row["child"]);
            $this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $_GET["ref_id"]);
        }
    }


    /**
     * @param int    $a_ref_id
     * @param string $a_cmd
     */
    protected function redirectToRefId($a_ref_id, $a_cmd = "")
    {
        $obj_type = ilObject::_lookupType($a_ref_id, true);
        if ($obj_type != "orgu") {
            parent::redirectToRefId($a_ref_id, $a_cmd);
        } else {
            $this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $a_ref_id);
            $this->ctrl->redirectByClass("ilObjOrgUnitGUI", $a_cmd);
        }
    }


    /**
     * @param ilTabsGUI $tabs_gui
     */
    public function getTabs()
    {
        $read_access_ref_id = $this->rbacsystem->checkAccess('visible,read', $this->object->getRefId());
        if ($read_access_ref_id) {
            $this->tabs_gui->addTab("view_content", $this->lng->txt("content"), $this->ctrl->getLinkTarget($this, ""));
            $this->tabs_gui->addTab("info_short", "Info", $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
        }

        // Tabs for OrgUnits exclusive root!
        if ($this->object->getRefId() != ilObjOrgUnit::getRootOrgRefId()) {
            if (ilObjOrgUnitAccess::_checkAccessStaff($this->object->getRefId())) {
                // $this->tabs_gui->addTab('legacy_staff', 'legacy_staff', $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "showStaff"));
                $this->tabs_gui->addTab(self::TAB_STAFF, $this->lng->txt(self::TAB_STAFF), $this->ctrl->getLinkTargetByClass(ilOrgUnitUserAssignmentGUI::class, ilOrgUnitUserAssignmentGUI::CMD_INDEX));
            }
            if ($read_access_ref_id) {
                $this->tabs_gui->addTab(self::TAB_SETTINGS, $this->lng->txt(self::TAB_SETTINGS), $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS));
                $this->tabs_gui->addTab("administrate_users", $this->lng->txt("administrate_users"), $this->ctrl->getLinkTargetByClass("ilLocalUserGUI", "index"));
            }
        }

        if ($read_access_ref_id) {
            if ($this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
                $this->tabs_gui->addTab(self::TAB_GLOBAL_SETTINGS, $this->lng->txt('settings'), $this->ctrl->getLinkTargetByClass(ilOrgUnitGlobalSettingsGUI::class));
            }
            $this->tabs_gui->addTab(self::TAB_EXPORT, $this->lng->txt(self::TAB_EXPORT), $this->ctrl->getLinkTargetByClass(ilOrgUnitExportGUI::class));

            // Add OrgUnit types and positions tabs
            if ($this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
                $this->tabs_gui->addTab(self::TAB_ORGU_TYPES, $this->lng->txt(self::TAB_ORGU_TYPES), $this->ctrl->getLinkTargetByClass(ilOrgUnitTypeGUI::class));
                $this->tabs_gui->addTab(self::TAB_POSITIONS, $this->lng->txt(self::TAB_POSITIONS), $this->ctrl->getLinkTargetByClass(ilOrgUnitPositionGUI::class));
            }
        }
        parent::getTabs();
    }


    /**
     * @param $active_tab_id
     */
    protected function setSubTabsSettings($active_tab_id)
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->tabs_gui->addSubTab('edit_settings', $this->lng->txt(self::TAB_SETTINGS), $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS));
        $this->tabs_gui->addSubTab("edit_translations", $this->lng->txt("obj_multilinguality"), $this->ctrl->getLinkTargetByClass("iltranslationgui", "editTranslations"));

        $ilOrgUnitType = $this->object->getOrgUnitType();
        if ($ilOrgUnitType instanceof ilOrgUnitType) {
            if (count($ilOrgUnitType->getAssignedAdvancedMDRecords(true))) {
                $this->tabs_gui->addSubTab('edit_advanced_settings', $this->lng->txt('orgu_adv_settings'), $this->ctrl->getLinkTarget($this, 'editAdvancedSettings'));
            }
        }

        $this->tabs_gui->setSubTabActive($active_tab_id);
        switch ($next_class) {
            case 'iltranslationgui':
                $this->tabs_gui->setSubTabActive("edit_translations");
                break;
            case '':
                switch ($cmd) {
                    case self::CMD_EDIT_SETTINGS:
                        $this->tabs_gui->setSubTabActive('edit_settings');
                        break;
                    case 'editAdvancedSettings':
                    case 'updateAdvancedSettings':
                        $this->tabs_gui->setSubTabActive('edit_advanced_settings');
                        break;
                }
                break;
        }

        return;
    }


    /**
     * Set content sub tabs
     */
    public function setContentSubTabs()
    {
        $this->addStandardContainerSubTabs();
        //only display the import tab at the first level
        if ($this->rbacsystem->checkAccess("visible, read", $_GET["ref_id"]) and $this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
            $this->tabs_gui->addSubTab("import", $this->lng->txt("import"), $this->ctrl->getLinkTargetByClass("ilOrgUnitSimpleImportGUI", "chooseImport"));
        }
    }


    /**
     * Initialize the form for editing advanced meta data
     *
     * @return ilPropertyFormGUI
     */
    protected function initAdvancedSettingsForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton('updateAdvancedSettings', $this->lng->txt('save'));
        $form->addCommandButton(self::CMD_EDIT_SETTINGS, $this->lng->txt('cancel'));

        return $form;
    }


    /**
     * Edit Advanced Metadata
     */
    protected function editAdvancedSettings()
    {
        if (!$this->ilAccess->checkAccess("write", "", $this->ref_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }
        $form = $this->initAdvancedSettingsForm();
        $gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
        $gui->setPropertyForm($form);
        $gui->parse();
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Update Advanced Metadata
     */
    protected function updateAdvancedSettings()
    {
        if (!$this->ilAccess->checkAccess("write", "", $this->ref_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }
        $form = $this->initAdvancedSettingsForm();
        $gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
        $gui->setPropertyForm($form);
        $form->checkInput();
        $gui->parse();
        if ($gui->importEditFormPostValues()) {
            $gui->writeEditForm();
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'editAdvancedSettings');
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }


    public function editSettings()
    {
        if (!$this->ilAccess->checkAccess("write", "", $this->ref_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }
        $form = new ilObjOrgUnitSettingsFormGUI($this, $this->object);
        $this->tpl->setContent($form->getHTML());
    }


    public function updateSettings()
    {
        if (!$this->ilAccess->checkAccess("write", "", $this->ref_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }
        $form = new ilObjOrgUnitSettingsFormGUI($this, $this->object);
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }


    /**
     * @param $tpl
     */
    public function showAdministrationPanel()
    {
        parent::showAdministrationPanel();
        global $DIC;
        //an ugly encapsulation violation in order to remove the "verknüpfen"/"link" and copy button.
        /** @var $toolbar ilToolbarGUI */
        if (!$toolbar = $DIC->ui()->mainTemplate()->admin_panel_commands_toolbar) {
            return;
        }
        if (is_array($toolbar->items)) {
            foreach ($toolbar->items as $key => $item) {
                if ($item["cmd"] == "link" || $item["cmd"] == "copy"
                    || $item["cmd"] == "download") {
                    unset($toolbar->items[$key]);
                }
            }
        }
    }


    public static function _goto($ref_id)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilCtrl->initBaseClass("ilAdministrationGUI");
        $ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $ref_id);
        $ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "admin_mode", self::TAB_SETTINGS);
        $ilCtrl->setParameterByClass("IlObjPluginDispatchGUI", "admin_mode", self::TAB_SETTINGS);
        $ilCtrl->redirectByClass(array( "ilAdministrationGUI", "ilObjOrgUnitGUI" ), "view");
    }


    protected function getTreeSelectorGUI($cmd)
    {
        global $DIC;
        $tree = $DIC['tree'];
        $explorer = new ilOrgUnitExplorerGUI("rep_exp_sel", $this, "showPasteTree", $tree);
        $explorer->setAjax(false);
        $explorer->setSelectMode('nodes[]', false);

        return $explorer;
    }


    /**
     * @param ilTabsGUI $tabs_gui
     */
    public function getAdminTabs()
    {
        $this->getTabs();
    }


    /**
     * @description Prepare $_POST for the generic method performPasteIntoMultipleObjectsObject
     */
    public function performPaste()
    {
        if (!in_array($_SESSION['clipboard']['cmd'], array( 'cut' ))) {
            $message = __METHOD__ . ": cmd was not 'cut' ; may be a hack attempt!";
            $this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
        }
        if ($_SESSION['clipboard']['cmd'] == 'cut') {
            if (isset($_GET['ref_id']) && (int) $_GET['ref_id']) {
                $this->pasteObject();
            }
        }
        $this->ctrl->returnToParent($this);
    }


    /**
     * ??
     */
    public function doUserAutoCompleteObject()
    {
    }

    //
    // METHODS for local user administration.
    //
    /**
     * @return ilTableGUI
     * @description Make protected function avaiable for ilLocalUserGUI...
     */
    public function &__initTableGUI()
    {
        return parent::__initTableGUI();
    }


    /**
     * confirmed deletion of org units -> org units are deleted immediately, without putting them to the trash
     */
    public function confirmedDeleteObject()
    {
        if (count($_POST['id']) > 0) {
            foreach ($_POST['id'] as $ref_id) {
                $il_obj_orgunit = new ilObjOrgUnit($ref_id);
                $il_obj_orgunit->delete();
            }
        }
        $this->ctrl->returnToParent($this);
    }


    /**
     * Display deletion confirmation screen for Org Units.
     * Information to the user that Org units will be deleted immediately.
     *
     * @access    public
     */
    public function deleteObject($a_error = false)
    {
        $ilCtrl = $this->ctrl;
        require_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
        $ru = new ilRepUtilGUI($this);

        $arr_ref_ids = [];
        //Delete via Manage (more than one)
        if (count($_POST['id']) > 0) {
            $arr_ref_ids = $_POST['id'];
        } elseif ($_GET['item_ref_id'] > 0) {
            $arr_ref_ids = [$_GET['item_ref_id']];
        }

        if (!$ru->showDeleteConfirmation($arr_ref_ids, false)) {
            $ilCtrl->returnToParent($this);
        }
    }


    /**
     * @return ilTableGUI
     * @description Make protected function avaiable for ilLocalUserGUI...
     */
    public function __setTableGUIBasicData(&$tbl, &$result_set, $a_from = "")
    {
        return parent::__setTableGUIBasicData($tbl, $result_set, $a_from);
    }


    public function cancelMoveLinkObject()
    {
        global $DIC;
        $parent_ref_id = $_SESSION["clipboard"]["parent"];
        unset($_SESSION['clipboard']);
        $DIC->ctrl()->setParameter($this, 'ref_id', $parent_ref_id);
        $DIC->ctrl()->redirect($this);
    }
}
