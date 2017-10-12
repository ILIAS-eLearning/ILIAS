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
	 *      Inputs fields are different from other UI components.
	 *      They bundle two things:
	 *      First, the displaying of the component (as the other components do as well)
	 *      and second, the processing of data as it is received from the client.
	 *      An input field so defines which visual input elements a user can see,
	 *      which constraints are put on those fields and which values developers
	 *      on the server side retrieve from these inputs.
	 *      Constraints may be attached to fields. They put some restriction on
	 *      the values supplied by the user.
	 *      Fields are mostly enclosed by a container which defines the means of
	 *      submitting the data collected by the fields and the way those inputs
	 *      are arranged to be displayed for some client.
	 *   composition: >
	 *      Fields are either individuals or groups of inputs.
	 *      Both, individual fields and groups, share the same basic input interface.
	 *      Input-Fields may have a label and byline.
	 *
	 * rules:
	 *   style:
	 *     1: Disabled input elements MUST be indicated by setting the “disabled” attribute.
	 *     2: If focused, the input elements MUST change their input-border-color to the input-focus-border-color.
	 *
	 *   accessibility:
	 *     1: All Input Elements visible in a view MUST be accessible by keyboard by using the ‘Tab’-Key.
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
	 *      A input container defines which means are used to submit the data
	 *      to the system and how input-fields are beeing displayed in the UI.
	 *      Furthermore, containers will process received data according to the
	 *      transformations and constraints of its fields and their own rules.
	 *   composition: >
	 *      A Container holds one ore more fields.
	 *   rivals:
	 *      Group Field Input: >
	 *        Groups are used within containers to functionallybundle input-fields.
	 *      Section Field Input: >
	 *        Sections are used within containers to visually tie fields together.
	 *
	 * ---
	 *
	 * @return	\ILIAS\UI\Component\Input\Container\Factory
	 */
	public function container();
}
