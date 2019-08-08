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
	 *     The bulky button is highly obtrusive. It combines the recognisability
	 *     of a graphical element with an explicit textual label on an unusually
	 *     sized button-like area.
	 *   composition: >
	 *       The Bulky Link is built as a clickable area with an icon or glyph and
	 *       a label.
	 *
	 * rules:
	 *   usage:
	 *     1: >
	 *       TODO Since Bulky Buttons are so obtrusive they MUST only be used
	 *       to indicate important actions on the screen.
	 *   wording:
	 *     1: TODO The icon/glyph and the text on the Bulky Button MUST be corresponding.
	 *   style:
	 *     1: >
	 *       TODO Bulky Buttons MUST occupy as much space as their container
	 *       leaves them.
	 *     2: >
	 *       TODO When used to toggle the visibility of another component, the button
	 *       MUST reflect the componentes state of visibility.
	 *   responsiveness:
	 *     1: >
	 *        TODO On screens larger than small size, Bulky Buttons MUST contain an icon or glyph plus text.
	 *     2: >
	 *        TODO On small-sized screens, Bulky Buttons SHOULD contain only an icon or glyph.
	 *   accessibility:
	 *     1: >
	 *       TODO The functionality of the Bulky Button MUST be indicated for screen
	 *       readers by an aria-label.
	 *     2: >
	 *        TODO Some Buttons can be stateful; when engaged, the state MUST be
	 *        reflected in the "aria-pressed"-, respectively the "aria-checked"-attribute.
	 *        If the Button is not stateful (which is the default), the
	 *        aria-attribute SHOULD be omitted.
	 *
	 * ---
	 * @param	\ILIAS\UI\Component\Symbol\Symbol		$symbol
	 * @param	string		$label
	 * @param	string		$action
	 * @return  \ILIAS\UI\Component\Link\Bulky
	 */
	public function bulky(Symbol $symbol, string $label, string $action): Bulky;
}
