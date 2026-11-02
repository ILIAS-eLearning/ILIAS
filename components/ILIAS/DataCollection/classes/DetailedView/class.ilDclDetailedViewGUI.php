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

use ILIAS\HTTP\Services as HTTP;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\ResourceStorage\Services as RS;
use ILIAS\UI\Factory;

/**
 * @ilCtrl_Calls ilDclDetailedViewGUI: ilDclDetailedViewDefinitionGUI, ilEditClipboardGUI, ilCommentGUI
 */
class ilDclDetailedViewGUI
{
    protected Factory $ui_factory;
    protected ilObjDataCollectionGUI $dcl_gui_object;
    protected ilDclTable $table;
    protected int $tableview_id;
    protected ilDclBaseRecordModel $record_obj;
    protected bool $is_enabled_paging = true;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $main_tpl;

    protected HTTP $http;
    protected Refinery $refinery;
    protected ?int $record_id;
    protected ilCommentGUI $commentGUI;
    protected ilDclBaseFieldModel $currentField;
    private RS $irss;
    private ilHelpGUI $help;
    private ilToolbarGUI $toolbar;
    private ilTabsGUI $tabs;

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
        $this->irss = $DIC->resourceStorage();
        $this->help = $DIC->help();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();

        if (
            !$this->http->wrapper()->query()->has('table_id') ||
            !$this->http->wrapper()->query()->has('tableview_id')
        ) {
            $this->main_tpl->setOnScreenMessage($this->main_tpl::MESSAGE_TYPE_FAILURE, $this->lng->txt('table_not_found'), true);
            $this->ctrl->redirectByClass(ilDclRecordListGUI::class, "show");
        }

        $this->tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id', $this->refinery->kindlyTo()->int());
        $table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        $this->table = ilDclCache::getTableCache($table_id);
        $this->loadSession();

        $this->record_id = null;
        if ($this->http->wrapper()->query()->has('record_id')) {
            $this->record_id = $this->http->wrapper()->query()->retrieve(
                'record_id',
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->http->wrapper()->query()->has('record_pos')) {
            $record_ids = ilSession::get('dcl_record_ids');
            $pos = $this->http->wrapper()->query()->retrieve('record_pos', $this->refinery->kindlyTo()->int());
            if ($record_ids === [] || !array_key_exists($pos, $record_ids)) {
                $this->ctrl->redirectToURL($this->http->request()->getServerParams()['HTTP_REFERER']);
            }
            $this->record_id = $record_ids[$pos];
        }
        if ($this->http->wrapper()->post()->has('record_id')) {
            $this->record_id = $this->http->wrapper()->post()->retrieve(
                'record_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        if ($this->record_id) {
            $this->record_obj = ilDclCache::getRecordCache($this->record_id);
            if (!$this->record_obj->hasPermissionToView($this->dcl_gui_object->getRefId())) {
                $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_msg_no_perm_view'), true);
                $this->ctrl->redirectByClass(ilDclRecordListGUI::class, "show");
            }
        }

        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $tpl->parseCurrentBlock();

        $repId = $this->dcl_gui_object->getDataCollectionObject()->getId();
        $objId = $this->record_id;
        $this->commentGUI = new ilCommentGUI($repId, $objId);
        $this->commentGUI->enablePublicNotes();
        $this->commentGUI->enablePublicNotesDeletion();
        $this->ctrl->setParameterByClass(ilCommentGUI::class, "record_id", $this->record_id);
        $this->ctrl->setParameterByClass(ilCommentGUI::class, "rep_id", $repId);

        $this->tableview_id = $tableview_id;

        if ($this->http->wrapper()->query()->has('disable_paging')
            && $this->http->wrapper()->query()->retrieve('disable_paging', $this->refinery->kindlyTo()->bool())) {
            $this->is_enabled_paging = false;
        }
    }

    public function executeCommand(): void
    {
        $this->ctrl->setParameter($this, 'table_id', $this->table->getId());
        $this->ctrl->setParameter($this, 'tableview_id', $this->tableview_id);

        if (!$this->checkAccess()) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            return;
        }

        $cmd = $this->ctrl->getCmd('renderRecord');
        $cmdClass = $this->ctrl->getCmdClass();
        switch (strtolower($cmdClass)) {
            case 'ilcommentgui':
                $this->commentGUI->executeCommand();
                break;
            default:
                $this->$cmd();
                break;
        }
    }

