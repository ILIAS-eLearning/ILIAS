<?php

declare(strict_types=1);

use \ILIAS\UI\Component\Input\Container\Form;

class ilIndividualAssessmentSettingsGUI
{
    const TAB_EDIT = 'settings';
    const TAB_EDIT_INFO = 'infoSettings';

    protected $ctrl;
    protected $object;
    protected $tpl;
    protected $lng;
    protected $tabs_gui;
    protected $iass_access;
    protected $input_factory;
    protected $refinery;
    protected $ui_renderer;
    protected $http_request;

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
        $this->tabs_gui = $DIC['ilTabs'];
        $this->getSubTabs($this->tabs_gui);
        $this->iass_access = $this->object->accessHandler();

        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('obj');
        $this->lng->loadLanguageModule('cntr');

        $this->input_factory = $DIC->ui()->factory()->input();
        $this->refinery = $DIC->refinery();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http_request = $DIC->http()->request();
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
        if (!$this->iass_access->mayEditObject()) {
            $this->parent_gui->handleAccessViolation();
        }
        switch ($cmd) {
            case 'edit':
                $this->edit();
                break;
            case 'update':
                $this->update();
                break;
            case 'editInfo':
                $this->editInfo();
                break;
            case 'updateInfo':
                $this->updateInfo();
                $this->$cmd();
            break;
        }
    }

    protected function buildForm() : Form\Form
    {
        $settings = $this->object->getSettings();
        $field = $settings->toFormInput(
            $this->input_factory->field(),
            $this->lng,
            $this->refinery
        );
        return $this->input_factory->container()->form()->standard(
            $this->ctrl->getFormAction($this, "update"),
            [$field]
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function($v) {
                return array_shift($v);
            })
        );
    }

    protected function edit()
    {
        $this->tabs_gui->setSubTabActive(self::TAB_EDIT);
        $form = $this->buildForm(); 
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    protected function update()
    {
        $form = $this->buildForm();
        $form = $form->withRequest($this->http_request);

        $settings = $form->getData();

        if (!is_null($settings)) {
            $this->object->setSettings($settings);
            $this->object->update();
            $this->ctrl->redirect($this, "edit");
        }
        else {
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
    }

    protected function editInfo()
    {
        $this->tabs_gui->setSubTabActive(self::TAB_EDIT_INFO);
        $form = $this->buildInfoSettingsForm();
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    protected function updateInfo()
    {
        $form = $this->buildInfoSettingsForm();
        $form = $form->withRequest($this->http_request);

        $info_settings = $form->getData();

        if (!is_null($info_settings)) {
            $this->object->setInfoSettings($info_settings);
            $this->object->updateInfo();
            $this->ctrl->redirect($this, "editInfo");
        }
        else {
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
    }

    protected function buildInfoSettingsForm() : Form\Form
    {
        $info_settings = $this->object->getInfoSettings();
        $field = $info_settings->toFormInput(
            $this->input_factory->field(),
            $this->lng,
            $this->refinery
        );
        return $this->input_factory->container()->form()->standard(
            $this->ctrl->getFormAction($this, "updateInfo"),
            [$field]
        )
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(function($v) {
                    return array_shift($v);
                })
            );
    }
}
