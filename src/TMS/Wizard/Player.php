<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Wizard;

require_once(__DIR__."/../../../Services/Form/classes/class.ilFormSectionHeaderGUI.php");

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Displays the steps for the wizard a row, gathers user input and afterwards
 * completes the wizard by processing the steps.
 */
class Player {
	const START_WITH_STEP = 0;
	const COMMAND_START = "start";
	const COMMAND_ABORT = "abort";
	const COMMAND_NEXT	= "next";
	const COMMAND_CONFIRM = "confirm";
	const COMMAND_PREVIOUS = "previous";
	const COMMAND_SAVE = "save_step";

	/**
	 * @var Wizard
	 */
	protected $wizard;

	/**
	 * @var ILIASBindings
	 */
	protected $ilias_bindings;

	/**
	 * @var	StateDB
	 */
	protected $state_db;

	public function __construct(ILIASBindings $ilias_bindings, Wizard $wizard, StateDB $state_db) {
		$this->wizard = $wizard;
		$this->ilias_bindings = $ilias_bindings;
		$this->state_db = $state_db;
	}

	/**
	 * Process the user input and build the appropriate view.
	 *
	 * The wizard ended when null is returned.
	 *
	 * @param	string|null	$cmd
	 * @param	array|null	$post
	 * @return	string|null
	 */
	public function run($cmd = null, array $post = null) {
		assert('is_null($cmd) || is_string($cmd)');

		if ($cmd === null) {
			$cmd = self::COMMAND_NEXT;
		}

		$state = $this->getState();
		switch ($cmd) {
			case self::COMMAND_START:
				$this->state_db->delete($state);
				return $this->run(self::COMMAND_NEXT, $post);
			case self::COMMAND_ABORT:
				$this->state_db->delete($state);
				$aborted = $this->ilias_bindings->txt("aborted");
				return $this->ilias_bindings->redirectToPreviousLocation([$aborted], false);
			case self::COMMAND_NEXT:
				return $this->runStep($state, $post);
			case self::COMMAND_SAVE:
				return $this->runStep($state, $post, false);
			case self::COMMAND_PREVIOUS:
				return $this->runPreviousStep($state, $post);
			case self::COMMAND_CONFIRM:
				return $this->finish($state);
		}
		throw new \LogicException("Unknown command: '$cmd'");
	}

	/**
	 * Build the view for the current step in the wizard.
	 *
	 * @param	State	$state
	 * @param	array|null	$post
	 * @param	bool|null	$next_when_ok   advance to next step when current step is ok.
	 * @return	string
	 */
	protected function runStep(State $state, array $post = null, $next_when_ok = true) {
		$steps = $this->wizard->getSteps();

		if(count($steps) === 0) {
			require_once "./Services/Utilities/classes/class.ilUtil.php";
			\ilUtil::sendInfo($this->ilias_bindings->txt("no_steps_available"));
			return '';
		}

		if ($content = $this->maybeRunOverview($state, count($steps))) {
			return $content;
		}

		$step_number = $state->getStepNumber();
		if($step_number < 0) {
			throw new \LogicException("It is impossible that the number of step is smaller than 0.");
		}

		$current_step = $steps[$step_number];
		$form = $this->buildStepForm($step_number, $current_step);

		if ($content = $this->maybeProcessUserInput($state, $step_number, $current_step, $form, $post, $next_when_ok)) {
			return $content;
		}

		$this->maybeAddStepDataToForm($state, $step_number, $current_step, $form);

		return $form->getHtml();
	}

	/**
	 * Run the overview if the last step was already processed.
	 *
	 * @param	State	$state
	 * @param	int		$num_steps
	 * @return	null|string	returns null if overview was not run
	 */
	protected function maybeRunOverview(State $state, $num_steps) {
		assert('is_int($num_steps)');
		$step_number = $state->getStepNumber();
		if ($step_number == $num_steps) {
			return $this
				->buildOverviewForm($state)
				->getHtml();
		}
		return null;
	}

	/**
	 * Process user input, if there is any.
	 *
	 * @param	State	$state
	 * @param	int		$step_number
	 * @param	Step	$step
	 * @param	\ilPropertyFormGUI	$form
	 * @param	array|null	$post
	 * @param	bool	$next_when_ok   advance to next step when current step is ok.
	 * @return	null|string	returns null if input was processed
	 */
	public function maybeProcessUserInput(State $state, $step_number, Step $step, \ilPropertyFormGUI $form, $post, $next_when_ok) {
		assert('is_int($step_number)');
		assert('is_array($post) || is_null($post)');
		if ($post) {
			$form->setValuesByArray($post);
			if ($form->checkInput()) {
				$data = $step->getData($form);
				if ($data !== null) {
					$state = $state
						->withStepData($step_number, $data);
					if ($next_when_ok) {
						$state = $state
							->withNextStep();
					}
					$this->state_db->save($state);
					return $this->runStep($state);
				}
			}
		}
		return null;
	}

