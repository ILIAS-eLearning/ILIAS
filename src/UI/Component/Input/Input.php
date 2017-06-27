<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes commonalities between all inputs.
 */
interface Input extends Component, JavaScriptBindable {
	/**
	 * Get the label off the input.
	 *
	 * @return	string
	 */
	public function getLabel();

	/**
	 * Get an input like this, but with an replaced label.
	 *
	 * @param	string	$label
	 * @return	Button
	 */
	public function withLabel($label);

	/**
	 * Get the byline off the input.
	 *
	 * @return	string|null
	 */
	public function getByline();

	/**
	 * Get an input like this, but with an additional/replaced label.
	 *
	 * @param	string|null $byline
	 * @return	Button
	 */
	public function withByline($byline);
}
