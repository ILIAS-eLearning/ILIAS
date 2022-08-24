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

use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Location;

class ilWebDAVMountInstructionsDocumentFormGUI extends ilPropertyFormGUI
{
    protected ilWebDAVMountInstructionsDocument $document;
    protected ilWebDAVMountInstructionsRepository $mount_instructions_repository;
    protected ilHtmlPurifierInterface $document_purifier;
    protected ilObjUser $actor;
    protected FileUpload $file_upload;
    protected Filesystem $tmp_filesystem;
    protected string $form_action;
    protected string $save_command;
    protected string $cancel_command;
    protected bool $is_editable = false;
    protected string $translated_error = '';
    protected string $translated_info = '';

    public function __construct(
        ilWebDAVMountInstructionsDocument $document,
        ilWebDAVMountInstructionsRepository $mount_instructions_repository,
        ?ilHtmlPurifierInterface $document_purifier,
        ilObjUser $actor,
        Filesystem $tmp_filesystem,
        FileUpload $fileupload,
        string $form_action,
        string $save_command,
        string $cancel_command,
        bool $is_editable
    ) {
        $this->document = $document;
        $this->mount_instructions_repository = $mount_instructions_repository;
        $this->document_purifier = $document_purifier;
        $this->actor = $actor;
        $this->tmp_filesystem = $tmp_filesystem;
        $this->file_upload = $fileupload;
        $this->form_action = $form_action;
        $this->save_command = $save_command;
        $this->cancel_command = $cancel_command;
        $this->is_editable = $is_editable;

        parent::__construct();

        $this->initForm();
    }

    protected function initForm(): void
    {
        $document_already_exists = $this->document->getId() > 0;
        if ($document_already_exists) {
            $this->setTitle($this->lng->txt('webdav_form_edit_doc_head'));
        } else {
            $this->setTitle($this->lng->txt('webdav_form_new_doc_head'));
        }

        $this->setFormAction($this->form_action);

        $title = new ilTextInputGUI($this->lng->txt('webdav_form_document_title'), 'title');
        $title->setInfo($this->lng->txt('webdav_form_document_title_info'));
        $title->setRequired(true);
        $title->setDisabled(!$this->is_editable);
        $title->setValue($this->document->getTitle());
        $title->setMaxLength(255);
        $this->addItem($title);

        $document_label = $this->lng->txt('webdav_form_document');
        $document_by_line = $this->lng->txt('webdav_form_document_info');

        $language_selection = new ilSelectInputGUI(
            $this->lng->txt('language'),
            'lng'
        );
        $language_selection->setRequired(true);

        $options = [];
        foreach ($this->lng->getInstalledLanguages() as $lng) {
            $options[$lng] = $this->lng->txt('meta_l_' . $lng, 'meta');
        }

        asort($options);

        $language_selection->setOptions(['' => $this->lng->txt('please_choose')] + $options);
        $language_selection->setValue($this->document->getLanguage());

        $this->addItem($language_selection);

        if ($document_already_exists) {
            $document_id = new ilHiddenInputGUI('document_id');
            $document_id->setValue((string) $this->document->getId());
            $this->addItem($document_id);
        } else {
            $document_upload = new ilFileInputGUI($document_label, 'document');
            $document_upload->setInfo($document_by_line);
            $document_upload->setRequired(true);
            $document_upload->setDisabled(!$this->is_editable);
            $document_upload->setSuffixes(['html', 'htm', 'txt']);
            $this->addItem($document_upload);
        }

        if ($this->is_editable) {
            $this->addCommandButton($this->save_command, $this->lng->txt('save'));
        }
    }

    public function saveObject(): bool
    {
        try {
            $this->document = $this->createFilledObject($this->document);
            $this->mount_instructions_repository->createMountInstructionsDocumentEntry($this->document);
        } catch (InvalidArgumentException $e) {
            $this->setValuesByPost();
            $this->translated_error .= $e->getMessage();
            return false;
        }

        return true;
    }

    public function updateObject(): bool
    {
        try {
            $this->document = $this->createFilledObject($this->document);
            $this->mount_instructions_repository->updateMountInstructions($this->document);
        } catch (InvalidArgumentException $e) {
            $this->setValuesByPost();
            $this->translated_error .= $e->getMessage();
            return false;
        }

        return true;
    }

