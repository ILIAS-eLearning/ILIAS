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

use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Services;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilAccessibilityDocumentGUI
 */
class ilAccessibilityDocumentGUI implements ilAccessibilityControllerEnabled
{
    protected ilAccessibilityTableDataProviderFactory $tableDataProviderFactory;
    protected ilObjAccessibilitySettings $accs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;
    protected ilErrorHandling $error;
    protected ilObjUser $user;
    protected ilLogger $log;
    protected Factory $uiFactory;
    protected Renderer $uiRenderer;
    protected ILIAS\HTTP\Services $httpState;
    protected ilToolbarGUI $toolbar;
    protected FileUpload $fileUpload;
    protected Filesystems $fileSystems;
    protected ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory;
    protected ilHtmlPurifierInterface $documentPurifier;

    public function __construct(
        ilObjAccessibilitySettings $accs,
        ilAccessibilityCriterionTypeFactoryInterface $criterionTypeFactory,
        ilGlobalTemplateInterface $tpl,
        ilObjUser $user,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilRbacSystem $rbacsystem,
        ilErrorHandling $error,
        ilLogger $log,
        ilToolbarGUI $toolbar,
        Services $httpState,
        Factory $uiFactory,
        Renderer $uiRenderer,
        Filesystems $fileSystems,
        FileUpload $fileUpload,
        ilAccessibilityTableDataProviderFactory $tableDataProviderFactory,
        ilHtmlPurifierInterface $documentPurifier
    ) {
        $this->accs = $accs;
        $this->criterionTypeFactory = $criterionTypeFactory;
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->rbacsystem = $rbacsystem;
        $this->error = $error;
        $this->user = $user;
        $this->log = $log;
        $this->toolbar = $toolbar;
        $this->httpState = $httpState;
        $this->uiFactory = $uiFactory;
        $this->uiRenderer = $uiRenderer;
        $this->fileSystems = $fileSystems;
        $this->fileUpload = $fileUpload;
        $this->tableDataProviderFactory = $tableDataProviderFactory;
        $this->documentPurifier = $documentPurifier;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        if (!$this->rbacsystem->checkAccess('read', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if ($cmd == '' || !method_exists($this, $cmd)) {
            $cmd = 'showDocuments';
        }
        $this->$cmd();
    }


    /**
     * @throws ilAccessibilityMissingDatabaseAdapterException
     * @throws ilCtrlException
     */
    protected function showDocuments(): void
    {
        if ($this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $addDocumentBtn = $this->uiFactory->button()->primary(
                $this->lng->txt('acc_add_document_btn_label'),
                $this->ctrl->getLinkTarget($this, 'showAddDocumentForm')
            );
            $this->toolbar->addStickyItem($addDocumentBtn);
        }

        $documentTableGui = new ilAccessibilityDocumentTableGUI(
            $this,
            'showDocuments',
            $this->criterionTypeFactory,
            $this->uiFactory,
            $this->uiRenderer,
            $this->rbacsystem->checkAccess('write', $this->accs->getRefId())
        );
        $documentTableGui->setProvider($this->tableDataProviderFactory->getByContext(ilAccessibilityTableDataProviderFactory::CONTEXT_DOCUMENTS));
        $documentTableGui->populate();

        $this->tpl->setContent($documentTableGui->getHTML());
    }

    /**
     * @throws ilCtrlException
     */
    protected function getDocumentForm(ilAccessibilityDocument $document): ilAccessibilityDocumentFormGUI
    {
        if ($document->getId() > 0) {
            $this->ctrl->setParameter($this, 'acc_id', $document->getId());
        }

        $formAction = $this->ctrl->getFormAction($this, 'saveAddDocumentForm');
        $saveCommand = 'saveAddDocumentForm';

        if ($document->getId() > 0) {
            $formAction = $this->ctrl->getFormAction($this, 'saveEditDocumentForm');
            $saveCommand = 'saveEditDocumentForm';
        }

        $form = new ilAccessibilityDocumentFormGUI(
            $document,
            $this->documentPurifier,
            $this->user,
            $this->fileSystems->temp(),
            $this->fileUpload,
            $formAction,
            $saveCommand,
            'showDocuments',
            $this->rbacsystem->checkAccess('write', $this->accs->getRefId())
        );

        return $form;
    }

    protected function saveAddDocumentForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm(new ilAccessibilityDocument());
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
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm(new ilAccessibilityDocument());
        $this->tpl->setContent($form->getHTML());
    }

    protected function showEditDocumentForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getDocumentForm($document);
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveEditDocumentForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
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
     * @return ilAccessibilityDocument[]
     */
    protected function getDocumentsByServerRequest(): array
    {
        $documents = [];

        $documentIds = $this->httpState->request()->getParsedBody()['acc_id'] ?? [];
        if (!is_array($documentIds) || 0 === count($documentIds)) {
            $documentIds = $this->httpState->request()->getQueryParams()['acc_id'] ? [$this->httpState->request()->getQueryParams()['acc_id']] : [];
        }

        if (0 === count($documentIds)) {
            return $documents;
        }

        $documents = ilAccessibilityDocument::where(
            ['id' => array_filter(array_map('intval', $documentIds))],
            ['id' => 'IN']
        )->getArray();

        return $documents;
    }

    /**
     * @param array $documents
     * @return ilAccessibilityDocument
     * @throws UnexpectedValueException
     */
    protected function getFirstDocumentFromList(array $documents): ilAccessibilityDocument
    {
        if (1 !== count($documents)) {
            throw new UnexpectedValueException('Expected exactly one document in list');
        }

        $document = new ilAccessibilityDocument(0);
        /** @var ilAccessibilityDocument $document */
        $document = $document->buildFromArray(current($documents));

        return $document;
    }

    protected function deleteDocuments(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $documents = $this->getDocumentsByServerRequest();
        if (0 === count($documents)) {
            $this->showDocuments();
            return;
        } else {
            $documents = array_map(function (array $data) {
                $document = new ilAccessibilityDocument(0);
                $document = $document->buildFromArray($data);

                return $document;
            }, $documents);
        }

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

            $confirmation->setHeaderText($this->lng->txt('acc_sure_delete_documents_p'));
            if (1 === count($documents)) {
                $confirmation->setHeaderText($this->lng->txt('acc_sure_delete_documents_s'));
            }

            foreach ($documents as $document) {
                /** @var $document ilAccessibilityDocument */
                $confirmation->addItem('acc_id[]', $document->getId(), implode(' | ', [
                    $document->getTitle()
                ]));
            }

            $this->tpl->setContent($confirmation->getHTML());
        }
    }

