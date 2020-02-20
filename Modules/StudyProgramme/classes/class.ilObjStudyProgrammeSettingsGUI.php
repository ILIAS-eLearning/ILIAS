<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\UI\Component\Input\Factory;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as InputFieldFactory;
use ILIAS\UI\Renderer;

/**
 * Class ilObjStudyProgrammeSettingsGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjStudyProgrammeSettingsGUI
{
    const PROP_TITLE = "title";
    const PROP_DESC = "desc";
    const PROP_DEADLINE = "deadline";
    const PROP_VALIDITY_OF_QUALIFICATION = "validity_qualification";
    const PROP_ACCESS_CONTROL_BY_ORGU_POSITION = "access_ctr_by_orgu_position";

    const OPT_NO_DEADLINE = 'opt_no_deadline';
    const OPT_DEADLINE_PERIOD = "opt_deadline_period";
    const OPT_DEADLINE_DATE = "opt_deadline_date";

    const OPT_NO_VALIDITY_OF_QUALIFICATION = 'opt_no_validity_qualification';
    const OPT_VALIDITY_OF_QUALIFICATION_PERIOD = "opt_validity_qualification_period";
    const OPT_VALIDITY_OF_QUALIFICATION_DATE = "opt_validity_qualification_date";


    /**
     * @var ilCtrl
     */
    public $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    public $tpl;

    /**
     * @var ilObjStudyProgramme
     */
    public $object;

    /**
     * @var ilLanguage
     */
    public $lng;

    /**
     * @var ilObjStudyProgrammeGUI
     */
    protected $parent_gui;

    /**
     * @var string
     */
    protected $tmp_heading;

    /**
     * @var ILIAS\UI\Component\Input\Factory
     */
    protected $input_factory;

    /**
     * @var ILIAS\UI\Renderer
     */
    protected $renderer;

    /**
     * @var Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery_factory;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var \ILIAS\Data\Factory
     */
    protected $data_factory;

    /**
     * @var ilStudyProgrammeTypeRepository
     */
    protected $type_repository;

    public function __construct(
        \ilGlobalTemplateInterface $tpl,
        \ilCtrl $ilCtrl,
        \ilLanguage $lng,
        Factory $input_factory,
        Renderer $renderer,
        ServerRequest $request,
        \ILIAS\Refinery\Factory $refinery_factory,
        \ILIAS\Data\Factory $data_factory,
        ilStudyProgrammeTypeRepository $type_repository
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
        $this->object = null;

        $lng->loadLanguageModule("prg");
    }

    public function setParentGUI($a_parent_gui)
    {
        $this->parent_gui = $a_parent_gui;
    }

    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();


        if ($cmd == "") {
            $cmd = "view";
        }

        switch ($cmd) {
            case "view":
            case "update":
            case "cancel":
                $content = $this->$cmd();
                break;
            default:
                throw new ilException("ilObjStudyProgrammeSettingsGUI: " .
                    "Command not supported: $cmd");
        }

        if (!$this->ctrl->isAsynch()) {
            $this->tpl->setContent($content);
        } else {
            $output_handler = new ilAsyncOutputHandler();
            $heading = $this->lng->txt("prg_async_" . $this->ctrl->getCmd());
            if (isset($this->tmp_heading)) {
                $heading = $this->tmp_heading;
            }
            $output_handler->setHeading($heading);
            $output_handler->setContent($content);
            $output_handler->terminate();
        }
    }

    protected function view()
    {
        $this->buildModalHeading($this->lng->txt('prg_async_settings'), isset($_GET["currentNode"]));

        $form = $this->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "update"));
        return $this->renderer->render($form);
    }

    protected function update()
    {
        $form = $this
            ->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "update"))
            ->withRequest($this->request);

        $result = $form->getInputGroup()->getContent();

        // This could further improved by providing a new container for asynch-forms in the
        // UI-Framework.

        if ($result->isOK()) {
            $result->value()->update();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

            if ($this->ctrl->isAsynch()) {
                $response = ilAsyncOutputHandler::encodeAsyncResponse(
                    array(
                    "success" => true,
                    "message" => $this->lng->txt("msg_obj_modified"))
                );
                return ilAsyncOutputHandler::handleAsyncOutput($form->getHTML(), $response, false);
            } else {
                $this->ctrl->redirect($this);
            }
        } else {
            ilUtil::sendFailure($this->lng->txt("msg_form_save_error"));

            if ($this->ctrl->isAsynch()) {
                $response = ilAsyncOutputHandler::encodeAsyncResponse(
                    array(
                    "success" => false,
                    "errors" => $form->getErrors())
                );
                return ilAsyncOutputHandler::handleAsyncOutput($form->getHTML(), $response, false);
            } else {
                return $this->renderer->render($form);
            }
        }
    }

    protected function cancel()
    {
        ilAsyncOutputHandler::handleAsyncOutput(ilAsyncOutputHandler::encodeAsyncResponse());

        $this->ctrl->redirect($this->parent_gui);
    }

    protected function buildModalHeading($label, $current_node)
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
        \ilObjStudyProgramme $prg,
        string $submit_action
    ) : ILIAS\UI\Component\Input\Container\Form\Form {
        $trans = $prg->getObjectTranslation();
        $ff = $this->input_factory->field();
        $sp_types = $this->type_repository->readAllTypesArray();

        return $this->input_factory->container()->form()->standard(
            $submit_action,
            $this->buildFormElements(
                $ff,
                $trans,
                $sp_types,
                $prg
            )
        )->withAdditionalTransformation(
            $this->refinery_factory->custom()->transformation(
                function ($values) use ($prg) {
                    // to the section they originated from.
                    $object_data = $values[0];
                    $prg->setTitle($object_data[self::PROP_TITLE]);
                    $prg->setDescription($object_data[self::PROP_DESC]);

                    $type_settings = $values['prg_type'];
                    $type = $type_settings->getTypeId();
                    if ($prg->getTypeSettings()->getTypeId() != $type) {
                        $prg->setTypeSettings($type_settings);
                        $prg->updateCustomIcon();
                        $this->parent_gui->setTitleAndDescription();
                    }

                    $prg->setAssessmentSettings($values['prg_assessment']);
                    $prg->setDeadlineSettings($values['prg_deadline']);
                    $prg->setValidityOfQualificationSettings(
                        $values['prg_validity_of_qualification']
                    );

                    $prg->setAutoMailSettings($values["automail_settings"]);

                    if (array_key_exists(6, $values)) {
                        $prg->setAccessControlByOrguPositions(
                            $values[5][self::PROP_ACCESS_CONTROL_BY_ORGU_POSITION] === "checked"
                        );
                    }
                    return $prg;
                }
            )
        );
    }

    protected function buildFormElements(
        InputFieldFactory $ff,
        ilObjectTranslation $trans,
        array $sp_types,
        ilObjStudyProgramme $prg
    ) : array {
        global $DIC;
        $ilLng = $DIC->language();
        $refinery = $DIC["refinery"];

        $return = [
            $this->getEditSection($ff, $trans),
            "prg_type" => $prg
                ->getTypeSettings()
                ->toFormInput($ff, $ilLng, $refinery, $sp_types)
            ,
            "prg_assessment" => $prg
                ->getAssessmentSettings()
                ->toFormInput($ff, $ilLng, $refinery)
            ,
            "prg_deadline" => $prg
                ->getDeadlineSettings()
                ->toFormInput($ff, $ilLng, $refinery, $this->data_factory)
            ,
            "prg_validity_of_qualification" => $prg
                ->getValidityOfQualificationSettings()
                ->toFormInput($ff, $ilLng, $refinery, $this->data_factory)
            ,
            "automail_settings" => $prg
                ->getAutoMailSettings()
                ->toFormInput($ff, $ilLng, $refinery)
        ];
        if ($prg->getPositionSettingsIsActiveForPrg()
            && $prg->getPositionSettingsIsChangeableForPrg()) {
            $return[] = $ff->section(
                [
                    self::PROP_ACCESS_CONTROL_BY_ORGU_POSITION =>
                        $this->getAccessControlByOrguPositionsForm(
                            $ff,
                            $prg
                        )
                ],
                $this->txt('access_ctr_by_orgu_position'),
                ''
            );
        }
        return $return;
    }

    protected function getEditSection(
        InputFieldFactory $ff,
        ilObjectTranslation $trans
    ) {
        $languages = ilMDLanguageItem::_getLanguages();
        return $ff->section(
            [
                self::PROP_TITLE =>
                    $ff->text($this->txt("title"))
                       ->withValue($trans->getDefaultTitle())
                       ->withRequired(true),
                self::PROP_DESC =>
                    $ff->textarea($this->txt("description"))
                       ->withValue($trans->getDefaultDescription())
            ],
            $this->txt("prg_edit"),
            $this->txt("language") . ": " . $languages[$trans->getDefaultLanguage()] .
            ' <a href="' . $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "") .
            '">&raquo; ' . $this->txt("obj_more_translations") . '</a>'
        );
    }

    protected function getAccessControlByOrguPositionsForm(
        InputFieldFactory $ff,
        ilObjStudyProgramme $prg
    ) {
        $checkbox = $ff->checkbox($this->txt("prg_status"), '');
        return $prg->getAccessControlByOrguPositions() ?
            $checkbox->withValue(true) :
            $checkbox->withValue(false);
    }

    protected function getObject() : ilObjStudyProgramme
    {
        if ($this->object === null) {
            $this->object = ilObjStudyProgramme::getInstanceByRefId($this->ref_id);
        }
        return $this->object;
    }

    protected function txt(string $code) : string
    {
        return $this->lng->txt($code);
    }
}
