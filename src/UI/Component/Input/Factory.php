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
	 * @return	\ILIAS\UI\Component\Input\Text
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
	 * @return	\ILIAS\UI\Component\Input\Numeric
	 */
	public function numeric($label, $byline = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Input groups are an unlabeled collection of inputs to be used to build logical units.
	 *   composition: >
	 *      Groups are composed of inputs. They do not contain a label. The grouping remains invisible for the client.
	 *   effect: >
	 *      TBD
	 * 	 rivals: >
	 *      Sections are used to generate visible separations among labeled groups.
	 *
	 * context: >
	 *   TBD
	 *
	 * rules: []
	 *
	 * ---
	 *
	 *
	 * @param	array<mixed,\ILIAS\UI\Component\Input\Input>	$inputs
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
	 * @param	array<mixed,\ILIAS\UI\Component\Input\Input>	$inputs
	 * @param	string|null    $label
	 * @param	string $byline
	 * @return	\ILIAS\UI\Component\Input\Section
	 */
	public function section(array $inputs, $label, $byline = null);
}
