<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Changeable;
use ILIAS\UI\Component\Onloadable;

/**
 * This describes checkbox inputs.
 */
interface Checkbox extends Group {

	/**
	 * Creates a Checkbox like this but with a dependant group attached which appears if the
	 * control is clicked.
	 *
	 * @param DependantGroup $dependant_group group to be attached to the checkbox
	 *
	 * @return Checkbox
	 */
	public function withDependantGroup(DependantGroup $dependant_group);


	/**
	 * Returns the attached DependantGroup or null if none is attached.
	 *
	 * @return $dependantGroup|null
	 */
	public function getDependantGroup();
}
