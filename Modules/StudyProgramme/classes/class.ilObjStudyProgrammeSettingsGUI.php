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
	 * @var \ILIAS\Refinery\Factory
	 */
	protected $trafo_factory;
	private $refinery;

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
		global $DIC;
        $refinery = $DIC->refinery();
        $this->refinery = $refinery;
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

	protected function buildForm(\ilObjStudyProgramme $prg, string $submit_action) : ILIAS\UI\Component\Input\Container\Form\Standard {
		$ff = $this->input_factory->field();
		$txt = function($id) { return $this->lng->txt($id); };
		$sp_types = $this->type_repository->readAllTypesArray();
		$status_options = self::getStatusOptions();
		return $this->input_factory->container()->form()->standard(
			$submit_action,
			[
				$ff->section(
					[
						self::PROP_TITLE =>
							$ff->text($txt("title"))
								->withValue($prg->getTitle())
								->withRequired(true),
						self::PROP_DESC =>
							$ff->textarea($txt("description"))
								->withValue($prg->getDescription())
					],
					$txt("prg_edit"),
					""
				),
				$ff->section(
					[
						self::PROP_TYPE =>
							$ff->select($txt("type"), $sp_types)
								->withValue($prg->getSubtypeId() == 0 ? "" : $prg->getSubtypeId())
								->withAdditionalTransformation($this->refinery->custom()->transformation(function($v) {
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
								->withAdditionalTransformation($this->refinery->int()->isGreaterThan(-1)),
						self::PROP_STATUS =>
							$ff->select($txt("prg_status"), $status_options)
								->withValue((string)$prg->getStatus())
								->withRequired(true)
					],
					$txt("prg_assessment"),
					""
				)
			]
		)
		->withAdditionalTransformation($this->refinery->custom()->transformation(function($values) {
			// values now contains the results of the single sections,
			// i.e. a list of arrays that each contains keys according
			// to the section they originated from.
			return call_user_func_array("array_merge", $values);
		}));
	}

	protected function updateWith(\ilObjStudyProgramme $prg, array $data) {
		$prg->setTitle($data[self::PROP_TITLE]);
		$prg->setDescription($data[self::PROP_DESC]);

		if($prg->getSubtypeId() != $data[self::PROP_TYPE]) {
			$prg->setSubtypeId($data[self::PROP_TYPE]);
			$prg->updateCustomIcon();
			$this->parent_gui->setTitleAndDescription();
		}

		$prg->setPoints($data[self::PROP_POINTS]);
		$prg->setStatus($data[self::PROP_STATUS]);

		$prg->update();
	}
	
	protected function getObject() {
		if ($this->object === null) {
			$this->object = ilObjStudyProgramme::getInstanceByRefId($this->ref_id);
		}
		return $this->object;
	}
	
	static protected function getStatusOptions() {
		global $DIC;
		$lng = $DIC['lng'];
		
		return array( ilStudyProgrammeSettings::STATUS_DRAFT 
						=> $lng->txt("prg_status_draft")
					, ilStudyProgrammeSettings::STATUS_ACTIVE
						=> $lng->txt("prg_status_active")
					, ilStudyProgrammeSettings::STATUS_OUTDATED
						=> $lng->txt("prg_status_outdated")
					);
	}
}

?>
