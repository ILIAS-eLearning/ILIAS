<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This is how a factory for inputs looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      TBD
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
	 * @param	string      $label
	 * @param	string|null $byline
	 * @return	\ILIAS\UI\Component\Input\Field\Text
	 */
	public function text($label, $byline = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      TBD
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
	 * @param	string      $label
	 * @param	string|null $byline
	 * @return	\ILIAS\UI\Component\Input\Field\Numeric
	 */
	public function numeric($label, $byline = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Input groups are an unlabeled collection of inputs
     *      to be used to build logical units.
	 *   composition: >
	 *      Groups are composed of inputs. They do not contain a label.
     *      The grouping remains invisible for the client.
	 *   effect: >
	 *      TBD
	 *   rivals:
	 *      Sections: Sections are used to generate visible separations among labeled groups.
	 *
	 * context: >
	 *   TBD
	 *
	 * rules: []
	 *
	 * ---
	 *
	 *
	 * @param	array<mixed,\ILIAS\UI\Component\Input\Field\Input>	$inputs
     * @return	\ILIAS\UI\Component\Input\Field\Group
     */
	public function group(array $inputs);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Labeled section to be used to group inputs of similar category.
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
	 *
	 * @param	array<mixed,\ILIAS\UI\Component\Input\Field\Input>	$inputs
	 * @param	string|null    $label
	 * @param	string $byline
	 * @return	\ILIAS\UI\Component\Input\Field\Section
	 */
	public function section(array $inputs, $label, $byline = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
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
	 *
	 * @param	array<mixed,\ILIAS\UI\Component\Input\Field\Input>	$inputs
	 * @return	\ILIAS\UI\Component\Input\Field\SubSection
	 */
	public function subSection(array $inputs);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A checkbox is used to govern a state, action, or set / not to set a value.
	 *     Checkboxes are typically used to switch on some additional behaviour or services.
	 *   composition: >
	 *      Each Checkbox is labeled by an identifier stating something positive to
	 *      describe the effect of checking the Checkbox.
	 *   effect: >
	 *      If used in a form, a checkbox may open a sub form.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *      A checkbox MUST NOT be used whenever a user has to perform a binary choice where
	 *      option is not automatically the inverse of the other (such as 'Order by Date' and
	 *      'Order by Name'). A  Select Input or a Radio Group in MUST be used in this case.
	 *   wording:
	 *     1: The checkbox’s identifier MUST always state something positive.
	 *
	 * ---
	 *
	 * @return	\ILIAS\UI\Component\Input\Field\Checkbox
	 */
	public function checkbox($label, $byline = null);
}
