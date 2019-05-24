<?php
declare(strict_types=1);

namespace ILIAS\UI\Component\Symbol;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Symbol
 */
interface Factory
{

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     Icons are quickly comprehensible and recognizable graphics.
	 *     They indicate the functionality or nature of a text-element or context:
	 *     Icons will mainly be used in front of object-titles, e.g. in the
	 *     header, the tree and in repository listing.
	 *     Icons can be disabled. Disabled Icons visually communicate that the depicted
	 *     functionality is not available for the intended audience.
	 *   composition: >
	 *     Icons come in three fixed sizes: small, medium and large.
	 *     They can be configured with an additional "abbreviation",
	 *     a text of a few characters that will be rendered on top of the image.
	 *     The Disabled Icons merely stand out visually: A color shade covers the Icon.
	 *   effect: >
	 *     Icons themselves are not interactive; however they are allowed
	 *     within interactive containers.
	 *   rivals:
	 *     Glyph: >
	 *       Glyphs are typographical characters that act as a trigger for
	 *       some action.
	 *     Image: >
	 *       Images belong to the content and can be purely decorative.
	 *
	 *
	 * rules:
	 *   usage:
	 *     1: Icons MUST be used to represent objects or context.
	 *     2: Icons MUST be used in combination with a title or label.
	 *     3: An unique Icon MUST always refer to the same thing.
	 *   style:
	 *     1: Icons MUST have a class indicating their usage.
	 *     2: Icons MUST be tagged with a CSS-class indicating their size.
	 *   accessibility:
	 *     1: Icons MUST use aria-label.
	 *     2: Disabled Icons MUST bear an aria-label indicating the special status.
	 *   wording:
	 *     1: The aria-label MUST state the represented object-type.
	 *     2: The abbreviation SHOULD consist of one or two letters.
	 * ---
	 *
	 * @return \ILIAS\UI\Component\Symbol\Icon\Factory
	 **/
	public function icon(): Icon\Factory;

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Glyphs map a generally known concept or symbol to a specific concept in ILIAS.
	 *       Glyphs are used when space is scarce.
	 *   composition: >
	 *       A glyph is a typographical character that represents
	 *       something else. As any other typographical character, they can be
	 *       manipulated by regular CSS. If hovered they change their background
	 *       to indicate possible interactions.
	 *   effect: >
	 *       Glyphs act as trigger for some action such as opening a certain
	 *       Overlay type or as shortcut.
	 *   rivals:
	 *       icon: >
	 *           Standalone Icons are not interactive. Icons can be in an interactive container however.
	 *           Icons merely serve as additional hint of the functionality described by some title.
	 *           Glyphs are visually distinguished from object icons: they are monochrome.
	 * background: >
	 *     "In typography, a glyph is an elemental symbol within an agreed set of
	 *     symbols, intended to represent a readable character for the purposes
	 *     of writing and thereby expressing thoughts, ideas and concepts."
	 *     (https://en.wikipedia.org/wiki/Glyph)
	 *
	 *     Lidwell states that such symbols are used "to improve the recognition
	 *     and recall of signs and controls".
	 *
	 * rules:
	 *   usage:
	 *       1: Glyphs MUST NOT be used in content titles.
	 *       2: >
	 *          Glyphs MUST be used for cross-sectional functionality such as mail for
	 *          example and NOT for representing objects.
	 *       3: >
	 *          Glyphs SHOULD be used for very simple tasks that are repeated at
	 *          many places throughout the system.
	 *       4: >
	 *          Services such as mail MAY be represented by a glyph AND an icon.
	 *   style:
	 *       1: >
	 *          All Glyphs MUST be taken from the Bootstrap Glyphicon Halflings
	 *          set. Exceptions MUST be approved by the JF.
	 *   accessibility:
	 *       1: >
	 *          The functionality triggered by the Glyph must be indicated to
	 *          screen readers with by the attribute aria-label or aria-labelledby attribute.
	 * ---
	 * @return  \ILIAS\UI\Component\Symbol\Glyph\Factory
	 */
	public function glyph(): Glyph\Factory;

}
