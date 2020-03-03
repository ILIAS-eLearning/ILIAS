<?php

class ilIndividualAssessmentSettingsGUI
{
    const PROP_CONTENT = "content";
    const PROP_RECORD_TEMPLATE = "record_template";
    const PROP_TITLE = "title";
    const PROP_DESCRIPTION = "description";
    const PROP_EVENT_TIME_PLACE_REQUIRED = "event_time_place_required";
    const PROP_FILE_REQUIRED = "file_required";

    const PROP_INFO_CONTACT = "contact";
    const PROP_INFO_RESPONSIBILITY = "responsibility";
    const PROP_INFO_PHONE = "phone";
    const PROP_INFO_MAILS = "mails";
    const PROP_INFO_CONSULTATION = "consultation";

    const TAB_EDIT = 'settings';
    const TAB_EDIT_INFO = 'infoSettings';

    public function __construct($a_parent_gui, $a_ref_id)
    {
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->parent_gui = $a_parent_gui;
        /** @var ilObjIndividualAssessment object */
        $this->object = $a_parent_gui->object;
        $this->ref_id = $a_ref_id;
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->tabs_gui = $a_parent_gui->tabsGUI();
        $this->getSubTabs($this->tabs_gui);
        $this->iass_access = $this->object->accessHandler();
        $this->obj_service = $DIC->object();

        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('obj');
        $this->lng->loadLanguageModule('cntr');
    }

