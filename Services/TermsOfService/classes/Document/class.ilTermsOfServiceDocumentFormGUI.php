<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\FileSystem\Filesystem;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Location;

/**
 * Class ilTermsOfServiceDocumentFormGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentFormGUI extends \ilPropertyFormGUI
{
    /** @var \ilTermsOfServiceDocument */
    protected $document;

    /** @var \ilObjUser */
    protected $actor;

    /** @var FileUpload */
    protected $fileUpload;
    
    /** @var Filesystem */
    protected $tmpFileSystem;

    /** @var string */
    protected $formAction;

    /** @var string */
    protected $saveCommand;

    /** @var string */
    protected $cancelCommand;

    /** @var $bool */
    protected $isEditable = false;

    /** @var string */
    protected $translatedError = '';

    /** @var string */
    protected $translatedInfo = '';

    /** @var \ilHtmlPurifierInterface */
    protected $documentPurifier;

    /**
     * ilTermsOfServiceDocumentFormGUI constructor.
     * @param \ilTermsOfServiceDocument $document
     * @param \ilHtmlPurifierInterface $documentPurifier
     * @param \ilObjUser $actor
     * @param Filesystem $tmpFileSystem
     * @param FileUpload $fileUpload
     * @param string $formAction
     * @param string $saveCommand
     * @param string $cancelCommand
     * @param bool $isEditable
     */
    public function __construct(
        \ilTermsOfServiceDocument $document,
        \ilHtmlPurifierInterface $documentPurifier,
        \ilObjUser $actor,
        Filesystem $tmpFileSystem,
        FileUpload $fileUpload,
        string $formAction = '',
        string $saveCommand = 'saveDocument',
        string $cancelCommand = 'showDocuments',
        bool $isEditable = false
    ) {
        $this->document = $document;
        $this->documentPurifier = $documentPurifier;
        $this->actor = $actor;
        $this->tmpFileSystem = $tmpFileSystem;
        $this->fileUpload = $fileUpload;
        $this->formAction = $formAction;
        $this->saveCommand = $saveCommand;
        $this->cancelCommand = $cancelCommand;
        $this->isEditable = $isEditable;

        parent::__construct();

        $this->initForm();
    }

    /**
     * @param bool $status
     */
    public function setCheckInputCalled(bool $status)
    {
        $this->check_input_called = $status;
    }

    /**
     *
     */
    protected function initForm()
    {
        if ($this->document->getId() > 0) {
            $this->setTitle($this->lng->txt('tos_form_edit_doc_head'));
        } else {
            $this->setTitle($this->lng->txt('tos_form_new_doc_head'));
        }

        $this->setFormAction($this->formAction);

        $title = new \ilTextInputGUI($this->lng->txt('tos_form_document_title'), 'title');
        $title->setInfo($this->lng->txt('tos_form_document_title_info'));
        $title->setRequired(true);
        $title->setDisabled(!$this->isEditable);
        $title->setValue($this->document->getTitle());
        $title->setMaxLength(255);
        $this->addItem($title);

        $documentLabel = $this->lng->txt('tos_form_document');
        $documentByline = $this->lng->txt('tos_form_document_info');
        if ($this->document->getId() > 0) {
            $documentLabel = $this->lng->txt('tos_form_document_new');
            $documentByline = $this->lng->txt('tos_form_document_new_info');
        }

        $document = new \ilFileStandardDropzoneInputGUI($documentLabel, 'document');
        $document->setInfo($documentByline);
        if (!$this->document->getId()) {
            $document->setRequired(true);
        }
        $document->setDisabled(!$this->isEditable);
        $document->setMaxFiles(1);
        $document->setSuffixes(['html', 'txt']);
        $this->addItem($document);

        if ($this->isEditable) {
            $this->addCommandButton($this->saveCommand, $this->lng->txt('save'));
        }

        $this->addCommandButton($this->cancelCommand, $this->lng->txt('cancel'));
    }

    /**
     * @return bool
     */
    public function hasTranslatedError() : bool
    {
        return strlen($this->translatedError);
    }

    /**
     * @return string
     */
    public function getTranslatedError() : string
    {
        return $this->translatedError;
    }

    /**
     * @return bool
     */
    public function hasTranslatedInfo() : bool
    {
        return strlen($this->translatedInfo);
    }

    /**
     * @return string
     */
    public function getTranslatedInfo() : string
    {
        return $this->translatedInfo;
    }

    /**
     * @return bool
     */
    public function saveObject() : bool
    {
        if (!$this->fillObject()) {
            $this->setValuesByPost();
            return false;
        }

        $this->document->save();

        return true;
    }

    /**
     *
     */
    protected function fillObject() : bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        if ($this->fileUpload->hasUploads() && !$this->fileUpload->hasBeenProcessed()) {
            try {
                $this->fileUpload->process();

                /** @var UploadResult $uploadResult */
                $uploadResult = array_values($this->fileUpload->getResults())[0];
                if (!$uploadResult) {
                    $this->getItemByPostVar('document')->setAlert($this->lng->txt('form_msg_file_no_upload'));
                    throw new \ilException($this->lng->txt('form_input_not_valid'));
                }

                if (!$this->document->getId() || $uploadResult->getName() !== '') {
                    if ($uploadResult->getStatus()->getCode() != ProcessingStatus::OK) {
                        $this->getItemByPostVar('document')->setAlert($uploadResult->getStatus()->getMessage());
                        throw new \ilException($this->lng->txt('form_input_not_valid'));
                    }

                    $this->fileUpload->moveOneFileTo(
                        $uploadResult,
                        '/agreements',
                        Location::TEMPORARY,
                        '',
                        true
                    );

                    $pathToFile = '/agreements/' . $uploadResult->getName();
                    if (!$this->tmpFileSystem->has($pathToFile)) {
                        $this->getItemByPostVar('document')->setAlert($this->lng->txt('form_msg_file_no_upload'));
                        throw new \ilException($this->lng->txt('form_input_not_valid'));
                    }

                    $originalContent = $content = $this->tmpFileSystem->read($pathToFile);

                    $purifiedHtmlContent = $this->documentPurifier->purify($content);

                    $htmlValidator = new ilTermsOfServiceDocumentsContainsHtmlValidator($purifiedHtmlContent);
                    if (!$htmlValidator->isValid()) {
                        $purifiedHtmlContent = nl2br($purifiedHtmlContent);
                    }

                    if (trim($purifiedHtmlContent) !== trim($originalContent)) {
                        $this->translatedInfo = $this->lng->txt('tos_form_document_content_changed');
                    }

                    $this->document->setText($purifiedHtmlContent);
                    $this->tmpFileSystem->delete($pathToFile);
                }
            } catch (Exception $e) {
                $this->translatedError = $e->getMessage();
                return false;
            }
        }

        $this->document->setTitle($this->getInput('title'));

        if ($this->document->getId() > 0) {
            $this->document->setLastModifiedUsrId($this->actor->getId());
        } else {
            $this->document->setOwnerUsrId($this->actor->getId());

            $documentWithMaxSorting = \ilTermsOfServiceDocument::orderBy('sorting', 'DESC')->limit(0, 1)->first();
            if ($documentWithMaxSorting instanceof \ilTermsOfServiceDocument) {
                $this->document->setSorting((int) $documentWithMaxSorting->getSorting() + 1);
            } else {
                $this->document->setSorting(1);
            }
        }

        return true;
    }
}
