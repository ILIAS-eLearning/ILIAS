<?php

declare(strict_types=1);

use \ILIAS\UI\Component\Input\Container\Form;
use \ILIAS\UI\Component\Input;
use \ILIAS\Refinery;
use \ILIAS\UI;

/**
 * @ilCtrl_Calls ilIndividualAssessmentSettingsGUI: ilIndividualAssessmentCommonSettingsGUI
 */
class ilIndividualAssessmentSettingsGUI
{
    const TAB_EDIT = 'settings';
    const TAB_EDIT_INFO = 'infoSettings';
    const TAB_COMMON_SETTINGS = 'commonSettings';

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjIndividualAssessment
     */
    protected $object;

    /**
     * @var ilGlobalPageTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var IndividualAssessmentAccessHandler
     */
    protected $iass_access;

    /**
     * @var Input\Factory
     */
    protected $input_factory;

    /**
     * @var Refinery\Factory
     */
    protected $refinery;

    /**
     * @var UI\Renderer
     */
    protected $ui_renderer;

    /**
     * @var \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ServerRequestInterface
     */
    protected $http_request;

    /**
     * @var
     */
    protected $error_object;

    protected $common_settings_gui;

    public function __construct(
        ilObjIndividualAssessment $object,
        ilCtrl $ctrl,
        ilGlobalPageTemplate $tpl,
        ilLanguage $lng,
        ilTabsGUI $tabs_gui,
        Input\Factory $factory,
        Refinery\Factory $refinery,
        UI\Renderer $ui_renderer,
        $http_request,
        ilErrorHandling $error_object,
        ilIndividualAssessmentCommonSettingsGUI $common_settings_gui
    ) {
        $this->ctrl = $ctrl;
        $this->object = $object;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->tabs_gui = $tabs_gui;
        $this->iass_access = $this->object->accessHandler();

        $this->input_factory = $factory;
        $this->refinery = $refinery;
        $this->ui_renderer = $ui_renderer;
        $this->http_request = $http_request;

        $this->error_object = $error_object;
        $this->common_settings_gui = $common_settings_gui;

        $this->getSubTabs($this->tabs_gui);
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
            self::TAB_COMMON_SETTINGS,
            $this->lng->txt("obj_features"),
            $this->ctrl->getLinkTargetByClass(
                [
                    self::class,
                    ilIndividualAssessmentCommonSettingsGUI::class
                ],
                ilIndividualAssessmentCommonSettingsGUI::CMD_EDIT
            )
        );
        $tabs->addSubTab(
            self::TAB_EDIT_INFO,
            $this->lng->txt("iass_edit_info"),
            $this->ctrl->getLinkTarget($this, 'editInfo')
        );
    }

    public function executeCommand()
    {
        if (!$this->iass_access->mayEditObject()) {
            $this->handleAccessViolation();
        }
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            case 'ilindividualassessmentcommonsettingsgui':
                $this->tabs_gui->activateSubTab(self::TAB_COMMON_SETTINGS);
                $this->ctrl->forwardCommand($this->common_settings_gui);
                break;
            default:
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
                        break;
                }
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
            $this->refinery->custom()->transformation(function ($v) {
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
            ilUtil::sendSuccess($this->lng->txt("iass_settings_saved"), true);
            $this->ctrl->redirect($this, "edit");
        } else {
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
            ilUtil::sendSuccess($this->lng->txt("iass_settings_saved"), true);
            $this->ctrl->redirect($this, "editInfo");
        } else {
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
                $this->refinery->custom()->transformation(function ($v) {
                    return array_shift($v);
                })
            );
    }

    public function handleAccessViolation()
    {
        $this->error_object->raiseError($this->txt("msg_no_perm_read"), $this->error_object->WARNING);
    }
}
