<?php

/* Copyright (c) 2015, 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Factory;

/**
 * This is how a factory for glyphs looks like.
 */
interface Glyph {
	/**
	 * description:
	 *   purpose:
	 *       The glyphed up-button allow for manually arranging rows in tables
	 *       embedded in forms. It allows moving a new item which is otherwise
	 *       appended to the end of the table.
	 *   composition:
	 *       The up-glyph uses the glyphicon-chevron-up. The glyphed up-button
	 *       can be combined with the add/remove glyph-buttons.
	 *   effect:
	 *       Clicking on one of the glyph-buttons moves an item up.
	 *
	 * context: Moving answers up in Survey matrix questions.
	 *
	 * featurewiki: http://www.ilias.de/docu/goto_docu_wiki_wpage_813_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: The up-glyph MUST NOT be used to sort tables. There is an
	 *          established sorting control for that.
	 *       2: The glyphed up-button SHOULD not come without a glyphed down-
	 *          button and vice versa.
	 *       3: The up-glyphs are actions and SHOULD be listed in the action
	 *          column of a form.
	 *
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function up();

	/**
	 * description:
	 *   purpose:
	 *       The glyphed down-button allow for manually arranging rows in tables
	 *       embedded in forms. It allows moving a new item which is otherwise
	 *       appended to the end of the table.
	 *   composition:
	 *       The down-glyph uses the glyphicon-chevron-down. The glyphed down-button
	 *       may be combined with the add/remove Glyph-buttons.
	 *   effect:
	 *       Clicking on one of the glyph-buttons moves an item down.
	 *
	 * context: Moving answers up in Survey matrix questions.
	 *
	 * featurewiki: http://www.ilias.de/docu/goto_docu_wiki_wpage_813_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: The down-glyph MUST NOT be used to sort tables. There is an
	 *          established sorting control for that.
	 *       2: The glyphed down-button SHOULD not come without a glyphed up-
	 *          button and vice versa.
	 *       3: The down-glyphs are actions and SHOULD be listed in the action
	 *          column of a form.
	 *
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function down();

	/**
	 * description:
	 *   purpose:
	 *       The glyphed add-button serves as stand-in for the respective textual
	 *       buttons in very crowded screens. It allows adding a new item.
	 *   composition:
	 *      The add-glyph uses the glyphicon-add.
	 *   effect:
	 *      Clicking on the add-glyph adds a new input to a form or an event to
	 *      the calendar.
	 *
	 * context:
	 *   Adding answer options or taxonomies in questions-editing forms in tests,
	 *   adding events to the calendar.
	 *
	 * rules:
	 *   usage:
	 *       1: The glyphed add-button SHOULD not come without a glyphed remove-
	 *          button and vice versa. Because either there is not enough place
	 *          for textual buttons or there is place. Exceptions to this rule,
	 *          such as the Calendar, where only elements can be added in a
	 *          certain place are possible, are to be run through the Jour Fixe.
	 *       2: The glyphed add-buttons are Actions and SHOULD be placed in the
	 *          action column of a form.
	 *       3: The glyphed add-button MUST not be used to add lines to tables.
	 *
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function add();
	
	/**
	 * description:
	 *   purpose:
	 *       The glyphed remove-button serves as stand-in for the respective textual
	 *       buttons in very crowded screens. It allows adding a new item.
	 *   composition:
	 *       The remove-glyph uses the glyphicon-remove.
	 *   effect:
	 *       Clicking on the remove-glyph adds a new input to a form or an event to
	 *       the calendar.
	 *
	 * context:
	 *   Adding answer options or taxonomies in questions-editing forms in tests,
	 *   adding events to the calendar.
	 *
	 * rules:
	 *   usage:
	 *       1: The glyphed remove-button SHOULD not come without a glyphed add-
	 *          button and vice versa. Because either there is not enough place
	 *          for textual buttons or there is place. Exceptions to this rule,
	 *          such as the Calendar, where only elements can be added in a
	 *          certain place are possible, are to be run through the Jour Fixe.
	 *       2: The glyphed remove-buttons are Actions and SHOULD be placed in the
	 *          action column of a form.
	 *       3: The glyphed remove-button MUST not be used to add lines to tables.
	 *
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function remove();

	/**
	 * description:
	 *   purpose:
	 *       The previous-glyph indicates a possible change of the view.
	 *   composition:
	 *       The chevron-left is used.
	 *   effect:
	 *       The click on a previous-glyph leads back to a previous view.
	 *   rival:
	 *       The caret handles opening and closing hidden aspects of a view such
	 *       as a dropdown or content of an accordion.
	 *
	 * context: "Show Member View" in courses.
	 *
	 * rules: TBD
	 *
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function previous();

	/**
	 * description:
	 *   purpose:
	 *       The next-glyph indicates a possible change of the view.
	 *   composition:
	 *       The chevron-right is used.
	 *   effect:
	 *       The click on a next-glyph opens a new view of an object.
	 *   rival:
	 *       The caret handles opening and closing hidden aspects of a view such
	 *       as a dropdown or content of an accordion.
	 *
	 * context: "Show Member View" in courses.
	 *
	 * rules: TBD
	 *
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function next();

	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function calendar();

	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function close();

	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function attachment();

	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function caret();

	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function drag();

	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function search();

	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function filter();

	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function info();
	/**
	 * @return \ILIAS\UI\Element\Glyph
	 */
	public function envelope();
}