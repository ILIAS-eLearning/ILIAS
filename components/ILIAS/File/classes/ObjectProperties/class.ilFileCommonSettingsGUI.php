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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilFileCommonSettingsGUI
{
    public const CMD_EDIT = 'editSettings';
    public const CMD_SAVE = 'saveSettings';
    private array $services;

    protected ilObjFile $object;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilObjectService $object_service;

    public function __construct(
        ilObjFile $object,
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilObjectService $object_service
    ) {
        $this->object = $object;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->object_service = $object_service;
        $this->services = [
            ilObjectServiceSettingsGUI::CUSTOM_METADATA
        ];
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT:
                $this->editSettings();
                break;
            case self::CMD_SAVE:
                $this->saveSettings();
                break;
            default:
                throw new Exception('Unknown command ' . $cmd);
        }
    }

    protected function editSettings(ilPropertyFormGUI $form = null): void
    {
        if (is_null($form)) {
            $form = $this->buildForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function buildForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt('obj_features'));
        $form->addCommandButton(self::CMD_SAVE, $this->txt('save'));
        $form->addCommandButton(self::CMD_EDIT, $this->txt('cancel'));

        $this->addServiceSettingsToForm($form);
        $this->addCommonFieldsToForm($form);

        return $form;
    }

    protected function addServiceSettingsToForm(ilPropertyFormGUI $form): void
    {
        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $form,
            $this->services
        );
    }

    protected function addCommonFieldsToForm(ilPropertyFormGUI $form): void
    {
        //
    }

    protected function saveSettings(): void
    {
        $form = $this->buildForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $form,
            $this->services
        );

        $this->tpl->setOnScreenMessage("success", $this->lng->txt('service_settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_EDIT);
    }

    protected function txt(string $code): string
    {
        return $this->lng->txt($code);
    }
}
