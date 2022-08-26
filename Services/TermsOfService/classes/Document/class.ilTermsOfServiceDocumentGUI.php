<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilTermsOfServiceDocumentGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentGUI implements ilTermsOfServiceControllerEnabled
{
    public function __construct(
        protected ilObjTermsOfService $tos,
        protected ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        protected ilGlobalTemplateInterface $tpl,
        protected ilObjUser $user,
        protected ilCtrlInterface $ctrl,
        protected ilLanguage $lng,
        protected ilRbacSystem $rbacsystem,
        protected ilErrorHandling $error,
        protected ilLogger $log,
        protected ilToolbarGUI $toolbar,
        protected GlobalHttpState $httpState,
        protected Factory $uiFactory,
        protected Renderer $uiRenderer,
        protected Filesystems $fileSystems,
        protected FileUpload $fileUpload,
        protected ilTermsOfServiceTableDataProviderFactory $tableDataProviderFactory,
        protected ilHtmlPurifierInterface $documentPurifier,
        protected Refinery $refinery
    ) {
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        if (!$this->rbacsystem->checkAccess('read', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if ($cmd === null || $cmd === '' || !method_exists($this, $cmd)) {
            $cmd = 'showDocuments';
        }
        $this->$cmd();
    }

    protected function confirmReset(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmReset'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'reset');
        $confirmation->setCancel($this->lng->txt('cancel'), 'showDocuments');
        $confirmation->setHeaderText($this->lng->txt('tos_sure_reset_tos'));

        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function reset(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tos->resetAll();

        $this->log->info('Terms of service reset by ' . $this->user->getId() . ' [' . $this->user->getLogin() . ']');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tos_reset_successful'));

        $this->showDocuments();
    }

    protected function showDocuments(): void
    {
        if ($this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $addDocumentBtn = ilLinkButton::getInstance();
            $addDocumentBtn->setPrimary(true);
            $addDocumentBtn->setUrl($this->ctrl->getLinkTarget($this, 'showAddDocumentForm'));
            $addDocumentBtn->setCaption('tos_add_document_btn_label');
            $this->toolbar->addStickyItem($addDocumentBtn);
        }

        $documentTableGui = new ilTermsOfServiceDocumentTableGUI(
            $this,
            'showDocuments',
            $this->criterionTypeFactory,
            $this->uiFactory,
            $this->uiRenderer,
            $this->rbacsystem->checkAccess('write', $this->tos->getRefId())
        );
        $documentTableGui->setProvider($this->tableDataProviderFactory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_DOCUMENTS));
        $documentTableGui->populate();

        $this->tpl->setCurrentBlock('mess');
        $this->tpl->setVariable('MESSAGE', $this->getResetMessageBoxHtml());
        $this->tpl->parseCurrentBlock('mess');
        $this->tpl->setContent($documentTableGui->getHTML());
    }

    protected function getResetMessageBoxHtml(): string
    {
        if (((int) $this->tos->getLastResetDate()->get(IL_CAL_UNIX)) !== 0) {
            $status = ilDatePresentation::useRelativeDates();
            ilDatePresentation::setUseRelativeDates(false);
            $resetText = sprintf(
                $this->lng->txt('tos_last_reset_date'),
                ilDatePresentation::formatDate($this->tos->getLastResetDate())
            );
            ilDatePresentation::setUseRelativeDates($status);
        } else {
            $resetText = $this->lng->txt('tos_never_reset');
        }

        $buttons = [];
        if ($this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $buttons = [
                $this->uiFactory
                    ->button()
                    ->standard(
                        $this->lng->txt('tos_reset_tos_for_all_users'),
                        $this->ctrl->getLinkTarget($this, 'confirmReset')
                    )
            ];
        }

        return $this->uiRenderer->render(
            $this->uiFactory->messageBox()
                            ->info($resetText)
                            ->withButtons($buttons)
        );
    }

    protected function getDocumentForm(ilTermsOfServiceDocument $document): ilTermsOfServiceDocumentFormGUI
    {
        if ($document->getId() > 0) {
            $this->ctrl->setParameter($this, 'tos_id', $document->getId());
        }

        $formAction = $this->ctrl->getFormAction($this, 'saveAddDocumentForm');
        $saveCommand = 'saveAddDocumentForm';

        if ($document->getId() > 0) {
            $formAction = $this->ctrl->getFormAction($this, 'saveEditDocumentForm');
            $saveCommand = 'saveEditDocumentForm';
        }

        return new ilTermsOfServiceDocumentFormGUI(
            $document,
            $this->documentPurifier,
            $this->user,
            $this->fileSystems->temp(),
            $this->fileUpload,
            $formAction,
            $saveCommand,
            'showDocuments',
            $this->rbacsystem->checkAccess('write', $this->tos->getRefId())
        );
    }

    protected function saveAddDocumentForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm(new ilTermsOfServiceDocument());
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            if ($form->hasTranslatedInfo()) {
                $this->tpl->setOnScreenMessage('info', $form->getTranslatedInfo(), true);
            }
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function showAddDocumentForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm(new ilTermsOfServiceDocument());
        $this->tpl->setContent($form->getHTML());
    }

    protected function showEditDocumentForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getDocumentForm($document);
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveEditDocumentForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getDocumentForm($document);
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            if ($form->hasTranslatedInfo()) {
                $this->tpl->setOnScreenMessage('info', $form->getTranslatedInfo(), true);
            }
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @return ilTermsOfServiceDocument[]
     */
    protected function getDocumentsByServerRequest(): array
    {
        $documentIds = [];

        if ($this->httpState->wrapper()->post()->has('tos_id')) {
            $documentIds = $this->httpState->wrapper()->post()->retrieve(
                'tos_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if ($documentIds === [] && $this->httpState->wrapper()->query()->has('tos_id')) {
            $documentIds = [$this->httpState->wrapper()->query()->retrieve(
                'tos_id',
                $this->refinery->kindlyTo()->int()
            )];
        }

        if ($documentIds === []) {
            return $documentIds;
        }

        return ilTermsOfServiceDocument::where(
            ['id' => array_filter(array_map('intval', $documentIds))],
            ['id' => 'IN']
        )->getArray();
    }

    protected function getFirstDocumentFromList(array $documents): ilTermsOfServiceDocument
    {
        if (1 !== count($documents)) {
            throw new UnexpectedValueException('Expected exactly one document in list');
        }

        $document = new ilTermsOfServiceDocument(0);
        $document = $document->buildFromArray(current($documents));

        return $document;
    }

    protected function deleteDocuments(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $documents = $this->getDocumentsByServerRequest();
        if ([] === $documents) {
            $this->showDocuments();
            return;
        }

        $documents = array_map(static function (array $data): ilTermsOfServiceDocument {
            $document = new ilTermsOfServiceDocument(0);
            $document = $document->buildFromArray($data);

            return $document;
        }, $documents);

        $isDeletionRequest = (bool) ($this->httpState->request()->getQueryParams()['delete'] ?? false);
        if ($isDeletionRequest) {
            $this->processDocumentDeletion($documents);

            $this->ctrl->redirect($this);
        } else {
            $this->ctrl->setParameter($this, 'delete', 1);
            $confirmation = new ilConfirmationGUI();
            $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteDocuments'));
            $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteDocuments');
            $confirmation->setCancel($this->lng->txt('cancel'), 'showDocuments');

            $confirmation->setHeaderText($this->lng->txt('tos_sure_delete_documents_p'));
            if (1 === count($documents)) {
                $confirmation->setHeaderText($this->lng->txt('tos_sure_delete_documents_s'));
            }

            foreach ($documents as $document) {
                /** @var ilTermsOfServiceDocument $document */
                $confirmation->addItem('tos_id[]', (string) $document->getId(), implode(' | ', [
                    $document->getTitle()
                ]));
            }

            $this->tpl->setContent($confirmation->getHTML());
        }
    }

    protected function processDocumentDeletion(array $documents): void
    {
        foreach ($documents as $document) {
            /** @var ilTermsOfServiceDocument $document */
            $document->delete();
        }

        if (0 === ilTermsOfServiceDocument::getCollection()->count()) {
            $this->tos->saveStatus(false);
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tos_disabled_no_docs_left'), true);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tos_deleted_documents_p'), true);
        if (1 === count($documents)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('tos_deleted_documents_s'), true);
        }
    }

    protected function deleteDocument(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $this->processDocumentDeletion([$document]);

        $this->ctrl->redirect($this);
    }

    protected function saveDocumentSorting(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $sorting = $this->httpState->request()->getParsedBody()['sorting'] ?? [];
        if (!is_array($sorting) || [] === $sorting) {
            $this->showDocuments();
            return;
        }

        asort($sorting, SORT_NUMERIC);

        $position = 0;
        foreach (array_keys($sorting) as $documentId) {
            if (!is_numeric($documentId)) {
                continue;
            }

            try {
                $document = new ilTermsOfServiceDocument((int) $documentId);
                $document->setSorting(++$position);
                $document->store();
            } catch (ilException) {
                // Empty catch block
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tos_saved_sorting'), true);
        $this->ctrl->redirect($this);
    }

    protected function getCriterionForm(
        ilTermsOfServiceDocument $document,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment
    ): ilTermsOfServiceCriterionFormGUI {
        $this->ctrl->setParameter($this, 'tos_id', $document->getId());

        if ($criterionAssignment->getId() > 0) {
            $this->ctrl->setParameter($this, 'crit_id', $criterionAssignment->getId());
        }

        $formAction = $this->ctrl->getFormAction($this, 'saveAttachCriterionForm');
        $saveCommand = 'saveAttachCriterionForm';

        if ($criterionAssignment->getId() > 0) {
            $formAction = $this->ctrl->getFormAction($this, 'saveChangeCriterionForm');
            $saveCommand = 'saveChangeCriterionForm';
        }

        return new ilTermsOfServiceCriterionFormGUI(
            $document,
            $criterionAssignment,
            $this->criterionTypeFactory,
            $this->user,
            $formAction,
            $saveCommand,
            'showDocuments'
        );
    }

    protected function saveAttachCriterionForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getCriterionForm($document, new ilTermsOfServiceDocumentCriterionAssignment());
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('tos_doc_crit_attached'), true);
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function showAttachCriterionForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getCriterionForm($document, new ilTermsOfServiceDocumentCriterionAssignment());
        $this->tpl->setContent($form->getHTML());
    }

    protected function showChangeCriterionForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $criterionId = $this->httpState->request()->getQueryParams()['crit_id'] ?? 0;
        if (!is_numeric($criterionId) || $criterionId < 1) {
            $this->showDocuments();
            return;
        }

        $criterionAssignment = array_values(array_filter(
            $document->criteria(),
            static function (ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment) use (
                $criterionId
            ): bool {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $form = $this->getCriterionForm($document, $criterionAssignment);
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveChangeCriterionForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $criterionId = $this->httpState->request()->getQueryParams()['crit_id'] ?? 0;
        if (!is_numeric($criterionId) || $criterionId < 1) {
            $this->showDocuments();
            return;
        }

        $criterionAssignment = array_values(array_filter(
            $document->criteria(),
            static function (ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment) use (
                $criterionId
            ): bool {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $form = $this->getCriterionForm($document, $criterionAssignment);
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('tos_doc_crit_changed'), true);
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    public function detachCriterionAssignment(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $criterionId = $this->httpState->request()->getQueryParams()['crit_id'] ?? 0;
        if (!is_numeric($criterionId) || $criterionId < 1) {
            $this->showDocuments();
            return;
        }

        $criterionAssignment = array_values(array_filter(
            $document->criteria(),
            static function (ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment) use (
                $criterionId
            ): bool {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $document->detachCriterion($criterionAssignment);
        $document->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('tos_doc_crit_detached'), true);
        $this->ctrl->redirect($this, 'showDocuments');
    }
}
