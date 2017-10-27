<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Form;

/**
 * This is how a factory for forms looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Standard Forms are used for creating content of sub-items or for
	 *      configuring objects or services.
	 *   composition: >
	 *      Standard forms provide a submit-button.
	 *   effect: >
	 *      The users manipulates input-values and saves the form to apply the
	 *      settings to the object or service.
	 *
	 * rules:
	 *   usage:
	 *     1: Standard Forms MUST NOT be used on the same content screen as tables.
	 *     2: Standard Forms MUST NOT be used on the same content screen as toolbars.
	 *   composition:
	 *     1: Each form MUST contain at least one titled form section.
	 *     2: Standard Forms MUST only be submitted by their submit-button. They MUST NOT be submitted by anything else.
	 *     3: Standard Foms SHOULD have an „Cancel“-Button
	 *
	 * ---
	 *
	 * @param	string	$post_url
	 * @param	array<mixed,\ILIAS\UI\Component\Input\Input>	$inputs
	 * @return	\ILIAS\UI\Component\Input\Container\Form\Standard
	 */
	public function standard($post_url, array $inputs);
}
