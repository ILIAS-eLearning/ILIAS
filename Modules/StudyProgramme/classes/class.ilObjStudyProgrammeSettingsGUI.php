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
	 * @var ILIAS\Transformation\Factory
	 */
	protected $trafo_factory;

	public function __construct(
		\ilTemplate $tpl,
		\ilCtrl $ilCtrl,
		\ilLanguage $lng,
		\ILIAS\UI\Component\Input\Factory $input_factory,
		\ILIAS\UI\Renderer $renderer,
		\GuzzleHttp\Psr7\ServerRequest $request,
		\ILIAS\Transformation\Factory $trafo_factory,
		\ILIAS\Validation\Factory $validation,
		ilStudyProgrammeTypeRepository $type_repository
	) {

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->input_factory = $input_factory;
		$this->renderer = $renderer;
		$this->request = $request;
		$this->trafo_factory = $trafo_factory; // TODO: replace this with the version from the DIC once available
		$this->validation = $validation;
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
		$content = $form->getData();
		$prg = $this->getObject();

		// This could further improved by providing a new container for asynch-forms in the
		// UI-Framework.
		$update_possible = !is_null($content);
		if ($update_possible) {
			$this->updateWith($prg, $content);
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
	const OPT_NO_DEADLINE = 'opt_no_deadline';
	const OPT_DEADLINE_PERIOD = "opt_deadline_period";
	const OPT_DEADLINE_DATE = "opt_deadline_date";

	protected function buildForm(\ilObjStudyProgramme $prg, string $submit_action) : ILIAS\UI\Component\Input\Container\Form\Standard {
		$trans = $prg->getObjectTranslation();
		$ff = $this->input_factory->field();
		$tf = $this->trafo_factory;
		$txt = function($id) { return $this->lng->txt($id); };
		$sp_types = $this->type_repository->readAllTypesArray();

		$languages = ilMDLanguageItem::_getLanguages();
		return $this->input_factory->container()->form()->standard(
			$submit_action,
			[
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
					$this->lng->txt("language").": ".$languages[$trans->getDefaultLanguage()].
						' <a href="'.$this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "").
						'">&raquo; '.$this->lng->txt("obj_more_translations").'</a>'
				),
				$ff->section(
					[
						self::PROP_TYPE =>
							$ff->select($txt("type"), $sp_types)
								->withValue($prg->getSubtypeId() == 0 ? "" : $prg->getSubtypeId())
								->withAdditionalTransformation($tf->custom(function($v) {
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
								->withAdditionalConstraint($this->validation->greaterThan(-1)),
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
					$txt("prg_deadline"),
					""
				)
			]
		)
		->withAdditionalTransformation($tf->custom(function($values) {
			// values now contains the results of the single sections,
			// i.e. a list of arrays that each contains keys according
			// to the section they originated from.
			return call_user_func_array("array_merge", $values);
		}));
	}


	protected function getDeadlineSubform($prg)
	{
		$ff = $this->input_factory->field();
		$txt = function($id) { return $this->lng->txt($id); };
		$deadline_period_subform = $ff->numeric('',$txt('prg_deadline_period_desc'))
										->withAdditionalConstraint(
											$this->validation->greaterThan(-1)
									);
		$period = $prg->getDeadlinePeriod();
		$radion_option = self::OPT_NO_DEADLINE;
		if($period > 0) {
			$deadline_period_subform = $deadline_period_subform->withValue($period);
			$radion_option = self::OPT_DEADLINE_PERIOD;
		}
		$deadline_date = $prg->getDeadlineDate();
		$deadline_date_subform = $ff
			->text('',$txt('prg_deadline_date_desc'))
			->withAdditionalConstraint(
				$this->validation->custom(
					function($string) {
						$string = trim($string);
						return \DateTime::createFromFormat('Y-m-d',$string) instanceof \DateTime || $string === '';
					},
					function($txt, $value) {
						return $txt('prg_improper_deadline_date');
					}
				)
			);
		if($deadline_date !== null) {
			$deadline_date_subform = $deadline_date_subform->withValue($deadline_date->get(IL_CAL_DATE));
			$radion_option = self::OPT_DEADLINE_DATE;
		}
		$radio = $ff->radio("","")
			->withOption(
				self::OPT_NO_DEADLINE,
				$txt('prg_no_deadline'),
				''
			)
			->withOption(
				self::OPT_DEADLINE_PERIOD,
				$txt('prg_deadline_period'),
				'',
				[self::PROP_DEADLINE_PERIOD => $deadline_period_subform]
			)
			->withOption(
				self::OPT_DEADLINE_DATE,
				$txt('prg_deadline_date'),
				'',
				[self::PROP_DEADLINE_DATE => $deadline_date_subform]
			);
		if($radion_option) {
			return $radio->withValue($radion_option);
		}
		return $radio;
	}

	protected function updateWith(\ilObjStudyProgramme $prg, array $data)
	{
		$prg->setTitle($data[self::PROP_TITLE]);
		$prg->setDescription($data[self::PROP_DESC]);

		if($prg->getSubtypeId() != $data[self::PROP_TYPE]) {
			$prg->setSubtypeId($data[self::PROP_TYPE]);
			$prg->updateCustomIcon();
			$this->parent_gui->setTitleAndDescription();
		}

		$prg->setPoints($data[self::PROP_POINTS]);
		$prg->setStatus($data[self::PROP_STATUS]);

		if(array_key_exists('value', $data[self::PROP_DEADLINE])) {
			if($data[self::PROP_DEADLINE]['value'] === self::OPT_DEADLINE_PERIOD) {
				$prg->setDeadlinePeriod((int)$data[self::PROP_DEADLINE]['group_values'][self::PROP_DEADLINE_PERIOD]);
			}
			if($data[self::PROP_DEADLINE]['value'] === self::OPT_DEADLINE_DATE) {
				$date_string = trim($data[self::PROP_DEADLINE]['group_values'][self::PROP_DEADLINE_DATE]);
				$prg->setDeadlineDate($date_string === '' ? null : new ilDateTime($date_string,IL_CAL_DATE));
			}
			if($data[self::PROP_DEADLINE]['value'] === self::OPT_NO_DEADLINE) {
				$prg->setDeadlineDate(null); // deadline period will be set to 0 automatically
			}
		}

		$prg->update();
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
