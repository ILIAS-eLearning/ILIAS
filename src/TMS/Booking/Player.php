<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

require_once(__DIR__."/../../../Services/Form/classes/class.ilFormSectionHeaderGUI.php");

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Displays the steps for the booking of one spefic course in a row, gathers user
 * input and afterwards completes the booking.
 *
 * TODO: This rather should take the abstract methods via an interface and be final
 * instead of forcing to derive from this class. This will make the ugly init go away.
 */
abstract class Player {
	use ilHandlerObjectHelper;

	const START_WITH_STEP = 0;
	const COMMAND_START = "start";
	const COMMAND_ABORT = "abort";
	const COMMAND_NEXT	= "next";
	const COMMAND_CONFIRM = "confirm";
	const COMMAND_PREVIOUS = "previous";

	/**
	 * @var	\ArrayAccess
	 */
	protected $dic;

	/**
	 * @var	int
	 */
	protected $crs_ref_id;

	/**
	 * @var	int
	 */
	protected $usr_id;

	/**
	 * @var	ProcessStateDB
	 */
	protected $process_db;

	/**
	 * @param	\ArrayAccess|array $dic
	 * @param	int	$crs_ref_id 	course that should get booked
	 * @param	int	$usr_id			the usr the booking is made for
	 */
	public function __construct($dic, $crs_ref_id, $usr_id, ProcessStateDB $process_db) {
		$this->init($dic, $crs_ref_id, $usr_id, $process_db);
	}

	/**
	 * @param	\ArrayAccess|array $dic
	 * @param	int	$crs_ref_id 	course that should get booked
	 * @param	int	$usr_id			the usr the booking is made for
	 */
	public function init($dic, $crs_ref_id, $usr_id, ProcessStateDB $process_db) {
		assert('is_array($dic) || ($dic instanceof \ArrayAccess)');
		assert('is_int($crs_ref_id)');
		assert('is_int($usr_id)');
		$this->dic = $dic;
		$this->crs_ref_id = $crs_ref_id;
		$this->usr_id = $usr_id;
		$this->process_db = $process_db;
	}

	/**
	 * @inheritdoc
	 */
	protected function getDIC() {
		return $this->dic;
	}