    public function hasTranslatedInfo(): bool
    {
        return strlen($this->translated_info) > 0;
    }

    public function hasTranslatedError(): bool
    {
        return strlen($this->translated_error) > 0;
    }

    public function getTranslatedInfo(): string
    {
        return $this->translated_info;
    }

    public function getTranslatedError(): string
    {
        return $this->translated_error;
    }

    protected function createFilledObject(ilWebDAVMountInstructionsDocument $document): ilWebDAVMountInstructionsDocument
    {
        if (!$this->checkInput()) {
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        $document_already_exists = $document->getId() > 0;

        if (!$document_already_exists) {
            $upload_result = $this->getFileUploadResult();
        }

        if (!$document_already_exists && !$upload_result->isOK()) {
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        $title = $this->getInput('title');
        $language = $this->getInput('lng');
        $creation_ts = $document_already_exists ? $document->getCreationTs() : ilUtil::now();
        $modification_ts = $document_already_exists ? ilUtil::now() : $creation_ts;
        $owner_id = $document_already_exists ? $document->getOwnerUsrId() : $this->actor->getId();
        $last_modified_usr_id = $this->actor->getId();
        $sorting = $document_already_exists ? $document->getSorting() : $this->mount_instructions_repository->getHighestSortingNumber() + 1;

        $mount_instruction_for_language_exists = $this->mount_instructions_repository->doMountInstructionsExistByLanguage($language);

        if (!$document_already_exists && $mount_instruction_for_language_exists) {
            throw new InvalidArgumentException($this->lng->txt("webdav_choosen_language_already_used"));
        }

        if ($document_already_exists && $document->getLanguage() != $language &&
            $mount_instruction_for_language_exists > 0 &&
            $mount_instruction_for_language_exists != $document->getId()) {
            throw new InvalidArgumentException($this->lng->txt("webdav_chosen_language_already_used"));
        }

        if ($document_already_exists) {
            $raw_mount_instructions = '';
            $processed_mount_instructions = '';
        } else {
            $raw_mount_instructions = $this->getRawMountInstructionsFromFileUpload($upload_result);
            $document_processor = $upload_result->getMimeType() == 'text/html'
                ? new ilWebDAVMountInstructionsHtmlDocumentProcessor($this->document_purifier)
                : new ilWebDAVMountInstructionsTextDocumentProcessor();
            $processed_mount_instructions = $document_processor->processMountInstructions($raw_mount_instructions);
        }

        $id = $document_already_exists ? $document->getId()
            : $this->mount_instructions_repository->getNextMountInstructionsDocumentId();

        return new ilWebDAVMountInstructionsDocument(
            $id,
            $title,
            $raw_mount_instructions,
            json_encode($processed_mount_instructions),
            $language,
            $creation_ts,
            $modification_ts,
            $owner_id,
            $last_modified_usr_id,
            $sorting
        );
    }

    protected function getRawMountInstructionsFromFileUpload(UploadResult $upload_result): string
    {
        if ($upload_result->getName() === '') {
            throw new InvalidArgumentException('uploaded file has no name');
        }

        if (!$upload_result->isOK()) {
            $this->getItemByPostVar('document')->setAlert($upload_result->getStatus()->getMessage());
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        $this->file_upload->moveOneFileTo(
            $upload_result,
            '/mount_instructions',
            Location::TEMPORARY,
            '',
            true
        );

        $path_to_file = '/mount_instructions/' . $upload_result->getName();
        if (!$this->tmp_filesystem->has($path_to_file)) {
            $this->getItemByPostVar('document')->setAlert($this->lng->txt('form_msg_file_no_upload'));
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        $raw_content = $this->tmp_filesystem->read($path_to_file);

        $this->tmp_filesystem->delete($path_to_file);

        return $raw_content;
    }

    protected function getFileUploadResult(): UploadResult
    {
        if (!$this->file_upload->hasUploads()) {
            throw new InvalidArgumentException("webdav_error_no_upload");
        } elseif ($this->file_upload->hasBeenProcessed()) {
            throw new InvalidArgumentException("webdav_error_upload_already_processed");
        }

        $this->file_upload->process();

        $upload_result = array_values($this->file_upload->getResults())[0];

        if (!$upload_result) {
            $this->getItemByPostVar('document')->setAlert($this->lng->txt('form_msg_file_no_upload'));
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        return $upload_result;
    }
}
