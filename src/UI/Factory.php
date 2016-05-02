<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 *
 * Consumers of the UI-Service MUST program against this interface and not
 * use any concrete implementations from Internal.
 */
interface Factory {
	/**
	 * description:
	 *   purpose:
	 *       Counter inform users about the quantity of items indicated
	 *       by a glyph.
	 *   composition:
	 *       Counters consist of a number and some background color and are
	 *       placed one the 'end of the line' in reading direction of the item
	 *       they state the count for.
	 *   effect:
	 *       Counters convey information, they are not interactive.
	 *   rival:
	 *       none
	 *
	 * featurewiki:
	 *       http://www.ilias.de/docu/goto_docu_wiki_wpage_3854_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: A counter MUST only be used in combination with a glyph.
	 *   composition:
	 *       2: A counter MUST contain exactly one number greater than zero and no
	 *          other characters.
	 *
	 * @return  \ILIAS\UI\Factory\Counter
	 */
	public function counter();

	/**
	 * description:
	 *   purpose:
	 *       Glyphs are used to map a generally known concept or symbol to a specific
	 *       concept in ILIAS. Glyph are used when space is scarce.
	 *   composition:
	 *       A glyph is a typographical character that represents
	 *       something else. As any other typographical character, they can be
	 *       manipulated by regular CSS. If hovered they change to the link-hover-
	 *       color to indicate possible interactions.
	 *   effect:
	 *       Glyphs act as trigger for some action such as opening a certain
	 *       Overlay type or as shortcut.
	 *   rival:
	 *       icon:
	 *           Icons are not interactive as standalone (they can be in an
	 *           interactive container however). They only serve as additional
	 *           hint of the functionality described by some title. Glyphs are
	 *           visually distinguished from object icons.
	 *
	 * background: |-
	 *     "In typography, a glyph is an elemental symbol within an agreed set of
	 *     symbols, intended to represent a readable character for the purposes
	 *     of writing and thereby expressing thoughts, ideas and concepts."
	 *     (https://en.wikipedia.org/wiki/Glyph)
	 *
	 *     Lidwell states that such symbols are used "to improve the recognition
	 *     and recall of signs and controls".
	 *     (W.Lidwell,K.Holden,and J.Butler,Universal Principles of Design:
	 *     100 Ways to Enhance Usability, Influence Perception, Increase Appeal,
	 *     Make Better Design Decisions, and Teach Through Design. Rockport
	 *     Publishers, 2003, ch. Iconic Representation, pp. 110 – 111)
	 *
	 * rules:
	 *   usage:
	 *       1: Glyphs MUST NOT be used in content titles.
	 *       2: Glyphs MUST be used for cross-sectional functionality as mail for
	 *          example and NOT for representing objects.
	 *       3: Glyphs SHOULD be used for very simple tasks that are repeated at
	 *          many places throughout the system.
	 *       4: Services such as mail MAY be represented by a glyph AND an icon.
	 *   style:
	 *       5: All Glyphs MUST be taken from the Bootstrap Glyphicon Halflings
	 *          set. Exceptions MUST be approved by the JF.
	 *   accessability:
	 *       6: The functionality triggered by the Glyph must be indicated to
	 *          screen readers with by the attribute aria-label. If the Glyph
	 *          accompanies some text describing the functionality of the triggered,
	 *          this MUST be indicated by the aria-labelledby attribute.
	 *
	 * @return  \ILIAS\UI\Factory\Glyph
	 */
	public function glyph();
}