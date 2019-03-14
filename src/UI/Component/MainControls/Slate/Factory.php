<?php

namespace ILIAS\UI\Component\MainControls\Slate;

/**
 * This is what a factory for slates looks like.
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Legacy Slate is used to wrap content into a slate when there is
	 *     no other possibility (yet). In general, this should not be used and
	 *     may vanish with the progress of specific slates.
	 *
	 *   composition: >
	 *     The Legacy Slate will take a Legacy-Component and render it.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       This component MUST NOT be used to display elements that can be
	 *       generated using other UI Components.
	 *
	 * ----
	 *
	 * @param string $name
	 * @param \ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph $symbol
	 * @param \ILIAS\UI\Component\Legacy\Legacy $content
	 * @return \ILIAS\UI\Component\MainControls\Slate\Legacy
	 */
	public function legacy(string $name, $symbol, \ILIAS\UI\Component\Legacy\Legacy $content): Legacy;


	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Combined Slate bundles related controls; these can also be further
	 *     Slates. Combined Slates are used when a specific purpose is being
	 *     subdivided into further aspects.
	 *
	 *   composition: >
	 *     The Combined Slate consists of more Slates and/or Bulky Buttons.
	 *     The symbol and name of contained Slates are turned into a Bulky Button
	 *     to control opening and closing the contained Slate.
	 *
	 *   effect: >
	 *     Opening a Combined Slate will display its contained Slates with an
	 *     operating Bulky Button for closing/expanding.
	 *     Clicking on a Button not connected to a Slate will carry out its action.
 	 *
	 * context:
	 *   - The Combined Slate is used in the Main Bar.
	 *
	 * ----
	 *
	 * @param string $name
	 * @param \ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph $symbol
	 * @return \ILIAS\UI\Component\MainControls\Slate\Combined
	 */
	public function combined(string $name, $symbol): Combined;

}
