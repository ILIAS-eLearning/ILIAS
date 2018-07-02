<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

require_once(__DIR__."/../../../Services/Form/classes/class.ilFormSectionHeaderGUI.php");

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Displays the steps for the booking of one spefic course in a row, gathers user
 * input and afterwards completes the booking.
 */
class Wizard implements \ILIAS\TMS\Wizard\Wizard {
	use ilHandlerObjectHelper;

	/**
	 * @var	\ArrayAccess
	 */
	protected $dic;

	/**
	 * @var	string
	 */
	protected $component_class;

	/**
	 * @var int
	 */
	protected $acting_user_id;

	/**
	 * @var	int
	 */
	protected $crs_ref_id;

	/**
	 * @var	int
	 */
	protected $target_user_id;

	/**
	 * @var	ProcessStateDB
	 */
	protected $process_db;

	/**
	 * @var	callable
	 */
	protected $call_on_finish;

	/**
	 * @param	\ArrayAccess|array $dic
	 * @param	string 	$component_class
	 * @param	int		$acting_user_id			the user that performs the wizard
	 * @param	int		$crs_ref_id 			course that should get booked
	 * @param	int		$target_user_id			the user the booking is made for
	 * @param	callable | null	$call_on_finish 	when the wizard is finished, execute this.
	 */
	public function __construct($dic, $component_class, $acting_user_id, $crs_ref_id, $target_user_id, $call_on_finish) {
		assert('is_array($dic) || ($dic instanceof \ArrayAccess)');
		assert('is_string($component_class)');
		assert('is_int($acting_user_id)');
		assert('is_int($crs_ref_id)');
		assert('is_int($target_user_id)');
		assert('is_callable($call_on_finish) || is_null($call_on_finish)');
		$this->dic = $dic;
		$this->component_class = $component_class;
		$this->acting_user_id = $acting_user_id;
		$this->crs_ref_id = $crs_ref_id;
		$this->target_user_id = $target_user_id;
		$this->call_on_finish = $call_on_finish;
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
		return $this->target_user_id;
	}

	/**
	 * Get the class of component player is searching steps
	 *
	 * @return string
	 */
	protected function getComponentClass() {
		return $this->component_class;
	}

	/**
	 * Get the steps that are applicable for a given user.
	 *
	 * @return	Step[]
	 */
	protected function getApplicableSteps() {
		$steps = $this->getComponentsOfType($this->getComponentClass());
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
			return $steps;
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

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->component_class
			."_".$this->acting_user_id
			."_".$this->crs_ref_id
			."_".$this->target_user_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getSteps() {
		return array_map(function($s) {
			return new StepAdapter($s, $this->crs_ref_id, $this->target_user_id);
		}, $this->getSortedSteps());
	}

	/**
	 * @inheritdoc
	 */
	public function finish() {
		if(! is_null($this->call_on_finish)) {
			call_user_func(
				$this->call_on_finish,
				$this->acting_user_id,
				$this->target_user_id,
				$this->crs_ref_id
			);
		}
	}

}