	/**
	 * Displays previously inputted data, if there is any.
	 *
	 * @param	State	$state
	 * @param	int		$step_number
	 * @param	Step	$step
	 * @param	\ilPropertyFormGUI $form
	 * @return	void
	 */
	public function maybeAddStepDataToForm(State $state, $step_number, Step $step, \ilPropertyFormGUI $form) {
		assert('is_int($step_number)');
		if($state->hasStepData($step_number)) {
			$step_data = $state->getStepData($step_number);
			$step->addDataToForm($form, $step_data);
		}
	}

	/**
	 * Build the view for the previous step in the wizard.
	 *
	 * @param	State	$state
	 * @return	string
	 */
	protected function runPreviousStep(State $state, $post) {
		//save current step
		assert('is_array($post) || is_null($post)');
		$steps = $this->wizard->getSteps();
		$step_number = $state->getStepNumber();
		if($step_number < 0) {
			throw new \LogicException("It is impossible that the number of step is smaller than 0.");
		}

		if(!is_null($post) && array_key_exists($step_number,$steps)) {
			$step = $steps[$step_number];
			$form = $this->buildStepForm($step_number, $step);
			if ($form->checkInput()) {
				$data = $step->getData($form);
				if($data != null) {
					$state = $state
						->withStepData($step_number, $data);
					$this->state_db->save($state);
				} else {
					return $form->getHtml();
				}
			} else {
				$form->setValuesByPost();
				return $form->getHtml();
			}
		}

		//get back to previous step
		$state = $state->withPreviousStep();
		$this->state_db->save($state);
		$step_number = $state->getStepNumber();

		if($step_number < 0) {
			while ($state->getStepNumber() < 0) {
				$state = $state->withNextStep();
			}
			$this->state_db->save($state);
		}
		return $this->runStep($state);
	}

	/**
	 * Build the form for a single step.
	 *
	 * @param int	$step_number
	 * @param Step  $current_step
	 * @return	\ilPropertyFormGUI
	 */
	protected function buildStepForm($step_number, Step $current_step) {
		$form = $this->ilias_bindings->getForm();
		if($step_number > 0) {
			$form->addCommandButton(self::COMMAND_PREVIOUS, $this->ilias_bindings->txt("previous"));
		}
		$form->addCommandButton(self::COMMAND_NEXT, $this->ilias_bindings->txt("next"));
		$form->addCommandButton(self::COMMAND_ABORT, $this->ilias_bindings->txt("abort"));

		$form->setTitle($this->ilias_bindings->txt("title"));
		$current_step->appendToStepForm($form);
		return $form;
	}

	/**
 	 * Build the final overview form.
	 *
	 * @param	State $state
	 * @return	\ilPropertyFormGUI
	 */
	protected function buildOverviewForm(State $state) {
		$steps = $this->wizard->getSteps();
		$form = $this->ilias_bindings->getForm();

		$form->addCommandButton(self::COMMAND_PREVIOUS, $this->ilias_bindings->txt("previous"));
		$form->addCommandButton(self::COMMAND_CONFIRM, $this->ilias_bindings->txt("confirm"));
		$form->addCommandButton(self::COMMAND_ABORT, $this->ilias_bindings->txt("abort"));

		$form->setTitle($this->ilias_bindings->txt("title"));
		$form->setDescription($this->ilias_bindings->txt("overview_description"));

		for($i = 0; $i < count($steps); $i++) {
			$step = $steps[$i];
			$header = new \ilFormSectionHeaderGUI();
			$header->setTitle($step->getLabel());
			$form->addItem($header);
			$data = $state->getStepData($i);
			$step->appendToOverviewForm($form, $data);
		}

		return $form;
	}

	/**
	 * Finish the wizard by actually processing the steps.
	 *
	 * @param	State	$state
	 * @return	void
	 */
	protected function finish(State $state) {
		$steps = $this->wizard->getSteps();
		assert('$state->getStepNumber() == count($steps)');

		if ($state->getStepNumber() !== count($steps)) {
			throw new \LogicException("User did not work through the wizard.");
		}

		$messages = [];
		for ($i = 0; $i < count($steps); $i++) {
			$step = $steps[$i];
			$data = $state->getStepData($i);
			$message = $step->processStep($data);
			if ($message) {
				$messages[] = $message;
			}
		}
		$this->state_db->delete($state);
		$this->wizard->finish();
		$this->ilias_bindings->redirectToPreviousLocation($messages, true);
	}

	/**
	 * Get the state information about the booking process.
	 *
	 * @return	State
	 */
	protected function getState() {
		$state = $this->state_db->load($this->wizard->getId());
		if ($state !== null) {
			return $state;
		}
		return new State($this->wizard->getId(), self::START_WITH_STEP);
	}
}
