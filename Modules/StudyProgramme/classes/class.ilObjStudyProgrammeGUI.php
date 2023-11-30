<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Container/classes/class.ilContainerGUI.php");
require_once("./Services/AccessControl/classes/class.ilObjRole.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/AccessControl/classes/class.ilPermissionGUI.php");
require_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
require_once("./Services/Object/classes/class.ilObjectAddNewItemGUI.php");
require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeTreeGUI.php");
require_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
require_once("./Modules/StudyProgramme/classes/types/class.ilStudyProgrammeTypeGUI.php");
require_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php");
require_once("./Services/Object/classes/class.ilObjectCopyGUI.php");
require_once("./Services/Repository/classes/class.ilRepUtil.php");

/**
 * Class ilObjStudyProgrammeGUI class
 *
 * @author				Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilColumnGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjStudyProgrammeSettingsGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjStudyProgrammeTreeGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjStudyProgrammeMembersGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjStudyProgrammeAutoMembershipsGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjectTranslationGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilCertificateGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjStudyProgrammeAutoCategoriesGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilPropertyFormGUI
 */

class ilObjStudyProgrammeGUI extends ilContainerGUI
{
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
     * @var ilObjStudyProgramme
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

    /**
     * @var ilHelp
     */
    protected $help;

    /**
     * @var ilObjStudyProgrammeSettingsGUI
     */
    protected $settings_gui;

    /**
     * @var ilObjStudyProgrammeMembersGUI
     */
    protected $members_gui;

    /**
     * @var ilObjStudyProgrammeAutoMembershipsGUI
     */
    protected $memberships_gui;

    /**
     * @var ilObjStudyProgrammeTreeGUI
     */
    protected $tree_gui;

    /**
     * @var ilStudyProgrammeTypeGUI
     */
    protected $type_gui;

    /**
     * @var ilStudyProgrammeTypeRepository
     */
    protected $type_repository;

    /**
     * @var ilObjStudyProgrammeAutoCategoriesGUI
     */
    protected $autocategories_gui;

    /**
     * @var ilPRGPermissionsHelper
     */
    protected $permissions;

    public function __construct()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilLocator = $DIC['ilLocator'];
        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilLog = $DIC['ilLog'];
        $ilias = $DIC['ilias'];
        $ilHelp = $DIC['ilHelp'];
        $ilUser = $DIC['ilUser'];

        parent::__construct(array(), (int) $_GET['ref_id'], true, false);

        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->ilLocator = $ilLocator;
        $this->tree = $tree;
        $this->toolbar = $ilToolbar;
        $this->ilLog = $ilLog;
        $this->ilias = $ilias;
        $this->type = "prg";
        $this->help = $ilHelp;
        $this->user = $ilUser;

        $lng->loadLanguageModule("prg");

