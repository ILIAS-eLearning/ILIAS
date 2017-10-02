<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input;

/**
 * This is how a factory for inputs looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
     *      Inputs fields are different from other UI components. They bundle two things:
     *      the displaying of the component (as the other components do as well)
     *      and the processing of data as it is received from the client.
	 *      An input field so defines, which visual input elements a user can see, which constraints
     *      are put on those fields and which values developers on the server side retrieve from these inputs.
     *      Constraints may be attached to fields. They put some restriction on the values supplied by the user. Fields
     *      Are mostly enclosed by a container which defines the the means of submitting the data collected by the fields
     *      and the way those inputs are arranged to be displayed for some client.
	 *   composition: >
	 *      Fields are either individuals groups of inputs. Both, individuals fields and groups share the same basic input
     *      interface.
	 *   effect: >
	 *      TBD
	 * context: >
	 *   TBD
	 *
	 * rules: []
	 *
	 * ---
	 *
	 * @return	\ILIAS\UI\Component\Input\Field\Factory
	 */
	public function field();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      An input container defines, which means of submitting the forms are used and how the fields are displayed together.
	 *   composition: >
	 *      TBD
	 *   effect: >
	 *      TBD
	 * context: >
	 *   TBD
	 *
	 * rules: []
	 *
	 * ---
	 *
	 * @return	\ILIAS\UI\Component\Input\Container\Factory
	 */
	public function container();
}
