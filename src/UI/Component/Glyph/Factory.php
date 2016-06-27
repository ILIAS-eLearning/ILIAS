<?php
/* Copyright (c) 2015, 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\Glyph;
/**
 * This is how a factory for glyphs looks like.
 */
interface Factory {
	/**
	 * ---
	 * title: Up
	 * description:
	 *   purpose: >
	 *       The glyphed up-button allow for manually arranging rows in tables
	 *       embedded in forms. It allows moving a new item which is otherwise
	 *       appended to the end of the table.
	 *   composition: >
	 *       The up-glyph uses the glyphicon-chevron-up. The glyphed up-button
	 *       can be combined with the add/remove glyph-buttons.
	 *   effect: >
	 *       Clicking on one of the glyph-buttons moves an item up.
	 *
	 * context: Moving answers up in Survey matrix questions.
	 *
	 * featurewiki:
	 *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_813_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The up-glyph MUST NOT be used to sort tables. There is an
	 *          established sorting control for that.
	 *       2: >
	 *          The glyphed up-button SHOULD not come without a glyphed down-
	 *          button and vice versa.
	 *       3: >
	 *          The up-glyphs are actions and SHOULD be listed in the action
	 *          column of a form.
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function up($action);

	/**
	 * ---
	 * title: Down
	 * description:
	 *   purpose: >
	 *       The glyphed down-button allow for manually arranging rows in tables
	 *       embedded in forms. It allows moving a new item which is otherwise
	 *       appended to the end of the table.
	 *   composition: >
	 *       The down-glyph uses the glyphicon-chevron-down. The glyphed down-button
	 *       may be combined with the add/remove Glyph-buttons.
	 *   effect: >
	 *       Clicking on one of the glyph-buttons moves an item down.
	 *
	 * context: Moving answers up in Survey matrix questions.
	 *
	 * featurewiki:
	 *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_813_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The down-glyph MUST NOT be used to sort tables. There is an
	 *          established sorting control for that.
	 *       2: >
	 *          The glyphed down-button SHOULD not come without a glyphed up-
	 *          button and vice versa.
	 *       3: >
	 *          The down-glyphs are actions and SHOULD be listed in the action
	 *          column of a form.
	 * ---
	 * @param	string	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function down($action);

	/**
	 * ---
	 * title: Add
	 * description:
	 *   purpose: >
	 *       The glyphed add-button serves as stand-in for the respective textual
	 *       buttons in very crowded screens. It allows adding a new item.
	 *   composition: >
	 *      The add-glyph uses the glyphicon-add.
	 *   effect: >
	 *      Clicking on the add-glyph adds a new input to a form or an event to
	 *      the calendar.
	 *
	 * context: >
	 *   Adding answer options or taxonomies in questions-editing forms in tests,
	 *   adding events to the calendar.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The glyphed add-button SHOULD not come without a glyphed remove-
	 *          button and vice versa. Because either there is not enough place
	 *          for textual buttons or there is place. Exceptions to this rule,
	 *          such as the Calendar, where only elements can be added in a
	 *          certain place are possible, are to be run through the Jour Fixe.
	 *       2: >
	 *          The glyphed add-buttons are Actions and SHOULD be placed in the
	 *          action column of a form.
	 *       3: The glyphed add-button MUST not be used to add lines to tables.
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function add($action);

	/**
	 * ---
	 * title: Remove
	 * description:
	 *   purpose: >
	 *       The glyphed remove-button serves as stand-in for the respective textual
	 *       buttons in very crowded screens. It allows adding a new item.
	 *   composition: >
	 *       The remove-glyph uses the glyphicon-remove.
	 *   effect: >
	 *       Clicking on the remove-glyph adds a new input to a form or an event to
	 *       the calendar.
	 *
	 * context: >
	 *   Adding answer options or taxonomies in questions-editing forms in tests,
	 *   adding events to the calendar.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The glyphed remove-button SHOULD not come without a glyphed add-
	 *          button and vice versa. Because either there is not enough place
	 *          for textual buttons or there is place. Exceptions to this rule,
	 *          such as the Calendar, where only elements can be added in a
	 *          certain place are possible, are to be run through the Jour Fixe.
	 *       2: >
	 *          The glyphed remove-buttons are Actions and SHOULD be placed in the
	 *          action column of a form.
	 *       3: The glyphed remove-button MUST not be used to add lines to tables.
	 * ---
	 * @param	string	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function remove($action);

	/**
	 * ---
	 * title: Previous
	 * description:
	 *   purpose: >
	 *       The previous-glyph indicates a possible change of the view.
	 *   composition: >
	 *       The chevron-left is used.
	 *   effect: >
	 *       The click on a previous-glyph leads back to a previous view.
	 *   rivals:
	 *       Caret: The caret handles opening and closing hidden aspects of a view such as a dropdown or content of an accordion.
	 *
	 * context: Show Member View in courses.
	 *
	 * ---
	 * @param	string	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function previous($action);

	/**
	 * ---
	 * title: Next
	 * description:
	 *   purpose: >
	 *       The next-glyph indicates a possible change of the view.
	 *   composition: >
	 *       The chevron-right is used.
	 *   effect: >
	 *       The click on a next-glyph opens a new view of an object.
	 *   rivals:
	 *       Caret: The caret handles opening and closing hidden aspects of a view such as a dropdown or content of an accordion.
	 *
	 * context: Show Member View in courses.
	 *
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function next($action);

	/**
	 * ---
	 * title: Calendar
	 * ---
	 * @param	string	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function calendar($action);

	/**
	 * ---
	 * title: Close
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function close($action);

	/**
	 * ---
	 * title: Attachement
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function attachment($action);

	/**
	 * ---
	 * title: Caret
	 * description:
	 *   purpose: >
	 *       The Caret Glyph is used to trigger the the display of some neighbouring Container Collection such as a the content of a Dropdown or an Accordion.
	 *   composition: >
	 *       Carets indicating an underlying Overlay such as a Dropdown the default Caret class may be used.
	 *       In most cases the glyphicon-triangle should be chosen in the correct orientation to indicate whether to content is currently displayed or not.
	 *       Triangle-right indicates that underlying content is hidden Triangle-bottom indicates that the underlying content is currently shown.
	 *   effect: >
	 *       Clicking the caret toggles the display of some Container Collection.
	 *   rivals:
	 *       Previous/Next Glyph: The Previous/Next Glyph opens a completely new view. It serves a navigational purpose.
	 *
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function caret($action);

	/**
	 * ---
	 * title: Drag
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function drag($action);

	/**
	 * ---
	 * title: Search
	 * description:
	 *   purpose: >
	 *       The Search Glyph is used whenever content is to be searched. E.g. The Search Glyph triggers the Top Search Popover. This is the only access to the global search.
	 *   composition: >
	 *       The Search Glyph uses the glyphicon-search.
	 *   effect: >
	 *       Clicking the Search Glyph triggers the display of the Top Search Popover. This is the only access to the search.
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function search($action);

	/**
	 * ---
	 * title: Filter
	 * ---
	 * @param	string	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function filter($action);

	/**
	 * ---
	 * title: Info
	 * ---
	 * @param	string	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function info($action);

	/**
	 * ---
	 * title: Envelope
	 * ---
	 * @param	string	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function envelope($action);
}
