<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncOutputHandler.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncPropertyFormGUI.php");
require_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");

use ILIAS\UI\Implementation\Component\Input\Field\Factory as InputFieldFactory;

/**
 * Class ilObjStudyProgrammeSettingsGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjStudyProgrammeSettingsGUI
{
    const PROP_TITLE = "title";
    const PROP_DESC = "desc";
    const PROP_TYPE = "type";
    const PROP_POINTS = "points";
    const PROP_STATUS = "status";
    const PROP_DEADLINE = "deadline";
    const PROP_VALIDITY_OF_QUALIFICATION = "validity_qualification";
    const PROP_RESTART = "restart";
    const PROP_ACCESS_CONTROL_BY_ORGU_POSITION = "access_ctr_by_orgu_position";
    const PROP_CRON_JOB_PRG_NOT_RESTARTED = "prg_not_restarted_by_user";
    const PROP_CRON_JOB_PROCESSING_ENDS_NOT_SUCCESSFUL = "prg_processing_ends_not_successful";
	const PROP_SEND_RE_ASSIGNED_MAIL = "send_re_assigned_mail";
	const PROP_SEND_INFO_TO_RE_ASSIGN_MAIL = "send_info_to_re_assign_mail";
	const PROP_SEND_RISKY_TO_FAIL_MAIL = "send_risky_to_fail_mail";

    const OPT_NO_DEADLINE = 'opt_no_deadline';
    const OPT_DEADLINE_PERIOD = "opt_deadline_period";
    const OPT_DEADLINE_DATE = "opt_deadline_date";

    const OPT_NO_VALIDITY_OF_QUALIFICATION = 'opt_no_validity_qualification';
    const OPT_VALIDITY_OF_QUALIFICATION_PERIOD = "opt_validity_qualification_period";
    const OPT_VALIDITY_OF_QUALIFICATION_DATE = "opt_validity_qualification_date";

    const OPT_NO_RESTART = "opt_no_restart";
    const OPT_RESTART_PERIOD = "opt_restart_period";

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
        \ILIAS\UI\Component\Input\Factory $input_factory,
        \ILIAS\UI\Renderer $renderer,
        \GuzzleHttp\Psr7\ServerRequest $request,
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
                $response = ilAsyncOutputHandler::encodeAsyncResponse(array("success" => true, "message" => $this->lng->txt("msg_obj_modified")));
                return ilAsyncOutputHandler::handleAsyncOutput($form->getHTML(), $response, false);
            } else {
                $this->ctrl->redirect($this);
            }
        } else {
            ilUtil::sendFailure($this->lng->txt("msg_form_save_error"));

            if ($this->ctrl->isAsynch()) {
                $response = ilAsyncOutputHandler::encodeAsyncResponse(array("success" => false, "errors" => $form->getErrors()));
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
            $heading_button->setUrl($this->ctrl->getLinkTargetByClass('ilobjstudyprogrammetreegui', 'view'));

            $heading = "<div class=''>" . $label . "<div class='pull-right'>" . $heading_button->render() . "</div></div>";
            $this->tmp_heading = $heading;
        } else {
            $this->tmp_heading = "<div class=''>" . $label . "</div>";
        }

    }

    protected function buildForm(
        \ilObjStudyProgramme $prg,
        string $submit_action
    ): ILIAS\UI\Component\Input\Container\Form\Form {
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
                    $type_data = $values[1];
                    if ($prg->getSubtypeId() != $type_data[self::PROP_TYPE]) {
                        $prg->setSubtypeId($type_data[self::PROP_TYPE]);
                        $prg->updateCustomIcon();
                        $this->parent_gui->setTitleAndDescription();
                    }
                    $points_data = $values[2];
                    $prg->setPoints($points_data[self::PROP_POINTS]);
                    $prg->setStatus($points_data[self::PROP_STATUS]);
                    $deadline_data = $values[3][self::PROP_DEADLINE];
                    $deadline_type = $deadline_data[0];
                    switch ($deadline_type) {
                        case self::OPT_NO_DEADLINE:
                            $prg->setDeadlineDate(null);
                            break;
                        case self::OPT_DEADLINE_PERIOD:
                            $prg->setDeadlinePeriod((int)array_shift($deadline_data[1]));
                            break;
                        case self::OPT_DEADLINE_DATE:
                            $prg->setDeadlineDate(\DateTime::createFromFormat('d.m.Y', array_shift($deadline_data[1])));
                            break;
                        //default:
                        //	throw new Exception('invalid deadline type '.$deadline_type);
                    }
                    $vq_data = $values[4][self::PROP_VALIDITY_OF_QUALIFICATION];
                    $vq_type = $vq_data[0];
                    switch ($vq_type) {
                        case self::OPT_NO_VALIDITY_OF_QUALIFICATION:
                            $prg->setValidityOfQualificationDate(null);
                            break;
                        case self::OPT_VALIDITY_OF_QUALIFICATION_PERIOD:
                            $prg->setValidityOfQualificationPeriod((int)array_shift($vq_data[1]));
                            break;
                        case self::OPT_VALIDITY_OF_QUALIFICATION_DATE:
                            $prg->setValidityOfQualificationDate(\DateTime::createFromFormat('d.m.Y', array_shift($vq_data[1])));
                            break;
                    }
                    $restart_data = $values[4][self::PROP_RESTART];
                    $restart_type = $restart_data[0];
                    switch ($restart_type) {
                        case self::OPT_NO_RESTART:
                            $prg->setRestartPeriod(ilStudyProgrammeSettings::NO_RESTART);
                            break;
                        case self::OPT_RESTART_PERIOD:
                            $prg->setRestartPeriod((int)array_shift($restart_data[1]));
                            break;
                    }

					$send_re_assigned_mail = $values[5][self::PROP_SEND_RE_ASSIGNED_MAIL];
					$send_info_to_re_assign_mail = !is_null($values[5][self::PROP_SEND_INFO_TO_RE_ASSIGN_MAIL]);
					if ($send_info_to_re_assign_mail) {
						$prg_not_restarted_by_user_days = $values[5][self::PROP_SEND_INFO_TO_RE_ASSIGN_MAIL][0];
						$prg->setReminderNotRestartedByUserDays($prg_not_restarted_by_user_days);
					}
					$send_risky_to_fail_mail = !is_null($values[5][self::PROP_SEND_RISKY_TO_FAIL_MAIL]);
					if ($send_risky_to_fail_mail) {
	                    $prg_processing_ends_not_successful_days = $values[5][self::PROP_SEND_RISKY_TO_FAIL_MAIL][0];
						$prg->setProcessingEndsNotSuccessfulDays($prg_processing_ends_not_successful_days);
					}
					$prg->setSendReAssignedMail($send_re_assigned_mail);
					$prg->setSendInfoToReAssignMail($send_info_to_re_assign_mail);
					$prg->setSendRiskyToFailMail($send_risky_to_fail_mail);

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
    ): array {
        $languages = ilMDLanguageItem::_getLanguages();
        $return = [
            $ff->section(
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
            ),
            $ff->section(
                [
                    self::PROP_TYPE =>
                        $ff->select($this->txt("type"), $sp_types)
                            ->withValue($prg->getSubtypeId() == 0 ? "" : $prg->getSubtypeId())
                            ->withAdditionalTransformation($this->refinery_factory->custom()->transformation(function ($v) {
                                if ($v == "") {
                                    return 0;
                                }
                                return $v;
                            }))
                ],
                $this->txt("prg_type"),
                ""
            ),
            $ff->section(
                [
                    self::PROP_POINTS =>
                        $ff->numeric($this->txt("prg_points"))
                            ->withValue((string)$prg->getPoints())
                            ->withAdditionalTransformation($this->refinery_factory->int()->isGreaterThan(-1)),
                    self::PROP_STATUS =>
                        $ff->select($this->txt("prg_status"), $this->getStatusOptions())
                            ->withValue((string)$prg->getStatus())
                            ->withRequired(true)
                ],
                $this->txt("prg_assessment"),
                ""
            ),
            $ff->section(
                [
                    self::PROP_DEADLINE => $this->getDeadlineSubform($prg)
                ],
                $this->txt("prg_deadline_settings"),
                ""
            ),
            $ff->section(
                [
                    self::PROP_VALIDITY_OF_QUALIFICATION => $this->getValidityOfQualificationSubform($prg),
                    self::PROP_RESTART => $this->getRestartSubform($prg)
                ],
                $this->txt("prg_validity_of_qualification"),
                ""
            ),
            $ff->section(
                $this->getCronJobConfiguration($prg),
                $this->txt("prg_cron_job_configuration"),
                ""
            )
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

    protected function getAccessControlByOrguPositionsForm(
        InputFieldFactory $ff,
        ilObjStudyProgramme $prg
    )
    {
        $checkbox = $ff->checkbox($this->txt("prg_status"), '');
        return $prg->getAccessControlByOrguPositions() ?
            $checkbox->withValue(true) :
            $checkbox->withValue(false);

    }


    protected function getDeadlineSubform(ilObjStudyProgramme $prg)
    {
        $ff = $this->input_factory->field();
        $deadline_period_subform = $ff->numeric('', $this->txt('prg_deadline_period_desc'))
            ->withAdditionalTransformation(
                $this->refinery_factory->int()->isGreaterThan(-1)
            );
        $period = $prg->getDeadlinePeriod();
        $option = self::OPT_NO_DEADLINE;
        if ($period > 0) {
            $deadline_period_subform = $deadline_period_subform->withValue($period)->withAdditionalTransformation($this->refinery_factory->int()->isGreaterThan(-1));
            $option = self::OPT_DEADLINE_PERIOD;
        }
        $deadline_date = $prg->getDeadlineDate();
        $format = $this->data_factory->dateFormat()->germanShort();
        $deadline_date_subform = $ff
            ->dateTime('', $this->txt('prg_deadline_date_desc'))
            ->withFormat($format)
            ->withMinValue(new DateTimeImmutable());
        if ($deadline_date !== null) {
            $deadline_date_subform = $deadline_date_subform->withValue($deadline_date->format($format->toString()));
            $option = self::OPT_DEADLINE_DATE;
        }
        $sg = $ff->switchableGroup(
            [
                self::OPT_NO_DEADLINE =>
                    $ff->group([], $this->txt('prg_no_deadline')),
                self::OPT_DEADLINE_PERIOD =>
                    $ff->group([$deadline_period_subform], $this->txt('prg_deadline_period')),
                self::OPT_DEADLINE_DATE =>
                    $ff->group([$deadline_date_subform], $this->txt('prg_deadline_date'))
            ],
            ''
        );
        return $sg->withValue($option);
    }

    protected function getValidityOfQualificationSubform(ilObjStudyProgramme $prg)
    {
        $ff = $this->input_factory->field();
        $vq_period_subform = $ff
            ->numeric('', $this->txt('validity_qalification_period_desc'))
            ->withAdditionalTransformation(
                $this->refinery_factory->int()->isGreaterThan(-1)
            );
        $option = self::OPT_NO_VALIDITY_OF_QUALIFICATION;
        $period = $prg->getValidityOfQualificationPeriod();
        if ($period !== ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD) {
            $vq_period_subform = $vq_period_subform->withValue($period)->withAdditionalTransformation($this->refinery_factory->int()->isGreaterThan(-1));
            $option = self::OPT_VALIDITY_OF_QUALIFICATION_PERIOD;
        }
        $format = $this->data_factory->dateFormat()->germanShort();
        $vq_date_subform = $ff
            ->dateTime('', $this->txt('validity_qalification_date_desc'))
            ->withMinValue(new DateTimeImmutable())
            ->withFormat($format);
        $date = $prg->getValidityOfQualificationDate();
        if ($date !== null) {
            $vq_date_subform = $vq_date_subform->withValue($date->format($format->toString()));
            $option = self::OPT_VALIDITY_OF_QUALIFICATION_DATE;
        }

        $sg = $ff->switchableGroup(
            [
                self::OPT_NO_VALIDITY_OF_QUALIFICATION =>
                    $ff->group([], $this->txt('prg_no_validity_qalification')),
                self::OPT_VALIDITY_OF_QUALIFICATION_PERIOD =>
                    $ff->group([$vq_period_subform], $this->txt('validity_qalification_period')),
                self::OPT_VALIDITY_OF_QUALIFICATION_DATE =>
                    $ff->group([$vq_date_subform], $this->txt('validity_qalification_date'))
            ],
            ''
        );
        return $sg->withValue($option);
    }

    protected function getCronJobConfiguration(ilObjStudyProgramme $prg)
    {
        $ff = $this->input_factory->field();
        $prg_not_restarted_input =
            $ff->numeric(
                $this->txt('prg_user_not_restarted_time_input'),
                $this->txt('prg_user_not_restarted_time_input_info')
            )
            ->withAdditionalTransformation(
                $this->refinery_factory->int()->isGreaterThan(-1)
            )
			->withRequired(true)
        ;

        $prg_processing_ends_no_success_input =
            $prg_not_restarted_input
                ->withLabel($this->txt('prg_processing_ends_no_success'))
                ->withByLine($this->txt('prg_processing_ends_no_success_info'))
        ;

        $send_re_assigned_mail = $ff->checkbox(
				$this->txt("send_re_assigned_mail"),
				$this->txt('send_re_assigned_mail_info')
			)
			->withValue($prg->shouldSendReAssignedMail())
		;
		$send_info_to_re_assign_mail = $ff->optionalGroup(
				[ $prg_not_restarted_input
				],
				$this->txt("send_info_to_re_assign_mail"),
				$this->txt("send_info_to_re_assign_mail_info")
			)
			->withValue(
				$prg->shouldSendInfoToReAssignMail()
					? [(int)$prg->getReminderNotRestartedByUserDays()]
					: null
			)
		;
		$send_risky_to_fail_mail = $ff->optionalGroup(
				[ $prg_processing_ends_no_success_input
				],
				$this->txt("send_risky_to_fail_mail"),
				$this->txt("send_risky_to_fail_mail_info")
			)
			->withValue(
				$prg->shouldSendRiskyToFailMail()
					? [(int)$prg->getProcessingEndsNotSuccessfulDays()]
					: null
			)
		;

        return [
			self::PROP_SEND_RE_ASSIGNED_MAIL => $send_re_assigned_mail,
			self::PROP_SEND_INFO_TO_RE_ASSIGN_MAIL => $send_info_to_re_assign_mail,
			self::PROP_SEND_RISKY_TO_FAIL_MAIL => $send_risky_to_fail_mail,
        ];
    }

    protected function getRestartSubform(ilObjStudyProgramme $prg)
    {
        $ff = $this->input_factory->field();
        $restart_period_subform = $ff
            ->numeric('', $this->txt('restart_period_desc'))
            ->withAdditionalTransformation(
                $this->refinery_factory->int()->isGreaterThan(-1)
            );
        $option = self::OPT_NO_RESTART;
        $restart_period = $prg->getRestartPeriod();
        if ($restart_period !== ilStudyProgrammeSettings::NO_RESTART) {
            $option = self::OPT_RESTART_PERIOD;
            $restart_period_subform = $restart_period_subform->withValue($restart_period);
        }

        $sg = $ff->switchableGroup(
            [
                self::OPT_NO_RESTART =>
                    $ff->group([], $this->txt('prg_no_restart')),
                self::OPT_RESTART_PERIOD =>
                    $ff->group([$restart_period_subform], $this->txt('restart_period'))
            ],
            ''
        );
        return $sg->withValue($option);
    }

    protected function getObject(): ilObjStudyProgramme
    {
        if ($this->object === null) {
            $this->object = ilObjStudyProgramme::getInstanceByRefId($this->ref_id);
        }
        return $this->object;
    }

    protected function getStatusOptions(): array
    {
        return [
            ilStudyProgrammeSettings::STATUS_DRAFT => $this->lng->txt("prg_status_draft"),
            ilStudyProgrammeSettings::STATUS_ACTIVE => $this->lng->txt("prg_status_active"),
            ilStudyProgrammeSettings::STATUS_OUTDATED => $this->lng->txt("prg_status_outdated")
        ];
    }

    protected function txt(string $code): string
    {
        return $this->lng->txt($code);
    }
}
