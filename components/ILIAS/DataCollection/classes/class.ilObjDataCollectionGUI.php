<?php

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

declare(strict_types=1);

/**
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilPermissionGUI, ilObjectCopyGUI, ilDclExportGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclRecordListGUI, ilDclRecordEditGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclDetailedViewGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclTableListGUI, ilObjFileGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilObjUserGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilRatingGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilPropertyFormGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclPropertyFormGUI
 */
class ilObjDataCollectionGUI extends ilObject2GUI
{
    public const GET_REF_ID = "ref_id";
    public const GET_VIEW_ID = "tableview_id";

    public const TAB_EDIT_DCL = 'settings';
    public const TAB_LIST_TABLES = 'dcl_tables';
    public const TAB_EXPORT = 'export';
    public const TAB_LIST_PERMISSIONS = 'perm_settings';
    public const TAB_INFO = 'info_short';
    public const TAB_CONTENT = 'content';
    private \ILIAS\Notes\Service $notes;

    public ?ilObject $object = null;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ILIAS\HTTP\Services $http;
    protected ilTabsGUI $tabs;
    protected int $table_id;

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->http = $DIC->http();
        $this->tabs = $DIC->tabs();
        $this->notes = $DIC->notes();

        $this->lng->loadLanguageModule("dcl");
        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('obj');
        $this->lng->loadLanguageModule('cntr');

        $this->setTableId($this->getRefId());

        if ($this->ctrl->isAsynch() === false) {
            $this->addJavaScript();
        }

