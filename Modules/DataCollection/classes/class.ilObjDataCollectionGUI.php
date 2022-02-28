<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjDataCollectionGUI
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author       Martin Studer <martin@fluxlabs.ch>
 * @author       Marcel Raimann <mr@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       Oskar Truffer <ot@studer-raimann.ch>
 * @author       Stefan Wanzenried <sw@studer-raimann.ch>
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilPermissionGUI, ilObjectCopyGUI, ilDclExportGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclTreePickInputGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclRecordListGUI, ilDclRecordEditGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclDetailedViewGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclTableListGUI, ilObjFileGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilObjUserGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilRatingGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilPropertyFormGUI
 * @ilCtrl_Calls ilObjDataCollectionGUI: ilDclPropertyFormGUI
 * @extends      ilObject2GUI
 */
class ilObjDataCollectionGUI extends ilObject2GUI
{
    const GET_DCL_GTR = "dcl_gtr";
    const GET_REF_ID = "ref_id";
    const GET_VIEW_ID = "tableview_id";
    const TAB_EDIT_DCL = 'settings';
    const TAB_LIST_TABLES = 'dcl_tables';
    const TAB_EXPORT = 'export';
    const TAB_LIST_PERMISSIONS = 'perm_settings';

    public ?ilObject $object;

    private ilDataCollectionUiPort $dclUi;
    private ilDataCollectionLanguagePort $dclLanguage;
    private ilDataCollectionEndpointPort $dclEndPoint;
    private ilDataCollectionAccessAdapter $dclAccess;

    protected \ilCtrl $ctrl;
    protected \ilErrorHandling $error;

    protected \ilTabsGUI $tabs;
    protected \ilLanguage $language;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilNavigationHistory $navigationHistory;
    protected \ilHelp $help;
    protected \ilLocatorGUI $locatorGui;

    private int $tableId;

    private function init(
        ilDataCollectionUiPort $dataCollectionTemplate,
        ilDataCollectionLanguagePort $dclLanguage,
        ilDataCollectionEndpointPort $dclEndPoint,
        ilDataCollectionAccessAdapter $dclAccess
    ) {
        $this->dclUi = $dataCollectionTemplate;
        $this->dclLanguage = $dclLanguage;
        $this->dclEndPoint = $dclEndPoint;
        $this->dclAccess = $dclAccess;
    }

    public function __construct(int $objectOrRefId = 0, int $idType = self::REPOSITORY_NODE_ID, $parentNodeId = 0)
    {
        global $DIC;

        parent::__construct($objectOrRefId, $idType, $parentNodeId);

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->language = $DIC->language();
        $this->ui = $DIC->ui();
        $this->tabs = $DIC->tabs();
        $this->navigationHistory = $DIC['ilNavigationHistory'];
        $this->help = $DIC['ilHelp'];
        $this->error = $DIC['ilErr'];
        $this->locatorGui = $DIC['ilLocator'];

        if ($this->ctrl->isAsynch() === false) {
            $this->addJavaScript();
        }

        $this->ctrl->saveParameter($this, "table_id");
    }

    private function setTableId(int $objectOrRefId = 0)
    {
        if (isset($_GET['table_id'])) {
            $this->tableId = $_GET['table_id'];
        } elseif (isset($_GET['tableview_id'])) {
            $tableView = ilDclTableView::find($_GET['tableview_id']);
            $this->tableId = $tableView->getTableId();
        } elseif ($objectOrRefId > 0) {
            $this->tableId = $this->object->getFirstVisibleTableId();
        }
    }

    private function addJavaScript() : void
    {
        ilYuiUtil::initConnection();
        ilOverlayGUI::initJavascript();
        $this->dclUi->addJavaScriptFile('Modules/DataCollection/js/ilDataCollection.js');
        // # see  https://mantis.ilias.de/view.php?id=26463
        $this->dclUi->addJavaScriptFile("./Services/UIComponent/Modal/js/Modal.js");
        $this->dclUi->addJavaScriptFile("Modules/DataCollection/js/datacollection.js");
        $this->dclUi->addOnLoadJavaScriptCode(
            "ilDataCollection.setEditUrl('" . $this->dclEndPoint->getEditDclLink($this) . "');"
        );
        $this->dclUi->addOnLoadJavaScriptCode(
            "ilDataCollection.setCreateUrl('" . $this->dclEndPoint->getCreateDclLink($this) . "');"
        );
        $this->dclUi->addOnLoadJavaScriptCode(
            "ilDataCollection.setSaveUrl('" . $this->dclEndPoint->getSaveDclEndpoint($this) . "');"
        );
        $this->dclUi->addOnLoadJavaScriptCode(
            "ilDataCollection.setDataUrl('" . $this->dclEndPoint->getQueryRecordDataEndpoint() . "' );"
        );
    }

