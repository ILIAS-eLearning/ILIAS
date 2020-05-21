<?php

declare(strict_types=1);

class ilIndividualAssessmentCommonSettingsGUI
{
    const CMD_EDIT = 'editSettings';
    const CMD_SAVE = 'saveSettings';

    /**
     * @var ilObjIndividualAssessment
     */
    protected $object;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjectService
     */
    protected $object_service;

    public function __construct(
        ilObjIndividualAssessment $object,
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
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch($cmd) {
            case self::CMD_EDIT:
                $this->editSettings();
                break;
            case self::CMD_SAVE:
                $this->saveSettings();
                break;
            default:
                throw new Exception('Unknown command '.$cmd);
        }
    }

    protected function editSettings(ilPropertyFormGUI $form = null)
    {
        if(is_null($form)) {
            $form = $this->buildForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function buildForm() : ilPropertyFormGUI
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

    protected function addServiceSettingsToForm(ilPropertyFormGUI $form)
    {
        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $form,
            [
                ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            ]
        );
    }

    protected function addCommonFieldsToForm(ilPropertyFormGUI $form)
    {
        $section_appearance = new ilFormSectionHeaderGUI();
        $section_appearance->setTitle($this->txt('cont_presentation'));
        $form->addItem($section_appearance);
        $form_service = $this->object_service->commonSettings()->legacyForm($form, $this->object);
        $form = $form_service->addTitleIconVisibility();
        $form = $form_service->addTopActionsVisibility();
        $form = $form_service->addIcon();
        $form = $form_service->addTileImage();
    }

    protected function saveSettings()
    {
        $form = $this->buildForm();

        if(! $form->checkInput()) {
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $form,
            [
                ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            ]
        );

        $form_service = $this->object_service->commonSettings()->legacyForm($form, $this->object);
        $form_service->saveTitleIconVisibility();
        $form_service->saveTopActionsVisibility();
        $form_service->saveIcon();
        $form_service->saveTileImage();

        ilUtil::sendSuccess($this->lng->txt('iass_settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_EDIT);
    }

    protected function txt(string $code) : string
    {
        return $this->lng->txt($code);
    }
}