    public function jumpToRecord(): void
    {
        $this->ctrl->setParameterByClass(self::class, 'record_id', $this->record_id);
        $this->ctrl->redirectByClass(self::class, 'renderRecord');
    }

    public function renderRecord(bool $editComments = false): void
    {
        if (!(new ilDclDetailedViewDefinitionGUI($this->tableview_id))->getPageObject()->isActive()) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            return;
        }

        if (!$this->tableview_id) {
            $this->ctrl->redirectByClass(ilDclRecordListGUI::class, "listRecords");
        }

        $this->help->setScreenId('dcl_record');
        $this->tabs->clearTargets();

        $pageObj = new ilDclDetailedViewDefinitionGUI($this->tableview_id);
        $pageObj->setOutputMode($pageObj::PRESENTATION);
        $pageObj->setRecord($this->record_obj);
        $html = $pageObj->showPage();

        $this->main_tpl->setPermanentLink(
            'dcl',
            filter_input(INPUT_GET, 'ref_id', FILTER_VALIDATE_INT),
            $this->table->getId() . '_' . $this->tableview_id . '_' . $this->record_obj->getId()
        );

        if ($this->is_enabled_paging) {
            $record_ids = ilSession::get('dcl_record_ids');
            if ($record_ids !== [] && in_array($this->record_id, $record_ids)) {
                $this->toolbar->addComponent(
                    $this->ui_factory->viewControl()->pagination()
                        ->withTargetURL($this->ctrl->getLinkTargetByClass(self::class, 'jumpToRecord'), 'record_pos')
                        ->withPageSize(1)
                        ->withDropdownAt(5)
                        ->withDropdownLabel($this->lng->txt('entry_of'))
                        ->withCurrentPage(array_search($this->record_id, $record_ids))
                        ->withTotalEntries(count($record_ids))
                        ->withMaxPaginationButtons(20)
                );
            }
        }

        if ($this->record_obj->hasPermissionToEdit($this->dcl_gui_object->getRefId())) {
            $this->toolbar->addSpacer('100%');
            $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, 'table_id', $this->table->getId());
            $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, 'tableview_id', $this->tableview_id);
            $this->ctrl->setParameterByClass(ilDclRecordEditGUI::class, 'redirect', ilDclRecordEditGUI::REDIRECT_DETAIL);
            $this->ctrl->saveParameterByClass(ilDclRecordEditGUI::class, 'record_id');
            $this->toolbar->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('edit'),
                $this->ctrl->getLinkTargetByClass(ilDclRecordEditGUI::class, 'edit')
            ));
        }

        $comments = $this->table->getPublicCommentsEnabled() ? $this->renderComments($editComments) : '';

        $this->main_tpl->setContent($html . $comments);
    }

    public function sendFile(): void
    {
        $rec_id = $this->http->wrapper()->query()->retrieve('record_id', $this->refinery->kindlyTo()->int());

        $record = ilDclCache::getRecordCache($rec_id);

        if ($this->dcl_gui_object->getObjectId() != $record->getTable()->getCollectionObject()->getId()) {
            return;
        }

        $field_id = $this->http->wrapper()->query()->retrieve('field_id', $this->refinery->kindlyTo()->string());
        $rid_string = $record->getRecordFieldValue($field_id);
        $identification = $this->irss->manage()->find($rid_string);
        if ($identification === null) {
            return;
        }
        $current_revision = $this->irss->manage()->getCurrentRevision($identification);
        $this->irss->consume()->download($identification)->overrideFileName($current_revision->getTitle())->run();
    }

    protected function renderComments(bool $edit = false): string
    {
        if (!$edit) {
            return $this->commentGUI->getListHTML();
        } else {
            return $this->commentGUI->addNoteForm();
        }
    }

    private function loadSession(): void
    {
        $list = new ilDclRecordListTableGUI(
            new ilDclRecordListGUI($this->dcl_gui_object, $this->table->getId(), $this->tableview_id),
            "listRecords",
            $this->table,
            $this->tableview_id
        );
        $list->initFilter();
        $list->determineOffsetAndOrder();
        $this->table->getPartialRecords(
            (string) $this->table->getId(),
            $list->getOrderField(),
            $list->getOrderDirection(),
            $list->getLimit(),
            $list->getOffset(),
            $list->getFilter()
        );
    }

    protected function checkAccess(): bool
    {
        return ilObjDataCollectionAccess::hasAccessTo($this->dcl_gui_object->getRefId(), $this->table->getId(), $this->tableview_id);
    }
}
