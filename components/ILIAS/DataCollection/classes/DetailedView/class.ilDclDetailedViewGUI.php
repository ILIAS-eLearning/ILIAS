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
 * @ilCtrl_Calls ilDclDetailedViewGUI: ilDclDetailedViewDefinitionGUI, ilEditClipboardGUI
 */
class ilDclDetailedViewGUI
{
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $renderer;
    protected ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected ilObjDataCollectionGUI $dcl_gui_object;
    protected ilNoteGUI $notes_gui;
    protected ilDclTable $table;
    protected int $tableview_id;
    protected ilDclBaseRecordModel $record_obj;
    protected int $next_record_id = 0;
    protected int $prev_record_id = 0;
    protected int $current_record_position = 0;
    protected array $record_ids = [];
    protected bool $is_enabled_paging = true;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $main_tpl;

    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ?int $record_id;
    protected ilNoteGUI $notesGUI;
    protected ilDclBaseFieldModel $currentField;

    public function __construct(ilObjDataCollectionGUI $a_dcl_object, int $tableview_id)
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->dcl_gui_object = $a_dcl_object;
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        $this->record_id = null;
        if ($this->http->wrapper()->query()->has('record_id')) {
            $this->record_id = $this->http->wrapper()->query()->retrieve(
                'record_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($this->http->wrapper()->post()->has('record_id')) {
            $this->record_id = $this->http->wrapper()->post()->retrieve(
                'record_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $ref_id = $this->dcl_gui_object->getRefId();

        if ($this->record_id) {
            $this->record_obj = ilDclCache::getRecordCache($this->record_id);
            $this->table = $this->record_obj->getTable();
            if (!$this->record_obj->hasPermissionToView($ref_id)) {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_msg_no_perm_view'), true);
                $this->ctrl->redirectByClass(ilDclRecordListGUI::class, "show");
            }
        }

        // content style (using system defaults)
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $tpl->parseCurrentBlock();

        // Comments
        $repId = $this->dcl_gui_object->getDataCollectionObject()->getId();
        $objId = $this->record_id;
        $this->notesGUI = new ilNoteGUI($repId, $objId);
        $this->notesGUI->enablePublicNotes();
        $this->notesGUI->enablePublicNotesDeletion();
        $this->ctrl->setParameterByClass(ilNoteGUI::class, "record_id", $this->record_id);
        $this->ctrl->setParameterByClass(ilNoteGUI::class, "rep_id", $repId);

        $this->tableview_id = $tableview_id;

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

    public function executeCommand(): void
    {
        $this->ctrl->setParameter($this, 'tableview_id', $this->tableview_id);

        if (!$this->checkAccess()) {
            if ($this->table->getVisibleTableViews($this->dcl_gui_object->getRefId(), true)) {
                $this->offerAlternativeViews();
            } else {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            }

            return;
        }

        $cmd = $this->ctrl->getCmd();
        $cmdClass = $this->ctrl->getCmdClass();
        switch (strtolower($cmdClass)) {
            case 'ilnotegui':
                $this->notesGUI->executeCommand();
                break;
            default:
                $this->$cmd();
                break;
        }
    }

    protected function offerAlternativeViews(): void
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('dcl_msg_info_alternatives'));
        $table_gui = new ilDclTableViewTableGUI($this, 'renderRecord', $this->table, $this->dcl_gui_object->getRefId());
        $tpl->setContent($table_gui->getHTML());
    }

    public function renderRecord(bool $editComments = false): void
    {
        global $DIC;
        $ilTabs = $DIC->tabs();
        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();

        $rctpl = new ilDataCollectionGlobalTemplate("tpl.record_view.html", false, true, "components/ILIAS/DataCollection");

        $ilTabs->setTabActive("id_content");

        if (!$this->tableview_id) {
            $ilCtrl->redirectByClass(ilDclRecordListGUI::class, "listRecords");
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
                $html = preg_replace_callback($pattern, [$this, "doReplace"], $html);
            }

            $pattern = '/\[ext tableOf="' . preg_quote($field->getTitle(), "/") . '" field="(.*?)"\]/';
            if (preg_match($pattern, $html)) {
                $this->currentField = $field;
                $html = preg_replace_callback($pattern, [$this, "doExtReplace"], $html);
            }

            $html = str_ireplace(
                "[" . $field->getTitle() . "]",
                $this->record_obj->getRecordFieldSingleHTML($field->getId(), ['tableview_id' => $this->tableview_id]),
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
            $ilCtrl->setParameterByClass(ilDclRecordEditGUI::class, 'table_id', $this->table->getId());
            $ilCtrl->setParameterByClass(ilDclRecordEditGUI::class, 'tableview_id', $this->tableview_id);
            $ilCtrl->setParameterByClass(ilDclRecordEditGUI::class, 'redirect', ilDclRecordEditGUI::REDIRECT_DETAIL);
            $ilCtrl->saveParameterByClass(ilDclRecordEditGUI::class, 'record_id');
            $edit_button = $this->ui_factory->button()->standard(
                $this->lng->txt('dcl_edit_record'),
                $ilCtrl->getLinkTargetByClass(ilDclRecordEditGUI::class, 'edit')
            );
            $rctpl->setVariable('EDIT_RECORD_BUTTON', $this->renderer->render($edit_button));
        }

        // Comments
        if ($this->table->getPublicCommentsEnabled()) {
            $rctpl->setVariable('COMMENTS', $this->renderComments($editComments));
        }

        $tpl->setContent($rctpl->get());
    }

    public function doReplace(array $found): string
    {
        return $this->record_obj->getRecordFieldSingleHTML($this->currentField->getId(), $this->setOptions($found[1]));
    }

    public function doExtReplace(array $found): ?string
    {
        $ref_rec_ids = $this->record_obj->getRecordFieldValue($this->currentField->getId());
        if (!is_array($ref_rec_ids)) {
            $ref_rec_ids = [$ref_rec_ids];
        }
        if (!count($ref_rec_ids) || !$ref_rec_ids) {
            return null;
        }
        $ref_recs = [];
        foreach ($ref_rec_ids as $ref_rec_id) {
            $ref_recs[] = ilDclCache::getRecordCache($ref_rec_id);
        }
        $field = $ref_recs[0]->getTable()->getFieldByTitle($found[1]);

        $tpl = new ilTemplate("tpl.reference_list.html", true, true, "components/ILIAS/DataCollection");
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
        return $tpl->get();
    }

    protected function renderComments(bool $edit = false): string
    {
        if (!$edit) {
            return $this->notesGUI->getCommentsHTML();
        } else {
            return $this->notesGUI->editNoteForm();
        }
    }

    /**
     * Find the previous/next record from the current position. Also determine position of current record in whole set.
     */
    protected function determineNextPrevRecords(): void
    {
        if (!ilSession::has("dcl_record_ids") || ilSession::get('dcl_record_ids') != $this->table->getId()) {
            $this->loadSession();
        }

        if (ilSession::has("dcl_record_ids") && count(ilSession::get("dcl_record_ids"))) {
            $this->record_ids = ilSession::get("dcl_record_ids");
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
    protected function renderPrevNextLinks(): string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilCtrl->setParameter($this, 'tableview_id', $this->tableview_id);
        $prevStr = $this->lng->txt('dcl_prev_record');
        $nextStr = $this->lng->txt('dcl_next_record');
        $ilCtrl->setParameter($this, 'record_id', $this->prev_record_id);
        $url = $ilCtrl->getLinkTarget($this, 'renderRecord');
        $out = ($this->prev_record_id) ? "<a href='$url'>$prevStr</a>" : "<span class='light'>$prevStr</span>";
        $out .= " | ";
        $ilCtrl->setParameter($this, 'record_id', $this->next_record_id);
        $url = $ilCtrl->getLinkTarget($this, 'renderRecord');
        $out .= ($this->next_record_id) ? "<a href='$url'>$nextStr</a>" : "<span class='light'>$nextStr</span>";

        return $out;
    }

    /**
     * Render select options
     */
    protected function renderSelectOptions(): string
    {
        $out = '';
        foreach ($this->record_ids as $k => $recId) {
            $selected = ($recId == $this->record_id) ? " selected" : "";
            $out .= "<option value='$recId'$selected>" . ($k + 1) . "</option>";
        }

        return $out;
    }

    /**
     * setOptions
     * string $link_name
     */
    private function setOptions(string $link_name): array
    {
        $options = [];
        $options['link']['display'] = true;
        $options['link']['name'] = $link_name;

        return $options;
    }

    /**
     * If we come from a goto Link we need to build up the session data.
     */
    private function loadSession(): void
    {
        // We need the default sorting etc. to dertermine on which position we currently are, thus we instantiate the table gui.
        $list = new ilDclRecordListTableGUI(
            new ilDclRecordListGUI($this->dcl_gui_object, $this->table->getId(), $this->tableview_id),
            "listRecords",
            $this->table,
            $this->tableview_id
        );
        //we then partially load the records. note that this also fills up session data.
        $this->table->getPartialRecords(
            (string)$this->table->getId(),
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
    protected function checkAccess(): bool
    {
        $ref_id = $this->dcl_gui_object->getRefId();
        $has_accass = ilObjDataCollectionAccess::hasAccessTo($ref_id, $this->table->getId(), $this->tableview_id);
        $is_active = ilDclDetailedViewDefinition::isActive($this->tableview_id);
        return $has_accass && $is_active;
    }
}