        $this->settings_gui = ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeSettingsGUI'];
        $this->members_gui = ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeMembersGUI'];
        $this->memberships_gui = ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeAutoMembershipsGUI'];
        $this->tree_gui = ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeTreeGUI'];
        $this->type_gui = ilStudyProgrammeDIC::dic()['ilStudyProgrammeTypeGUI'];
        $this->autocategories_gui = ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeAutoCategoriesGUI'];
        $this->type_repository = ilStudyProgrammeDIC::dic()['model.Type.ilStudyProgrammeTypeRepository'];
    }


    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        if ($cmd == "") {
            $cmd = "view";
        }

        $this->addToNavigationHistory();

        parent::prepareOutput();

        $this->addHeaderAction();

        switch ($next_class) {
            case "ilinfoscreengui":
                $this->tabs_gui->activateTab(self::TAB_INFO);
                $this->denyAccessIfNotAnyOf([
                    ilPRGPermissionsHelper::ROLEPERM_VIEW,
                    ilPRGPermissionsHelper::ROLEPERM_READ
                ]);
                $info = new ilInfoScreenGUI($this);
                $this->fillInfoScreen($info);
                $this->ctrl->forwardCommand($info);

                // I guess this is how it was supposed to work, but it doesn't... it won't respect our sub-id and sub-type when creating the objects!
                // So we reimplemented the stuff in the method parseInfoScreen()
                //                $info = new ilInfoScreenGUI($this);
                //                $amd_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
                //                $amd_gui->setInfoObject($info);
                //                $amd_gui->setSelectedOnly(true);
                //                $amd_gui->parse();
                //                $this->ctrl->forwardCommand($info);
                break;
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                $ilPermissionGUI = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($ilPermissionGUI);
                break;
            case "ilcommonactiondispatchergui":
                require_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilobjstudyprogrammesettingsgui":
                $this->denyAccessIfNot(ilPRGPermissionsHelper::ROLEPERM_WRITE);
                $this->getSubTabs('settings');
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $this->tabs_gui->activateSubTab('settings');
                $this->settings_gui->setRefId($this->ref_id);
                $this->ctrl->forwardCommand($this->settings_gui);
                break;

            case "ilobjstudyprogrammeautocategoriesgui":
                $this->denyAccessIfNot(ilPRGPermissionsHelper::ROLEPERM_WRITE);
                $this->getSubTabs('settings');
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $this->tabs_gui->activateSubTab('auto_content');
                $this->autocategories_gui->setRefId($this->ref_id);
                $this->initTreeJS();
                $this->ctrl->forwardCommand($this->autocategories_gui);
                break;

            case "ilobjstudyprogrammemembersgui":
                $this->denyAccessIfNot(ilOrgUnitOperation::OP_VIEW_MEMBERS);
                $this->getSubTabs('members');

                $this->tabs_gui->activateTab(self::TAB_MEMBERS);
                $this->tabs_gui->activateSubTab('edit_participants');

                $this->members_gui->setParentGUI($this);
                $this->members_gui->setRefId($this->ref_id);
                $this->ctrl->forwardCommand($this->members_gui);

                break;

            case "ilobjstudyprogrammeautomembershipsgui":
                $this->denyAccessIfNot(ilOrgUnitOperation::OP_MANAGE_MEMBERS);
                $this->getSubTabs('members');

                $this->tabs_gui->activateTab(self::TAB_MEMBERS);
                $this->tabs_gui->activateSubTab('auto_memberships');

                $this->memberships_gui->setParentGUI($this);
                $this->memberships_gui->setRefId($this->ref_id);
                $this->ctrl->forwardCommand($this->memberships_gui);

                break;


            case "ilobjstudyprogrammetreegui":
                $this->denyAccessIfNot(ilPRGPermissionsHelper::ROLEPERM_WRITE);

                $this->getSubTabs($cmd);
                $this->setContentSubTabs();
                $this->tabs_gui->activateTab(self::TAB_VIEW_CONTENT);
                $this->tabs_gui->activateSubTab(self::SUBTAB_VIEW_TREE);

                // disable admin panel
                $_SESSION["il_cont_admin_panel"] = false;

                $this->tree_gui->setRefId($this->id);
                $this->ctrl->forwardCommand($this->tree_gui);
                break;
            case 'ilstudyprogrammetypegui':
                $this->tabs_gui->activateTab('subtypes');

                $this->type_gui->setParentGUI($this);
                $this->ctrl->forwardCommand($this->type_gui);
                break;
            case 'ilobjectcopygui':
                $gui = new ilobjectcopygui($this);
                $this->ctrl->forwardCommand($gui);
                break;
            case 'ilobjecttranslationgui':
                $this->denyAccessIfNot(ilPRGPermissionsHelper::ROLEPERM_WRITE);
                $this->getSubTabs('settings');
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $this->tabs_gui->activateSubTab('settings_trans');
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;
            case "ilcertificategui":
                $this->getSubTabs('settings');
                $this->denyAccessIfNot(ilPRGPermissionsHelper::ROLEPERM_WRITE);
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $this->tabs_gui->activateSubTab('certificate');
                $guiFactory = new ilCertificateGUIFactory();
                $output_gui = $guiFactory->create($this->object);
                $this->ctrl->forwardCommand($output_gui);
                break;
            case strtolower(ilPropertyFormGUI::class):
                /*
                 * Only used for async loading of the repository tree in custom md
                 * internal links (see #28060, #37974). This is necessary since StudyProgrammes don't
                 * use ilObjectMetaDataGUI.
                 */
                $form = $this->initAdvancedSettingsForm();
                $gui = new ilAdvancedMDRecordGUI(
                    ilAdvancedMDRecordGUI::MODE_EDITOR,
                    'prg',
                    $this->object->getId(),
                    'prg_type',
                    $this->object->getSettings()->getTypeSettings()->getTypeId()
                );
                $gui->setPropertyForm($form);
                $gui->parse();
                $this->ctrl->forwardCommand($form);
                break;
            case false:
                $this->getSubTabs($cmd);
                switch ($cmd) {
                    case "cancelDelete":
                        $cmd = "view";
                        // no break
                    case "create":
                    case "save":
                    case "view":
                    case "cancel":
                    case "edit":
                        $this->$cmd();
                        break;
                    case "delete":
                        $this->tabs_gui->clearTargets();
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
                        parent::deleteObject();
                        break;
                    case 'confirmedDelete':
                        parent::confirmedDeleteObject();
                        break;
                    case 'editAdvancedSettings':
                        $this->tabs_gui->activateTab('edit_advanced_settings');
                        $this->editAdvancedSettings();
                        break;
                    case 'updateAdvancedSettings':
                        $this->tabs_gui->activateTab('edit_advanced_settings');
                        $this->updateAdvancedSettings();
                        break;
                    case "infoScreen":
                        $this->ctrl->redirectByClass("ilInfoScreenGUI", "showSummary");
                        break;
                    case 'getAsynchItemList':
                        parent::getAsynchItemListObject();
                        break;
                    case 'trash':
                    case 'undelete':
                    case 'confirmRemoveFromSystem':
                    case 'removeFromSystem':
                    case 'deliverCertificate':
                    case 'addToDesk':
                    case 'removeFromDesk':
                        $cmd .= "Object";
                        $this->$cmd();
                        break;
                    default:
                        throw new ilException("ilObjStudyProgrammeGUI: Command not supported: $cmd");
                }
                break;

            default:
                throw new ilException("ilObjStudyProgrammeGUI: Can't forward to next class $next_class");
        }
    }


    protected function create()
    {
        parent::createObject();
    }

    protected function save()
    {
        parent::saveObject();

        if ($this->ctrl->isAsynch()) {
            $form = $this->getAsyncCreationForm();
            $form->setValuesByPost();
            echo ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" => $this->ctrl->getCmd(), "success" => false, "errors" => $form->getErrors()));
            exit();
        }
    }

    protected function cancel()
    {
        $async_response = ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" => "cancel", "success" => false));

        ilAsyncOutputHandler::handleAsyncOutput("", $async_response, false);

        parent::cancelCreation();
    }


    /**
     * Sets the sorting of the container correctly. If its a async call, a json string is returned.
     */
    protected function afterSave(ilObject $a_new_object)
    {
        // set default sort to manual
        $settings = new ilContainerSortingSettings($a_new_object->getId());
        $settings->setSortMode(ilContainer::SORT_MANUAL);
        $settings->setSortDirection(ilContainer::SORT_DIRECTION_ASC);
        $settings->setSortNewItemsOrder(ilContainer::SORT_NEW_ITEMS_ORDER_CREATION);
        $settings->setSortNewItemsPosition(ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM);
        $settings->save();

        $async_response = ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" => "cancel", "success" => true, "message" => $this->lng->txt("object_added")));

        ilAsyncOutputHandler::handleAsyncOutput("", $async_response, false);

        ilUtil::sendSuccess($this->lng->txt("object_added"), true);

        $this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
        ilUtil::redirect($this->getReturnLocation(
            "save",
            $this->ctrl->getLinkTarget($this, "edit", "", false, false)
        ));
    }

    protected function getPermissionsHelper() : ilPRGPermissionsHelper
    {
        if (!$this->permisssions) {
            if (!$this->object || !$this->object->getRefId()) {
                throw new LogicException('Cannot ask for permission when not in tree!');
            }

            $this->permissions = ilStudyProgrammeDIC::specificDicFor($this->object)['permissionhelper'];
        }
        return $this->permissions;
    }

    protected function view()
    {
        $this->denyAccessIfNot(ilPRGPermissionsHelper::ROLEPERM_READ);
        $this->tabs_gui->activateTab(self::TAB_VIEW_CONTENT);
        parent::renderObject();
    }

    public function isActiveAdministrationPanel() : bool
    {
        return false;
    }

    protected function initAdvancedSettingsForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton('updateAdvancedSettings', $this->lng->txt('save'));
        $form->addCommandButton('editAdvancedSettings', $this->lng->txt('cancel'));

        return $form;
    }

    protected function editAdvancedSettings() : void
    {
        if (!$this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_WRITE)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }
        $form = $this->initAdvancedSettingsForm();
        $gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            'prg',
            $this->object->getId(),
            'prg_type',
            $this->object->getSettings()->getTypeSettings()->getTypeId()
        );
        $gui->setPropertyForm($form);
        $gui->parse();
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateAdvancedSettings() : void
    {
        if (!$this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_WRITE)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }

        $form = $this->initAdvancedSettingsForm();
        $gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            'prg',
            $this->object->getId(),
            'prg_type',
            $this->object->getSettings()->getTypeSettings()->getTypeId()
        );
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


    /**
     * Overwritten from ilObjectGUI since copy and import are not implemented.
     */
    protected function initCreationForms($a_new_type) : array
    {
        return array( self::CFORM_NEW => $this->initCreateForm($a_new_type));
    }


    /**
     * Method for implementing async windows-output
     * Should be moved into core to enable async requests on creation-form
     */
    public function getAsyncCreationForm() : ilAsyncPropertyFormGUI
    {
        $asyncForm = new ilAsyncPropertyFormGUI();

        $tmp_forms = $this->initCreationForms('prg');
        $asyncForm->cloneForm($tmp_forms[self::CFORM_NEW]);
        $asyncForm->setAsync(true);

        return $asyncForm;
    }

    ////////////////////////////////////
    // HELPERS
    ////////////////////////////////////

    protected function checkAccess(string $permission) : bool
    {
        return $this->getPermissionsHelper()->may($permission);
    }

    protected function denyAccessIfNot(string $permission) : void
    {
        $this->denyAccessIfNotAnyOf([$permission]);
    }

    protected function denyAccessIfNotAnyOf(array $permissions) : void
    {
        if (!$this->getPermissionsHelper()->mayAnyOf($permissions)) {
            if ($this->getPermissionsHelper()->may(ilPRGPermissionsHelper::ROLEPERM_VIEW)) {
                ilUtil::sendFailure($this->lng->txt("msg_no_perm_write"));
                $this->ctrl->redirectByClass('ilinfoscreengui', '');
            } else {
                $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->WARNING);
            }
        }
    }

    const TAB_VIEW_CONTENT = "view_content";
    const SUBTAB_VIEW_TREE = "view_tree";
    const TAB_INFO = "info_short";
    const TAB_SETTINGS = "settings";
    const TAB_MEMBERS = "members";
    const TAB_METADATA = "edit_advanced_settings";
    const TAB_SUBTYPES = "subtypes";

    public function getTabs()
    {
        $this->help->setScreenIdComponent("prg");
        if ($this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_READ)) {
            $this->tabs_gui->addTab(self::TAB_VIEW_CONTENT, $this->lng->txt("content"), $this->getLinkTarget("view"));
        }

        if ($this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_READ)) {
            $this->tabs_gui->addTab(
                self::TAB_INFO,
                $this->lng->txt("info_short"),
                $this->getLinkTarget("info_short")
            );
        }

        if ($this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_WRITE)) {
            $this->tabs_gui->addTab(
                self::TAB_SETTINGS,
                $this->lng->txt("settings"),
                $this->getLinkTarget("settings")
            );
        }

        if ($this->checkAccess(ilOrgUnitOperation::OP_VIEW_MEMBERS)) {
            $this->tabs_gui->addTab(
                self::TAB_MEMBERS,
                $this->lng->txt("assignments"),
                $this->getLinkTarget("members")
            );
        }

        if ($this->object->hasAdvancedMetadata()
            && $this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_WRITE)
        ) {
            $this->tabs_gui->addTab(
                self::TAB_METADATA,
                $this->lng->txt('meta_data'),
                $this->ctrl->getLinkTarget($this, 'editAdvancedSettings')
            );
        }

        parent::getTabs();
    }

    /**
     * Adds subtabs based on the parent tab
     *
     * @param $a_parent_tab | string of the parent tab-id
     */
    public function getSubTabs($a_parent_tab)
    {
        switch ($a_parent_tab) {
            case self::TAB_VIEW_CONTENT:
            case self::SUBTAB_VIEW_TREE:
            case 'view':
                if ($this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_READ)) {
                    $this->tabs_gui->addSubTab(self::TAB_VIEW_CONTENT, $this->lng->txt("view"), $this->getLinkTarget("view"));
                }

                if ($this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_WRITE)) {
                    $this->tabs_gui->addSubTab(self::SUBTAB_VIEW_TREE, $this->lng->txt("cntr_manage"), $this->getLinkTarget(self::SUBTAB_VIEW_TREE));
                }
                break;
            case 'settings':
                $this->tabs_gui->addSubTab('settings', $this->lng->txt('settings'), $this->getLinkTarget('settings'));

                if ($this->object->isAutoContentApplicable()) {
                    $this->tabs_gui->addSubTab("auto_content", $this->lng->txt("content_automation"), $this->getLinkTarget("auto_content"));
                }

                $this->tabs_gui->addSubTab("settings_trans", $this->lng->txt("obj_multilinguality"), $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", ""));
                //$this->tabs_gui->addSubTab("edit_translations", $this->lng->txt("obj_multilinguality"), $this->ctrl->getLinkTargetByClass("iltranslationgui", "editTranslations"));

                $validator = new ilCertificateActiveValidator();
                if (true === $validator->validate()) {
                    $this->tabs_gui->addSubTabTarget(
                        "certificate",
                        $this->ctrl->getLinkTargetByClass("ilcertificategui", "certificateeditor"),
                        "",
                        "ilcertificategui"
                    );
                }

                $this->tabs_gui->addSubTab(
                    'commonSettings',
                    $this->lng->txt("obj_features"),
                    $this->getLinkTarget("commonSettings")
                );
                break;

            case 'members':
                $this->tabs_gui->addSubTab('edit_participants', $this->lng->txt('edit_participants'), $this->getLinkTarget('members'));
                if ($this->getPermissionsHelper()->may(ilOrgUnitOperation::OP_MANAGE_MEMBERS)) {
                    $this->tabs_gui->addSubTab('auto_memberships', $this->lng->txt('auto_memberships'), $this->getLinkTarget('memberships'));
                }
                break;
        }
    }


    /**
     * Disable default content subtabs
     */
    public function setContentSubTabs()
    {
        return;
    }


    /**
     * Generates a link based on a cmd
     *
     * @param $a_cmd
     *
     * @return string
     */
    protected function getLinkTarget($a_cmd)
    {
        if ($a_cmd == "info_short") {
            return $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary");
        }
        if ($a_cmd == "settings") {
            return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammesettingsgui", "view");
        }
        if ($a_cmd == "auto_content") {
            return $this->ctrl->getLinkTargetByClass("ilObjStudyProgrammeAutoCategoriesGUI", "view");
        }

        if ($a_cmd == self::SUBTAB_VIEW_TREE) {
            return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammetreegui", "view");
        }
        if ($a_cmd == "members") {
            return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammemembersgui", "view");
        }
        if ($a_cmd == "memberships") {
            return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammeautomembershipsgui", "view");
        }
        if ($a_cmd == "subtypes") {
            return $this->ctrl->getLinkTargetByClass("ilstudyprogrammetypegui", "listTypes");
        }
        if ($a_cmd == "commonSettings") {
            return $this->ctrl->getLinkTargetByClass(
                [
                    "ilobjstudyprogrammesettingsgui",
                    "ilStudyProgrammeCommonSettingsGUI"
                ],
                "editSettings"
            );
        }

        return $this->ctrl->getLinkTarget($this, $a_cmd);
    }


    /**
     * Adding meta-data to the info-screen
     *
     * @param $a_info_screen
     */
    protected function fillInfoScreen($a_info_screen)
    {
        if (!$this->object->getSettings()->getTypeSettings()->getTypeId() ||
            !ilStudyProgrammeDIC::dic()['model.Type.ilStudyProgrammeTypeRepository']
                ->getType($this->object->getSettings()->getTypeSettings()->getTypeId())
        ) {
            return;
        }
        $record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_INFO,
            'prg',
            $this->object->getId(),
            'prg_type',
            $this->object->getSettings()->getTypeSettings()->getTypeId()
        );
        $record_gui->setInfoObject($a_info_screen);
        $record_gui->parse();
    }

    protected function edit()
    {
        $this->denyAccessIfNot(ilPRGPermissionsHelper::ROLEPERM_WRITE);

        $link = $this->ctrl->getLinkTargetByClass(ilObjStudyProgrammeSettingsGUI::class, 'view');
        $this->ctrl->redirectToURL($link);
    }

    /**
     * _goto
     * Deep link
     *
     * @param string $a_target
     */
    public static function _goto($a_target)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilCtrl = $DIC['ilCtrl'];
        $id = explode("_", $a_target);
        $ilCtrl->initBaseClass("ilRepositoryGUI");
        $ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $id[0]);

        $ilCtrl->redirectByClass(array( "ilRepositoryGUI", "ilobjstudyprogrammegui" ), "view");
    }

    public function addToNavigationHistory()
    {
        global $DIC;
        $ilNavigationHistory = $DIC['ilNavigationHistory'];

        if (!$this->getCreationMode()
            && $this->checkAccess(ilPRGPermissionsHelper::ROLEPERM_READ)
        ) {
            $link = ilLink::_getLink($_GET["ref_id"], "iass");
            $ilNavigationHistory->addItem(
                $_GET['ref_id'],
                $link,
                'prg'
            );
        }
    }

    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
    {
        $lg = parent::initHeaderAction($a_sub_type, $a_sub_id);
        $validator = new ilCertificateDownloadValidator();
        if (true === $validator->isCertificateDownloadable($this->user->getId(), $this->object->getId()) && $lg) {
            $cert_url = $this->ctrl->getLinkTarget($this, "deliverCertificate");
            $this->lng->loadLanguageModule("certificate");
            $lg->addCustomCommand($cert_url, "download_certificate");
            $lg->addHeaderIcon(
                "cert_icon",
                ilUtil::getImagePath("icon_cert.svg"),
                $this->lng->txt("download_certificate"),
                null,
                null,
                $cert_url
            );
        }
        return $lg;
    }


    protected function deliverCertificateObject()
    {
        global $DIC;

        $this->lng->loadLanguageModule('cert');

        $user_id = (int) $this->user->getId();
        $obj_id = (int) $this->object->getId();

        $validator = new ilCertificateDownloadValidator();
        if (false === $validator->isCertificateDownloadable($user_id, $obj_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }
        $repository = new ilUserCertificateRepository();
        $cert_logger = $DIC->logger()->cert();
        $pdf_action = new ilCertificatePdfAction(
            $cert_logger,
            new ilPdfGenerator($repository, $cert_logger),
            new ilCertificateUtilHelper(),
            $this->lng->txt('error_creating_certificate_pdf')
        );
        $pdf_action->downloadPdf($user_id, $obj_id);
    }

    protected function initTreeJS() : void
    {
        ilExplorerBaseGUI::init();
    }

    protected function supportsPageEditor() : bool
    {
        return false;
    }
}
