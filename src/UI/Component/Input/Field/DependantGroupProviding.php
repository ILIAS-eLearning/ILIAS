<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * These are the commonalities for inputs tirggering a dependant group.
 */
interface DependantGroupProviding {

	/**
	 * Creates an input like this but with a dependant group attached which appears if the
	 * control is clicked.
	 *
	 * @param DependantGroup $dependant_group group to be attached to the checkbox
	 *
	 * @return Input
	 */
	public function withDependantGroup(DependantGroup $dependant_group);


	/**
	 * Returns the attached DependantGroup or null if none is attached.
	 *
	 * @return $dependantGroup|null
	 */
	public function getDependantGroup();
}
