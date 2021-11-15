<?php

use ILIAS\FileSystem\Filesystem;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Location;

class ilWebDAVMountInstructionsDocumentFormGUI extends ilPropertyFormGUI
{
    /** @var ilWebDAVMountInstructionsDocument */
    protected $document;

    /** @var ilWebDAVMountInstructionsRepository */
    protected $mount_instructions_repository;

    /** @var ilHtmlPurifierInterface */
    protected $document_purifier;

    /** @var ilObjUser */
    protected $actor;

    /** @var FileUpload */
    protected $file_upload;

    /** @var Filesystem */
    protected $tmp_filesystem;

    /** @var string */
    protected $form_action;

    /** @var string */
    protected $save_command;

    /** @var string */
    protected $cancel_command;

    /** @var bool */
    protected $is_editable = false;

    /** @var string */
    protected $translated_error;

    /** @var string */
    protected $translated_info;

    /**
     * ilWebDAVMountInstructionsDocumentFormGUI constructor.
     * @param ilWebDAVMountInstructionsDocument $a_document
     * @param ilWebDAVMountInstructionsRepository $mount_instructions_repository
     * @param ilHtmlPurifierInterface|null $a_document_purifier
     * @param ilObjUser $a_actor
     * @param Filesystem $a_tmp_filesystem
     * @param FileUpload $a_fileupload
     * @param string $a_form_action
     * @param string $a_save_command
     * @param string $a_cancel_command
     * @param bool $a_is_editable
     */
    public function __construct(
        ilWebDAVMountInstructionsDocument $a_document,
        ilWebDAVMountInstructionsRepository $mount_instructions_repository,
        ?ilHtmlPurifierInterface $a_document_purifier,
        ilObjUser $a_actor,
        FileSystem $a_tmp_filesystem,
        FileUpload $a_fileupload,
        string $a_form_action,
        string $a_save_command,
        string $a_cancel_command,
        bool $a_is_editable
    ) {
        $this->document = $a_document;
        $this->mount_instructions_repository = $mount_instructions_repository;
        $this->document_purifier = $a_document_purifier;
        $this->actor = $a_actor;
        $this->tmp_filesystem = $a_tmp_filesystem;
        $this->file_upload = $a_fileupload;
        $this->form_action = $a_form_action;
        $this->save_command = $a_save_command;
        $this->cancel_command = $a_cancel_command;
        $this->is_editable = $a_is_editable;

        parent::__construct();

        $this->initForm();
    }

    /**
     * Initializes the property form
     */
    protected function initForm() : void
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

        if ($document_already_exists) {
            $document_label = $this->lng->txt('webdav_form_document');
            $document_by_line = $this->lng->txt('webdav_form_document_info');
        } else {
            $document_label = $this->lng->txt('webdav_form_document');
            $document_by_line = $this->lng->txt('webdav_form_document_info');
        }

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
        $language_selection->setValue((string) ($this->document->getLanguage() ?? ''));

        $this->addItem($language_selection);

        if ($document_already_exists) {
            $webdav_id = new ilHiddenInputGUI('webdav_id');
            $webdav_id->setValue($this->document->getId());
            $this->addItem($webdav_id);
        } else {
            $document_upload = new ilFileInputGUI($document_label, 'document');
            $document_upload->setInfo($document_by_line);
            $document_upload->setRequired($document_already_exists ? false : true);
            $document_upload->setDisabled(!$this->is_editable);
            $document_upload->setSuffixes(['html', 'htm', 'txt']);
            $this->addItem($document_upload);
        }

