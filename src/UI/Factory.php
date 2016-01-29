<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 *
 * Consumers of the UI-Service must program against this interface and not
 * use any concrete implementations.
 */
interface Factory {
    /**
     * Description
     *  * Purpose: Counter inform users about the quantity of items indicated
     *    by a glyph.
     *  * Composition: Counters consist of a number and some background color
     *    and are placed one the 'end of the line' in reading direction of the
     *    the item they state the count for.
     *  * Effect: Counters convey information, they are not interactive.
     *  * Rival elements: none
     *
     * Rules:
     *  * A counter MUST only be used in combination with a glyph.
     *  * A counter MUST contain exactly one number greater than zero and no
     *    other characters.
     *
     * @return  \ILIAS\UI\Factory\Counter
     */
    public function counter();

    /**
     * Description
     *  * Purpose: Glyphs are used to map a generally known concept or symbol
     *    to a specific concept in ILIAS. Glyph are used when space is scarce.
     *  * Composition: A glyph is a typographical character that represents
     *    something else. As any other typographical character, they can be
     *    manipulated by regular CSS. If hovered they change to the link-hover-
     *    color to indicate possible interactions.
     *  * Effect: Glyphs act as trigger for some action such as opening a
     *    certain Overlay type or as shortcut.
     *  * Rival Elements:
     *      - Icon: Icons are not interactive as standalone (they can be in
     *        an interactive container however). They only serve as additional
     *        hint of the functionality described by some title. Glyphs are
     *        visually distinguished from object icons.
     *
     * Background:
     *  “In typography, a glyph is an elemental symbol within an agreed set of
     *  symbols, intended to represent a readable character for the purposes
     *  of writing and thereby expressing thoughts, ideas and concepts.”
     *  (https://en.wikipedia.org/wiki/Glyph)
     *
     *  Lidwell states that such symbols are used ”to improve the recognition
     *  and recall of signs and controls”.
     *  (W.Lidwell,K.Holden,and J.Butler,Universal Principles of Design:
     *  100 Ways to Enhance Usability, Influence Perception, Increase Appeal,
     *  Make Better Design Decisions, and Teach Through Design. Rockport
     *  Publishers, 2003, ch. Iconic Representation, pp. 110 – 111)
     *
     * Rules:
     *  * Usage:
     *      - Glyphs MUST NOT be used in Content Titles.
     *      - If an additional Glyph is needed, it MUST be added it to the
     *        Kitchen Sink first with a proper description and context and the
     *        ilGlyphGUI class must be updated accordingly.
     *      - Glyphs MUST be used for cross-sectional functionality as mail for
     *        example and NOT for representing objects.
     *      - Glyphs SHOULD be used for very simple tasks that are repeated at
     *        many places throughout the system.
     *      - Services such as mail MAY be represented by a glyph AND an icon.
     *  * Style:
     *      - All Glyphs MUST be taken from the Bootstrap Glyphicon Halflings
     *        set. Exceptions MUST be approved by the JF.
     *  * Accessability:
     *      - The functionality triggered by the Glyph must be indicated to
     *        screen readers with by the attribute aria-label. If the Glyph
     *        accompanies some text describing the functionality of the
     *        triggered, this MUST be indicated by the aria-labelledby
     *        attribute.
     *
     * @return  \ILIAS\UI\Factory\Glyph
     */
    public function glyph();
}