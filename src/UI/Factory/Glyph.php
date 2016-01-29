<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Factory;

/**
 * This is how a factory for glyphs looks like.
 */
interface Glyph {
    /**
     * Description:
     *  * Purpose: The glyphed up-button allow for manually arranging rows in
     *    tables embedded in forms. It allows moving a new item which is
     *    otherwise appended to the end of the table.
     *  * Composition: The Up Glyph uses the glyphicon-chevron-up. The glyphed
     *    up-button can be combined with the Add/Remove Glyph-buttons.
     *  * Effect: Clicking on one of the Glyph-Buttons moves an item up.
     *
     * Context:
     *  * Moving answers up in Survey matrix questions.
     *
     * Rules:
     *   * Usage:
     *      - The Up Glyph MUST NOT be used to sort tables. There is an
     *        established sorting control for that.
     *      - The glyphed up-button SHOULD not come without a glyphed down-
     *        button and vice versa.
     *      - The Up Glyphs are Actions and SHOULD be listed in the Action
     *        column of a form.
     *
     * @return \ILIAS\UI\Element\Glyph
     */
    public function up();

    /**
     * Description:
     *  * Purpose: The glyphed down-button allow for manually arranging rows in
     *    tables embedded in forms. It allows moving a new item which is
     *    otherwise appended to the end of the table.
     *  * Composition: The Down Glyph uses the glyphicon-chevron-down. The
     *    glyphed down-button can be combined with the Add/Remove Glyph-buttons.
     *  * Effect: Clicking on one of the Glyph-Buttons moves an item down.
     *
     * Context:
     *  * Moving answers down in Survey matrix questions.
     *
     * Rules:
     *   * Usage:
     *      - The Down Glyph MUST NOT be used to sort tables. There is an
     *        established sorting control for that.
     *      - The glyphed down-button SHOULD not come without a glyphed up-
     *        button and vice versa.
     *      - The Down Glyphs are Actions and SHOULD be listed in the Action
     *        column of a form.
     *
     * @return \ILIAS\UI\Element\Glyph
     */
    public function down();

    /**
     * Description:
     *  * Purpose: The glyphed add-button serves as stand-in for the respective
     *    textual buttons in very crowded screens. It allows adding a new item.
     *  * Composition: The Add Glyph uses the glyphicon-add
     *  * Effect: Clicking on the Add Glyph adds a new input to a form or an
     *    event to the calendar.
     *
     * Context:
     *  * Adding answer options or taxonomies in questions-editing forms in
     *    Tests, adding events to the calendar.
     *
     * Rules:
     *  * Usage:
     *      - The glyphed add-button SHOULD not come without a glyphed remove-
     *        button and vice versa. Because either there is not enough place
     *        for textual buttons or there is place. Exceptions to this rule,
     *        such as the Calendar, where only elements can be added in a
     *        certain place are possible, are to be run through the Jour Fixe.
     *      - The glyphed add-buttons are Actions and SHOULD be placed in the
     *        Action column of a form.
     *      - The glyphed add-button MUST not be used to add lines to tables.
     *
     * @return \ILIAS\UI\Element\Glyph
     */
    public function add();
    
    /**
     * Description:
     *  * Purpose: The glyphed remove-button serves as stand-in for the
     *    respective textual buttons in very crowded screens. It allows
     *    removing an existing item.
     *  * Composition: The Remove Glyph uses the glyphicon-remove
     *  * Effect: Clicking on the Remove Glyph removes an existing input from a
     *    form or an event from the calendar.
     *
     * Context:
     *  * Removing answer options or taxonomies in questions-editing forms in
     *    Tests, removing events from the calendar.
     *
     * Rules:
     *  * Usage:
     *      - The glyphed remove-button SHOULD not come without a glyphed add-
     *        button and vice versa. Because either there is not enough place
     *        for textual buttons or there is place. Exceptions to this rule,
     *        such as the Calendar, where only elements can be added in a
     *        certain place are possible, are to be run through the Jour Fixe.
     *      - The glyphed remove-buttons are Actions and SHOULD be placed in
     *        the Action column of a form.
     *      - The glyphed remove-button MUST not be used to add lines to
     *        tables.
     *
     * @return \ILIAS\UI\Element\Glyph
     */
    public function remove();

    /**
     * @return \ILIAS\UI\Element\Glyph
     */
    public function previous();

    /**
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
}