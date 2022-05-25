<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Marcel Raimann <mr@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @version      $Id:
 * @ilCtrl_Calls ilDclDetailedViewGUI: ilDclDetailedViewDefinitionGUI, ilEditClipboardGUI
 */
class ilDclDetailedViewGUI
{
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected ilObjDataCollectionGUI $dcl_gui_object;
    protected ilNoteGUI $notes_gui;
    protected ilDclTable $table;
    protected int $tableview_id;
    protected ilDclBaseRecordModel $record_obj;
    protected int $next_record_id = 0;
    protected int $prev_record_id = 0;
    protected int $current_record_position = 0;
    protected array $record_ids = array();
    protected bool $is_enabled_paging = true;
    protected ilLanguage $lng;
    private \ilGlobalTemplateInterface $main_tpl;

    private ilDataCollectionUiPort $dclUi;
    private ilDataCollectionEndpointPort $dclEndPoint;
    private ilDataCollectionAccessPort $dclAccess;

    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;


    private function init(
        ilDataCollectionOutboundsAdapter $adapter
    ) : void {
        $this->dclUi = $adapter->getDataCollectionUi();
        $this->dclAccess = $adapter->getDataCollectionAccess();
        $this->dclEndPoint = $adapter->getDataCollectionEndpoint();
    }

    /**
     * @param ilObjDataCollectionGUI $a_dcl_object
     */
    public function __construct(ilObjDataCollectionGUI $a_dcl_object)
    {
        global $DIC;
        $this->init(ilDataCollectionOutboundsAdapter::new());

        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();

        $this->dcl_gui_object = $a_dcl_object;
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->record_id = (int) $_REQUEST['record_id'];
        $this->record_obj = ilDclCache::getRecordCache($this->record_id);

        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
        if (!$this->record_obj->hasPermissionToView($ref_id)) {
            $this->dclUi->displayFailureMessage($this->lng->txt('dcl_msg_no_perm_view'));

            $this->dclEndPoint->redirect($this->dclEndPoint->getListRecordsLink());
        }

        // content style (using system defaults)
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $tpl->parseCurrentBlock();

        $this->table = $this->record_obj->getTable();

        // Comments
        $repId = $this->dcl_gui_object->getDataCollectionObject()->getId();
        $objId = (int) $this->record_id;
        $this->notesGUI = new ilNoteGUI($repId, $objId);
        $this->notesGUI->enablePublicNotes(true);
        $this->notesGUI->enablePublicNotesDeletion(true);
        $ilCtrl->setParameterByClass("ilnotegui", "record_id", $this->record_id);
        $ilCtrl->setParameterByClass("ilnotegui", "rep_id", $repId);



        if ($this->http->wrapper()->query()->has('disable_paging')
            && $this->http->wrapper()->query()->retrieve('disable_paging', $this->refinery->kindlyTo()->bool())) {
            $this->is_enabled_paging = false;
        }
        // Find current, prev and next records for navigation
        if ($this->is_enabled_paging) {
            $this->determineNextPrevRecords();
        }
        $this->content_style_domain = $DIC->contentStyle()
                                          ->domain()
                                          ->styleForRefId(
                                              $this->dcl_gui_object->getDataCollectionObject()->getRefId()
                                          );
    }

    public function executeCommand() : void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        if ($this->http->wrapper()->query()->has('tableview_id')) {
            $this->tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id', $this->refinery->kindlyTo()->int());
        } else {
            $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
            $this->tableview_id = $this->table->getFirstTableViewId($ref_id);
        }
        $ilCtrl->setParameter($this, 'tableview_id', $this->tableview_id);

        if ($this->http->wrapper()->query()->has('back_tableview_id')) {
            $ilCtrl->setParameter(
                $this->dcl_gui_object,
                'tableview_id',
                $this->http->wrapper()->query()->retrieve('back_tableview_id', $this->refinery->kindlyTo()->int())
            );
        } else {
            $ilCtrl->setParameter($this->dcl_gui_object, 'tableview_id', $this->tableview_id);
            $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
            $this->tableview_id = $this->table->getFirstTableViewId($ref_id);
        }

        if (!$this->checkAccess()) {
            $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
            if ($this->table->getVisibleTableViews($ref_id, true)) {
                $this->offerAlternativeViews();
            } else {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            }

            return;
        }

