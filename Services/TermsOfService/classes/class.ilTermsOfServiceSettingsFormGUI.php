<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceSettingsFormGUI
 */
class ilTermsOfServiceSettingsFormGUI extends \ilPropertyFormGUI
{
    /** @var \ilObjTermsOfService */
    protected $tos;

    /** @var string  */
    protected $formAction = '';

    /** @var string */
    protected $saveCommand = '';

    /** @var $bool */
    protected $isEditable = false;

    /** @var string */
    protected $translatedError = '';

    /**
     * ilTermsOfServiceSettingsForm constructor.
     * @param \ilObjTermsOfService $tos
     * @param string $formAction
     * @param string $saveCommand
     * @param bool $isEditable
     */
    public function __construct(
        \ilObjTermsOfService $tos,
        string $formAction = '',
        string $saveCommand = 'saveSettings',
        bool $isEditable = false
    ) {
        $this->tos = $tos;
        $this->formAction = $formAction;
        $this->saveCommand = $saveCommand;
        $this->isEditable = $isEditable;

        parent::__construct();

        $this->initForm();
    }

    /**
     *
     */
    protected function initForm()
    {
        $this->setTitle($this->lng->txt('tos_tos_settings'));
        $this->setFormAction($this->formAction);

        $status = new \ilCheckboxInputGUI($this->lng->txt('tos_status_enable'), 'tos_status');
        $status->setValue(1);
        $status->setChecked((bool) $this->tos->getStatus());
        $status->setInfo($this->lng->txt('tos_status_desc'));
        $status->setDisabled(!$this->isEditable);
        $this->addItem($status);

        if ($this->isEditable) {
            $this->addCommandButton($this->saveCommand, $this->lng->txt('save'));
        }
    }

    /**
     * @param bool $status
     */
    public function setCheckInputCalled(bool $status)
    {
        $this->check_input_called = $status;
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
    public function saveObject() : bool
    {
        if (!$this->fillObject()) {
            $this->setValuesByPost();
            return false;
        }

        if (!(int) $this->getInput('tos_status')) {
            $this->tos->saveStatus((bool) $this->getInput('tos_status'));
            return true;
        }

        $hasDocuments = \ilTermsOfServiceDocument::where([])->count() > 0;
        if ($hasDocuments) {
            $this->tos->saveStatus((bool) $this->getInput('tos_status'));
            return true;
        }

        if (!$this->tos->getStatus()) {
            $this->translatedError = $this->lng->txt('tos_no_documents_exist_cant_save');
            $this->getItemByPostVar('tos_status')->setChecked(false);
            return false;
        }

        $this->tos->saveStatus((bool) $this->getInput('tos_status'));
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

        return true;
    }
}