    protected function getSubTabs(ilTabsGUI $tabs)
    {
        $tabs->addSubTab(
            self::TAB_EDIT,
            $this->lng->txt("edit"),
            $this->ctrl->getLinkTarget($this, 'edit')
        );
        $tabs->addSubTab(
            self::TAB_EDIT_INFO,
            $this->lng->txt("iass_edit_info"),
            $this->ctrl->getLinkTarget($this, 'editInfo')
        );
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case 'edit':
            case 'update':
            case 'cancel':
            case 'editInfo':
            case 'updateInfo':
                if (!$this->iass_access->mayEditObject()) {
                    $this->parent_gui->handleAccessViolation();
                }
                $this->$cmd();
            break;
        }
    }


    protected function cancel()
    {
        $this->ctrl->redirect($this->parent_gui);
    }

    protected function edit()
    {
        $this->tabs_gui->setSubTabActive(self::TAB_EDIT);
        $form = $this->fillForm($this->initSettingsForm(), $this->object, $this->object->getSettings());
        $this->addCommonFieldsToForm($form);
        $this->renderForm($form);
    }

    protected function editInfo()
    {
        $this->tabs_gui->setSubTabActive(self::TAB_EDIT_INFO);
        $form = $this->fillInfoForm($this->initInfoSettingsForm(), $this->object->getInfoSettings());
        $this->renderForm($form);
    }

    protected function updateInfo()
    {
        $this->tabs_gui->setSubTabActive(self::TAB_EDIT_INFO);
        $form = $this->initInfoSettingsForm();
        $form->setValuesByArray($_POST);
        if ($form->checkInput()) {
            $this->object->getInfoSettings()
                ->setContact($_POST[self::PROP_INFO_CONTACT])
                ->setResponsibility($_POST[self::PROP_INFO_RESPONSIBILITY])
                ->setPhone($_POST[self::PROP_INFO_PHONE])
                ->setMails($_POST[self::PROP_INFO_MAILS])
                ->setConsultationHours($_POST[self::PROP_INFO_CONSULTATION]);
            $this->object->updateInfo();
            ilUtil::sendSuccess($this->lng->txt('iass_settings_saved'), true);
        }
        $this->ctrl->redirect($this, "editInfo");
    }

    protected function renderForm(ilPropertyFormGUI $a_form)
    {
        $this->tpl->setContent($a_form->getHTML());
    }

    protected function update()
    {
        $this->tabs_gui->setSubTabActive(self::TAB_EDIT);
        $form = $this->initSettingsForm();
        $form->setValuesByArray($_POST);
        $this->addCommonFieldsToForm($form);
        if ($form->checkInput()) {
            $this->object->setTitle($_POST[self::PROP_TITLE]);
            $this->object->setDescription($_POST[self::PROP_DESCRIPTION]);
            $this->object->getSettings()->setContent($_POST[self::PROP_CONTENT])
                                ->setRecordTemplate($_POST[self::PROP_RECORD_TEMPLATE])
                                ->setEventTimePlaceRequired((bool) $_POST[self::PROP_EVENT_TIME_PLACE_REQUIRED])
                                ->setFileRequired((bool) $_POST[self::PROP_FILE_REQUIRED]);
            $this->object->update();
            ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                $this->object->getId(),
                $form,
                [
                    ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
                    ilObjectServiceSettingsGUI::CUSTOM_METADATA
                ]
            );
            $form_service = $this->obj_service->commonSettings()->legacyForm($form, $this->object);
            $form_service->saveTitleIconVisibility();
            $form_service->saveTopActionsVisibility();
            $form_service->saveIcon();
            $form_service->saveTileImage();
            ilUtil::sendSuccess($this->lng->txt('iass_settings_saved'), true);
        }
        $this->ctrl->redirect($this, "edit");
    }


    protected function initSettingsForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('iass_edit'));

        // title
        $ti = new ilTextInputGUI($this->lng->txt('title'), self::PROP_TITLE);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt('description'), self::PROP_DESCRIPTION);
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);


        $item = new ilTextAreaInputGUI($this->lng->txt('iass_content'), self::PROP_CONTENT);
        $item->setInfo($this->lng->txt('iass_content_explanation'));
        $form->addItem($item);

        $item = new ilTextAreaInputGUI($this->lng->txt('iass_record_template'), self::PROP_RECORD_TEMPLATE);
        $item->setInfo($this->lng->txt('iass_record_template_explanation'));
        $form->addItem($item);

        $option = new ilCheckboxInputGUI($this->lng->txt('iass_event_time_place_required'), self::PROP_EVENT_TIME_PLACE_REQUIRED);
        $option->setInfo($this->lng->txt('iass_event_time_place_required_info'));
        $form->addItem($option);

        $option = new ilCheckboxInputGUI($this->lng->txt('iass_file_required'), self::PROP_FILE_REQUIRED);
        $option->setInfo($this->lng->txt('iass_file_required_info'));
        $form->addItem($option);

        $form->addCommandButton('update', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("obj_features"));
        $form->addItem($sh);

        ilObjectServiceSettingsGUI::initServiceSettingsForm(
            $this->object->getId(),
            $form,
            [
                ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            ]
        );

        return $form;
    }

    protected function addCommonFieldsToForm(\ilPropertyFormGUI $form)
    {
        $section_appearance = new ilFormSectionHeaderGUI();
        $section_appearance->setTitle($this->lng->txt('cont_presentation'));
        $form->addItem($section_appearance);
        $form_service = $this->obj_service->commonSettings()->legacyForm($form, $this->object);
        $form = $form_service->addTitleIconVisibility();
        $form = $form_service->addTopActionsVisibility();
        $form = $form_service->addIcon();
        $form = $form_service->addTileImage();
    }

    protected function initInfoSettingsForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('iass_edit_info'));

        $ti = new ilTextInputGUI($this->lng->txt('iass_contact'), self::PROP_INFO_CONTACT);
        $ti->setSize(40);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt('iass_responsibility'), self::PROP_INFO_RESPONSIBILITY);
        $ti->setSize(40);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt('iass_phone'), self::PROP_INFO_PHONE);
        $ti->setSize(40);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt('iass_mails'), self::PROP_INFO_MAILS);
        $ti->setInfo($this->lng->txt('iass_info_emails_expl'));
        $ti->setSize(300);
        $form->addItem($ti);

        $item = new ilTextAreaInputGUI($this->lng->txt('iass_consultation_hours'), self::PROP_INFO_CONSULTATION);
        $form->addItem($item);

        $form->addCommandButton('updateInfo', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        return $form;
    }

    protected function fillInfoForm(ilPropertyFormGUI $a_form, ilIndividualAssessmentInfoSettings $settings)
    {
        $a_form->setValuesByArray(array(
              self::PROP_INFO_CONTACT => $settings->contact()
            , self::PROP_INFO_RESPONSIBILITY => $settings->responsibility()
            , self::PROP_INFO_PHONE => $settings->phone()
            , self::PROP_INFO_MAILS => $settings->mails()
            , self::PROP_INFO_CONSULTATION => $settings->consultationHours()
            ));
        return $a_form;
    }

    protected function fillForm(ilPropertyFormGUI $a_form, ilObjIndividualAssessment $iass, ilIndividualAssessmentSettings $settings)
    {
        $a_form->setValuesByArray(array(
              self::PROP_TITLE => $iass->getTitle()
            , self::PROP_DESCRIPTION => $iass->getDescription()
            , self::PROP_CONTENT => $settings->content()
            , self::PROP_RECORD_TEMPLATE => $settings->recordTemplate()
            , self::PROP_EVENT_TIME_PLACE_REQUIRED => $settings->eventTimePlaceRequired()
            , self::PROP_FILE_REQUIRED => $settings->fileRequired()
            , ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS => (bool) ilOrgUnitGlobalSettings::getInstance()->isPositionAccessActiveForObject($iass->getId())
            , ilObjectServiceSettingsGUI::CUSTOM_METADATA => ilContainer::_lookupContainerSetting(
                $this->object->getId(),
                ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                false
            )
            ));
        return $a_form;
    }
}
