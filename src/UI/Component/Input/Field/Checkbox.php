<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes checkbox inputs.
 */
interface Checkbox extends Input {
	/**
	 * Creates a Checkbox like this but with a SubSection attached which appears if the
	 * control is clicked.
	 *
	 * @param SubSection $sub_section Section to be attached to the checkbox
	 * @return Checkbox
	 */
	public function withSubsection(SubSection $sub_section);


	/**
	 * Returns the attached SubSection or null if none is attached.
	 *
	 * @return SubSection|null
	 */
	public function getSubSection();
}
