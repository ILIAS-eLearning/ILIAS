<?php

declare(strict_types=1);

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

use ILIAS\Folder\StandardGUIRequest;

/**
 * Class ilObjFolderGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjFolderGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjFolderGUI: ilCourseContentGUI, ilLearningProgressGUI
 * @ilCtrl_Calls ilObjFolderGUI: ilInfoScreenGUI, ilContainerPageGUI, ilColumnGUI
 * @ilCtrl_Calls ilObjFolderGUI: ilObjectCopyGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjFolderGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilDidacticTemplateGUI
 * @ilCtrl_Calls ilObjFolderGUI: ilBackgroundTaskHub, ilObjectTranslationGUI, ilRepositoryTrashGUI
 */
class ilObjFolderGUI extends ilContainerGUI
{
    protected ilHelpGUI $help;
    public ilTree $folder_tree;
    protected StandardGUIRequest $folder_request;

    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = false
    ) {
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
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule("obj");
        $this->folder_request = $DIC
            ->folder()
            ->internal()
            ->gui()
            ->standardRequest();
    }


    public function viewObject(): void
    {
        $this->checkPermission('read');
        if (strtolower($this->folder_request->getBaseClass()) === "iladministrationgui") {
            parent::viewObject();
            return;
        }

        // Trac access - see ilObjCourseGUI
        ilLearningProgress::_tracProgress(
            $GLOBALS["ilUser"]->getId(),
            $this->object->getId(),
            $this->object->getRefId(),
            'fold'
        );

        $this->renderObject();
        $this->tabs_gui->setTabActive('view_content');
    }

    public function renderObject(): void
    {
        $ilTabs = $this->tabs;

        $this->checkPermission('read');

        $ilTabs->activateTab("view_content");
        parent::renderObject();
    }

    public function executeCommand(): void
    {
        $ilUser = $this->user;
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $header_action = true;
        switch ($next_class) {
            case strtolower(ilRepositoryTrashGUI::class):
                $ru = new ilRepositoryTrashGUI($this);
                $this->ctrl->setReturn($this, 'trash');
                $this->ctrl->forwardCommand($ru);
                break;

            case 'ilpermissiongui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;


            case 'ilcoursecontentgui':
                $this->prepareOutput();
                $course_content_obj = new ilCourseContentGUI($this);
                $this->ctrl->forwardCommand($course_content_obj);
                break;

            case "illearningprogressgui":
                $this->prepareOutput();

                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $this->folder_request->getUserId() ?: $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
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

            case 'ilinfoscreengui':
                $this->prepareOutput();
                $this->infoScreen();
                break;

            case 'ilobjectcopygui':
                $this->prepareOutput();

                $cp = new ilObjectCopyGUI($this);
                $cp->setType('fold');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->setTitleAndDescription();
                //$this->showContainerPageTabs();
                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case 'ilexportgui':
                $this->prepareOutput();

                $this->tabs_gui->setTabActive('export');
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
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
            case 'ilcolumngui':
                $this->tabs_gui->setTabActive('none');
                $this->checkPermission("read");
                $this->viewObject();
                break;

            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                $this->setSubTabs("settings_trans");
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

        if ($header_action) {
            $this->addHeaderAction();
        }
    }

    public function setFolderTree(ilTree $a_tree): void
    {
        $this->folder_tree = $a_tree;
    }

    protected function importFileObject(?int $parent_id = null, bool $catch_errors = true): void
    {
        $lng = $this->lng;

        parent::importFileObject($parent_id, $catch_errors);

        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $this->ctrl->returnToParent($this);
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $obj_service = $this->getObjectService();

        $lng->loadLanguageModule($this->object->getType());

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
            [
                ilContainer::SORT_INHERIT,
                ilContainer::SORT_TITLE,
                ilContainer::SORT_CREATION,
                ilContainer::SORT_MANUAL
            ]
        );

        $form->addCommandButton("update", $this->lng->txt("save"));
        //$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));

        return $form;
    }

    protected function getEditFormCustomValues(array &$a_values): void
    {
        // we cannot use $this->object->getOrderType()
        // if set to inherit it will be translated to parent setting
        $sort = new ilContainerSortingSettings($this->object->getId());
        $a_values["sor"] = $sort->getSortMode();
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        $obj_service = $this->getObjectService();

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

        $this->saveSortingSettings($form);
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function showSummaryObject(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    protected function afterSave(ilObject $new_object): void
    {
        $sort = new ilContainerSortingSettings($new_object->getId());
        $sort->setSortMode(ilContainer::SORT_INHERIT);
        $sort->update();

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("fold_added"), true);
        $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
        $this->redirectToRefId($new_object->getRefId(), "");
    }

    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    /**
     * @throws ilPermissionException
     */
    public function infoScreen(): void
    {
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess("visible", "", $this->ref_id)) {
            throw new ilPermissionException($this->lng->txt("msg_no_perm_read"));
        }

        $info = new ilInfoScreenGUI($this);

        $GLOBALS['ilTabs']->activateTab('info_short');

        $info->enablePrivateNotes();

        if ($ilAccess->checkAccess("read", "", $this->requested_ref_id)) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($ilAccess->checkAccess("write", "", $this->requested_ref_id)) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", '1');
                $info->setBlockProperty("news", "public_notifications_option", '1');
            }
        }


        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        // forward the command
        $this->ctrl->forwardCommand($info);
    }

    protected function getTabs(): void
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
            $force_active = $this->ctrl->getNextClass() === "ilinfoscreengui"
                || strtolower($this->ctrl->getCmdClass()) === "ilnotegui";
            $this->tabs_gui->addTarget(
                "info_short",
                $this->ctrl->getLinkTargetByClass(
                    ["ilobjfoldergui", "ilinfoscreengui"],
                    "showSummary"
                ),
                ["showSummary", "", "infoScreen"],
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
                ($ilCtrl->getCmd() === "edit")
            );
        }

        // learning progress
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(['ilobjfoldergui', 'illearningprogressgui'], ''),
                '',
                ['illplistofobjectsgui', 'illplistofsettingsgui', 'illearningprogressgui', 'illplistofprogressgui']
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
                $this->ctrl->getLinkTargetByClass([get_class($this), 'ilpermissiongui'], "perm"),
                ["perm", "info", "owner"],
                'ilpermissiongui'
            );
        }
    }

    /**
    * goto target group
    */
    public static function _goto($a_target): void
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        $a_target = (int) $a_target;

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target);
        }
        if ($ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    public function modifyItemGUI(ilObjectListGUI $a_item_list_gui, array $a_item_data): void
    {
        $tree = $this->tree;

        // if folder is in a course, modify item list gui according to course requirements
        if ($course_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs')) {
            $course_obj_id = ilObject::_lookupObjId($course_ref_id);
            ilObjCourseGUI::_modifyItemGUI(
                $a_item_list_gui,
                'ilcoursecontentgui',
                $a_item_data,
                ilObjCourse::_lookupAboStatus($course_obj_id),
                $course_ref_id,
                $course_obj_id,
                $this->object->getRefId()
            );
        }
    }

    /**
     * show possible sub objects selection list
     */
    protected function showPossibleSubObjects(): void
    {
        $gui = new ilObjectAddNewItemGUI($this->object->getRefId());
        $gui->render();
    }


    protected function forwardToTimingsView(): void
    {
        $tree = $this->tree;

        if (!$crs_ref = $tree->checkForParentType($this->ref_id, 'crs')) {
            return;
        }
        if (!$this->ctrl->getCmd() && ilObjCourse::_lookupViewMode(ilObject::_lookupObjId($crs_ref)) === ilContainer::VIEW_TIMING) {
            if (!ilSession::has('crs_timings')) {
                ilSession::set('crs_timings', true);
            }

            if (ilSession::get('crs_timings')) {
                $course_content_obj = new ilCourseContentGUI($this);
                $this->ctrl->setCmdClass(get_class($course_content_obj));
                $this->ctrl->setCmd('editUserTimings');
                $this->ctrl->forwardCommand($course_content_obj);
                return;
            }
        }
        ilSession::set('crs_timings', false);
    }

    public function editObject(): void
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

    public function setSubTabs(string $a_tab): void
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
}
