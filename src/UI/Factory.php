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
	 * ---
	 * description:
	 *   purpose: >
	 *       Counter inform users about the quantity of items indicated
	 *       by a glyph.
	 *   composition: >
	 *       Counters consist of a number and some background color and are
	 *       placed one the 'end of the line' in reading direction of the item
	 *       they state the count for.
	 *   effect: >
	 *       Counters convey information, they are not interactive.
	 *
	 * featurewiki:
	 *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_3854_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: A counter MUST only be used in combination with a glyph.
	 *   composition:
	 *       1: >
	 *          A counter MUST contain exactly one number greater than zero and no
	 *          other characters.
	 * ---
	 * @return  \ILIAS\UI\Component\Counter\Factory
	 */
	public function counter();
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
	 * @return  \ILIAS\UI\Component\Glyph\Factory
	 */
	public function glyph();
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Buttons trigger interactions that change the system’s status. Usually
	 *      Buttons are contained in an Input Collection. The Toolbar is the main
	 *      exception to this rule, since buttons in the Toolbar might also perform
     *      view changes.
	 *   composition: >
	 *      Button is a clickable, graphically obtrusive control element. It can
	 *      bear text.
	 *   effect: >
	 *      On-click, the action indicated by the button is carried out.
	 *   rivals:
	 *      glyph: >
	 *          Glyphs are used if the enclosing Container Collection can not provide
	 *          enough space for textual information or if such an information would
	 *          clutter the screen.
	 *      links: >
	 *          Links are used to trigger Interactions that do not change the systems
	 *          status. They are usually contained inside a Navigational Collection.
	 *
	 * background: >
	 *      Wording rules have been inspired by the iOS Human Interface Guidelines
	 *      (UI-Elements->Controls->System Button)
	 *
	 *      Style rules have been inspired from the GNOME Human Interface Guidelines->Buttons.
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *           Buttons MUST NOT be used inside a Textual Paragraph.
	 *   interaction:
	 *      1: >
	 *           A Button SHOULD trigger an action. Only in Toolbars, Buttons MAY also
	 *           change the view.
	 *      2: >
	 *           If an action is temporarily not available, Buttons MUST be disabled by
	 *           setting as type 'disabled'.
	 *   style:
	 *      1: >
	 *           If Text is used inside a Button, the Button MUST be at least six characters
	 *           wide.
	 *   wording:
	 *      1: >
	 *           The caption of a Button SHOULD contain no more than two words.
	 *      2: >
	 *           The wording of the button SHOULD describe the action the button performs
	 *           by using a verb or a verb phrase.
	 *      3: >
	 *           Every word except articles, coordinating conjunctions and prepositions
	 *           of four or fewer letters MUST be capitalized.
	 *      4: >
	 *           For standard events such as saving or canceling the existing standard
	 *           terms MUST be used if possible: Save, Cancel, Delete, Cut, Copy.
	 *      5: >
	 *           There are cases where a non-standard label such as “Send Mail” for saving
	 *           and sending the input of a specific form might deviate from the standard.
	 *           These cases MUST however specifically justified.
	 *   accessibility:
	 *      1: >
	 *           DOM elements of type "button" MUST be used to properly identify an
	 *           element as a Button if there is no good reason to do otherwise.
	 *      2: >
	 *           Button DOM elements MUST either be of type "button", of type "a"
	 *           accompanied with the aria-role “Button” or input along with the type
	 *           attribute “button” or "submit".
	 * ---
	 * @return  \ILIAS\UI\Component\Button\Factory
	 */
	public function button();
}
