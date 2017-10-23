<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

use CaT\Ente\Component;

/**
 * This keeps the value of placeholders for email templates.
 * It is provided as an ente-component, since there will be multiple plugins participating
 * in the process.
 */
interface PlaceholderValue extends Component {
	/**
	 * Get the placeholder text
	 *
	 * @return string
	 */
	public function getPlaceholder();

	/**
	 * Get the value of placeholder
	 *
	 * @return string
	 */
	public function getValue();
}