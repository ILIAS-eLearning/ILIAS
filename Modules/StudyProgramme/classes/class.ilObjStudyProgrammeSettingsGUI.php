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

use ILIAS\UI\Component\Input\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as InputFieldFactory;
use ILIAS\UI\Renderer;

/**
 * @ilCtrl_Calls ilObjStudyProgrammeSettingsGUI: ilStudyProgrammeCommonSettingsGUI
 *
 */
class ilObjStudyProgrammeSettingsGUI
{
    private const TAB_SETTINGS = 'settings';
    private const TAB_COMMON_SETTINGS = 'commonSettings';

    public const PROP_TITLE = "title";
    public const PROP_DESC = "desc";
    public const PROP_DEADLINE = "deadline";
    public const PROP_VALIDITY_OF_QUALIFICATION = "validity_qualification";

    public const OPT_NO_DEADLINE = 'opt_no_deadline';
    public const OPT_DEADLINE_PERIOD = "opt_deadline_period";
    public const OPT_DEADLINE_DATE = "opt_deadline_date";

    public const OPT_NO_VALIDITY_OF_QUALIFICATION = 'opt_no_validity_qualification';
    public const OPT_VALIDITY_OF_QUALIFICATION_PERIOD = "opt_validity_qualification_period";
    public const OPT_VALIDITY_OF_QUALIFICATION_DATE = "opt_validity_qualification_date";

    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ILIAS\UI\Component\Input\Factory $input_factory;
    protected ILIAS\UI\Renderer $renderer;
    protected Psr\Http\Message\ServerRequestInterface $request;
    protected ILIAS\Refinery\Factory $refinery_factory;
    protected ILIAS\Data\Factory $data_factory;
    protected ilStudyProgrammeTypeRepository $type_repository;
    protected ilStudyProgrammeCommonSettingsGUI $common_settings_gui;
    protected ilTabsGUI $tabs;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;

    protected ?ilObjStudyProgramme $object;
    protected string $tmp_heading;
    protected int $ref_id;

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ilCtrl,
        ilLanguage $lng,
        Factory $input_factory,
        Renderer $renderer,
        Psr\Http\Message\ServerRequestInterface $request,
        ILIAS\Refinery\Factory $refinery_factory,
        ILIAS\Data\Factory $data_factory,
        ilStudyProgrammeTypeRepository $type_repository,
        ilStudyProgrammeCommonSettingsGUI $common_settings_gui,
        ilTabsGUI $tabs,
        ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->input_factory = $input_factory;
        $this->renderer = $renderer;
        $this->request = $request;
        $this->refinery_factory = $refinery_factory;
        $this->data_factory = $data_factory;
        $this->type_repository = $type_repository;
        $this->common_settings_gui = $common_settings_gui;
        $this->tabs = $tabs;
        $this->request_wrapper = $request_wrapper;

        $this->object = null;

