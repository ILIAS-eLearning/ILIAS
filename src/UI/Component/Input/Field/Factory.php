<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This is what a factory for input fields looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     A text-field is intended for entering short single-line texts.
	 *   composition: >
	 *      Text fields will render an input-tag with type="text".
	 *   effect: >
   	 *      Text inputs are restricted to one line of text.
	 *
 	 * rules:
	 *   usage:
	 *     1: Text Input MUST NOT be used for choosing from predetermined options choices.
	 *     2: Text input MUST NOT be used for numeric input, a Number Input is to be used instead.
	 *     3: Text Input MUST NOT be used for letter-only input, an Alphabet Input is to be used instead.
	 *   interaction:
	 *     1: Text Input MUST limit the number of characters, if a certain length of text-input may not be exceeded (e.g. due to database-limitations)
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
	 *      A numeric-field is used for numeric values.
	 *   composition: >
   	 *      Numeric inputs will render an input-tag with type="number".
	 *   effect: >
	 *      The field does not accept any data other than numeric values.
	 *      When focused, a small vertical rocker is diplayed to increase/decrease
	 *      the value.
	 * rules:
	 *   usage:
	 *     1: Number Inputs MUST NOT be used for binary choices.
	 *     2: Magic numbers such as -1 or 0 to specify “limitless” or some other options MUST NOT be used.
	 *     3: A valid input range SHOULD be specified.
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
	 *      Input groups are an unlabeled collection of inputs.
	 *      They are used to build logical units of other fields.
	 *   composition: >
	 *      Groups are composed of inputs. They do not contain a label.
	 *      The grouping remains invisible for the client.
	 *   effect: >
	 *      There is no visible effect using groups.
	 *   rivals:
	 *      Sections: Sections are used to generate a visible relation of fields.
	 *
	 * rules: []
	 *
	 * ---
	 *
	 * @param	array<mixed,\ILIAS\UI\Component\Input\Field\Input>	$inputs
	 * @return	\ILIAS\UI\Component\Input\Field\Group
	 */
	public function group(array $inputs);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Sections are used to group inputs in a contextual way.
	 *   composition: >
	 *      Sections are composed of inputs. They carry a label and are visible
	 *      for the client.
	 *   rivals:
	 *      Groups: Groups are used as purely logical units, while sections visualize the correlation of fields.
	 *
	 * rules: []
	 *
	 * ---
	 *
	 * @param	array<mixed,\ILIAS\UI\Component\Input\Field\Input>	$inputs
	 * @param	string|null    $label
	 * @param	string $byline
	 * @return	\ILIAS\UI\Component\Input\Field\Section
	 */
	public function section(array $inputs, $label, $byline = null);
}
