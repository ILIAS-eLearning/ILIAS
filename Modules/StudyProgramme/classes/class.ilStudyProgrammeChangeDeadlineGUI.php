<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\UI\Component\Input\Factory;
use ILIAS\UI\Renderer;

class ilStudyProgrammeChangeDeadlineGUI
{
	const CMD_SHOW_DEADLINE_CONFIG = "showDeadlineConfig";
	const CMD_CHANGE_DEADLINE = "changeDeadline";
	const PROP_DEADLINE = "deadline";

	/**
	 * @var ilObjGroupGUI|ilObjCourseGUI
	 */
	protected $gui;

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
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var string
	 */
	protected $back_target;

	/**
	 * @var Factory
	 */
	protected $input_factory;

	/**
	 * @var Renderer
	 */
	protected $renderer;

	/**
	 * @var ServerRequest
	 */
	protected $request;

	/**
	 * @var Factory
	 */
	protected $refinery_factory;

	/**
	 * @var \ILIAS\Data\Factory
	 */
	protected $data_factory;

	public function __construct(
		ilCtrl $ctrl,
		ilGlobalTemplateInterface $tpl,
		ilLanguage $lng,
		ilAccessHandler $access,
		ilObjUser $user,
		Factory $input_factory,
		Renderer $renderer,
		ServerRequest $request,
		\ILIAS\Refinery\Factory $refinery_factory,
		\ILIAS\Data\Factory $data_factory
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->access = $access;
		$this->user = $user;
		$this->input_factory = $input_factory;
		$this->renderer = $renderer;
		$this->request = $request;
		$this->refinery_factory = $refinery_factory;
		$this->data_factory = $data_factory;
	}

	public function getBackTarget() : string
	{
		return $this->back_target;
	}

	public function setBackTarget(string $target) : void
	{
		$this->back_target = $target;
	}

	public function getAssignmentIds() : array
	{
		return $this->user_ids;
	}

	public function setAssignmentIds(array $user_ids) : void
	{
		$this->user_ids = $user_ids;
	}

	public function executeCommand() : void
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch ($next_class) {
			default:
				switch($cmd)
				{
					case self::CMD_SHOW_DEADLINE_CONFIG:
						$this->showDeadlineConfig();
						break;
					case self::CMD_CHANGE_DEADLINE:
						$this->changeDeadline();
						break;
					case 'cancel':
						$this->redirectToParent();
						break;
					default:
						throw new Exception('Unknown command ' . $cmd);
						break;
				}
				break;
		}
	}

	protected function showDeadlineConfig()
	{
		$this->tpl->loadStandardTemplate();
		$this->ctrl->setParameter($this, 'prgrs_ids', implode(',', $this->getAssignmentIds()));
		$action = $this->ctrl->getFormAction(
			$this,
			self::CMD_CHANGE_DEADLINE
		);
		$this->ctrl->clearParameters($this);

		$form = $this->buildForm($this->getObject(), $action);

		$this->tpl->setContent($this->renderer->render($form));
	}

	protected function buildForm(
		\ilObjStudyProgramme $prg,
		string $submit_action
	) {
		$ff = $this->input_factory->field();
		$txt = function($id) { return $this->lng->txt($id); };

		return $this->input_factory->container()->form()->standard(
			$submit_action,
			$this->buildFormElements(
				$ff,
				$txt,
				$prg
			)
		)->withAdditionalTransformation(
			$this->refinery_factory->custom()->transformation(function($values) {
				$return = [];
				foreach ($this->getGetPrgsIds() as $user_id) {
					$progress = $this->getObject()->getProgressForAssignment($user_id);
					$deadline_data = $values[0][self::PROP_DEADLINE];
					$deadline_type = $deadline_data[0];
					switch($deadline_type) {
						case ilObjStudyProgrammeSettingsGUI::OPT_NO_DEADLINE:
							$progress->setDeadline(null);
							break;
						case ilObjStudyProgrammeSettingsGUI::OPT_DEADLINE_DATE:
							$progress->setDeadline(\DateTime::createFromFormat('d.m.Y',array_shift($deadline_data[1])));
							break;
					}
					$return[] = $progress;
				}

				return $return;
			}));
	}

	protected function getDeadlineSubform($prg)
	{
		$ff = $this->input_factory->field();
		$txt = function($id) { return $this->lng->txt($id); };

		$option = ilObjStudyProgrammeSettingsGUI::OPT_NO_DEADLINE;
		$deadline_date = $prg->getDeadlineDate();
		$format = $this->data_factory->dateFormat()->germanShort();
		$deadline_date_subform = $ff
			->dateTime('',$txt('prg_deadline_date_desc'))
			->withFormat($format)
			->withMinValue(new DateTimeImmutable())
		;

		if ($deadline_date !== null) {
			$deadline_date_subform = $deadline_date_subform->withValue(
				$deadline_date->format($format->toString())
			);
			$option = ilObjStudyProgrammeSettingsGUI::OPT_DEADLINE_DATE;
		}

		$sg = $ff->switchableGroup(
			[
				ilObjStudyProgrammeSettingsGUI::OPT_NO_DEADLINE =>
					$ff->group([],$txt('prg_no_deadline')),
				ilObjStudyProgrammeSettingsGUI::OPT_DEADLINE_DATE =>
					$ff->group([$deadline_date_subform],$txt('prg_deadline_date'))
			],
			''
		);

		return $sg->withValue($option);
	}

	protected function buildFormElements(
		$ff,
		Closure $txt,
		ilObjStudyProgramme $prg
	) : array
	{
		$return = [
			$ff->section(
				[
					ilObjStudyProgrammeSettingsGUI::PROP_DEADLINE => $this->getDeadlineSubform($prg)
				],
				$txt("prg_deadline_settings"),
				""
			)
		];

		return $return;
	}

	protected function changeDeadline()
	{
		$form = $this
			->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "changeDeadline"))
			->withRequest($this->request);
		$result = $form->getInputGroup()->getContent();

		if ($result->isOK()) {
			foreach ($result->value() as $value) {
				$value->updateProgress($this->user->getId());
				$value->updateFromProgramNode();
			}

			ilUtil::sendSuccess($this->lng->txt('update_deadline'), true);
			$this->ctrl->redirectByClass('ilObjStudyProgrammeMembersGUI', 'view');
		}

		ilUtil::sendFailure($this->lng->txt('error_updating_deadline'), true);
		$this->ctrl->redirectByClass($this, self::CMD_SHOW_DEADLINE_CONFIG);
	}

	public function setRefId(int $ref_id) : void
	{
		$this->ref_id = $ref_id;
	}

	protected function getObject() {
		if ($this->object === null) {
			$this->object = ilObjStudyProgramme::getInstanceByRefId($this->ref_id);
		}
		return $this->object;
	}

	protected function getGetPrgsIds() : array
	{
		$prgrs_ids = $_GET['prgrs_ids'];
		if (is_null($prgrs_ids)) {
			return array();
		}
		return explode(',', $prgrs_ids);
	}

	protected function redirectToParent() : void
	{
		ilUtil::redirect($this->getBackTarget());
	}
}