        $lng->loadLanguageModule("prg");
    }

    public function setRefId(int $ref_id): void
    {
        $this->ref_id = $ref_id;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case 'ilstudyprogrammecommonsettingsgui':
                $this->tabs->activateSubTab(self::TAB_COMMON_SETTINGS);
                $this->common_settings_gui->setObject($this->getObject());
                $content = $this->ctrl->forwardCommand($this->common_settings_gui);
                break;
            default:
                $cmd = $this->ctrl->getCmd();
                if ($cmd === "" || $cmd === null) {
                    $cmd = "view";
                }
                switch ($cmd) {
                    case "view":
                        $content = $this->view();
                        break;
                    case "update":
                        $content = $this->$cmd();
                        break;
                    default:
                        throw new ilException(
                            "ilObjStudyProgrammeSettingsGUI: Command not supported: $cmd"
                        );
                }
        }

        if (!$this->ctrl->isAsynch()) {
            $this->tpl->setContent($content);
        } else {
            $output_handler = new ilAsyncOutputHandler();
            $heading = $this->tmp_heading ?? $this->lng->txt("prg_async_" . $this->ctrl->getCmd());
            $output_handler->setHeading($heading);
            $output_handler->setContent($content);
            $output_handler->terminate();
        }
    }

    protected function view(): string
    {
        $this->buildModalHeading(
            $this->lng->txt('prg_async_settings'),
            $this->request_wrapper->has("currentNode")
        );

        $form = $this->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "update"));
        return $this->renderer->render($form);
    }

    /**
     * @return string|void
     */
    protected function update()
    {
        $form = $this
            ->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "update"))
            ->withRequest($this->request);

        $result = $form->getInputGroup()->getContent();

        // This could further be improved by providing a new container for async-forms in the
        // UI-Framework.

        if ($result->isOK()) {
            $result->value()->update();
            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);

            if ($this->ctrl->isAsynch()) {
                $response = ilAsyncOutputHandler::encodeAsyncResponse(
                    array(
                    "success" => true,
                    "message" => $this->lng->txt("msg_obj_modified"))
                );
                return ilAsyncOutputHandler::handleAsyncOutput($this->renderer->render($form), $response, false);
            }

            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_form_save_error"));

            if ($this->ctrl->isAsynch()) {
                $response = ilAsyncOutputHandler::encodeAsyncResponse(
                    array(
                    "success" => false,
                    "errors" => $form->getError())
                );
                return ilAsyncOutputHandler::handleAsyncOutput($this->renderer->render($form), $response, false);
            }

            return $this->renderer->render($form);
        }
    }

    protected function buildModalHeading(string $label, bool $current_node): void
    {
        if (!$current_node) {
            $this->ctrl->saveParameterByClass('ilobjstudyprogrammesettingsgui', 'ref_id');
            $heading_button = ilLinkButton::getInstance();
            $heading_button->setCaption('prg_open_node');
            $heading_button->setUrl(
                $this->ctrl->getLinkTargetByClass(
                    'ilobjstudyprogrammetreegui',
                    'view'
                )
            );

            $heading =
                "<div class=''>" .
                $label .
                "<div class='pull-right'>" .
                $heading_button->render() .
                "</div></div>"
            ;
            $this->tmp_heading = $heading;
        } else {
            $this->tmp_heading = "<div class=''>" . $label . "</div>";
        }
    }

    protected function buildForm(
        ilObjStudyProgramme $prg,
        string $submit_action
    ): ILIAS\UI\Component\Input\Container\Form\Form {
        $trans = $prg->getObjectTranslation();
        $ff = $this->input_factory->field();
        $sp_types = $this->type_repository->getAllTypesArray();
        $settings = $prg->getSettings();

        return $this->input_factory->container()->form()->standard(
            $submit_action,
            $this->buildFormElements(
                $ff,
                $trans,
                $sp_types,
                $settings
            )
        )->withAdditionalTransformation(
            $this->refinery_factory->custom()->transformation(
                function ($values) use ($prg) {
                    $object_data = $values[0];
                    $prg->setTitle($object_data[self::PROP_TITLE]);
                    $prg->setDescription($object_data[self::PROP_DESC]);

                    $settings = $prg->getSettings()
                        ->withAssessmentSettings($values['prg_assessment'])
                        ->withDeadlineSettings($values['prg_deadline'])
                        ->withValidityOfQualificationSettings($values['prg_validity_of_qualification'])
                        ->withAutoMailSettings($values['automail_settings'])
                        ->withTypeSettings($values['prg_type']);

                    $prg->updateSettings($settings);
                    $prg->updateCustomIcon();
                    return $prg;
                }
            )
        );
    }

    protected function buildFormElements(
        InputFieldFactory $ff,
        ilObjectTranslation $trans,
        array $sp_types,
        ilStudyProgrammeSettings $settings
    ): array {
        $return = [
            $this->getEditSection($ff, $trans),
            "prg_type" => $settings
                ->getTypeSettings()
                ->toFormInput($ff, $this->lng, $this->refinery_factory, $sp_types)
            ,
            "prg_assessment" => $settings
                ->getAssessmentSettings()
                ->toFormInput($ff, $this->lng, $this->refinery_factory)
            ,
            "prg_deadline" => $settings
                ->getDeadlineSettings()
                ->toFormInput($ff, $this->lng, $this->refinery_factory, $this->data_factory)
            ,
            "prg_validity_of_qualification" => $settings
                ->getValidityOfQualificationSettings()
                ->toFormInput($ff, $this->lng, $this->refinery_factory, $this->data_factory)
            ,
            "automail_settings" => $settings
                ->getAutoMailSettings()
                ->toFormInput($ff, $this->lng, $this->refinery_factory)
        ];

        return $return;
    }

    protected function getEditSection(
        InputFieldFactory $ff,
        ilObjectTranslation $trans
    ): ILIAS\UI\Component\Input\Field\Section {
        $languages = ilMDLanguageItem::_getLanguages();
        return $ff->section(
            [
                self::PROP_TITLE =>
                    $ff->text($this->txt("title"))
                       ->withValue($trans->getDefaultTitle())
                       ->withRequired(true),
                self::PROP_DESC =>
                    $ff->textarea($this->txt("description"))
                       ->withValue($trans->getDefaultDescription() ?? "")
            ],
            $this->txt("prg_edit"),
            $this->txt("language") . ": " . $languages[$trans->getDefaultLanguage()] .
            ' <a href="' . $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "") .
            '">&raquo; ' . $this->txt("obj_more_translations") . '</a>'
        );
    }

    protected function getObject(): ilObjStudyProgramme
    {
        if ($this->object === null) {
            $this->object = ilObjStudyProgramme::getInstanceByRefId($this->ref_id);
        }
        return $this->object;
    }

    protected function txt(string $code): string
    {
        return $this->lng->txt($code);
    }
}
