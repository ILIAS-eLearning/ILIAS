<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes select field.
 */
interface Select extends Input{

	/**
	 * Add a disabled first option "Choose one"
	 *
	 * @return Select
	 */
	public function withFirstOptionDisabled();

	/**
	 * @return bool if the select has the first option disabled.
	 */
	public function hasFirstOptionDisabled();

	/**
	 * @return array of key=>value options.
	 */
	public function getOptions();

	//If none option is needed and we won't add it in the main array of options.
	//public function withNoneOption();
	//public function hasNoneOption();

}
