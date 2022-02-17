<?php

use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\Services;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\DI\UIServices;

/**
 * @author
 * @ilCtrl_isCalledBy ilWebDAVMountInstructionsUploadGUI:  ilObjWebDAVGUI
 */
class ilWebDAVMountInstructionsUploadGUI
{
    const ACTION_SAVE_ADD_DOCUMENT_FORM = 'saveAddDocumentForm';
    const ACTION_SAVE_EDIT_DOCUMENT_FORM = 'saveEditDocumentForm';
    
    private ilGlobalPageTemplate $tpl;
    private ilObjUser $user;
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilRbacSystem $rbacsystem;
    private ilErrorHandling $error;
    private ilLogger $log;
    private ilToolbarGUI $toolbar;
    private Services $http_state;
    private Factory $ui_factory;
    private Renderer $ui_renderer;
    private Filesystems $file_systems;
    private FileUpload $file_upload;
    private ilWebDAVMountInstructionsRepository $mount_instructions_repository;
    private int $webdav_object_ref_id;

    public function __construct(
        ilGlobalPageTemplate $tpl,
        ilObjUser $user,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilRbacSystem $rbacsystem,
        ilErrorHandling $error,
        ilLogger $log,
        ilToolbarGUI $toolbar,
        Services $http_state,
        UIServices $ui,
        Filesystems $file_systems,
        FileUpload $file_upload,
        ilWebDAVMountInstructionsRepository $mount_instructions_repository
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->rbacsystem = $rbacsystem;
        $this->error = $error;
        $this->user = $user;
        $this->log = $log;
        $this->toolbar = $toolbar;
        $this->http_state = $http_state;
        $this->ui_factory = $ui->factory();
        $this->ui_renderer = $ui->renderer();
        $this->file_systems = $file_systems;
        $this->file_upload = $file_upload;
        $this->mount_instructions_repository = $mount_instructions_repository;
    }

    /**
     *
     */
    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();

        if (!$this->rbacsystem->checkAccess('read', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        if ($cmd == 'delete') {
            $cmd .= 'Document';
        }
        if ($cmd == '' || !method_exists($this, $cmd)) {
            $cmd = 'showDocuments';
        }
        $this->$cmd();
    }
    
    public function setRefId(int $ref_id)
    {
        $this->webdav_object_ref_id = $ref_id;
    }

    /**
     * @throws ilTemplateException
     */
    protected function showDocuments() : void
    {
        if ($this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $addDocumentBtn = ilLinkButton::getInstance();
            $addDocumentBtn->setPrimary(true);
            $addDocumentBtn->setUrl($this->ctrl->getLinkTarget($this, 'showAddDocumentForm'));
            $addDocumentBtn->setCaption('webdav_add_instructions_btn_label');
            $this->toolbar->addStickyItem($addDocumentBtn);
        }

        $uri_builder = new ilWebDAVUriBuilder($this->http_state->request());

        $document_tbl_gui = new ilWebDAVMountInstructionsDocumentTableGUI(
            $this,
            $uri_builder,
            'showDocuments',
            $this->ui_factory,
            $this->ui_renderer,
            $this->http_state->request(),
            $this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)
        );
        $document_tbl_gui->setProvider(new ilWebDAVMountInstructionsTableDataProvider($this->mount_instructions_repository));
        $document_tbl_gui->populate();

        $this->tpl->setContent($document_tbl_gui->getHTML());
    }
    
    protected function getDocumentForm(ilWebDAVMountInstructionsDocument $a_document)
    {
        if ($a_document->getId() > 0) {
            $this->ctrl->setParameter($this, 'doc_id', $a_document->getId());

            $form_action = $this->ctrl->getFormAction($this, self::ACTION_SAVE_EDIT_DOCUMENT_FORM);
            $save_command = self::ACTION_SAVE_EDIT_DOCUMENT_FORM;
        } else {
            $form_action = $this->ctrl->getFormAction($this, self::ACTION_SAVE_ADD_DOCUMENT_FORM);
            $save_command = self::ACTION_SAVE_ADD_DOCUMENT_FORM;
        }

        $form = new ilWebDAVMountInstructionsDocumentFormGUI(
            $a_document,
            $this->mount_instructions_repository,
            new ilWebDAVMountInstructionsDocumentPurifier(),
            $this->user,
            $this->file_systems->temp(),
            $this->file_upload,
            $form_action,
            $save_command,
            'showDocuments',
            $this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)
        );

        return $form;
    }

    protected function showAddDocumentForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm(new ilWebDAVMountInstructionsDocument());
        $this->tpl->setContent($form->getHTML());
    }

    protected function showEditDocumentForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document_id = $_REQUEST['webdav_id'];
        $document = $this->mount_instructions_repository->getMountInstructionsDocumentById($document_id);
        $form = $this->getDocumentForm($document);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function saveAddDocumentForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        
        $form = $this->getDocumentForm(new ilWebDAVMountInstructionsDocument());
        if ($form->saveObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            if ($form->hasTranslatedInfo()) {
                $this->tpl->setOnScreenMessage('info', $form->getTranslatedInfo(), true);
            }
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError(), true);
        }

        $html = $form->getHTML();
        $this->tpl->setContent($html);
    }

    /**
     *
     */
    protected function saveEditDocumentForm()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm($this->mount_instructions_repository->getMountInstructionsDocumentById($_REQUEST['webdav_id']));
        if ($form->updateObject()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            if ($form->hasTranslatedInfo()) {
                $this->tpl->setOnScreenMessage('info', $form->getTranslatedInfo(), true);
            }
            $this->ctrl->redirect($this, 'showDocuments');
        } elseif ($form->hasTranslatedError()) {
            $this->tpl->setOnScreenMessage('failure', $form->getTranslatedError(), true);
        }
            
        $html = $form->getHTML();
        $this->tpl->setContent($html);
    }

    protected function getDocumentByServerRequest()
    {
        return $this->http_state->request() - getParsedBody()['instructions_id'] ?? [];
    }

    protected function deleteDocument()
    {
        if (!$this->rbacsystem->checkAccess('delete', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        } else {
            $webdav_id = $_REQUEST['webdav_id'];
            $this->mount_instructions_repository->deleteMountInstructionsById($webdav_id);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('deleted_successfully'), true);
            $this->ctrl->redirect($this, 'showDocuments');
        }
    }

    public function saveDocumentSorting()
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $sorting = $this->http_state->request()->getParsedBody()['sorting'] ?? [];
        if (!is_array($sorting) || 0 === count($sorting)) {
            $this->showDocuments();
            return;
        }

        // Sort array by give sort value
        asort($sorting, SORT_NUMERIC);

        $position = 0;
        foreach ($sorting as $document_id => $ignored_sort_value) {

            // Only accept numbers
            if (!is_numeric($document_id)) {
                continue;
            }

            $this->mount_instructions_repository->updateSortingValueById((int) $document_id, ++$position);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('webdav_saved_sorting'), true);
        $this->ctrl->redirect($this);
    }
}