        $this->ctrl->saveParameter($this, "table_id");
    }

    private function setTableId(int $objectOrRefId = 0): void
    {
        if ($this->http->wrapper()->query()->has('table_id')) {
            $this->table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        } elseif ($this->http->wrapper()->query()->has('tableview_id')) {
            $this->table_id = ilDclTableView::find(
                $this->http->wrapper()->query()->retrieve('tableview_id', $this->refinery->kindlyTo()->int())
            )->getTableId();
        } elseif ($objectOrRefId > 0) {
            $this->table_id = $this->object->getFirstVisibleTableId();
        }
    }

    public function getObjectId(): int
    {
        return $this->obj_id;
    }

    private function addJavaScript(): void
    {
        $this->notes->gui()->initJavascript();
        ilYuiUtil::initConnection();
        ilOverlayGUI::initJavascript();
        // # see  https://mantis.ilias.de/view.php?id=26463
        $this->tpl->addJavaScript("assets/js/Modal.js");
        $this->tpl->addJavaScript("assets/js/datacollection.js");
    }

    public function getStandardCmd(): string
    {
        return "render";
    }

    public function getType(): string
    {
        return "dcl";
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        global $DIC;

        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilHelp = $DIC['ilHelp'];
        $ilHelp->setScreenIdComponent('bibl');

        // Navigation History
        $link = $this->ctrl->getLinkTarget($this, "render");

        if ($this->getObject() !== null) {
            $ilNavigationHistory->addItem($this->object->getRefId(), $link, "dcl");
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$this->getCreationMode() && $next_class != "ilinfoscreengui" && $cmd != 'infoScreen' && !$this->checkPermissionBool("read")) {
            $DIC->ui()->mainTemplate()->loadStandardTemplate();
            $DIC->ui()->mainTemplate()->setContent("Permission Denied.");

            return;
        }

        switch ($next_class) {
            case strtolower(ilInfoScreenGUI::class):
                $this->prepareOutput();
                $this->tabs->activateTab(self::TAB_INFO);
                $this->infoScreenForward();
                break;

            case strtolower(ilCommonActionDispatcherGUI::class):
                $this->prepareOutput();
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $gui->enableCommentsSettings(false);
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilPermissionGUI::class):
                $this->prepareOutput();
                $this->tabs->activateTab(self::TAB_LIST_PERMISSIONS);
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case strtolower(ilObjectCopyGUI::class):
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("dcl");
                $DIC->ui()->mainTemplate()->loadStandardTemplate();
                $this->ctrl->forwardCommand($cp);
                break;

            case strtolower(ilDclTableListGUI::class):
                $this->prepareOutput();
                $this->tabs->activateTab(self::TAB_LIST_TABLES);
                $tablelist_gui = new ilDclTableListGUI($this);
                $this->ctrl->forwardCommand($tablelist_gui);
                break;

            case strtolower(ilDclRecordListGUI::class):
                $this->addHeaderAction();
                $this->prepareOutput();
                $this->tabs->activateTab(self::TAB_CONTENT);
                $recordlist_gui = new ilDclRecordListGUI($this, $this->table_id, $this->getTableViewId());
                $this->ctrl->forwardCommand($recordlist_gui);
                break;

            case strtolower(ilDclRecordEditGUI::class):
                $this->prepareOutput();
                $this->tabs->activateTab(self::TAB_CONTENT);
                $recordedit_gui = new ilDclRecordEditGUI($this, $this->table_id, $this->getTableViewId());
                $this->ctrl->forwardCommand($recordedit_gui);
                break;

            case strtolower(ilObjFileGUI::class):
                $this->prepareOutput();
                $this->tabs->activateTab(self::TAB_CONTENT);
                $file_gui = new ilObjFile($this->getRefId());
                $this->ctrl->forwardCommand($file_gui);
                break;

            case strtolower(ilRatingGUI::class):
                $rgui = new ilRatingGUI();

                $record_id = $this->http->wrapper()->query()->retrieve('record_id', $this->refinery->kindlyTo()->int());
                $field_id = $this->http->wrapper()->query()->retrieve('field_id', $this->refinery->kindlyTo()->int());

                $rgui->setObject($record_id, "dcl_record", $field_id, "dcl_field");
                $rgui->executeCommand();
                $this->listRecords();
                break;

            case strtolower(ilDclDetailedViewGUI::class):
                $this->prepareOutput();
                $recordview_gui = new ilDclDetailedViewGUI($this, $this->getTableViewId());
                $this->ctrl->forwardCommand($recordview_gui);
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTargetByClass(
                        ilDclRecordListGUI::class,
                        ilDclRecordListGUI::CMD_LIST_RECORDS
                    )
                );
                break;

            case strtolower(ilNoteGUI::class):
                $this->prepareOutput();
                $recordviewGui = new ilDclDetailedViewGUI($this, $this->getTableViewId());
                $this->ctrl->forwardCommand($recordviewGui);
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, ""));
                break;
            case strtolower(ilDclExportGUI::class):
                $this->handleExport();
                break;

            case strtolower(ilDclPropertyFormGUI::class):
                $recordedit_gui = new ilDclRecordEditGUI($this, $this->table_id, $this->getTableViewId());
                $recordedit_gui->getRecord();
                $recordedit_gui->initForm();
                $form = $recordedit_gui->getForm();
                $this->ctrl->forwardCommand($form);
                break;

            default:
                switch ($cmd) {
                    case 'edit': // this is necessary because ilObjectGUI only calls its own editObject (why??)
                        $this->prepareOutput();
                        $this->editObject();
                        break;
                    case 'export':
                        $this->handleExport(true);
                        break;
                    default:
                        parent::executeCommand();
                }
        }
    }

    protected function handleExport(bool $do_default = false)
    {
        $this->prepareOutput();
        $this->tabs->setTabActive(self::TAB_EXPORT);
        $exp_gui = new ilDclExportGUI($this);
        $exporter = new ilDclContentExporter($this->object->getRefId(), $this->table_id);
        $exp_gui->addFormat("xlsx", $this->lng->txt('dlc_xls_async_export'), $exporter, 'exportAsync');
        $exp_gui->addFormat("xml");
        if ($do_default) {
            $exp_gui->listExportFiles();
        } else {
            $this->ctrl->forwardCommand($exp_gui);
        }
    }

    protected function getTableViewId(): int
    {
        $tableview_id = null;
        if ($this->http->wrapper()->query()->has('tableview_id')) {
            $tableview_id = $this->http->wrapper()->query()->retrieve(
                'tableview_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($this->http->wrapper()->post()->has('tableview_id')) {
            $tableview_id = $this->http->wrapper()->post()->retrieve(
                'tableview_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if (!$tableview_id) {
            $table_obj = ilDclCache::getTableCache($this->table_id);
            $tableview_id = $table_obj->getFirstTableViewId($this->getRefId());
        }
        return $tableview_id;
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function infoScreen(): void
    {
        // @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
        // $this->ctrl->setCmd("showSummary");
        // $this->ctrl->setCmdClass(ilInfoScreenGUI::class);
        $this->infoScreenForward();
    }

    public function render(): void
    {
        $this->listRecords();
    }

    /**
     * show information screen
     */
    public function infoScreenForward(): void
    {
        $this->tabs->activateTab(self::TAB_INFO);

        if (!$this->checkPermissionBool('visible')) {
            $this->checkPermission('read');
        }

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        $this->ctrl->forwardCommand($info);
    }

    protected function addLocatorItems(): void
    {
        if (is_object($this->object) === true) {
            $this->locator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this, ""),
                (string)$this->object->getRefId()
            );
        }
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $lng = $DIC->language();

        $ilCtrl = $DIC->ctrl();
        $access = $DIC->access();
        $tpl = $DIC->ui()->mainTemplate();

        $target_parts = explode("_", $a_target);
        if (count($target_parts) === 1) {
            [$ref_id] = $target_parts;
        } elseif (count($target_parts) === 2) {
            [$ref_id, $viewId] = $target_parts;
        } else {
            [$ref_id, $viewId, $recordId] = $target_parts;
        }

        //load record list
        if ($access->checkAccess('read', "", (int)$ref_id)) {
            $ilCtrl->setParameterByClass(ilRepositoryGUI::class, self::GET_REF_ID, $ref_id);
            if (isset($viewId)) {
                $ilCtrl->setParameterByClass(ilRepositoryGUI::class, self::GET_VIEW_ID, $viewId);
            }
            $ilCtrl->redirectByClass(ilRepositoryGUI::class, "listRecords");
        }
        //redirect to info screen
        elseif ($access->checkAccess('visbile', "", (int)$ref_id)) {
            ilObjectGUI::_gotoRepositoryNode((int)$a_target, "infoScreen");
        }
        //redirect if no permission given
        else {
            $message = sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId((int)$a_target))
            );
            $tpl->setOnScreenMessage('failure', $message, true);

            ilObjectGUI::_gotoRepositoryRoot();
        }
    }

    protected function afterSave(ilObject $new_object): void
    {
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $this->ctrl->redirectByClass(ilDclTableListGUI::class, "listTables");
    }

    /**
     * setTabs
     * create tabs (repository/workspace switch)
     * this had to be moved here because of the context-specific permission tab
     */
    protected function setTabs(): void
    {
        $ref_id = $this->object->getRefId();

        // read permission
        if ($this->access->checkAccess('read', "", $ref_id) === true) {
            // list records
            $this->ctrl->setParameterByClass(ilDclRecordListGUI::class, "tableview_id", $this->getTableViewId());
            $link = $this->ctrl->getLinkTargetByClass(ilDclRecordListGUI::class, "show");
            $this->addTab(self::TAB_CONTENT, $link);
        }

        // visible or read permission
        if ($this->access->checkAccess('visible', "", $ref_id) === true
            || $this->access->checkAccess('read', "", $ref_id) === true) {
            // info screen
            $this->addTab(self::TAB_INFO, $this->ctrl->getLinkTargetByClass(
                ilInfoScreenGUI::class,
                "showSummary"
            ));
        }

        // write permission
        if ($this->access->checkAccess('write', "", $ref_id) === true) {
            // settings
            $this->addTab(self::TAB_EDIT_DCL, $this->ctrl->getLinkTarget($this, "editObject"));
            // list tables
            $this->addTab(self::TAB_LIST_TABLES, $this->ctrl->getLinkTargetByClass(ilDclTableListGUI::class, "listTables"));
            // export
            $this->addTab(self::TAB_EXPORT, $this->ctrl->getLinkTargetByClass(ilDclExportGUI::class, ""));
        }

        // edit permissions
        if ($this->access->checkAccess('edit_permission', "", $ref_id) === true) {
            //list permissions
            $this->addTab(self::TAB_LIST_PERMISSIONS, $this->ctrl->getLinkTargetByClass(
                ilPermissionGUI::class,
                "perm"
            ));
        }
    }

    private function addTab(string $langKey, string $link): void
    {
        $this->tabs->addTab($langKey, $this->lng->txt($langKey), $link);
    }

    /**
     * edit object
     * @access    public
     */
    public function editObject(): void
    {
        $dataCollectionTemplate = $this->tpl;

        $ref_id = $this->object->getRefId();
        if ($this->access->checkAccess('write', "", $ref_id) === false) {
            $this->error->raiseError($this->lng->txt("msg_no_perm_write"), null);
        }

        $this->tabs->activateTab(self::TAB_EDIT_DCL);

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values, true);
        }

        $this->addExternalEditFormCustom($form);

        $dataCollectionTemplate->setContent($form->getHTML());
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $this->tabs->activateTab(self::TAB_EDIT_DCL);

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

        // is_online
        $cb = new ilCheckboxInputGUI($this->lng->txt("online"), "is_online");
        $cb->setInfo($this->lng->txt("dcl_online_info"));
        $form->addItem($cb);

        // Notification
        $cb = new ilCheckboxInputGUI($this->lng->txt("dcl_activate_notification"), "notification");
        $cb->setInfo($this->lng->txt("dcl_notification_info"));
        $form->addItem($cb);

        // tile img upload
        $section_appearance = new ilFormSectionHeaderGUI();
        $section_appearance->setTitle($this->lng->txt('cont_presentation'));
        $form->addItem($section_appearance);
        $form = $this->object_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

        $form->addCommandButton("update", $this->lng->txt("save"));

        return $form;
    }

    final public function listRecords(): void
    {
        $this->ctrl->setParameterByClass(ilDclRecordListGUI::class, "tableview_id", $this->getTableViewId());
        $this->ctrl->redirectByClass(ilDclRecordListGUI::class, "show");
    }

    public function getDataCollectionObject(): ilObjDataCollection
    {
        return new ilObjDataCollection($this->ref_id, true);
    }

    protected function getEditFormCustomValues(array &$a_values): void
    {
        $a_values["is_online"] = $this->object->getOnline();
        $a_values["rating"] = $this->object->getRating();
        $a_values["public_notes"] = $this->object->getPublicNotes();
        $a_values["approval"] = $this->object->getApproval();
        $a_values["notification"] = $this->object->getNotification();
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        $this->object->setOnline((bool)$form->getInput("is_online"));
        $this->object->setRating((bool)$form->getInput("rating"));
        $this->object->setPublicNotes((bool)$form->getInput("public_notes"));
        $this->object->setApproval((bool)$form->getInput("approval"));
        $this->object->setNotification((bool)$form->getInput("notification"));

        $this->object_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

        $this->emptyInfo();
    }

    private function emptyInfo(): void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $table = ilDclCache::getTableCache($this->object->getFirstVisibleTableId());
        $tables = $this->object->getTables();
        if (count($tables) === 1 && count($table->getRecordFields()) === 0 && count($table->getRecords()) === 0
            && $this->object->getOnline()
        ) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("dcl_no_content_warning"), true);
        }
    }

    final public function toggleNotification(): void
    {
        $ntf = $this->http->wrapper()->query()->retrieve('ntf', $this->refinery->kindlyTo()->int());
        switch ($ntf) {
            case 1:
                ilNotification::setNotification(
                    ilNotification::TYPE_DATA_COLLECTION,
                    $this->user->getId(),
                    $this->obj_id,
                    false
                );
                break;
            case 2:
                ilNotification::setNotification(
                    ilNotification::TYPE_DATA_COLLECTION,
                    $this->user->getId(),
                    $this->obj_id
                );
                break;
        }
        $this->ctrl->redirectByClass(ilDclRecordListGUI::class, "show");
    }

    protected function addHeaderAction(): void
    {
        ilObjectListGUI::prepareJsLinks(
            $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
            "",
            $this->ctrl->getLinkTargetByClass([ilCommonActionDispatcherGUI::class, ilTaggingGUI::class], "", "", true)
        );

        $dispatcher = new ilCommonActionDispatcherGUI(ilCommonActionDispatcherGUI::TYPE_REPOSITORY, $this->access, "dcl", $this->ref_id, $this->obj_id);

        $lg = $dispatcher->initHeaderAction();

        // notification
        if ($this->user->getId() != ANONYMOUS_USER_ID and $this->object->getNotification() == 1) {
            if (ilNotification::hasNotification(ilNotification::TYPE_DATA_COLLECTION, $this->user->getId(), $this->obj_id)) {
                //Command Activate Notification
                $this->ctrl->setParameter($this, "ntf", 1);
                $lg->addCustomCommand($this->ctrl->getLinkTarget($this, "toggleNotification"), "dcl_notification_deactivate_dcl");

                $lg->addHeaderIcon("not_icon", ilUtil::getImagePath("object/notification_on.svg"), $this->lng->txt("dcl_notification_activated"));
            } else {
                //Command Deactivate Notification
                $this->ctrl->setParameter($this, "ntf", 2);
                $lg->addCustomCommand($this->ctrl->getLinkTarget($this, "toggleNotification"), "dcl_notification_activate_dcl");

                $lg->addHeaderIcon("not_icon", ilUtil::getImagePath("object/notification_off.svg"), $this->lng->txt("dcl_notification_deactivated"));
            }
            $this->ctrl->setParameter($this, "ntf", "");
        }

        $this->tpl->setHeaderActionMenu($lg->getHeaderAction());
    }
}
