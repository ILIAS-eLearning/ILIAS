<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Component;

/**
 * This describes checkbox inputs.
 */
interface Checkbox extends Component {
	/**
	 * Creates an input like this but with a dependant group attached which appears if the
	 * control is clicked.
	 *
	 * @param DependantGroup $dependant_group group to be attached to the checkbox
	 *
	 * @return Input
	 */
	public function withDependantGroup(DependantGroup $dependant_group) :Input;


	/**
	 * Returns the attached DependantGroup or null if none is attached.
	 *
	 * @return DependantGroup|null
	 */
	public function getDependantGroup();
}
