<?php declare(strict_types = 1);

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
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Factory as UIFactory;
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
    
    private ilGlobalTemplateInterface $tpl;
    private ilObjUser $user;
    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilRbacSystem $rbacsystem;
    private ilErrorHandling $error;
    private ilLogger $log;
    private ilToolbarGUI $toolbar;
    private Services $http;
    private RefineryFactory $refinery;
    private UIFactory $ui_factory;
    private Renderer $ui_renderer;
    private Filesystems $file_systems;
    private FileUpload $file_upload;
    private ilWebDAVMountInstructionsRepository $mount_instructions_repository;
    private int $webdav_object_ref_id;

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilObjUser $user,
        ilCtrlInterface $ctrl,
        ilLanguage $lng,
        ilRbacSystem $rbacsystem,
        ilErrorHandling $error,
        ilLogger $log,
        ilToolbarGUI $toolbar,
        Services $http,
        RefineryFactory $refinery,
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
        $this->http = $http;
        $this->refinery = $refinery;
        $this->ui_factory = $ui->factory();
        $this->ui_renderer = $ui->renderer();
        $this->file_systems = $file_systems;
        $this->file_upload = $file_upload;
        $this->mount_instructions_repository = $mount_instructions_repository;
        
        $this->lng->loadLanguageModule('meta');
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
    
    public function setRefId(int $ref_id) : void
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

        $uri_builder = new ilWebDAVUriBuilder($this->http->request());

        $document_tbl_gui = new ilWebDAVMountInstructionsDocumentTableGUI(
            $this,
            $uri_builder,
            'showDocuments',
            $this->ui_factory,
            $this->ui_renderer,
            $this->http->request(),
            $this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)
        );
        $document_tbl_gui->setProvider(new ilWebDAVMountInstructionsTableDataProvider($this->mount_instructions_repository));
        $document_tbl_gui->populate();

        $this->tpl->setContent($document_tbl_gui->getHTML());
    }
    
    protected function getDocumentForm(ilWebDAVMountInstructionsDocument $a_document) : ilWebDAVMountInstructionsDocumentFormGUI
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

    protected function showAddDocumentForm() : void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $form = $this->getDocumentForm(new ilWebDAVMountInstructionsDocument());
        $this->tpl->setContent($form->getHTML());
    }

    protected function showEditDocumentForm() : void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document_id = $this->http->wrapper()->query()->retrieve('document_id', $this->refinery->kindlyTo()->int());
        $document = $this->mount_instructions_repository->getMountInstructionsDocumentById($document_id);
        $form = $this->getDocumentForm($document);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     *
     */
    protected function saveAddDocumentForm() : void
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
    protected function saveEditDocumentForm() : void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $document_id = $this->http->wrapper()->query()->retrieve('document_id', $this->refinery->kindlyTo()->int());
        $form = $this->getDocumentForm($this->mount_instructions_repository->getMountInstructionsDocumentById($document_id));
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

    protected function deleteDocument() : void
    {
        if (!$this->rbacsystem->checkAccess('delete', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        
        $document_id = $this->http->wrapper()->query()->retrieve('document_id', $this->refinery->kindlyTo()->int());
        
        $this->mount_instructions_repository->deleteMountInstructionsById($document_id);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('deleted_successfully'), true);
        $this->ctrl->redirect($this, 'showDocuments');
    }

    public function saveDocumentSorting() : void
    {
        if (!$this->rbacsystem->checkAccess('write', $this->webdav_object_ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $sorting = $this->http->request()->getParsedBody()['sorting'] ?? [];
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
