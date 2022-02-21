<?php declare(strict_types=1);

class ilStudyProgrammeCommonSettingsGUI
{
    const CMD_EDIT = 'editSettings';
    const CMD_SAVE = 'saveSettings';

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilObjectService $object_service;

    protected ?ilObjStudyProgramme $object = null;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilObjectService $object_service
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->object_service = $object_service;
    }

    /**
     * @return string|void
     * @throws Exception
     */
    public function executeCommand()
    {
        if (is_null($this->object)) {
            throw new Exception('Object of ilObjStudyProgramme is not set');
        }

        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT:
                return $this->editSettings();
            case self::CMD_SAVE:
                $this->saveSettings();
                break;
            default:
                throw new Exception('Unknown command ' . $cmd);
        }
    }

    public function setObject(ilObjStudyProgramme $object) : void
    {
        $this->object = $object;
    }

    protected function editSettings(ilPropertyFormGUI $form = null) : string
    {
        if (is_null($form)) {
            $form = $this->buildForm();
        }
        return $form->getHTML();
    }

    protected function buildForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt('obj_features'));
        $form->addCommandButton(self::CMD_SAVE, $this->txt('save'));
        $form->addCommandButton(self::CMD_EDIT, $this->txt('cancel'));

        $this->addServiceSettingsToForm($form);

        return $form;
    }

    protected function addServiceSettingsToForm(ilPropertyFormGUI $form) : void
    {
        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $form,
            [
                ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS
            ]
        );
    }

    protected function saveSettings() : void
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
            [
                ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS
            ]
        );

        $this->tpl->setOnScreenMessage("success", $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, self::CMD_EDIT);
    }

    protected function txt(string $code) : string
    {
        return $this->lng->txt($code);
    }
}