    public function getStandardCmd() : string
    {
        return "render";
    }

    public function getType() : string
    {
        return "dcl";
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand() : void
    {
        $refId = $this->object->getRefId();

        if (is_null($this->object) === true) {
            $this->navigationHistory->addItem($refId, $this->dclEndPoint->getDataCollectionHomeLink($this), "dcl");
        }

        // Direct-Link Resource, redirect to viewgui
        if ($_GET[self::GET_DCL_GTR]) {
            //ToDo get rid of GET
            $editRecordLink = $this->dclEndPoint->getEditRecordLink($_GET[self::GET_VIEW_ID], $_GET[self::GET_DCL_GTR]);
            $this->dclEndPoint->redirect($editRecordLink);
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if ($next_class !== strtolower(\ilInfoScreenGUI::class)
            && $cmd !== 'infoScreen'
            && $this->getCreationMode() === false
            && $this->dclAccess->hasReadPermission($refId) === false
        ) {
            $this->dclUi->displayErrorMessage("Permission Denied.");
            return;
        }

        switch ($next_class) {
            case strtolower(\ilInfoScreenGUI::class):
                $this->prepareOutput();
                $this->tabs->activateTab("id_info");
                $this->infoScreenForward();
                break;

            case strtolower(\ilCommonActionDispatcherGUI::class):
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $gui->enableCommentsSettings(false);
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(\ilPermissionGUI::class):
                $this->prepareOutput();
                $this->tabs->activateTab("id_permissions");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case strtolower(\ilObjectCopyGUI::class):
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("dcl");
                $this->ui->mainTemplate()->loadStandardTemplate();
                $this->ctrl->forwardCommand($cp);
                break;

            case strtolower(\ilDclTableListGUI::class):
                $this->prepareOutput();
                $this->tabs->setTabActive("id_tables");
                $tablelist_gui = new ilDclTableListGUI($this);
                $this->ctrl->forwardCommand($tablelist_gui);
                break;

            case strtolower(\ilDclRecordListGUI::class):
                $this->addHeaderAction();
                $this->prepareOutput();
                $this->tabs->activateTab("id_records");
                $this->ctrl->setParameterByClass(ilDclRecordListGUI::class, 'tableview_id', $_REQUEST['tableview_id']);
                $recordlist_gui = new ilDclRecordListGUI($this, $this->tableId);
                $this->ctrl->forwardCommand($recordlist_gui);
                break;

            case  strtolower(\ilDclRecordEditGUI::class):
                $this->prepareOutput();
                $this->tabs->activateTab("id_records");
                $recordedit_gui = new ilDclRecordEditGUI($this);
                $this->ctrl->forwardCommand($recordedit_gui);
                break;

            case strtolower(\ilObjFileGUI::class):
                $this->prepareOutput();
                $this->tabs->setTabActive("id_records");
                $file_gui = new ilObjFile($this);
                $this->ctrl->forwardCommand($file_gui);
                break;

            case strtolower(\ilRatingGUI::class):
                $rgui = new ilRatingGUI();
                $rgui->setObject($_GET['record_id'], "dcl_record", $_GET["field_id"], "dcl_field");
                $rgui->executeCommand();
                $this->ctrl->redirectByClass("ilDclRecordListGUI", "listRecords");
                break;

            case strtolower(\ilDclDetailedViewGUI::class):
                $this->prepareOutput();
                $recordview_gui = new ilDclDetailedViewGUI($this);
                $this->ctrl->forwardCommand($recordview_gui);
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget($this->lng->txt("back"),
                    $this->ctrl->getLinkTargetByClass(ilDclRecordListGUI::class,
                        ilDclRecordListGUI::CMD_LIST_RECORDS));
                break;

            case strtolower(\ilNoteGUI::class):
                $this->prepareOutput();
                $recordviewGui = new ilDclDetailedViewGUI($this);
                $this->ctrl->forwardCommand($recordviewGui);
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, ""));
                break;

            case strtolower(\ilDclExportGUI::class):
                $this->prepareOutput();
                $this->tabs->setTabActive("export");
                $exp_gui = new ilDclExportGUI($this);
                $exporter = new ilDclContentExporter($this->object->getRefId());
                $exp_gui->addFormat("xlsx", $this->lng->txt('dlc_xls_async_export'), $exporter, 'exportAsync');
                $exp_gui->addFormat("xml");

                $this->ctrl->forwardCommand($exp_gui);
                break;

            case strtolower(ilDclPropertyFormGUI::class):
                $recordedit_gui = new ilDclRecordEditGUI($this);
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
                    default:
                        parent::executeCommand();
                }
        }
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    public function infoScreen()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }

    /**
     * show Content; redirect to ilDclRecordListGUI::listRecords
     */
    public function render()
    {
        $this->ctrl->setParameterByClass('ilDclRecordListGUI', 'tableview_id', $_GET['tableview_id']);
        $this->ctrl->redirectByClass("ildclrecordlistgui", "show");
    }

    /**
     * show information screen
     */
    public function infoScreenForward()
    {
        $this->dclUi->activateTab('info_short');
        $refId = $this->object->getRefId();

        if ($this->dclAccess->hasVisibleOrReadPermission($refId) === true) {
            $this->dclUi->displayErrorMessage($this->dclLanguage->translate('msg_no_perm_read'));
        }

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        $this->dclEndPoint->forwardCommand($info);
    }

    /**
     * @throws ilCtrlException
     */
    public function addLocatorItems() : void
    {
        if (is_object($this->object) === true) {
            $this->dclUi->addLocatorItem(
                $this->object->getTitle(),
                $this->dclEndPoint->getDataCollectionHomeLink($this),
                $this->object->getRefId()
            );
        }
    }

    public static function _goto(string $a_target) : void
    {
        $dclConfig = ilDataCollectionConfigAdapter::new();
        $dclUi = $dclConfig->getDataCollectionUi();
        $dclTranslation = $dclConfig->getDataCollectionLanguage();
        $dclAccess = $dclConfig->getDataCollectionAccess();

        $targetParts = explode("_", $a_target);
        [$refId, $viewId, $recordId] = $targetParts;

        //redirect if no permission given
        if ($dclAccess->hasVisibleOrReadPermission($refId) === false) {
            $dclUi->displayFailureMessage(
                sprintf($dclTranslation->translate("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                )
            );
            ilObjectGUI::_gotoRepositoryRoot();
        }

        //load record list
        if ($dclAccess->hasReadPermission($refId) === true) {
            //todo get rid of the GET Parameters
            $_GET["baseClass"] = "ilRepositoryGUI";
            $_GET[self::GET_REF_ID] = $refId;
            $_GET[self::GET_VIEW_ID] = $viewId;
            $_GET[self::GET_DCL_GTR] = $recordId;
            $_GET["cmd"] = "listRecords";
            require_once('./ilias.php');
            exit;
        }

        //redirect to info screen
        if ($dclAccess->hasVisiblePermission($refId) === true) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        }
    }

    final function initCreationForms(string $new_type) : array
    {
        return parent::initCreationForms($new_type);
    }

    protected function afterSave(ilObject $new_object) : void
    {
        $this->dclUi->displaySuccessMessage($this->dclLanguage->translate("object_added"));

        $listTablesLink = $this->dclEndPoint->getListTablesLink();
        $this->dclEndPoint->redirect($listTablesLink);
    }

    /**
     * setTabs
     * create tabs (repository/workspace switch)
     * this had to be moved here because of the context-specific permission tab
     */
    final public function setTabs() : void
    {
        $refId = $this->object->getRefId();

        // visible permission
        if ($this->dclAccess->hasVisibleOrReadPermission($refId) === true) {
            // info screen
            $this->addTab("info_short", $this->dclEndPoint->getInfoScreenLink());
        }

        // read permission
        if ($this->dclAccess->hasReadPermission($refId) === true) {
            // list records
            $this->addTab('content', $this->dclEndPoint->getListRecordsLink());
        }

        // write permission
        if ($this->dclAccess->hasWritePermission($refId) === true) {
            // settings
            $this->addTab(self::TAB_EDIT_DCL, $this->dclEndPoint->getEditDclLink($this));
            // list tables
            $this->addTab(self::TAB_LIST_TABLES, $this->dclEndPoint->getListTablesLink());
            // export
            $this->addTab(self::TAB_EXPORT, $this->dclEndPoint->getDataCollectionExportLink());
        }

        // edit permissions
        if ($this->dclAccess->hasEditPermission($refId) === true) {
            //list permissions
            $this->addTab(self::TAB_LIST_PERMISSIONS, $this->dclEndPoint->getListPermissionsLink());
        }
    }

    private function addTab(string $langKey, string $link) : void
    {
        $this->dclUi->addTab($langKey, $this->dclLanguage->translate($langKey), $link);
    }

    /**
     * edit object
     * @access    public
     */
    public function editObject() : void
    {
        $dataCollectionTemplate = $this->tpl;
        $ilTabs = $this->tabs_gui;
        $ilErr = $this->error;

        $refId = $this->object->getRefId();
        if ($this->dclAccess->hasEditPermission($refId) === false) {
            $this->dclUi->displayErrorMessage(
                $this->dclLanguage->translate("msg_no_perm_write")
            );
        }

        $this->dclUi->activateTab(self::TAB_EDIT_DCL);

        $form = $this->initEditForm();
        $values = $this->getEditFormValues();
        if ($values) {
            $form->setValuesByArray($values, true);
        }

        $this->addExternalEditFormCustom($form);

        $dataCollectionTemplate->setContent($form->getHTML());
    }

    final public function initEditForm() : ilPropertyFormGUI
    {
        $this->dclUi->activateTab(self::TAB_EDIT_DCL);

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

    final public function listRecords() : void
    {
        //todo get rid of GET
        $viewId = $_GET["tableview_id"];
        $listRecordsLink = $this->dclEndPoint->getListRecordsLink($viewId);
        $this->dclEndPoint->redirect($listRecordsLink);
    }

    public function getDataCollectionObject() : ilObjDataCollection
    {
        return new ilObjDataCollection($this->ref_id, true);
    }

    final public function getEditFormCustomValues(array &$values) : void
    {
        $values["is_online"] = $this->object->getOnline();
        $values["rating"] = $this->object->getRating();
        $values["public_notes"] = $this->object->getPublicNotes();
        $values["approval"] = $this->object->getApproval();
        $values["notification"] = $this->object->getNotification();
    }

    final public function updateCustom(ilPropertyFormGUI $orm) : void
    {
        $this->object->setOnline($orm->getInput("is_online"));
        $this->object->setRating($orm->getInput("rating"));
        $this->object->setPublicNotes($orm->getInput("public_notes"));
        $this->object->setApproval($orm->getInput("approval"));
        $this->object->setNotification($orm->getInput("notification"));

        $this->object_service->commonSettings()->legacyForm($orm, $this->object)->saveTileImage();

        $this->emptyInfo();
    }

    private function emptyInfo() : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $this->table = ilDclCache::getTableCache($this->object->getFirstVisibleTableId());
        $tables = $this->object->getTables();
        if (count($tables) == 1 and count($this->table->getRecordFields()) == 0 and count($this->table->getRecords()) == 0
            and $this->object->getOnline()
        ) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("dcl_no_content_warning"), true);
        }
    }

    final public function toggleNotification() : void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        switch ($_GET["ntf"]) {
            case 1:
                ilNotification::setNotification(ilNotification::TYPE_DATA_COLLECTION, $ilUser->getId(), $this->obj_id,
                    false);
                break;
            case 2:
                ilNotification::setNotification(ilNotification::TYPE_DATA_COLLECTION, $ilUser->getId(), $this->obj_id,
                    true);
                break;
        }
        $ilCtrl->redirectByClass("ildclrecordlistgui", "show");
    }

    final public function addHeaderAction() : void
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $dataCollectionTemplate = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $dispatcher = new ilCommonActionDispatcherGUI(ilCommonActionDispatcherGUI::TYPE_REPOSITORY, $ilAccess, "dcl",
            $this->ref_id, $this->obj_id);

        ilObjectListGUI::prepareJSLinks(
            $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
            $ilCtrl->getLinkTargetByClass(
                array(
                    "ilcommonactiondispatchergui",
                    "ilnotegui",
                ),
                "",
                "",
                true,
                false
            ),
            $ilCtrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false)
        );

        $lg = $dispatcher->initHeaderAction();
        //$lg->enableNotes(true);
        //$lg->enableComments(ilObjWiki::_lookupPublicNotes($this->getPageObject()->getParentId()), false);

        // notification
        if ($ilUser->getId() != ANONYMOUS_USER_ID and $this->object->getNotification() == 1) {
            if (ilNotification::hasNotification(ilNotification::TYPE_DATA_COLLECTION, $ilUser->getId(),
                $this->obj_id)) {
                //Command Activate Notification
                $ilCtrl->setParameter($this, "ntf", 1);
                $lg->addCustomCommand($ilCtrl->getLinkTarget($this, "toggleNotification"),
                    "dcl_notification_deactivate_dcl");

                $lg->addHeaderIcon("not_icon", ilUtil::getImagePath("notification_on.svg"),
                    $lng->txt("dcl_notification_activated"));
            } else {
                //Command Deactivate Notification
                $ilCtrl->setParameter($this, "ntf", 2);
                $lg->addCustomCommand($ilCtrl->getLinkTarget($this, "toggleNotification"),
                    "dcl_notification_activate_dcl");

                $lg->addHeaderIcon("not_icon", ilUtil::getImagePath("notification_off.svg"),
                    $lng->txt("dcl_notification_deactivated"));
            }
            $ilCtrl->setParameter($this, "ntf", "");
        }

        $dataCollectionTemplate->setHeaderActionMenu($lg->getHeaderAction());
    }
}
