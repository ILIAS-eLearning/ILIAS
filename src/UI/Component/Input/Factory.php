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
	 *      Inputs fields are different from other UI components. They bundle two
	 *      things:
	 *      First, they are used for displaying, as similar to other componentes.
	 *      Second, they are used to define the server side processing of data that
	 *      is received from the client.
	 *      Thus, sn input field defines which visual input elements a user will see,
	 *      which constraints are put on the data entered in these fields and which
	 *      data developers on the server side retrieve from these inputs.
	 *      Fields need to be enclosed by a container which defines the means of
	 *      submitting the data collected by the fields and the way those inputs
	 *      are arranged to be displayed for some client.
	 *   composition: >
	 *      Fields are either individuals or groups of inputs. Both, individual fields
	 *      and groups, share the same basic input interface. Input-Fields may have a
	 *      label and byline.
	 *
	 * rules:
	 *   style:
	 *     1: >
	 *         Disabled input elements MUST be indicated by setting the “disabled”
	 *         attribute.
	 *     2: >
	 *         If focused, the input elements MUST change their input-border-color
	 *         to the input-focus-border-color.
	 *   accessibility:
	 *     1: >
	 *         All fields visible in a view MUST be accessible by keyboard by using the
	 *         ‘Tab’-Key.
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
	 *      A input container defines which means are used to submit the data to the
	 *      system and how input-fields are beeing displayed in the UI. Furthermore
	 *      containers will process received data according to the transformations
	 *      and constraints of its fields.
	 *   composition: >
	 *      A Container holds one ore more fields.
	 *   rivals:
	 *      Group Field Input: >
	 *        Groups are used within containers to functionally bundle input-fields.
	 *      Section Field Input: >
	 *        Sections are used within containers to visually tie fields together.
	 *
	 * ---
	 *
	 * @return	\ILIAS\UI\Component\Input\Container\Factory
	 */
	public function container();
}
