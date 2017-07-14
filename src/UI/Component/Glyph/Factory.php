<?php
/* Copyright (c) 2015, 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\Glyph;
/**
 * This is how a factory for glyphs looks like.
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Settings Glyph triggers opening a Dropdown to edit settings of the displayed block.
	 *   composition: >
	 *       The Settings Glyph uses the glyphicon-cog.
	 *   effect: >
	 *      Upon clicking a settings Dropdown is opened.
	 *
	 * context: >
	 *   Adding answer options or taxonomies in questions-editing forms in tests,
	 *   adding events to the calendar.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The Settings Glyph MUST only be used in Blocks.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be “Settings”.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function settings($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Collapse Glyph is used to trigger the collapsing of
	 *       some neighbouring Container Collection such as a the content of a Dropdown or an Accordion currently shown.
	 *   composition: >
	 *       The Collapse Glyph is composed of a triangle pointing to the bottom indicating that content is currently shown.
	 *   effect: >
	 *      Clicking the Collapse Glyph hides the display of some Container Collection.
	 *   rivals:
	 *      Expand Glyph: The Expand Glyphs triggers the display of some Container Collection.
	 *      Previous Glyph: The Previous/Next Glyph opens a completely new view. It serves a navigational purpose.
	 *
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The Collapse Glyph MUST indicate if the toggled Container Collection is visible or not.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Collapse Content'.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function collapse($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Expand Glyph is used to trigger the display of
	 *       some neighbouring Container Collection such as a the content of a Dropdown or an Accordion currently shown.
	 *   composition: >
	 *       The Expand Glyph is composed of a triangle pointing to the right indicating that content is currently shown.
	 *   effect: >
	 *      Clicking the Expand Glyph displays some Container Collection.
	 *   rivals:
	 *      Collapse Glyph: The Collapse Glyphs hides the display of some Container Collection.
	 *      Previous Glyph: The Previous/Next Glyph opens a completely new view. It serves a navigational purpose.
	 *
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The Expand Glyph MUST indicate if the toggled Container Collection is visible or not.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Expand Content'.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function expand($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The glyphed add-button serves as stand-in for the respective textual
	 *       buttons in very crowded screens. It allows adding a new item.
	 *   composition: >
	 *      The Add Glyph uses the glyphicon-plus-sign.
	 *   effect: >
	 *      Clicking on the Add Glyph adds a new input to a form or an event to the calendar.
	 *
	 * context: >
	 *   Adding answer options or taxonomies in questions-editing forms in tests,
	 *   adding events to the calendar.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The Add Glyph SHOULD not come without a Remove Glyph and vice versa.
	 *          Because either there is not enough place
	 *          for textual buttons or there is place. Exceptions to this rule,
	 *          such as the Calendar, where only elements can be added in a
	 *          certain place are possible, are to be run through the Jour Fixe.
	 *       2: >
	 *          The Add Glyph stands for an Action and SHOULD be placed in the
	 *          action column of a form.
	 *       3: The Add Glyph MUST not be used to add lines to tables.
	 *   interaction:
	 *       1: Newly added items MUST be placed below the line in which the Add Glyph has been clicked
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Add'.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function add($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Remove Glyph serves as stand-in for the respective textual
	 *       buttons in very crowded screens. It allows removing an item.
	 *   composition: >
	 *       The Remove Glyph uses the glyphicon-plus-sign.
	 *   effect: >
	 *       Clicking on the Remove Glyph adds a new input to a form or an event to
	 *       the calendar.
	 *
	 * context: >
	 *   Adding answer options or taxonomies in questions-editing forms in tests,
	 *   adding events to the calendar.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The Remove Glyph SHOULD not come without a glyphed Add Glyph and vice versa.
	 *          Because either there is not enough place
	 *          for textual buttons or there is place. Exceptions to this rule,
	 *          such as the Calendar, where only elements can be added in a
	 *          certain place are possible, are to be run through the Jour Fixe.
	 *       2: >
	 *          The Remove Glyph stands for an Action and SHOULD be placed in the
	 *          action column of a form.
	 *       3: The Remove Glyph MUST not be used to add lines to tables.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Remove'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function remove($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Up Glyph allows for manually arranging rows in tables embedded in forms.
	 *       It allows moving an item up.
	 *   composition: >
	 *       The Up Glyph uses the glyphicon-circle-arrow-up. The Up Glyph
	 *       can be combined with the Add/Remove Glyph.
	 *   effect: >
	 *       Clicking on the Up Glyph moves an item up.
	 *
	 * context: Moving answers up in Survey matrix questions.
	 *
	 * featurewiki:
	 *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_813_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The Up Glyph MUST NOT be used to sort tables. There is an
	 *          established sorting control for that.
	 *       2: >
	 *          The Up Glyph SHOULD not come without a Down and vice versa.
	 *       3: >
	 *          The Up Glyph is an action and SHOULD be listed in the action
	 *          column of a form.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Up'.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function up($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Down Glyph allows for manually arranging rows in tables embedded in forms.
	 *       It allows moving an item down.
	 *   composition: >
	 *       The Down Glyph uses the glyphicon-circle-arrow-down. The Down Glyph
	 *       can be combined with the Add/Remove Glyph.
	 *   effect: >
	 *       Clicking on the Down Glyph moves an item up.
	 *
	 * context: Moving answers up in Survey matrix questions.
	 *
	 * featurewiki:
	 *       - http://www.ilias.de/docu/goto_docu_wiki_wpage_813_1357.html
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The Down Glyph MUST NOT be used to sort tables. There is an
	 *          established sorting control for that.
	 *       2: >
	 *          The Down Glyph SHOULD not come without a Up and vice versa.
	 *       3: >
	 *          The Down Glyph is an action and SHOULD be listed in the action
	 *          column of a form.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Down'.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function down($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Back Glyph indicates a possible change of the view. The view change leads back to some previous view.
	 *   composition: >
	 *       The chevron-left glyphicon is used.
	 *   effect: >
	 *       The click on a Back Glyph leads back to a previous view.
	 *
	 * context: Show Member View in courses.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Back and Next Buttons MUST be accompanied by the respective Back/Next Glyph.
	 *   style:
	 *       1: >
	 *          If clicking on the Back/Next GLYPH opens a new view of an object, the Next Glyph MUST be used.
	 *       2: >
	 *          If clicking on the Back/Next GLYPH opens a previous view of an object, the Back Glyph MUST be used.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Back'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function back($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Next Glyph indicates a possible change of the view. The view change leads back to some previous view.
	 *   composition: >
	 *       The chevron-right glyphicon is used.
	 *   effect: >
	 *       The click on a Next Glyph opens a new view.
	 *
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Back and Next Buttons MUST be accompanied by the respective Back/Next Glyph.
	 *   style:
	 *       1: >
	 *          If clicking on the Back/Next GLYPH opens a new view of an object, the Next Glyph MUST be used.
	 *       2: >
	 *          If clicking on the Back/Next GLYPH opens a previous view of an object, the Back Glyph MUST be used.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Next'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function next($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Sorting Glyphs indicate the sorting direction of a column in a table as ascending (up) or descending (down).
	 *       It is a toggle reversing the ordering of a column.
	 *   composition: >
	 *       The Sort Ascending Glyph uses glyphicon-arrow-up.
	 *   effect: >
	 *       Clicking the Sort Ascending Glyph reverses the direction of ordering in a table.
	 *
	 * rules:
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Sort Ascending'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function sortAscending($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Sorting Glyphs indicate the sorting direction of a column in a table as ascending (up) or descending (down).
	 *       It is a toggle reversing the ordering of a column.
	 *   composition: >
	 *       The Sort Descending Glyph uses glyphicon-arrow-descending.
	 *   effect: >
	 *       Clicking the Sort Descending Glyph reverses the direction of ordering in a table.
	 *
	 * rules:
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Sort Descending'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function sortDescending($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The briefcase symbolize some ongoing work that is done. Momentarily in the background tasks.
	 *   composition: >
	 *       The briefcase Glyph uses glyphicon-briefcase.
	 *   effect: >
	 *       The click on the briefcase opens a popup to the background tasks.
	 *
	 * rules:
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Background Tasks'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function briefcase($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The User Glyph triggers the “Who is online?” Popover in the Top Navigation.
	 *       The User Glyph indicates the number of pending contact requests and users online via the the Novelty Counter and Status Counter respectively.
	 *   composition: >
	 *       The User Glyph uses the glyphicon-user.
	 *   effect: >
	 *       Clicking the User Glyph opens the “Who is online?” Popover.
	 *
	 *
	 * rules:
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Show who is online'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function user($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Mail Glyph provides a shortcut to the mail service. The Mail Glyph indicates the number of new mails received.
	 *   composition: >
	 *       The Mail Glyph uses the glyphicon-envelope.
	 *   effect: >
	 *       Upon clicking on the Mail Glyph the user is transferred to the full-screen mail service.
	 *   rivals:
	 *      Mail Icon: The Mail Icon is used to indicate the user is currently located in the Mail service The Mail Glyph acts as shortcut to the Mail service.
	 *
	 * rules:
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Mail'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function mail($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Notification Glyph allows users to activate / deactivate the notification service for a specific object or sub-item.
	 *       It is a toggle indicating by colour  whether it is activated or not.
	 *   composition: >
	 *       The Notification Glyph uses the glyphicon-bell in link-color if notifications are not active or brand-warning color if they are.
	 *   effect: >
	 *       Upon clicking the notification activation is toggled: Clicking the Notification Glyph activates respectively
	 *       deactivates the notification service for the current object or sub-item.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          The Notification Glyph MUST only be used in the Content Top Actions.
	 *   interaction:
	 *       1: >
	 *          Clicking the Notification Glyph MUST toggle the activation of Notifications.
	 *   style:
	 *       1: >
	 *          If notifications are activated the Notification Glyph MUST use the brand-warning color.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Notifications'.
	 * ---
	 * @param	string|null	$action
	 * @return 	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function notification($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Tag Glyph is used to indicate the possibility of adding tags to an object.
	 *   composition: >
	 *       The Tag Glyph uses the glyphicon-tag.
	 *   effect: >
	 *       Upon clicking the Round Trip Modal to add new Tags is opened.
	 *
	 * rules:
	 *   composition:
	 *       1: >
	 *          Novelty and Status Counter MUST show the amount of tags that has been given for an specific object.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Tags'.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function tag($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Note Glyph is used to indicate the possibilty of adding notes to an object.
	 *   composition: >
	 *       The Note Glyph uses the glyphicon-pushpin.
	 *   effect: >
	 *       Upon clicking the Round Trip Modal to add new notes is opened
	 *
	 * rules:
	 *   composition:
	 *       1: >
	 *          Novelty and Status Counter MUST show the amount of notes that has been given for an specific object.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Notes'.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function note($action = null);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Comment Glyph is used to indicate the possibilty of adding comments to an object.
	 *   composition: >
	 *       The Comment Glyph uses the glyphicon-comment.
	 *   effect: >
	 *       Upon clicking the Round Trip Modal to add new comments is opened.
	 *
	 * rules:
	 *   composition:
	 *       1: >
	 *          Novelty and Status Counter MUST show the amount of comments that has been given for an specific object.
	 *   accessibility:
	 *       1: >
	 *          The aria-label MUST be ‘Comments'.
	 * ---
	 * @param	string|null	$action
	 * @return	\ILIAS\UI\Component\Glyph\Glyph
	 */
	public function comment($action = null);
}