    protected function processDocumentDeletion(array $documents): void
    {
        foreach ($documents as $document) {
            /** @var $document ilAccessibilityDocument */
            $document->delete();
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('acc_deleted_documents_p'), true);
        if (1 === count($documents)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('acc_deleted_documents_s'), true);
        }
    }

    protected function deleteDocument(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $this->processDocumentDeletion([$document]);

        $this->ctrl->redirect($this);
    }

    protected function saveDocumentSorting(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $sorting = $this->httpState->request()->getParsedBody()['sorting'] ?? [];
        if (!is_array($sorting) || 0 === count($sorting)) {
            $this->showDocuments();
            return;
        }

        asort($sorting, SORT_NUMERIC);

        $position = 0;
        foreach ($sorting as $documentId => $ignoredSortValue) {
            if (!is_numeric($documentId)) {
                continue;
            }

            try {
                $document = new ilAccessibilityDocument((int) $documentId);
                $document->setSorting(++$position);
                $document->store();
            } catch (ilException $e) {
                // Empty catch block
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('acc_saved_sorting'), true);
        $this->ctrl->redirect($this);
    }

    /**
     * @throws ilCtrlException
     */
    protected function getCriterionForm(
        ilAccessibilityDocument $document,
        ilAccessibilityDocumentCriterionAssignment $criterionAssignment
    ): ilAccessibilityCriterionFormGUI {
        $this->ctrl->setParameter($this, 'acc_id', $document->getId());

        if ($criterionAssignment->getId() > 0) {
            $this->ctrl->setParameter($this, 'crit_id', $criterionAssignment->getId());
        }

        $formAction = $this->ctrl->getFormAction($this, 'saveAttachCriterionForm');
        $saveCommand = 'saveAttachCriterionForm';

        if ($criterionAssignment->getId() > 0) {
            $formAction = $this->ctrl->getFormAction($this, 'saveChangeCriterionForm');
            $saveCommand = 'saveChangeCriterionForm';
        }

        $form = new ilAccessibilityCriterionFormGUI(
            $document,
            $criterionAssignment,
            $this->criterionTypeFactory,
            $this->user,
            $formAction,
            $saveCommand,
            'showDocuments'
        );

        return $form;
    }

    protected function saveAttachCriterionForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getCriterionForm($document, new ilAccessibilityDocumentCriterionAssignment());
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('acc_doc_crit_attached'), true);
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function showAttachCriterionForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getCriterionForm($document, new ilAccessibilityDocumentCriterionAssignment());
        $this->tpl->setContent($form->getHTML());
    }

    protected function showChangeCriterionForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
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
            function (ilAccessibilityDocumentCriterionAssignment $criterionAssignment) use ($criterionId) {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $form = $this->getCriterionForm($document, $criterionAssignment);
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveChangeCriterionForm(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
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
            function (ilAccessibilityDocumentCriterionAssignment $criterionAssignment) use ($criterionId) {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $form = $this->getCriterionForm($document, $criterionAssignment);
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('acc_doc_crit_changed'), true);
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    public function detachCriterionAssignment(): void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->accs->getRefId())) {
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
            function (ilAccessibilityDocumentCriterionAssignment $criterionAssignment) use ($criterionId) {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $document->detachCriterion($criterionAssignment);
        $document->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('acc_doc_crit_detached'), true);
        $this->ctrl->redirect($this, 'showDocuments');
    }
}
