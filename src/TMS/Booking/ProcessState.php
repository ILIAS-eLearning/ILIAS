<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Booking;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Has state information about one booking process.
 */
class ProcessState {
	/**
	 * @var	int
	 */
	protected $crs_id;

	/**
	 * @var	int
	 */
	protected $usr_id;

	/**
	 * @var	int
	 */
	protected $step_number;

	/**
	 * @var array<int,string>
	 */
	protected $step_data;

	public function __construct($crs_id, $usr_id, $step_number, array $step_data = []) {
		assert('is_int($crs_id)');
		assert('is_int($usr_id)');
		assert('is_int($step_number)');
		$this->crs_id = $crs_id;
		$this->usr_id = $usr_id;
		$this->step_number = $step_number;
		$this->step_data = $step_data;
	}

	/**
	 * Get the id of the course the booking is made for.
	 *
	 * @return	int
	 */
	public function getCourseId() {
		return $this->crs_id;
	}

	/**
	 * Get the id of the user the booking is made for.
	 *
	 * @return	int
	 */
	public function getUserId() {
		return $this->usr_id;
	}

	/**
	 * Get the number of the step the user currently is in.
	 *
	 * @return	int
	 */
	public function getStepNumber() {
		return $this->step_number;
	}

	/**
	 * Set the step to the next step.
	 *
	 * @return ProcessState
	 */
	public function withNextStep() {
		$clone = clone $this;
		$clone->step_number++;
		return $clone;
	}

	/**
	 * Set the step to the previous step
	 *
	 * @return ProcessState
	 */
	public function withPreviousStep() {
		$clone = clone $this;
		$clone->step_number--;
		return $clone;
	}

	/**
	 * Set data for a certain step.
	 *
	 * @param	int		$step_number
 	 * @param	mixed	$data	needs to be json-serializable
	 * @return	ProcessState
	 */
	public function withStepData($step_number, $data) {
		$clone = clone $this;
		$encoded = json_encode($data);
		assert('is_string($encoded)');
		$clone->step_data[$step_number] = $encoded;
		return $clone;
	}

	/**
	 * Get the data for a certain step.
	 *
	 * @param	int		$step_number
	 * @throws	\OutOfBoundsException	if there is no data for the given key.
	 * @return	mixed
	 */
	public function getStepData($step_number) {
		if (!array_key_exists($step_number, $this->step_data)) {
			throw new \OutOfBoundsException("No data for $step_number.");
		}
		return json_decode($this->step_data[$step_number], true);
	}

	/**
	 * Checks whether the given step_number has saved data
	 *
	 * @param int 	$step_number
	 * @return bool
	 */
	public function hasStepData($step_number) {
		return array_key_exists($step_number, $this->step_data);
	}

	/**
	 * Get all step data in an array.
	 *
	 * @return	array<int,string>
	 */
	public function getAllStepData() {
		return $this->step_data;
	}
}
