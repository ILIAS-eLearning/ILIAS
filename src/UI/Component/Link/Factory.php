<?php

declare(strict_types=1);

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Link;

use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Link factory
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       A standard link uses text as the label of the link.
	 *   composition: >
	 *       The standard link uses the default link color as text color and no
	 *       background. Hovering a standard link underlines the text label.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Standard links MUST be used if there is no good reason to use
	 *          another instance.
	 *       2: >
	 *          Links to ILIAS screens that contain the general ILIAS
	 *          navigation MUST NOT be opened in a new viewport.
	 * ---
	 * @param	string		$label
	 * @param	string		$action
	 * @return  \ILIAS\UI\Component\Link\Standard
	 */
	public function standard(string $label, string $action): Standard;

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Bulky Link is highly obtrusive. It combines the recognisability
	 *     of a graphical element with an explicit textual label on an unusually
	 *     sized button-like area.
	 *
	 *   composition: >
	 *     The Bulky Link is built as a a-tag containing an icon or glyph and a (small) text.
	 *
	 *   rivals:
	 *     Bulky Button: >
	 *       Although visually very much alike, Bulky Buttons rather trigger a Signal
	 *       and execute JavaScript while the Bulky Link opens a URL.
	 *       Use Buttons to act upon other elements and Links to change the page.
	 *       Bulky Links are not stateful.
	 *
	 * context:
	 *   - Slate
	 *   - Drilldown Menu
	 *
	 * rules:
	 *   wording:
	 *     1: The symbol and the text of the Bulky Link MUST be corresponding.
	 *   style:
	 *     1: >
	 *       Bulky Links MUST occupy as much space as their container leaves them.
	 *   responsiveness:
	 *     1: >
	 *        On screens larger than small size, Bulky Links MUST contain a symbol plus text.
	 *     2: >
	 *        On small-sized screens, Bulky Links SHOULD contain only a symbol.
	 *
	 * ---
	 * @param	\ILIAS\UI\Component\Symbol\Symbol	$symbol
	 * @param	string	$label
	 * @param	\ILIAS\Data\URI		$target
	 * @return  \ILIAS\UI\Component\Link\Bulky
	 */
	public function bulky(Symbol $symbol, string $label, \ILIAS\Data\URI $target): Bulky;
}