	/**
	 * @inheritdoc
	 */
	protected function getEntityRefId() {
		return $this->crs_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	protected function getUserId() {
		return $this->usr_id;
	}

	/**
	 * Process the user input and build the appropriate view.
	 *
	 * @param	string|null	$cmd
	 * @param	array|null	$post
	 * @return	string|null
	 */
	public function process($cmd = null, array $post = null) {
		assert('is_null($cmd) || is_string($cmd)');
		if ($cmd == self::COMMAND_START) {
			$this->resetProcess();
			return $this->process(self::COMMAND_NEXT, $post);
		}
		$state = $this->getProcessState();
		if ($cmd === self::COMMAND_ABORT) {
			$this->deleteProcessState($state);
			$this->redirectToPreviousLocation([$this->txt("aborted")], false);
			return null;
		}
		if ($cmd === self::COMMAND_NEXT || $cmd === null) {
			return $this->processStep($state, $post);
		}
		if ($cmd === self::COMMAND_PREVIOUS || $cmd === null) {
			return $this->processPreviousStep($state);
		}
		if ($cmd === self::COMMAND_CONFIRM) {
			$this->finishProcess($state);
			return null;
		}
		throw new \LogicException("Unknown command: '$cmd'");
	}

	/**
	 * Build the view for the current step in the booking process.
	 *
	 * @param	ProcessState	$state
	 * @param	array|null	$post
	 * @return	string
	 */
	protected function processStep(ProcessState $state, array $post = null) {
		$steps = $this->getSortedSteps();
		$step_number = $state->getStepNumber();

		if ($step_number == count($steps)) {
			assert('is_null($post)');
			return $this
				->buildOverviewForm($state)
				->getHtml();
		}

		$current_step = $steps[$step_number];

		$form = $this->getForm();
		if($step_number > 0) {
			$form->addCommandButton(self::COMMAND_PREVIOUS, $this->txt("previous"));
		}
		$form->addCommandButton(self::COMMAND_NEXT, $this->txt("next"));
		$form->addCommandButton(self::COMMAND_ABORT, $this->txt("abort"));

		$form->setTitle($this->getPlayerTitle());
		$current_step->appendToStepForm($form);

		if ($post) {
			$form->setValuesByArray($post);
			if ($form->checkInput()) {
				$data = $current_step->getData($form);
				if ($data !== null) {
					$state = $state
						->withStepData($step_number, $data)
						->withNextStep();
					$this->saveProcessState($state);
					return $this->processStep($state);
				}
			}
		}

		if($state->hasStepData($step_number)) {
			$step_data = $state->getStepData($step_number);
			$current_step->addDataToForm($form, $step_data);
		}

		return $form->getHtml();
	}

	/**
	 * Build the view for the previews step in the booking process.
	 *
	 * @param	ProcessState	$state
	 * @return	string
	 */
	protected function processPreviousStep(ProcessState $state) {
		$steps = $this->getSortedSteps();
		$state = $state->withPreviousStep();
		$this->saveProcessState($state);
		$step_number = $state->getStepNumber();

		if($step_number < 0) {
			throw new \LogicException("It is impossible that the number of step is smaller than 0.");
		}

		$current_step = $steps[$step_number];
		$step_data = $state->getStepData($step_number);
		$form = $this->getForm();

		if($step_number > 0) {
			$form->addCommandButton(self::COMMAND_PREVIOUS, $this->txt("previous"));
		}
		$form->addCommandButton(self::COMMAND_NEXT, $this->txt("next"));
		$form->addCommandButton(self::COMMAND_ABORT, $this->txt("abort"));

		$form->setTitle($this->getPlayerTitle());
		$current_step->appendToStepForm($form);
		$current_step->addDataToForm($form, $step_data);

		return $form->getHtml();
	}

	/**
 	 * Build the final overview form.
	 *
	 * @param	ProcessState $state
	 * @return	\ilPropertyFormGUI
	 */
	protected function buildOverviewForm(ProcessState $state) {
		$steps = $this->getSortedSteps();
		$form = $this->getForm();
		$form->addCommandButton(self::COMMAND_PREVIOUS, $this->txt("previous"));
		$form->addCommandButton(self::COMMAND_CONFIRM, $this->getConfirmButtonLabel());
		$form->addCommandButton(self::COMMAND_ABORT, $this->txt("abort"));
		$form->setTitle($this->getPlayerTitle());
		$form->setDescription($this->getOverViewDescription());

		for($i = 0; $i < count($steps); $i++) {
			$step = $steps[$i];
			$header = new \ilFormSectionHeaderGUI();
			$header->setTitle($step->getLabel());
			$form->addItem($header);
			$data = $state->getStepData($i);
			$step->appendToOverviewForm($data, $form);
		}
		return $form;
	}

	/**
	 * Reset the process by erasing all process data.
	 *
	 * @return void
	 */
	protected function resetProcess() {
		$state = $this->process_db->load($this->crs_ref_id, $this->usr_id);
		if ($state !== null) {
			$this->process_db->delete($state);
		}
	}

	/**
	 * Finish the process by actually processing the steps.
	 *
	 * @param	ProcessState	$state
	 * @return	void
	 */
	protected function finishProcess(ProcessState $state) {
		$steps = $this->getSortedSteps();
		assert('$state->getStepNumber() == count($steps)');
		$messages = [];
		for ($i = 0; $i < count($steps); $i++) {
			$step = $steps[$i];
			$data = $state->getStepData($i);
			$message = $step->processStep($this->getEntityRefId(), $this->getUserId(), $data);
			if ($message) {
				$messages[] = $message;
			}
		}
		$this->deleteProcessState($state);
		$this->redirectToPreviousLocation($messages, true);
	}

	/**
	 * Get a form for the overview.
	 *
	 * @return \ilPropertyFormGUI
	 */
	abstract protected function getForm();

	/**
	 * I18n language string
	 *
	 * @param	string	$id
	 * @return	string
	 */
	abstract protected function txt($id);

	/**
	 * Redirect to previous location with a message.
	 *
	 * @param	string[] $messages
	 * @param	bool     $success
	 * @return	void
	 */
	abstract protected function redirectToPreviousLocation($messages, $sucess);

	/**
	 * Get the title of player
	 *
	 * @return string
	 */
	abstract protected function getPlayerTitle();

	/**
	 * Get description for oberview form
	 *
	 * @return string
	 */
	abstract protected function getOverViewDescription();

	/**
	 * Get the label for confirm button
	 *
	 * @return string
	 */
	abstract protected function getConfirmButtonLabel();

	/**
	 * Get the state information about the booking process.
	 *
	 * @return	ProcessState
	 */
	protected function getProcessState() {
		$state = $this->process_db->load($this->crs_ref_id, $this->usr_id);
		if ($state !== null) {
			return $state;
		}
		return new ProcessState($this->crs_ref_id, $this->usr_id, self::START_WITH_STEP);
	}

	/**
	 * Save the state information about the booking process.
	 *
	 * @param	ProcessState
	 * @return	void
	 */
	protected function saveProcessState(ProcessState $state) {
		$this->process_db->save($state);
	}

	/**
	 * Delete the state information about the booking process.
	 *
	 * @param	ProcessState
	 * @return	void
	 */
	protected function deleteProcessState(ProcessState $state) {
		$this->process_db->delete($state);
	}

	/**
	 * Get the steps that are applicable for a given user.
	 *
	 * @return	Step[]
	 */
	protected function getApplicableSteps() {
		$steps = $this->getComponentsOfType(Step::class);
		return array_values(array_filter($steps, function($step) {
			return $step->isApplicableFor($this->getUserId());
		}));
	}

	/**
	 * Get the steps for the booking of the couse sorted by period.
	 *
	 * @return 	Step[]
	 */
	protected function getSortedSteps() {
		$steps = $this->getApplicableSteps();
		if (count($steps) === 0) {
			throw new \LogicException("No booking steps defined.");
		}
		usort($steps, function (Step $a, Step $b) {
			if ($a->getPriority() < $b->getPriority()) {
				return -1;
			}
			if ($a->getPriority() > $b->getPriority()) {
				return 1;
			}
			return 0;
		});
		return $steps;
	}
} 
