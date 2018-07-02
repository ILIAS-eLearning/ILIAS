<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Wizard;

/**
 * Implementation of the state db over session.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class SessionStateDB implements StateDB {
	/**
	 * @inheritdocs
	 */
	public function load($key) {
		$value = $this->sessionGet($key);
		if ($value === null) {
			return null;
		}
		$value = json_decode($value, true);
		return new State($key, $value["step_number"], $value["step_data"]);
	}

	/**
	 * @inheritdocs
	 */
	public function save(State $state) {
		$key = $state->getWizardId();
		$value =
			[ "step_number" => $state->getStepNumber()
			, "step_data" => $state->getAllStepData()
			];
		$this->sessionSet($key, json_encode($value));
	}

	/**
	 * @inheritdocs
	 */
	public function delete(State $state) {
		$key = $state->getWizardId();
		$this->sessionClear($key);
	}

	/**
	 * Get from session.
	 *
	 * @param	string	$key
	 * @return	string
	 */
	protected function sessionGet($key) {
		return \ilSession::get($key);
	}

	/**	
	 * Set to the session.
	 *
	 * @param	string	$key
	 * @param	string	$value
	 * @return	void
	 */
	protected function sessionSet($key, $value) {
		return \ilSession::set($key, $value);
	}

	/**
	 * Clear the key in the session.
	 *
	 * @param	string	$key
	 * @return	void
	 */
	protected function sessionClear($key) {
		return \ilSession::clear($key);
	}
}
