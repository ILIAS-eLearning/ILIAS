<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncOutputHandler.php");
require_once("./Modules/StudyProgramme/classes/helpers/class.ilAsyncPropertyFormGUI.php");
require_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");

/**
 * Class ilObjStudyProgrammeSettingsGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */
use ILIAS\UI\Implementation\Component\Input\Field\Factory  as InputFieldFactory;

class ilObjStudyProgrammeSettingsGUI {
	/**
	 * @var ilCtrl
	 */
	public $ctrl;
	
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	
	/**
	 * @var ilObjStudyProgramme
	 */
	public $object;
	
	/**
	 * @var ilLng
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
		$this->validation = $validation;
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

	public function executeCommand() {
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
				throw new ilException("ilObjStudyProgrammeSettingsGUI: ".
									  "Command not supported: $cmd");
		}

		if(!$this->ctrl->isAsynch()) {
			$this->tpl->setContent($content);
		} else {
			$output_handler = new ilAsyncOutputHandler();
			$heading = $this->lng->txt("prg_async_".$this->ctrl->getCmd());
			if(isset($this->tmp_heading)) {
				$heading = $this->tmp_heading;
			}
			$output_handler->setHeading($heading);
			$output_handler->setContent($content);
			$output_handler->terminate();
		}
	}
	
	protected function view() {
		$this->buildModalHeading($this->lng->txt('prg_async_settings'),isset($_GET["currentNode"]));

		$form = $this->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "update"));
		return $this->renderer->render($form);
	}
	
	protected function update() {
		$form = $this
			->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "update"))
			->withRequest($this->request);
		$result = $form->getInputGroup()->getContent();
		// This could further improved by providing a new container for asynch-forms in the
		// UI-Framework.

		if ($result->isOK()) {
			$result->value()->update();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);

			if($this->ctrl->isAsynch()) {
				$response = ilAsyncOutputHandler::encodeAsyncResponse(array("success"=>true, "message"=>$this->lng->txt("msg_obj_modified")));
				return ilAsyncOutputHandler::handleAsyncOutput($form->getHTML(), $response, false);
			} else {
				$this->ctrl->redirect($this);
			}
		} else {
			ilUtil::sendFailure($this->lng->txt("msg_form_save_error"));

			if($this->ctrl->isAsynch()) {
				$response = ilAsyncOutputHandler::encodeAsyncResponse(array("success"=>false, "errors"=>$form->getErrors()));
				return ilAsyncOutputHandler::handleAsyncOutput($form->getHTML(), $response, false);
			} else {
				return $this->renderer->render($form);
			}
		}
	}

	protected function cancel() {
		ilAsyncOutputHandler::handleAsyncOutput(ilAsyncOutputHandler::encodeAsyncResponse());

		$this->ctrl->redirect($this->parent_gui);
	}

	protected function buildModalHeading($label, $current_node) {
		if(!$current_node) {
			$this->ctrl->saveParameterByClass('ilobjstudyprogrammesettingsgui', 'ref_id');
			$heading_button = ilLinkButton::getInstance();
			$heading_button->setCaption('prg_open_node');
			$heading_button->setUrl($this->ctrl->getLinkTargetByClass('ilobjstudyprogrammetreegui', 'view'));

			$heading = "<div class=''>".$label."<div class='pull-right'>".$heading_button->render()."</div></div>";
			$this->tmp_heading = $heading;
		} else {
			$this->tmp_heading = "<div class=''>".$label."</div>";
		}
		
	}
	
	const PROP_TITLE = "title";
	const PROP_DESC = "desc";
	const PROP_TYPE = "type";
	const PROP_POINTS = "points";
	const PROP_STATUS = "status";
	const PROP_DEADLINE = "deadline";
	const PROP_DEADLINE_PERIOD = "deadline_period";
	const PROP_DEADLINE_DATE = "deadline_date";
	const PROP_VALIDITY_OF_QUALIFICATION = "validity_qualification";
	const PROP_VALIDITY_OF_QUALIFICATION_PERIOD = "validity_qualification_period";
	const PROP_VALIDITY_OF_QUALIFICATION_DATE = "validity_qualification_date";
	const PROP_RESTART = "restart";
	const PROP_RESTART_PERIOD = "restart_period";
	const PROP_ACCESS_CONTROL_BY_ORGU_POSITION = "access_ctr_by_orgu_position";

	const OPT_NO_DEADLINE = 'opt_no_deadline';
	const OPT_DEADLINE_PERIOD = "opt_deadline_period";
	const OPT_DEADLINE_DATE = "opt_deadline_date";

	const OPT_NO_VALIDITY_OF_QUALIFICATION = 'opt_no_validity_qualification';
	const OPT_VALIDITY_OF_QUALIFICATION_PERIOD = "opt_validity_qualification_period";
	const OPT_VALIDITY_OF_QUALIFICATION_DATE = "opt_validity_qualification_date";

	const OPT_NO_RESTART = "opt_no_restart";
	const OPT_RESTART_PERIOD = "opt_restart_period";

	protected function buildForm(\ilObjStudyProgramme $prg, string $submit_action) : ILIAS\UI\Component\Input\Container\Form\Standard {
		$trans = $prg->getObjectTranslation();
		$ff = $this->input_factory->field();
		$txt = function($id) { return $this->lng->txt($id); };
		$sp_types = $this->type_repository->readAllTypesArray();
		$languages = ilMDLanguageItem::_getLanguages();
		return $this->input_factory->container()->form()->standard(
			$submit_action,
			$this->buildFormElements(
				$ff,
				$txt,
				$trans,
				$sp_types,
				$prg
			)
		)->withAdditionalTransformation($this->refinery_factory->custom()->transformation(function($values) use ($prg) {
			// values now contains the results of the single sections,
			// i.e. a list of arrays that each contains keys according
			// to the section they originated from.
			$object_data = $values[0];
			$prg->setTitle($object_data[self::PROP_TITLE]);
			$prg->setDescription($object_data[self::PROP_DESC]);
			$type_data = $values[1];
			if($prg->getSubtypeId() != $type_data[self::PROP_TYPE]) {
				$prg->setSubtypeId($type_data[self::PROP_TYPE]);
				$prg->updateCustomIcon();
				$this->parent_gui->setTitleAndDescription();
			}
			$points_data = $values[2];
			$prg->setPoints($points_data[self::PROP_POINTS]);
			$prg->setStatus($points_data[self::PROP_STATUS]);
			$deadline_data = $values[3][self::PROP_DEADLINE];
			$deadline_type = $deadline_data[0];
			switch($deadline_type) {
				case self::OPT_NO_DEADLINE:
					$prg->setDeadlineDate(null);
					break;
				case self::OPT_DEADLINE_PERIOD:
					$prg->setDeadlinePeriod((int)array_shift($deadline_data[1]));
					break;
				case self::OPT_DEADLINE_DATE:
					$prg->setDeadlineDate(\DateTime::createFromFormat('d.m.Y',array_shift($deadline_data[1])));
					break;
				//default:
				//	throw new Exception('invalid deadline type '.$deadline_type);
			}
			$vq_data = $values[4][self::PROP_VALIDITY_OF_QUALIFICATION];
			$vq_type = $vq_data[0];
			switch($vq_type) {
				case self::OPT_NO_VALIDITY_OF_QUALIFICATION:
					$prg->setValidityOfQualificationDate(null);
					break;
				case self::OPT_VALIDITY_OF_QUALIFICATION_PERIOD:
					$prg->setValidityOfQualificationPeriod((int)array_shift($vq_data[1]));
					break;
				case self::OPT_VALIDITY_OF_QUALIFICATION_DATE:
					$prg->setValidityOfQualificationDate(\DateTime::createFromFormat('d.m.Y',array_shift($vq_data[1])));
					break;
			}
			$restart_data = $values[4][self::PROP_RESTART];
			$restart_type = $restart_data[0];
			switch($restart_type) {
				case self::OPT_NO_RESTART:
					$prg->setRestartPeriod(ilStudyProgrammeSettings::NO_RESTART);
					break;
				case self::OPT_RESTART_PERIOD:
					$prg->setRestartPeriod((int)array_shift($restart_data[1]));
					break;
			}
			if(array_key_exists(5, $values)) {
				$prg->setAccessControlByOrguPositions(
					$values[5][self::PROP_ACCESS_CONTROL_BY_ORGU_POSITION] === "checked"
				);
			}
			return $prg;
		}));;
	}

	protected function buildFormElements(
		InputFieldFactory $ff,
		Closure $txt,
		ilObjectTranslation $trans,
		array $sp_types,
		ilObjStudyProgramme $prg
	) : array
	{
		$return = [
			$ff->section(
				[
					self::PROP_TITLE =>
						$ff->text($txt("title"))
							->withValue($trans->getDefaultTitle())
							->withRequired(true),
					self::PROP_DESC =>
						$ff->textarea($txt("description"))
							->withValue($trans->getDefaultDescription())
				],
				$txt("prg_edit"),
				$txt("language").": ".$languages[$trans->getDefaultLanguage()].
					' <a href="'.$this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "").
					'">&raquo; '.$txt("obj_more_translations").'</a>'
			),
			$ff->section(
				[
					self::PROP_TYPE =>
						$ff->select($txt("type"), $sp_types)
							->withValue($prg->getSubtypeId() == 0 ? "" : $prg->getSubtypeId())
							->withAdditionalTransformation($this->refinery_factory->custom()->transformation(function($v) {
								if ($v == "") {
									return 0;
								}
								return $v;
							}))
				],
				$txt("prg_type"),
				""
			),
			$ff->section(
				[
					self::PROP_POINTS =>
						$ff->numeric($txt("prg_points"))
							->withValue((string)$prg->getPoints())
							->withAdditionalTransformation($this->refinery_factory->int()->isGreaterThan(-1)),
					self::PROP_STATUS =>
						$ff->select($txt("prg_status"), $this->getStatusOptions())
							->withValue((string)$prg->getStatus())
							->withRequired(true)
				],
				$txt("prg_assessment"),
				""
			),
			$ff->section(
				[self::PROP_DEADLINE => $this->getDeadlineSubform($prg)],
				$txt("prg_deadline_settings"),
				""
			),
			$ff->section(
				[
					self::PROP_VALIDITY_OF_QUALIFICATION => $this->getValidityOfQualificationSubform($prg)
					,self::PROP_RESTART => $this->getRestartSubform($prg)
				],
				$txt("prg_validity_of_qualification"),
				""
			)
		];
		if($prg->getPositionSettingsIsActiveForPrg()
			&& $prg->getPositionSettingsIsChangeableForPrg()) {
			$return[] = $ff->section(
					[
						self::PROP_ACCESS_CONTROL_BY_ORGU_POSITION =>
							$this->getAccessControlByOrguPositionsForm(
								$ff,
								$txt,
								$prg
							)
					],
					$txt('access_ctr_by_orgu_position'),
					''
				);
		}
		return $return;
	}

	protected function getAccessControlByOrguPositionsForm(
		InputFieldFactory $ff,
		Closure $txt,
		ilObjStudyProgramme $prg
	)
	{
		$checkbox = $ff->checkbox($txt("prg_status"),'');
		return $prg->getAccessControlByOrguPositions() ?
			$checkbox->withValue(true) :
			$checkbox->withValue(false);

	}


	protected function getDeadlineSubform($prg)
	{
		$ff = $this->input_factory->field();
		$txt = function($id) { return $this->lng->txt($id); };
		$deadline_period_subform = $ff->numeric('',$txt('prg_deadline_period_desc'))
										->withAdditionalTransformation(
											$this->refinery_factory->int()->isGreaterThan(-1)
									);
		$period = $prg->getDeadlinePeriod();
		$option = self::OPT_NO_DEADLINE;
		if($period > 0) {
			$deadline_period_subform = $deadline_period_subform->withValue($period)->withAdditionalTransformation($this->refinery_factory->int()->isGreaterThan(-1));
			$option = self::OPT_DEADLINE_PERIOD;
		}
		$deadline_date = $prg->getDeadlineDate();
		$format = $this->data_factory->dateFormat()->germanShort();
		$deadline_date_subform = $ff
			->dateTime('',$txt('prg_deadline_date_desc'))
			->withFormat($format)
			->withMinValue(new DateTimeImmutable())
			;
		if($deadline_date !== null) {
			$deadline_date_subform = $deadline_date_subform->withValue($deadline_date->format($format->toString()));
			$option = self::OPT_DEADLINE_DATE;
		}
		$sg = $ff->switchableGroup(
			[
				self::OPT_NO_DEADLINE =>
					$ff->group([],$txt('prg_no_deadline')),
				self::OPT_DEADLINE_PERIOD =>
					$ff->group([$deadline_period_subform],$txt('prg_deadline_period')),
				self::OPT_DEADLINE_DATE =>
					$ff->group([$deadline_date_subform],$txt('prg_deadline_date'))
			],
			''
		);
		return $sg->withValue($option);
	}

	protected function getValidityOfQualificationSubform($prg)
	{
		$ff = $this->input_factory->field();
		$txt = function($id) { return $this->lng->txt($id); };
		$vq_period_subform = $ff
			->numeric('',$txt('validity_qalification_period_desc'))
			->withAdditionalTransformation(
				$this->refinery_factory->int()->isGreaterThan(-1)
			);
		$option = self::OPT_NO_VALIDITY_OF_QUALIFICATION;
		$period = $prg->getValidityOfQualificationPeriod();
		if($period !== ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD) {
			$vq_period_subform = $vq_period_subform->withValue($period)->withAdditionalTransformation($this->refinery_factory->int()->isGreaterThan(-1));
			$option = self::OPT_VALIDITY_OF_QUALIFICATION_PERIOD;
		}
		$format = $this->data_factory->dateFormat()->germanShort();
		$vq_date_subform = $ff
			->dateTime('',$txt('validity_qalification_date_desc'))
			->withMinValue(new DateTimeImmutable())
			->withFormat($format);
		$date = $prg->getValidityOfQualificationDate();
		if($date !== null) {
			$vq_date_subform = $vq_date_subform->withValue($date->format($format->toString()));
			$option = self::OPT_VALIDITY_OF_QUALIFICATION_DATE;
		}

		$sg = $ff->switchableGroup(
			[
				self::OPT_NO_VALIDITY_OF_QUALIFICATION =>
					$ff->group([],$txt('prg_no_validity_qalification')),
				self::OPT_VALIDITY_OF_QUALIFICATION_PERIOD =>
					$ff->group([$vq_period_subform],$txt('validity_qalification_period')),
				self::OPT_VALIDITY_OF_QUALIFICATION_DATE =>
					$ff->group([$vq_date_subform],$txt('validity_qalification_date'))
			],
			''
		);
		return $sg->withValue($option);
	}

	protected function getRestartSubform($prg)
	{
		$ff = $this->input_factory->field();
		$txt = function($id) { return $this->lng->txt($id); };
		$restart_period_subform = $ff
			->numeric('',$txt('restart_period_desc'))
			->withAdditionalTransformation(
				$this->refinery_factory->int()->isGreaterThan(-1)
			);
		$option = self::OPT_NO_RESTART;
		$restart_period = $prg->getRestartPeriod();
		if($restart_period !== ilStudyProgrammeSettings::NO_RESTART) {
			$option = self::OPT_RESTART_PERIOD;
			$restart_period_subform = $restart_period_subform->withValue($restart_period);
		}



		$sg = $ff->switchableGroup(
			[
				self::OPT_NO_RESTART =>
					$ff->group([],$txt('prg_no_restart')),
				self::OPT_RESTART_PERIOD =>
					$ff->group([$restart_period_subform],$txt('restart_period'))
			],
			''
		);
		return $sg->withValue($option);
	}

	protected function getObject() {
		if ($this->object === null) {
			$this->object = ilObjStudyProgramme::getInstanceByRefId($this->ref_id);
		}
		return $this->object;
	}
	
	protected function getStatusOptions() {
		
		return array( ilStudyProgrammeSettings::STATUS_DRAFT 
						=> $this->lng->txt("prg_status_draft")
					, ilStudyProgrammeSettings::STATUS_ACTIVE
						=> $this->lng->txt("prg_status_active")
					, ilStudyProgrammeSettings::STATUS_OUTDATED
						=> $this->lng->txt("prg_status_outdated")
					);
	}
}

?>