        $cmd = $ilCtrl->getCmd();
        $cmdClass = $ilCtrl->getCmdClass();
        switch ($cmdClass) {
            case 'ilnotegui':
                switch ($cmd) {
                    case 'editNoteForm':
                        $this->renderRecord(true);
                        break;
                    case 'getNotesHTML':
                        $this->renderRecord(false);
                        break;
                    case 'deleteNote':
                        $this->notesGUI->deleteNote();
                        $this->renderRecord();
                        break;
                    case 'cancelDelete':
                        $this->notesGUI->cancelDelete();
                        $this->renderRecord();
                        break;
                    default:
                        $this->notesGUI->$cmd();
                        break;
                }
                break;
            default:
                $this->$cmd();
                break;
        }
    }

    protected function offerAlternativeViews() : void
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('dcl_msg_info_alternatives'));
        $table_gui = new ilDclTableViewTableGUI($this, 'renderRecord', $this->table);
        $tpl->setContent($table_gui->getHTML());
    }

    public function renderRecord(bool $editComments = false) : void
    {
        global $DIC;
        $ilTabs = $DIC->tabs();
        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();

        $rctpl = new ilDataCollectionGlobalTemplate("tpl.record_view.html", false, true, "Modules/DataCollection");

        $ilTabs->setTabActive("id_content");

        if (!$this->tableview_id) {
            $ilCtrl->redirectByClass("ildclrecordlistgui", "listRecords");
        }

        // see ilObjDataCollectionGUI->executeCommand about instantiation
        $pageObj = new ilDclDetailedViewDefinitionGUI($this->tableview_id);
        $pageObj->setStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );

        $html = $pageObj->getHTML();
        $rctpl->addCss("./Services/COPage/css/content.css");
        $rctpl->fillCssFiles();
        $table = ilDclCache::getTableCache($this->record_obj->getTableId());
        foreach ($table->getRecordFields() as $field) {
            //ILIAS_Ref_Links
            $pattern = '/\[dcliln field="' . preg_quote($field->getTitle(), "/") . '"\](.*?)\[\/dcliln\]/';
            if (preg_match($pattern, $html)) {
                $html = preg_replace(
                    $pattern,
                    $this->record_obj->getRecordFieldSingleHTML($field->getId(), $this->setOptions("$1")),
                    $html
                );
            }

            //DataCollection Ref Links
            $pattern = '/\[dclrefln field="' . preg_quote($field->getTitle(), "/") . '"\](.*?)\[\/dclrefln\]/';
            if (preg_match($pattern, $html)) {
                $this->currentField = $field;
                $html = preg_replace_callback($pattern, array($this, "doReplace"), $html);
            }

            $pattern = '/\[ext tableOf="' . preg_quote($field->getTitle(), "/") . '" field="(.*?)"\]/';
            if (preg_match($pattern, $html)) {
                $this->currentField = $field;
                $html = preg_replace_callback($pattern, array($this, "doExtReplace"), $html);
            }

            $html = str_ireplace(
                "[" . $field->getTitle() . "]",
                $this->record_obj->getRecordFieldSingleHTML($field->getId()),
                $html
            );
        }
        foreach ($table->getStandardFields() as $field) {
            $html = str_ireplace(
                "[" . $field->getId() . "]",
                $this->record_obj->getRecordFieldSingleHTML($field->getId()),
                $html
            );
        }
        $rctpl->setVariable("CONTENT", $html);

        //Permanent Link
        $tpl->setPermanentLink(
            'dcl',
            filter_input(INPUT_GET, 'ref_id', FILTER_VALIDATE_INT),
            '_' . $this->tableview_id . '_' . $this->record_obj->getId()
        );

        // Buttons for previous/next records

        if ($this->is_enabled_paging) {
            $prevNextLinks = $this->renderPrevNextLinks();
            $rctpl->setVariable('PREV_NEXT_RECORD_LINKS', $prevNextLinks);
            $ilCtrl->clearParameters($this); // #14083
            $rctpl->setVariable('FORM_ACTION', $ilCtrl->getFormAction($this));
            $rctpl->setVariable('RECORD', $this->lng->txt('dcl_record'));
            $rctpl->setVariable(
                'RECORD_FROM_TOTAL',
                sprintf(
                    $this->lng->txt('dcl_record_from_total'),
                    $this->current_record_position,
                    count($this->record_ids)
                )
            );
            $rctpl->setVariable('TABLEVIEW_ID', $this->tableview_id);
            $rctpl->setVariable('SELECT_OPTIONS', $this->renderSelectOptions());
        }

        // Edit Button
        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());

        if ($this->record_obj->hasPermissionToEdit($ref_id)) {
            $button = ilLinkButton::getInstance();
            $ilCtrl->setParameterByClass('ildclrecordeditgui', 'table_id', $this->table->getId());
            $ilCtrl->setParameterByClass('ildclrecordeditgui', 'tableview_id', $this->tableview_id);
            $ilCtrl->setParameterByClass('ildclrecordeditgui', 'redirect', ilDclRecordEditGUI::REDIRECT_DETAIL);
            $ilCtrl->saveParameterByClass('ildclrecordeditgui', 'record_id');
            $button->setUrl($ilCtrl->getLinkTargetByClass('ildclrecordeditgui', 'edit'));
            $button->setCaption($this->lng->txt('dcl_edit_record'), false);
            $rctpl->setVariable('EDIT_RECORD_BUTTON', $button->render());
        }

        // Comments
        if ($this->table->getPublicCommentsEnabled()) {
            $rctpl->setVariable('COMMENTS', $this->renderComments($editComments));
        }

        $tpl->setContent($rctpl->get());
    }

    public function doReplace(array $found) : string
    {
        return $this->record_obj->getRecordFieldSingleHTML($this->currentField->getId(), $this->setOptions($found[1]));
    }

    public function doExtReplace(array $found) : ?string
    {
        $ref_rec_ids = $this->record_obj->getRecordFieldValue($this->currentField->getId());
        if (!is_array($ref_rec_ids)) {
            $ref_rec_ids = array($ref_rec_ids);
        }
        if (!count($ref_rec_ids) || !$ref_rec_ids) {
            return null;
        }
        $ref_recs = array();
        foreach ($ref_rec_ids as $ref_rec_id) {
            $ref_recs[] = ilDclCache::getRecordCache($ref_rec_id);
        }
        $field = $ref_recs[0]->getTable()->getFieldByTitle($found[1]);

        $tpl = new ilTemplate("tpl.reference_list.html", true, true, "Modules/DataCollection");
        $tpl->setCurrentBlock("reference_list");

        if (!$field) {
            if (ilObjDataCollectionAccess::hasWriteAccess($this->dcl_gui_object->getRefId())) {
                $this->main_tpl->setOnScreenMessage(
                    'info',
                    "Bad Viewdefinition at [ext tableOf=\"" . $found[1] . "\" ...]",
                    true
                );
            }

            return null;
        }

        foreach ($ref_recs as $ref_record) {
            $tpl->setCurrentBlock("reference");
            $tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($field->getId()));
            $tpl->parseCurrentBlock();
        }

        //$ref_rec->getRecordFieldHTML($field->getId())
        if ($field) {
            return $tpl->get();
        }

        return null;
    }

    protected function renderComments(bool $edit = false) : string
    {
        if (!$edit) {
            return $this->notesGUI->getCommentsHtml();
        } else {
            return $this->notesGUI->editNoteForm();
        }
    }

    /**
     * Find the previous/next record from the current position. Also determine position of current record in whole set.
     */
    protected function determineNextPrevRecords() : void
    {
        if (!isset($_SESSION['dcl_record_ids']) || $_SESSION['dcl_table_id'] != $this->table->getId()) {
            $this->loadSession();
        }

        if (isset($_SESSION['dcl_record_ids']) && count($_SESSION['dcl_record_ids'])) {
            $this->record_ids = $_SESSION['dcl_record_ids'];
            foreach ($this->record_ids as $k => $recId) {
                if ($recId == $this->record_id) {
                    if ($k != 0) {
                        $this->prev_record_id = $this->record_ids[$k - 1];
                    }
                    if (($k + 1) < count($this->record_ids)) {
                        $this->next_record_id = $this->record_ids[$k + 1];
                    }
                    $this->current_record_position = $k + 1;
                    break;
                }
            }
        }
    }

    /**
     * Determine and return the markup for the previous/next records
     */
    protected function renderPrevNextLinks() : string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilCtrl->setParameter($this, 'tableview_id', $this->tableview_id);
        $prevStr = $this->lng->txt('dcl_prev_record');
        $nextStr = $this->lng->txt('dcl_next_record');
        $ilCtrl->setParameter($this, 'record_id', $this->prev_record_id);
        $url = $ilCtrl->getLinkTarget($this, 'renderRecord');
        $out = ($this->prev_record_id) ? "<a href='{$url}'>{$prevStr}</a>" : "<span class='light'>{$prevStr}</span>";
        $out .= " | ";
        $ilCtrl->setParameter($this, 'record_id', $this->next_record_id);
        $url = $ilCtrl->getLinkTarget($this, 'renderRecord');
        $out .= ($this->next_record_id) ? "<a href='{$url}'>{$nextStr}</a>" : "<span class='light'>{$nextStr}</span>";

        return $out;
    }

    /**
     * Render select options
     */
    protected function renderSelectOptions() : string
    {
        $out = '';
        foreach ($this->record_ids as $k => $recId) {
            $selected = ($recId == $this->record_id) ? " selected" : "";
            $out .= "<option value='{$recId}'{$selected}>" . ($k + 1) . "</option>";
        }

        return $out;
    }

    /**
     * setOptions
     * string $link_name
     */
    private function setOptions(string $link_name) : array
    {
        $options = array();
        $options['link']['display'] = true;
        $options['link']['name'] = $link_name;

        return $options;
    }

    /**
     * If we come from a goto Link we need to build up the session data.
     */
    private function loadSession() : void
    {
        // We need the default sorting etc. to dertermine on which position we currently are, thus we instantiate the table gui.
        $list = new ilDclRecordListTableGUI(
            new ilDclRecordListGUI($this->dcl_gui_object, $this->table->getId()),
            "listRecords",
            $this->table,
            $this->tableview_id
        );
        //we then partially load the records. note that this also fills up session data.
        $this->table->getPartialRecords(
            $list->getOrderField(),
            $list->getOrderDirection(),
            $list->getLimit(),
            $list->getOffset(),
            $list->getFilter()
        );
    }

    /**
     * @return bool
     */
    protected function checkAccess() : bool
    {
        return ilObjDataCollectionAccess::hasAccessTo(
            filter_input(INPUT_GET, 'ref_id'),
            $this->table->getId(),
            $this->tableview_id
        )
            && ilDclDetailedViewDefinition::isActive($this->tableview_id);
    }
}
