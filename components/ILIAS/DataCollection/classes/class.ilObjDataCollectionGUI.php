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

use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectPropertyTileImage;
use ILIAS\UI\Component\Input\Container\Form\Form;

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
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilObjectContentStyleSettingsGUI

 */
class ilObjDataCollectionGUI extends ilObject2GUI
{
    public const GET_REF_ID = "ref_id";
    public const GET_TABLE_ID = "table_id";
    public const GET_VIEW_ID = "tableview_id";
    public const GET_RECORD_ID = "record_id";

    public const TAB_EDIT_DCL = 'settings';
    public const TAB_LIST_TABLES = 'dcl_tables';
    public const TAB_META_DATA = 'meta_data';
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
                $this->prepareOutput();
                $this->handleExport();
                break;

            case strtolower(ilDclPropertyFormGUI::class):
                $recordedit_gui = new ilDclRecordEditGUI($this, $this->table_id, $this->getTableViewId());
                $recordedit_gui->getRecord();
                $recordedit_gui->initForm();
                $form = $recordedit_gui->getForm();
                $this->ctrl->forwardCommand($form);
                break;

            case strtolower(ilObjectMetaDataGUI::class):
                $this->checkPermission('write');
                $this->prepareOutput();
                $this->tabs->activateTab(self::TAB_META_DATA);
                $gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilObjectContentStyleSettingsGUI::class):
                $this->prepareOutput();
                $this->setEditTabs();
                $this->tabs->activateTab('settings');
                $this->tabs->activateSubTab('cont_style');
                global $DIC;
                $settings_gui = $DIC->contentStyle()->gui()->objectSettingsGUIForRefId(null, $this->ref_id);
                $this->ctrl->forwardCommand($settings_gui);
                break;

            default:
                parent::executeCommand();
        }
    }

    protected function handleExport(bool $do_default = false): void
    {
        $this->tabs->setTabActive(self::TAB_EXPORT);
        $exp_gui = new ilDclExportGUI($this);
        $exporter = new ilDclContentExporter($this->object->getRefId(), $this->table_id);
        $exp_gui->addFormat("xlsx", $this->lng->txt('dcl_xls_async_export'), $exporter, 'exportAsync');
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
                (string) $this->object->getRefId()
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

        $values = [self::GET_REF_ID, self::GET_TABLE_ID, self::GET_VIEW_ID, self::GET_RECORD_ID];
        $values = array_combine($values, array_pad(explode("_", $a_target), count($values), null));

        $ref_id = (int) $values[self::GET_REF_ID];

        //load record list
        if ($access->checkAccess('read', "", $ref_id)) {
            $ilCtrl->setParameterByClass(ilRepositoryGUI::class, self::GET_REF_ID, $ref_id);
            if ($values['table_id'] !== null) {
                $ilCtrl->setParameterByClass(ilObjDataCollectionGUI::class, self::GET_TABLE_ID, $values['table_id']);
                if ($values['tableview_id'] !== null) {
                    $ilCtrl->setParameterByClass(ilObjDataCollectionGUI::class, self::GET_VIEW_ID, $values['tableview_id']);
                }
                if ($values['record_id'] !== null) {
                    $ilCtrl->setParameterByClass(ilDclDetailedViewGUI::class, self::GET_RECORD_ID, $values['record_id']);
                    $ilCtrl->redirectByClass([ilRepositoryGUI::class, self::class, ilDclDetailedViewGUI::class], "renderRecord");
                }
            }
            $ilCtrl->redirectByClass([ilRepositoryGUI::class, self::class, ilDclRecordListGUI::class], "listRecords");
        }
        //redirect to info screen
        elseif ($access->checkAccess('visbile', "", $ref_id)) {
            ilObjectGUI::_gotoRepositoryNode((int) $a_target, "infoScreen");
        }
        //redirect if no permission given
        else {
            $message = sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId((int) $a_target))
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
            $this->addTab(self::TAB_CONTENT, $this->ctrl->getLinkTargetByClass(ilDclRecordListGUI::class, "show"));
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
            $this->addTab(self::TAB_EDIT_DCL, $this->ctrl->getLinkTarget($this, 'edit'));
            // list tables
            $this->addTab(self::TAB_LIST_TABLES, $this->ctrl->getLinkTargetByClass(ilDclTableListGUI::class, "listTables"));
            // metadata
            $mdgui = new ilObjectMetaDataGUI($this->object);
            if ($mdtab = $mdgui->getTab()) {
                $this->tabs_gui->addTab(self::TAB_META_DATA, $this->lng->txt('meta_data'), $mdtab);
            }
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

    protected function initForm(): Form
    {
        $inputs = [];

        $edit = [];
        $edit['title'] = $this->ui_factory->input()->field()->text($this->lng->txt('title'))->withRequired(true);
        $edit['description'] = $this->ui_factory->input()->field()->textarea($this->lng->txt('description'));
        $edit['notification'] = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('dcl_activate_notification'),
            $this->lng->txt('dcl_notification_info')
        );
        $inputs['edit'] = $this->ui_factory->input()->field()->section($edit, $this->lng->txt($this->type . '_edit'))
            ->withValue([
                'title' => $this->object->getTitle(),
                'description' => $this->object->getLongDescription(),
                'notification' => $this->object->getNotification(),
            ]);

        $availability = [];
        $availability['online'] = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt('online'),
            $this->lng->txt('dcl_online_info')
        );
        $inputs['availability'] = $this->ui_factory->input()->field()->section($availability, $this->lng->txt('obj_activation_list_gui'))
            ->withValue(['online' => $this->object->getOnline(),]);

        $presentation = [];
        $presentation['tile_image'] = $this->object->getObjectProperties()->getPropertyTileImage()->toForm(
            $this->lng,
            $this->ui_factory->input()->field(),
            $this->refinery
        );
        //This is currently broken in core
        //$inputs['presentation'] = $this->ui_factory->input()->field()->section($presentation, $this->lng->txt('cont_presentation'))
        //    ->withValue(['tile_image' => $this->object->getObjectProperties()->getPropertyTileImage()]);

        return $this->ui_factory->input()->container()->form()->standard($this->ctrl->getFormAction($this, 'save'), $inputs);
    }

    public function edit(): void
    {
        if (!$this->checkPermissionBool('write')) {
            $this->tpl->setOnScreenMessage($this->tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('permission_denied'));
        }

        $this->setEditTabs();
        $this->tabs->activateTab(self::TAB_EDIT_DCL);

        $this->tpl->setContent($this->ui_renderer->render($this->initForm()));
    }

    public function save(): void
    {
        if (!$this->checkPermissionBool('write')) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->setEditTabs();
        $this->tabs->activateTab(self::TAB_EDIT_DCL);

        $form = $this->initForm()->withRequest($this->http->request());
        $data = $form->getData();

        if ($data !== null) {
            $this->object->setTitle($data['edit']['title']);
            $this->object->setDescription($data['edit']['description']);
            $this->object->setNotification($data['edit']['notification']);
            $this->object->setOnline($data['availability']['online']);
            //This is currentyl broken in core
            //$this->object->getObjectProperties()->storePropertyTileImage($data['presentation']['tile_image']);
            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        }

        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    protected function setEditTabs(): void
    {
        $this->tabs->addSubTab(
            'general',
            $this->lng->txt('general'),
            $this->ctrl->getLinkTargetByClass(self::class, 'edit')
        );
        $this->tabs->addSubTab(
            'cont_style',
            $this->lng->txt('cont_style'),
            $this->ctrl->getLinkTargetByClass(ilObjectContentStyleSettingsGUI::class)
        );

        $this->tabs->activateSubTab('general');
    }

    final public function listRecords(): void
    {
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
        $this->object->setOnline((bool) $form->getInput("is_online"));
        $this->object->setRating((bool) $form->getInput("rating"));
        $this->object->setPublicNotes((bool) $form->getInput("public_notes"));
        $this->object->setApproval((bool) $form->getInput("approval"));
        $this->object->setNotification((bool) $form->getInput("notification"));

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
