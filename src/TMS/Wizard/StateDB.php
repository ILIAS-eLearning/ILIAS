<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\Wizard;

/**
 * Stores state information about wizards.
 */
interface StateDB {
	/**
	 * @param	string	$wizard_id
	 * @return	State|null
	 */
	public function load($wizard_id);

	/**
	 * @param	State
	 * @return	void
	 */
	public function save(State $state);

	/**
	 * Deletes ProcessState if it exists.
	 *
	 * @param	State
	 * @return	void
	 */
	public function delete(State $state);
}
