<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilTermsOfServiceDocumentGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentGUI implements \ilTermsOfServiceControllerEnabled
{
    /** @var \ilTermsOfServiceTableDataProviderFactory */
    protected $tableDataProviderFactory;

    /** @var \ilObjTermsOfService */
    protected $tos;

    /** @var \ilTemplate */
    protected $tpl;

    /** @var \ilCtrl */
    protected $ctrl;

    /** @var \ilLanguage */
    protected $lng;

    /** @var \ilRbacSystem */
    protected $rbacsystem;

    /** @var \ilErrorHandling */
    protected $error;

    /** @var \ilObjUser */
    protected $user;

    /** @var \ilLogger */
    protected $log;

    /** @var Factory */
    protected $uiFactory;

    /** @var Renderer */
    protected $uiRenderer;

    /** @var ILIAS\HTTP\GlobalHttpState */
    protected $httpState;

    /** @var \ilToolbarGUI */
    protected $toolbar;

    /** @var FileUpload */
    protected $fileUpload;
    
    /** @var Filesystems */
    protected $fileSystems;

    /** @var \ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /** @var \ilHtmlPurifierInterface */
    protected $documentPurifier;

    /**
     * ilTermsOfServiceDocumentGUI constructor.
     * @param \ilObjTermsOfService $tos
     * @param ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
     * @param \ilTemplate $tpl
     * @param \ilObjUser $user
     * @param \ilCtrl $ctrl
     * @param \ilLanguage $lng
     * @param \ilRbacSystem $rbacsystem
     * @param \ilErrorHandling $error
     * @param \ilLogger $log
     * @param \ilToolbarGUI $toolbar
     * @param GlobalHttpState $httpState
     * @param Factory $uiFactory
     * @param Renderer $uiRenderer
     * @param Filesystems $fileSystems ,
     * @param FileUpload $fileUpload
     * @param \ilTermsOfServiceTableDataProviderFactory $tableDataProviderFactory
     * @param \ilHtmlPurifierInterface $documentPurifier
     */
    public function __construct(
        \ilObjTermsOfService $tos,
        \ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        \ilTemplate $tpl,
        \ilObjUser $user,
        \ilCtrl $ctrl,
        \ilLanguage $lng,
        \ilRbacSystem $rbacsystem,
        \ilErrorHandling $error,
        \ilLogger $log,
        \ilToolbarGUI $toolbar,
        GlobalHttpState $httpState,
        Factory $uiFactory,
        Renderer $uiRenderer,
        Filesystems $fileSystems,
        FileUpload $fileUpload,
        ilTermsOfServiceTableDataProviderFactory $tableDataProviderFactory,
        \ilHtmlPurifierInterface $documentPurifier
    ) {
        $this->tos                      = $tos;
        $this->criterionTypeFactory     = $criterionTypeFactory;
        $this->tpl                      = $tpl;
        $this->ctrl                     = $ctrl;
        $this->lng                      = $lng;
        $this->rbacsystem               = $rbacsystem;
        $this->error                    = $error;
        $this->user                     = $user;
        $this->log                      = $log;
        $this->toolbar                  = $toolbar;
        $this->httpState                = $httpState;
        $this->uiFactory                = $uiFactory;
        $this->uiRenderer               = $uiRenderer;
        $this->fileSystems              = $fileSystems;
        $this->fileUpload               = $fileUpload;
        $this->tableDataProviderFactory = $tableDataProviderFactory;
        $this->documentPurifier = $documentPurifier;
    }

    /**
     *
     */
    public function executeCommand()
    {
        $nextClass = $this->ctrl->getNextClass($this);
        $cmd       = $this->ctrl->getCmd();

        if (!$this->rbacsystem->checkAccess('read', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        switch (strtolower($nextClass)) {
            default:
                if ($cmd == '' || !method_exists($this, $cmd)) {
                    $cmd = 'showDocuments';
                }
                $this->$cmd();
                break;
        }
    }

    /**
     *
     */
    protected function confirmReset()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmReset'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'reset');
        $confirmation->setCancel($this->lng->txt('cancel'), 'showDocuments');
        $confirmation->setHeaderText($this->lng->txt('tos_sure_reset_tos'));

        $this->tpl->setContent($confirmation->getHTML());
    }

    /**
     *
     */
    protected function reset()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tos->resetAll();

        $this->log->info('Terms of service reset by ' . $this->user->getId() . ' [' . $this->user->getLogin() . ']');
        \ilUtil::sendSuccess($this->lng->txt('tos_reset_successful'));

        $this->showDocuments();
    }

    /**
     *
     */
    protected function showDocuments()
    {
        if ($this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $addDocumentBtn = \ilLinkButton::getInstance();
            $addDocumentBtn->setPrimary(true);
            $addDocumentBtn->setUrl($this->ctrl->getLinkTarget($this, 'showAddDocumentForm'));
            $addDocumentBtn->setCaption('tos_add_document_btn_label');
            $this->toolbar->addStickyItem($addDocumentBtn);
        }

        $documentTableGui = new \ilTermsOfServiceDocumentTableGUI(
            $this,
            'showDocuments',
            $this->criterionTypeFactory,
            $this->uiFactory,
            $this->uiRenderer,
            $this->rbacsystem->checkAccess('write', $this->tos->getRefId())
        );
        $documentTableGui->setProvider($this->tableDataProviderFactory->getByContext(\ilTermsOfServiceTableDataProviderFactory::CONTEXT_DOCUMENTS));
        $documentTableGui->populate();

        $this->tpl->setCurrentBlock('mess');
        $this->tpl->setVariable('MESSAGE', $this->getResetMessageBoxHtml());
        $this->tpl->parseCurrentBlock('mess');
        $this->tpl->setContent($documentTableGui->getHTML());
    }

    /**
     * @return string
     */
    protected function getResetMessageBoxHtml() : string
    {
        if ($this->tos->getLastResetDate() && $this->tos->getLastResetDate()->get(IL_CAL_UNIX) != 0) {
            $status = \ilDatePresentation::useRelativeDates();
            \ilDatePresentation::setUseRelativeDates(false);
            $resetText = sprintf(
                $this->lng->txt('tos_last_reset_date'),
                \ilDatePresentation::formatDate($this->tos->getLastResetDate())
            );
            \ilDatePresentation::setUseRelativeDates($status);
        } else {
            $resetText = $this->lng->txt('tos_never_reset');
        }

        $buttons = [];
        if ($this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $buttons = [
                $this->uiFactory
                    ->button()
                    ->standard($this->lng->txt('tos_reset_tos_for_all_users'), $this->ctrl->getLinkTarget($this, 'confirmReset'))
            ];
        }

        return $this->uiRenderer->render(
            $this->uiFactory->messageBox()
                ->info($resetText)
                ->withButtons($buttons)
        );
    }

    /**
     * @param \ilTermsOfServiceDocument $document
     * @return \ilTermsOfServiceDocumentFormGUI
     */
    protected function getDocumentForm(ilTermsOfServiceDocument $document) : \ilTermsOfServiceDocumentFormGUI
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

        $form = new \ilTermsOfServiceDocumentFormGUI(
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

        return $form;
    }

    /**
     *
     */
    protected function saveAddDocumentForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm(new ilTermsOfServiceDocument());
        if ($form->saveObject()) {
            \ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            if ($form->hasTranslatedInfo()) {
                \ilUtil::sendInfo($form->getTranslatedInfo(), true);
            }
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            \ilUtil::sendFailure($form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function showAddDocumentForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm(new ilTermsOfServiceDocument());
        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function showEditDocumentForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getDocumentForm($document);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function saveEditDocumentForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getDocumentForm($document);
        if ($form->saveObject()) {
            \ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            if ($form->hasTranslatedInfo()) {
                \ilUtil::sendInfo($form->getTranslatedInfo(), true);
            }
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            \ilUtil::sendFailure($form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @return array
     */
    protected function getDocumentsByServerRequest() : array
    {
        $documents = [];

        $documentIds = $this->httpState->request()->getParsedBody()['tos_id'] ?? [];
        if (!is_array($documentIds) || 0 === count($documentIds)) {
            $documentIds = $this->httpState->request()->getQueryParams()['tos_id'] ? [$this->httpState->request()->getQueryParams()['tos_id']] : [];
        }

        if (0 === count($documentIds)) {
            return $documents;
        }

        $documents = \ilTermsOfServiceDocument::where(
            ['id' => array_filter(array_map('intval', $documentIds))],
            ['id' => 'IN']
        )->getArray();

        return $documents;
    }

    /**
     * @param array $documents
     * @return \ilTermsOfServiceDocument
     * @throws \UnexpectedValueException
     */
    protected function getFirstDocumentFromList(array $documents) : \ilTermsOfServiceDocument
    {
        if (1 !== count($documents)) {
            throw new \UnexpectedValueException('Expected exactly one document in list');
        }

        $document = new \ilTermsOfServiceDocument(0);
        $document = $document->buildFromArray(current($documents));
        
        return $document;
    }

    /**
     *
     */
    protected function deleteDocuments()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $documents = $this->getDocumentsByServerRequest();
        if (0 === count($documents)) {
            $this->showDocuments();
            return;
        } else {
            $documents = array_map(function (array $data) {
                $document = new \ilTermsOfServiceDocument(0);
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
            $confirmation = new \ilConfirmationGUI();
            $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteDocuments'));
            $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteDocuments');
            $confirmation->setCancel($this->lng->txt('cancel'), 'showDocuments');

            $confirmation->setHeaderText($this->lng->txt('tos_sure_delete_documents_p'));
            if (1 === count($documents)) {
                $confirmation->setHeaderText($this->lng->txt('tos_sure_delete_documents_s'));
            }

            foreach ($documents as $document) {
                /** @var $document \ilTermsOfServiceDocument */
                $confirmation->addItem('tos_id[]', $document->getId(), implode(' | ', [
                    $document->getTitle()
                ]));
            }

            $this->tpl->setContent($confirmation->getHTML());
        }
    }

    /**
     * @param array $documents
     */
    protected function processDocumentDeletion(array $documents)
    {
        foreach ($documents as $document) {
            /** @var $document \ilTermsOfServiceDocument */
            $document->delete();
        }

        if (0 === \ilTermsOfServiceDocument::getCollection()->count()) {
            $this->tos->saveStatus(false);
            \ilUtil::sendInfo($this->lng->txt('tos_disabled_no_docs_left'), true);
        }

        \ilUtil::sendSuccess($this->lng->txt('tos_deleted_documents_p'), true);
        if (1 === count($documents)) {
            \ilUtil::sendSuccess($this->lng->txt('tos_deleted_documents_s'), true);
        }
    }

    /**
     *
     */
    protected function deleteDocument()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $this->processDocumentDeletion([$document]);

        $this->ctrl->redirect($this);
    }

    /**
     *
     */
    protected function saveDocumentSorting()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
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
                $document = new \ilTermsOfServiceDocument((int) $documentId);
                $document->setSorting(++$position);
                $document->store();
            } catch (\ilException $e) {
                // Empty catch block
            }
        }

        \ilUtil::sendSuccess($this->lng->txt('tos_saved_sorting'), true);
        $this->ctrl->redirect($this);
    }

    /**
     * @param \ilTermsOfServiceDocument $document
     * @param \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment
     * @return \ilTermsOfServiceCriterionFormGUI
     */
    protected function getCriterionForm(
        \ilTermsOfServiceDocument $document,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment
    ) : \ilTermsOfServiceCriterionFormGUI {
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

        $form = new \ilTermsOfServiceCriterionFormGUI(
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

    /**
     *
     */
    protected function saveAttachCriterionForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getCriterionForm($document, new \ilTermsOfServiceDocumentCriterionAssignment());
        if ($form->saveObject()) {
            \ilUtil::sendSuccess($this->lng->txt('tos_doc_crit_attached'), true);
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            \ilUtil::sendFailure($form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function showAttachCriterionForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->tos->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document = $this->getFirstDocumentFromList($this->getDocumentsByServerRequest());

        $form = $this->getCriterionForm($document, new \ilTermsOfServiceDocumentCriterionAssignment());
        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function showChangeCriterionForm()
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
            function (\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment) use ($criterionId) {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $form = $this->getCriterionForm($document, $criterionAssignment);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function saveChangeCriterionForm()
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
            function (\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment) use ($criterionId) {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $form = $this->getCriterionForm($document, $criterionAssignment);
        if ($form->saveObject()) {
            \ilUtil::sendSuccess($this->lng->txt('tos_doc_crit_changed'), true);
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            \ilUtil::sendFailure($form->getTranslatedError());
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    public function detachCriterionAssignment()
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
            function (\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment) use ($criterionId) {
                return $criterionAssignment->getId() == $criterionId;
            }
        ))[0];

        $document->detachCriterion($criterionAssignment);
        $document->update();

        \ilUtil::sendSuccess($this->lng->txt('tos_doc_crit_detached'), true);
        $this->ctrl->redirect($this, 'showDocuments');
    }
}
