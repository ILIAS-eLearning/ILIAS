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

/**
 * Class ilTermsOfServiceSettingsFormGUI
 */
class ilTermsOfServiceSettingsFormGUI extends ilPropertyFormGUI
{
    protected string $translatedError = '';

    public function __construct(
        protected ilObjTermsOfService $tos,
        protected string $formAction = '',
        protected string $saveCommand = 'saveSettings',
        protected bool $isEditable = false
    ) {
        parent::__construct();

        $this->initForm();
    }

    protected function initForm(): void
    {
        $this->setTitle($this->lng->txt('tos_tos_settings'));
        $this->setFormAction($this->formAction);

        $status = new ilCheckboxInputGUI($this->lng->txt('tos_status_enable'), 'tos_status');
        $status->setValue('1');
        $status->setChecked($this->tos->getStatus());
        $status->setInfo($this->lng->txt('tos_status_desc'));
        $status->setDisabled(!$this->isEditable);
        $this->addItem($status);

        $reevaluateOnLogin = new ilCheckboxInputGUI($this->lng->txt('tos_reevaluate_on_login'), 'tos_reevaluate_on_login');
        $reevaluateOnLogin->setValue('1');
        $reevaluateOnLogin->setChecked($this->tos->shouldReevaluateOnLogin());
        $reevaluateOnLogin->setInfo($this->lng->txt('tos_reevaluate_on_login_desc'));
        $reevaluateOnLogin->setDisabled(!$this->isEditable);
        $status->addSubItem($reevaluateOnLogin);

        if ($this->isEditable) {
            $this->addCommandButton($this->saveCommand, $this->lng->txt('save'));
        }
    }

    public function setCheckInputCalled(bool $status): void
    {
        $this->check_input_called = $status;
    }

    public function hasTranslatedError(): bool
    {
        return $this->translatedError !== '';
    }

    public function getTranslatedError(): string
    {
        return $this->translatedError;
    }

    public function saveObject(): bool
    {
        if (!$this->fillObject()) {
            $this->setValuesByPost();
            return false;
        }

        if ((int) $this->getInput('tos_status') === 0) {
            $this->tos->saveStatus((bool) $this->getInput('tos_status'));
            return true;
        }

        $hasDocuments = ilTermsOfServiceDocument::where([])->count() > 0;
        if ($hasDocuments) {
            $this->tos->saveStatus((bool) $this->getInput('tos_status'));
            $this->tos->setReevaluateOnLogin((bool) $this->getInput('tos_reevaluate_on_login'));
            return true;
        }

        if (!$this->tos->getStatus()) {
            $this->translatedError = $this->lng->txt('tos_no_documents_exist_cant_save');
            /** @var ilCheckboxInputGUI $item */
            $item = $this->getItemByPostVar('tos_status');
            $item->setChecked(false);
            return false;
        }

        $this->tos->saveStatus((bool) $this->getInput('tos_status'));
        $this->tos->setReevaluateOnLogin((bool) $this->getInput('tos_reevaluate_on_login'));
        return true;
    }

    protected function fillObject(): bool
    {
        return $this->checkInput();
    }
}