        if ($this->is_editable) {
            $this->addCommandButton($this->save_command, $this->lng->txt('save'));
        }
    }

    /**
     * Save uploaded mount instructions document
     * @return bool
     */
    public function saveObject()
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
    
    /**
     * Update the document with id from form
     */
    public function updateObject()
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

    /**
     * @return bool
     */
    public function hasTranslatedInfo()
    {
        return strlen($this->translated_info) > 0;
    }

    /**
     * @return bool
     */
    public function hasTranslatedError()
    {
        return strlen($this->translated_error) > 0;
    }

    /**
     * @return string
     */
    public function getTranslatedInfo()
    {
        return $this->translated_info;
    }

    /**
     * @return string
     */
    public function getTranslatedError()
    {
        return $this->translated_error;
    }

    /**
     * @param ilWebDAVMountInstructionsDocument $document
     * @return ilWebDAVMountInstructionsDocument
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function createFilledObject(ilWebDAVMountInstructionsDocument $document) : ilWebDAVMountInstructionsDocument
    {
        // early exit for invalid input
        if (!$this->checkInput()) {
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        // check if document already exists in db
        $document_already_exists = $document->getId() > 0;

        if (!$document_already_exists) {
            /** @var  $upload_result UploadResult*/
            $upload_result = $this->getFileUploadResult();
        }

        // Exit on failed file upload
        if (!$document_already_exists && !$upload_result->isOK()) {
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        // Get values for document
        $title = $this->getInput('title');
        $language = $this->getInput('lng');
        $creation_ts = $document_already_exists ? $document->getCreationTs() : ilUtil::now();
        $modification_ts = $document_already_exists ? ilUtil::now() : $creation_ts;
        $owner_id = $document_already_exists ? $document->getOwnerUsrId() : $this->actor->getId();
        $last_modified_usr_id = $this->actor->getId();
        $sorting = $document_already_exists ? $document->getSorting() : $this->mount_instructions_repository->getHighestSortingNumber() + 1;

        // On creating a new document -> check if language is already in use by another document
        if (!$document_already_exists && $this->mount_instructions_repository->doMountInstructionsExistByLanguage($language)) {
            throw new InvalidArgumentException($this->lng->txt("webdav_choosen_language_already_used"));
        }

        // On editing document -> check if language is changed and already is in use by another document
        if ($document_already_exists && $document->getLanguage() != $language
            && $this->mount_instructions_repository->doMountInstructionsExistByLanguage($language) != $document->getId()) {
            throw new InvalidArgumentException($this->lng->txt("webdav_chosen_language_already_used"));
        }

        if ($document_already_exists) {
            $raw_mount_instructions = '';
            $processed_mount_instructions = '';
        } else {
            // Get and process mount instructions
            $raw_mount_instructions = $this->getRawMountInstructionsFromFileUpload($upload_result);
            $document_processor = $upload_result->getMimeType() == 'text/html'
                ? new ilWebDAVMountInstructionsHtmlDocumentProcessor($this->document_purifier)
                : new ilWebDAVMountInstructionsTextDocumentProcessor();
            $processed_mount_instructions = $document_processor->processMountInstructions($raw_mount_instructions);
        }
        // Get or create new id for document
        $id = $document_already_exists ? $document->getId()
            : $this->mount_instructions_repository->getNextMountInstructionsDocumentId();

        // Create document with new values (no setter methods -> object from this class are immutable)
        $document = new ilWebDAVMountInstructionsDocument(
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

        return $document;
    }

    /**
     * Gets the content of the uploaded file
     *
     * @param UploadResult $upload_result
     * @return string
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function getRawMountInstructionsFromFileUpload(UploadResult $upload_result) : string
    {
        //  Check uploaded name
        if ($upload_result->getName() === '') {
            throw new InvalidArgumentException('uploaded file has no name');
        }

        // Check status
        if (!$upload_result->isOK()) {
            $this->getItemByPostVar('document')->setAlert($upload_result->getStatus()->getMessage());
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        // Move uploaded file to a temporary directory to read it
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

        // Get conetent of file
        $raw_content = $content = $this->tmp_filesystem->read($path_to_file);

        // Delete temporary file
        $this->tmp_filesystem->delete($path_to_file);

        return $raw_content;
    }

    /**
     * @return UploadResult
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    protected function getFileUploadResult() : UploadResult
    {
        // Early exit if file upload has errors (no uploads or uploads already processed)
        if (!$this->file_upload->hasUploads()) {
            throw new InvalidArgumentException("webdav_error_no_upload");
        } elseif ($this->file_upload->hasBeenProcessed()) {
            throw new InvalidArgumentException("webdav_error_upload_already_processed");
        }

        $this->file_upload->process();

        /** @var UploadResult $upload_result */
        $upload_result = array_values($this->file_upload->getResults())[0];

        if (!$upload_result) {
            $this->getItemByPostVar('document')->setAlert($this->lng->txt('form_msg_file_no_upload'));
            throw new InvalidArgumentException($this->lng->txt('form_input_not_valid'));
        }

        return $upload_result;
    }
}
