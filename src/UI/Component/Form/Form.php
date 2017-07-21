<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Form;

use ILIAS\UI\Component\Component;

use Psr\Http\Message\ServerRequestInterface;

/**
 * This describes commonalities between all forms.
 */
interface Form extends Component {
	/**
	 * Get the inputs contained in the form.
	 *
	 * @return	\ILIAS\UI\Component\Input\Input
	 */
	public function getInputs();

	/**
	 * Get a form like this where data from the request is attached.
	 *
	 * @param	ServerRequestInterface $request
	 * @return	Form
	 */
	public function withRequest(ServerRequestInterface $request);
}
