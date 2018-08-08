<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentFormGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentFormGUI extends \ilPropertyFormGUI
{
	/** @var \ilTermsOfServiceDocument */
	protected $document;

	/** @var \ilObjUser */
	protected $user;

	/** @var \\ILIAS\FileUpload\FileUpload */
	protected $fileUpload;

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

	/**
	 * ilTermsOfServiceDocumentFormGUI constructor.
	 * @param \ilTermsOfServiceDocument $document
	 * @param \ilObjUser $user
	 * @param \ILIAS\FileUpload\FileUpload $fileUpload
	 * @param ilLanguage $lng
	 * @param string $formAction
	 * @param string $saveCommand
	 * @param string $cancelCommand
	 * @param bool $isEditable
	 */
	public function __construct(
		\ilTermsOfServiceDocument $document,
		\ilObjUser $user,
		\ILIAS\FileUpload\FileUpload $fileUpload,
		string $formAction = '',
		string $saveCommand = 'saveDocument',
		string $cancelCommand = 'showDocuments',
		bool $isEditable = false
	) {
		$this->document = $document;
		$this->user = $user;
		$this->fileUpload = $fileUpload;
		$this->formAction = $formAction;
		$this->saveCommand = $saveCommand;
		$this->cancelCommand = $cancelCommand;
		$this->isEditable = $isEditable;

		parent::__construct();

		$this->initForm();
	}

	/**
	 *
	 */
	protected function initForm()
	{
		$this->setTitle($this->lng->txt('tos_form_new_doc_head'));
		$this->setFormAction($this->formAction);

		$title = new \ilTextInputGUI($this->lng->txt('tos_form_document_title'), 'title');
		$title->setInfo($this->lng->txt('tos_form_document_title_info'));
		$title->setRequired(true);
		$title->setDisabled(!$this->isEditable);
		$title->setValue($this->document->getText());
		$title->setMaxLength(255);
		$this->addItem($title);

		$document = new \ilFileInputGUI($this->lng->txt('tos_form_document'), 'document');
		$document->setInfo($this->lng->txt('tos_form_document_info'));
		if (!$this->document->getId()) {
			$document->setRequired(true);
		}
		$document->setDisabled(!$this->isEditable);
		$document->setSuffixes(['html']);
		$this->addItem($document);

		if ($this->isEditable) {
			$this->addCommandButton($this->saveCommand, $this->lng->txt('save'));
		}

		$this->addCommandButton($this->cancelCommand, $this->lng->txt('cancel'));
	}

	/**
	 * @return bool
	 */
	public function hasTranslatedError(): bool 
	{
		return strlen($this->translatedError);
	}

	/**
	 * @return string
	 */
	public function getTranslatedError(): string 
	{
		return $this->translatedError;
	}

	/**
	 * @return bool
	 */
	public function saveObject() :bool 
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
	protected function fillObject(): bool 
	{
		if (!$this->checkInput()) {
			return false;
		}

		if (!$this->fileUpload->hasUploads()) {
			return false;
		}

		$this->document->setTitle($this->getInput('title'));

		if ($this->document->getId() > 0) {
			$this->document->setLastModifiedUsrId($this->user->getId());
		} else {
			$this->document->setOwnerUsrId($this->user->getId());
		}

		return true;
	}
}