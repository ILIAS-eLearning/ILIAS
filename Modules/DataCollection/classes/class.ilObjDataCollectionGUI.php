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
 ********************************************************************
 */

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

    public ?ilObject $object = null;

    private ilDataCollectionUiPort $dclUi;
    private ilDataCollectionEndpointPort $dclEndPoint;
    private ilDataCollectionAccessPort $dclAccess;

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

        $this->lng->loadLanguageModule("dcl");
        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('obj');
        $this->lng->loadLanguageModule('cntr');

        $this->setTableId($this->getRefId());

        $this->dclEndPoint = ilDataCollectionEndpointAdapter::new();
        $this->dclAccess = ilDataCollectionAccessAdapter::new();
        $this->dclUi = ilDataCollectionUiAdapter::new();

        if ($this->ctrl->isAsynch() === false) {
            $this->addJavaScript();
        }

        $this->dclEndPoint->saveParameterTableId($this);
        $this->ctrl->saveParameter($this, "table_id");
    }

    private function setTableId(int $objectOrRefId = 0) : void
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

    public function getObjectId() : int
    {
        return $this->obj_id;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
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
        global $DIC;

        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilHelp = $DIC['ilHelp'];
        $ilHelp->setScreenIdComponent('bibl');

        // Navigation History
        $link = $this->ctrl->getLinkTarget($this, "render");

        if ($this->object != null) {
            $ilNavigationHistory->addItem($this->object->getRefId(), $link, "dcl");
        }

        $hasDclGtr = $this->http->wrapper()->query()->has(self::GET_DCL_GTR);
        // Direct-Link Resource, redirect to viewgui
        if ($hasDclGtr) {
            $viewId = $this->http->wrapper()->query()->retrieve(self::GET_VIEW_ID, $this->refinery->kindlyTo()->int());
            $record_id = $this->http->wrapper()->query()->retrieve(self::GET_DCL_GTR,
                $this->refinery->kindlyTo()->int());

            $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'tableview_id', $viewId);
            $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'record_id', $record_id);
            $this->ctrl->redirectByClass(ilDclDetailedViewGUI::class, 'renderRecord');
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$this->getCreationMode() and $next_class != "ilinfoscreengui" and $cmd != 'infoScreen' and !$this->checkPermissionBool("read")) {
            $DIC->ui()->mainTemplate()->loadStandardTemplate();
            $DIC->ui()->mainTemplate()->setContent("Permission Denied.");

            return;
        }

        switch ($next_class) {
            case "ilinfoscreengui":
                $this->prepareOutput();
                $this->tabs->activateTab("id_info");
                $this->infoScreenForward();
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $gui->enableCommentsSettings(false);
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilpermissiongui":
                $this->prepareOutput();
                $this->tabs->activateTab("id_permissions");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilobjectcopygui":
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("dcl");
                $DIC->ui()->mainTemplate()->loadStandardTemplate();
                $this->ctrl->forwardCommand($cp);
                break;

            case "ildcltablelistgui":
                $this->prepareOutput();
                $this->tabs->activateTab("id_tables");
                $tablelist_gui = new ilDclTableListGUI($this);
                $this->ctrl->forwardCommand($tablelist_gui);
                break;

            case "ildclrecordlistgui":
                $this->addHeaderAction();
                $this->prepareOutput();
                $this->tabs->activateTab("id_records");

                $tableview_id = 1;
                if ($this->http->wrapper()->query()->has('tableview_id')) {
                    $tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id',
                        $this->refinery->kindlyTo()->int());
                }
                if ($this->http->wrapper()->post()->has('tableview_id')) {
                    $tableview_id = $this->http->wrapper()->post()->retrieve('tableview_id',
                        $this->refinery->kindlyTo()->int());
                }

                $this->ctrl->setParameterByClass(ilDclRecordListGUI::class, 'tableview_id', $tableview_id);
                $recordlist_gui = new ilDclRecordListGUI($this, $this->table_id);
                $this->ctrl->forwardCommand($recordlist_gui);
                break;

            case "ildclrecordeditgui":
                $this->prepareOutput();
                $this->tabs->activateTab("id_records");
                $recordedit_gui = new ilDclRecordEditGUI($this);
                $this->ctrl->forwardCommand($recordedit_gui);
                break;

            case "ilobjfilegui":
                $this->prepareOutput();
                $this->tabs->activateTab("id_records");
                $file_gui = new ilObjFile($this->getRefId());
                $this->ctrl->forwardCommand($file_gui);
                break;

            case "ilratinggui":
                $rgui = new ilRatingGUI();

                $record_id = $this->http->wrapper()->query()->retrieve('record_id', $this->refinery->kindlyTo()->int());
                $field_id = $this->http->wrapper()->query()->retrieve('field_id', $this->refinery->kindlyTo()->int());

                $rgui->setObject($record_id, "dcl_record", $field_id, "dcl_field");
                $rgui->executeCommand();
                $this->ctrl->redirectByClass("ilDclRecordListGUI", "listRecords");
                break;

            case "ildcldetailedviewgui":
                $this->prepareOutput();
                $recordview_gui = new ilDclDetailedViewGUI($this);
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

            case 'ilnotegui':
                $this->prepareOutput();
                $recordviewGui = new ilDclDetailedViewGUI($this);
                $this->ctrl->forwardCommand($recordviewGui);
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, ""));
                break;
            case "ildclexportgui":
                $this->prepareOutput();
                $DIC->tabs()->setTabActive("export");
                $exp_gui = new ilDclExportGUI($this);
                $table_id = filter_input(INPUT_GET, 'table_id', FILTER_VALIDATE_INT);
                $exporter = new ilDclContentExporter($this->object->getRefId(), $table_id);
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
    public function infoScreen() : void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }

    /**
     * show Content; redirect to ilDclRecordListGUI::listRecords
     */
    public function render() : void
    {
        if ($this->http->wrapper()->query()->has('tableview_id')) {
            $tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id',
                $this->refinery->kindlyTo()->int());
        } else {
            $tableview_id = 0;
        }

        $this->ctrl->setParameterByClass('ilDclRecordListGUI', 'tableview_id', $tableview_id);
        $this->ctrl->redirectByClass("ildclrecordlistgui", "show");
    }

    /**
     * show information screen
     */
    public function infoScreenForward() : void
    {
        $this->tabs->activateTab('info_short');

        if (!$this->checkPermissionBool('visible')) {
            $this->checkPermission('read');
        }

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        $this->ctrl->forwardCommand($info);
    }

    /**
     * @throws ilCtrlException
     */
    protected function addLocatorItems() : void
    {
        if (is_object($this->object) === true) {
            $this->dclUi->addLocatorItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this, ""),
                $this->object->getRefId()
            );
        }
    }

    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $lng = $DIC->language();

        $dclConfig = ilDataCollectionOutboundsAdapter::new();
        $dclUi = $dclConfig->getDataCollectionUi();

        $ilCtrl = $DIC->ctrl();
        $dclAccess = $dclConfig->getDataCollectionAccess();

        $targetParts = explode("_", $a_target);
        [$refId, $viewId, $recordId] = $targetParts;

        //redirect if no permission given
        if ($dclAccess->hasVisibleOrReadPermission($refId) === false) {
            $dclUi->displayFailureMessage(
                sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                )
            );
            ilObjectGUI::_gotoRepositoryRoot();
        }

        //load record list
        if ($dclAccess->hasReadPermission($refId) === true) {
            $ilCtrl->setParameterByClass("ilRepositoryGUI", self::GET_REF_ID, $refId);
            $ilCtrl->setParameterByClass("ilRepositoryGUI", self::GET_VIEW_ID, $viewId);
            $ilCtrl->setParameterByClass("ilRepositoryGUI", self::GET_DCL_GTR, $recordId);
            $ilCtrl->redirectByClass("ilRepositoryGUI", "listRecords");
        }

        //redirect to info screen
        if ($dclAccess->hasVisiblePermission($refId) === true) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        }
    }

    protected function afterSave(ilObject $new_object) : void
    {
        $this->dclUi->displaySuccessMessage($this->lng->txt("object_added"));

        $listTablesLink = $this->dclEndPoint->getListTablesLink();
        $this->dclEndPoint->redirect($listTablesLink);
    }

    /**
     * setTabs
     * create tabs (repository/workspace switch)
     * this had to be moved here because of the context-specific permission tab
     */
    protected function setTabs() : void
    {
        $refId = $this->object->getRefId();

        // visible permission
        if ($this->dclAccess->hasVisibleOrReadPermission($refId) === true) {
            // info screen
            $this->addTab("info_short", $this->dclEndPoint->getInfoScreenLink());
        }

        // read permission
        if ($this->dclAccess->hasReadPermission($refId) === true) {
            $tableview_id = 1;
            if ($this->http->wrapper()->query()->has('tableview_id')) {
                $tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id',
                    $this->refinery->kindlyTo()->int());
            }

            // list records
            $this->addTab('content', $this->dclEndPoint->getListRecordsLink($tableview_id));
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
        $this->tabs->addTab($langKey, $this->lng->txt($langKey), $link);
    }

    /**
     * edit object
     * @access    public
     */
    public function editObject() : void
    {
        $dataCollectionTemplate = $this->tpl;

        $refId = $this->object->getRefId();
        if ($this->dclAccess->hasEditPermission($refId) === false) {
            $this->dclUi->displayErrorMessage(
                $this->lng->txt("msg_no_perm_write")
            );
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

    protected function initEditForm() : ilPropertyFormGUI
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

    final public function listRecords() : void
    {
        $tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id', $this->refinery->kindlyTo()->int());

        $viewId = $tableview_id;
        $listRecordsLink = $this->dclEndPoint->getListRecordsLink($viewId);
        $this->dclEndPoint->redirect($listRecordsLink);
    }

    public function getDataCollectionObject() : ilObjDataCollection
    {
        return new ilObjDataCollection($this->ref_id, true);
    }

    protected function getEditFormCustomValues(array &$values) : void
    {
        $values["is_online"] = $this->object->getOnline();
        $values["rating"] = $this->object->getRating();
        $values["public_notes"] = $this->object->getPublicNotes();
        $values["approval"] = $this->object->getApproval();
        $values["notification"] = $this->object->getNotification();
    }

    protected function updateCustom(ilPropertyFormGUI $form) : void
    {
        $this->object->setOnline($form->getInput("is_online"));
        $this->object->setRating($form->getInput("rating"));
        $this->object->setPublicNotes($form->getInput("public_notes"));
        $this->object->setApproval($form->getInput("approval"));
        $this->object->setNotification($form->getInput("notification"));

        $this->object_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

        $this->emptyInfo();
    }

    private function emptyInfo() : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $table = ilDclCache::getTableCache($this->object->getFirstVisibleTableId());
        $tables = $this->object->getTables();
        if (count($tables) == 1 and count($table->getRecordFields()) == 0 and count($table->getRecords()) == 0
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

        $ntf = $this->http->wrapper()->query()->retrieve('ntf', $this->refinery->kindlyTo()->int());
        switch ($ntf) {
            case 1:
                ilNotification::setNotification(
                    ilNotification::TYPE_DATA_COLLECTION,
                    $ilUser->getId(),
                    $this->obj_id,
                    false
                );
                break;
            case 2:
                ilNotification::setNotification(
                    ilNotification::TYPE_DATA_COLLECTION,
                    $ilUser->getId(),
                    $this->obj_id
                );
                break;
        }
        $ilCtrl->redirectByClass("ildclrecordlistgui", "show");
    }

    protected function addHeaderAction() : void
    {
        ilObjectListGUI::prepareJSLinks(
            $this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true),
            "",
            $this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true)
        );
    }
}
