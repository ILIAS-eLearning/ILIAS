<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Component\Symbol\Glyph;

/**
 * This is how a factory for glyphs looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The Settings Glyph triggers the opening of a dropdown that allows to edit settings of the displayed block.
     *   composition: >
     *       The Settings Glyph uses the glyphicon-cog.
     *   effect: >
     *      Upon clicking, a settings Dropdown is opened.
     *
     * context:
     *    - In ILIAS <5.4, blocks on the Personal Desktop feature the Settings Glyph.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function settings(string $action = null): Glyph;

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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function collapse(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Expand Glyph is used to trigger the display of
     *       some neighbouring Container Collection such as a the content of a Dropdown or an Accordion currently shown.
     *   composition: >
     *       The Expand Glyph is composed of a triangle pointing to the right indicating that content is currently collapsed.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function expand(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Add Glyph serves as a replacement for the respective textual
     *       button in very crowded screens. It allows adding a new item.
     *   composition: >
     *      The Add Glyph uses the glyphicon-plus-sign.
     *   effect: >
     *      Clicking on the Add Glyph adds a new input to a form or an event to the calendar.
     *
     * context:
     *   - Adding answer options or taxonomies in questions-editing forms in tests.
     *   - Adding events to the calendar in Month view of the agenda.
     *
     * rules:
     *   usage:
     *       1: >
     *          The Add Glyph SHOULD not come without a corresponding Remove Glyph and vice versa.
     *          Exceptions to this rule,
     *          such as the Calendar (where only elements can be added via Add Glyph, but not removed) are possible, but HAVE TO be run through the Jour Fixe.
     *       2: >
     *          The Add Glyph stands for an Action and SHOULD be placed in the
     *          action column of a form.
     *       3: The Add Glyph MUST NOT be used to add lines to tables.
     *   interaction:
     *       1: Newly added items MUST be placed below the line in which the Add Glyph has been clicked
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Add'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function add(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Remove Glyph serves as a replacement for the respective textual
     *       button in very crowded screens. It allows removing an item.
     *   composition: >
     *       The Remove Glyph uses the glyphicon-minus-sign.
     *   effect: >
     *       Clicking on the Remove Glyph deletes an existing input from a form.
     *
     * context:
     *   - Removing answer options or taxonomies in questions-editing forms in tests.
     *   - Removing user notifications in a calendar item.
     *
     * rules:
     *   usage:
     *       1: >
     *          The Remove Glyph SHOULD not come without a corresponding Add Glyph and vice versa.
     *          Exceptions to this rule,
     *          such as the Calendar (where only elements can be added via Add Glyph, but not removed) are possible, but HAVE TO be run through the Jour Fixe.
     *       2: >
     *          The Remove Glyph stands for an Action and SHOULD be placed in the
     *          action column of a form.
     *       3: The Remove Glyph MUST NOT be used to add lines to tables.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Remove'.
     * ---
     * @param	string|null	$action
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function remove(string $action = null): Glyph;

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
     * context:
     *   - Moving answers up in Survey matrix questions.
     *
     * featurewiki:
     *   - http://www.ilias.de/docu/goto_docu_wiki_wpage_813_1357.html
     *
     * rules:
     *   usage:
     *       1: >
     *          The Up Glyph MUST NOT be used to sort tables. There is an
     *          established sorting control for that.
     *       2: >
     *          The Up Glyph SHOULD not come without a Down Glyph and vice versa.
     *       3: >
     *          The Up Glyph is an action and SHOULD be listed in the action
     *          column of a form.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Up'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function up(string $action = null): Glyph;

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
     *       Clicking on the Down Glyph moves an item down.
     *
     * context:
     *   - Moving answers up in Survey matrix questions.
     *
     * featurewiki:
     *   - http://www.ilias.de/docu/goto_docu_wiki_wpage_813_1357.html
     *
     * rules:
     *   usage:
     *       1: >
     *          The Down Glyph MUST NOT be used to sort tables. There is an
     *          established sorting control for that.
     *       2: >
     *          The Down Glyph SHOULD not come without an Up Glyph and vice versa.
     *       3: >
     *          The Down Glyph is an action and SHOULD be listed in the action
     *          column of a form.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Down'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function down(string $action = null): Glyph;

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
     * context:
     *   - Exit Member View in courses.
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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function back(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Next Glyph indicates a possible change of the view. The view change leads back to some previous view.
     *   composition: >
     *       The chevron-right glyphicon is used.
     *   effect: >
     *       The click on a Next Glyph opens a new view.
     * context:
     *   - Enter Member View in a course tab bar.
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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function next(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Sorting Glyphs indicate the current sorting direction of a column in a table as ascending (up) or descending (down).
     *       Only one Glyph is shown at a time. Clicking on the glyph will reverse the sorting direction.
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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function sortAscending(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Sorting Glyphs indicate the current sorting direction of a column in a table as ascending (up) or descending (down).
     *       Only one Glyph is shown at a time. Clicking on the glyph will reverse the sorting direction.
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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function sortDescending(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Briefcase Glyph symbolizes some ongoing work that is done. It was introduced for the background tasks.
     *   composition: >
     *       The Briefcase Glyph uses glyphicon-briefcase.
     *   effect: >
     *       A click on the Briefcase Glyph opens a popup that shows the background tasks.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Background Tasks'.
     * ---
     * @param	string|null	$action
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function briefcase(string $action = null): Glyph;

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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function user(string $action = null): Glyph;

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
     *      Mail Icon: The Mail Icon is used to indicate the user is currently located in the Mail service. The Mail Glyph acts as shortcut to the Mail service.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Mail'.
     * ---
     * @param	string|null	$action
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function mail(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Notification Glyph indicates and controls functionality that allows the system to send notifications
     *       to the user, such as the notification center in the Meta Bar or the notification service at individual
     *       objects.
     *   composition: >
     *       If used to toggle the notifications at an individual object, the Notification Glyph uses link-color to
     *       indicate inactivity and the brand-warning color to indicate activity.
     *
     * rules:
     *   accessibility:
     *       2: >
     *          The aria-label MUST be "Notifications".
     * ---
     * @param	string|null	$action
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function notification(string $action = null): Glyph;

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
     *          Novelty and Status Counter MUST show the amount of tags that have been added to a specific object.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Tags'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function tag(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Note Glyph is used to indicate the possibility of adding notes to an object.
     *   composition: >
     *       The Note Glyph uses the glyphicon-pushpin.
     *   effect: >
     *       Upon clicking the Round Trip Modal to add new notes is opened
     *
     * rules:
     *   composition:
     *       1: >
     *          Novelty and Status Counter MUST show the amount of notes that have been added to a specific object.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Notes'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function note(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Comment Glyph is used to indicate the possibility of adding comments to an object.
     *   composition: >
     *       The Comment Glyph uses the glyphicon-comment.
     *   effect: >
     *       Upon clicking the Round Trip Modal to add new comments is opened.
     *
     * rules:
     *   composition:
     *       1: >
     *          Novelty and Status Counter MUST show the amount of comments that have been added to a specific object.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Comments'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function comment(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       Clicking the Like Glyph indicates a user approves an item, e.g. a posting.
     *   composition: >
     *       The Like Glyph uses the "thumbs up" unicode emoji U+1F44D, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        Upon clicking, the Like Glyph acts as a toggle: A first click adds a Like to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Like away, which is also reflected in colour and counter.
     *
     * context:
     *       - Show timeline in groups and courses.
     *
     * rules:
     *   composition:
     *       1: >
     *          A Status Counter MUST indicate the overall amount of like expressions.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Like'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function like(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       Clicking the Love Glyph indicates a user adores an item, e.g. a posting.
     *   composition: >
     *       The Love Glyph uses the "red heart" unicode emoji U+2764, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        Upon clicking, the Love Glyph acts as a toggle: A first click adds a Love to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Love away, which is also reflected in colour and counter.
     *
     * context:
     *       - Show timeline in groups and courses.
     *
     * rules:
     *   composition:
     *       1: >
     *          A Status Counter MUST indicate the overall amount of love expressions.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Love'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function love(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       Clicking the Dislike Glyph indicates a user disapproves an item, e.g. a posting.
     *   composition: >
     *       The Dislike Glyph uses the "thumbs down" unicode emoji U+1F44E, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        Upon clicking, the Dislike Glyph acts as a toggle: A first click adds a Dislike to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Dislike away, which is also reflected in colour and counter.
     *
     * context:
     *       - Show timeline in groups and courses.
     *
     * rules:
     *   composition:
     *       1: >
     *          A Status Counter MUST indicate the overall amount of dislike expressions.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Dislike'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function dislike(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       Clicking the Laugh Glyph indicates a user finds an item hilarious, e.g. a posting.
     *   composition: >
     *       The Laugh Glyph uses the "grinning face with smiling eyes" unicode emoji U+1F604, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        Upon clicking, the Laugh Glyph acts as a toggle: A first click adds a Laugh to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Laugh away, which is also reflected in colour and counter.
     *
     * context:
     *       - Show timeline in groups and courses.
     *
     * rules:
     *   composition:
     *       1: >
     *          A Status Counter MUST indicate the overall amount of laugh expressions.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Laugh'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function laugh(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       Clicking the Astounded Glyph indicates a user finds an item surprising, e.g. a posting.
     *   composition: >
     *       The Astounded Glyph uses the "face with open mouth" unicode emoji U+1F62E, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        Upon clicking, the Astounded Glyph acts as a toggle: A first click adds an Astounded to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Astounded away, which is also reflected in colour and counter.
     *
     * context:
     *       - Show timeline in groups and courses.
     *
     * rules:
     *   composition:
     *       1: >
     *          A Status Counter MUST indicate the overall amount of astounded expressions.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Astounded'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function astounded(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       Clicking the Sad Glyph indicates a user finds an item disconcerting, e.g. a posting.
     *   composition: >
     *       The Sad Glyph uses the "sad but relieved face" unicode emoji U+1F625, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        Upon clicking, the Sad Glyph acts as a toggle: A first click adds a Sad to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Sad away, which is also reflected in colour and counter.
     *
     * context:
     *       - Show timeline in groups and courses.
     *
     * rules:
     *   composition:
     *       1: >
     *          A Status Counter MUST indicate the overall amount of sad expressions.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Sad'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function sad(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       Clicking the Angry Glyph indicates a user finds an item outraging, e.g. a posting.
     *   composition: >
     *       The Angry Glyph uses the "angry face" unicode emoji U+1F620, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        Upon clicking, the Angry Glyph acts as a toggle: A first click adds an Angry to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Angry away, which is also reflected in colour and counter.
     *
     * context:
     *       - Show timeline in groups and courses.
     *
     * rules:
     *   composition:
     *       1: >
     *          A Status Counter MUST indicate the overall amount of angry expressions.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Angry'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function angry(string $action = null): Glyph;


    /**
     * ---
     * description:
     *   purpose: >
     *       The Eye Closed Glyph is used to toggle the revelation-mode of password fields.
     *       With the Eye Closed Glyph shown, the field is currently unmasked.
     *   composition: >
     *       The Eye Closed Glyph uses the glyphicon-eye-close.
     *   effect: >
     *       When clicked, the password-field is masked, thus hiding the input.
     *
     * context:
     *       - Used with password-fields to toggle mask/revealed mode.
     *
     * rules:
     *   composition:
     *       1: >
     *          The Eye Closed Glyph MUST only be used with Password-Inputs.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be "Eye Closed - Click to hide the input's contents".
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function eyeclosed(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Eye Open Glyph is used to toggle the revelation-mode of password fields.
     *       With the Eye Open Glyph shown, the field is currently masked.
     *   composition: >
     *       The Eye Open Glyph uses the glyphicon-eye-open.
     *   effect: >
     *       When clicked, the password-field is unmasked, thus revealing the input.
     *
     * context:
     *       - Used with password-fields to toggle mask/revealed mode.
     *
     * rules:
     *   composition:
     *       1: >
     *          The Eye Open Glyph MUST only be used with Password-Inputs.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be "Eye Opened - Click to reveal the input's contents".
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function eyeopen(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Attachment Glyph indicates that a file is attached or can be attached to an object or entity.
     *   composition: >
     *      The Attachment Glyph uses the glyphicon-paperclip.
     *   effect: >
     *       Clicking executes an action which delivers these attachments to the actor OR initiates a process to add new attachments.
     * context:
     *       - Indicate whether or not files have been attached to emails in the folder view of Mail.
     * rules:
     *   composition:
     *       1: >
     *          A Status Counter MAY indicate the overall amount of attachments.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Attachment'.
     * ---
     * @param string|null $action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function attachment(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Reset Glyph is used to indicate the possibilty of resetting changes made by the user
     *      within a control back to a previous state.
     *   composition: >
     *      The Reset Glyph uses the glyphicon-repeat.
     *   effect: >
     *       Upon clicking, the related control is reloaded immediately and goes back to state
     *       before the user changes.
     *
     * featurewiki:
     *       - https://www.ilias.de/docu/goto.php?target=wiki_1357_Responsive_Table_Filters#ilPageTocA121
     *
     * rules:
     *   usage:
     *       1: >
     *          The Reset Glyph SHOULD not come without an Apply Glyph and vice versa.
     *       2: >
     *          If there are no changes to reset, the Reset Glyph MUST be deactivated (or not be clickable).
     *   style:
     *       1: >
     *          The deactivated state of the Reset Glyph MUST be visually noticeable for the user, i.e. by
     *          greying out the Reset Glyph.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Reset'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function reset(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Apply Glyph is used to indicate the possibilty of applying changes which the user has made
     *      within a control, i.e. a filter.
     *   composition: >
     *      The Apply Glyph uses the glyphicon-ok.
     *   effect: >
     *       Upon clicking, the page is reloaded immediately with the updated content reflected in the control. In case of
     *       a filter, it means that the entries in a table change in accordance with the filter values set by the user.
     *
     * featurewiki:
     *       - https://www.ilias.de/docu/goto.php?target=wiki_1357_Responsive_Table_Filters#ilPageTocA121
     *
     * rules:
     *   usage:
     *       1: >
     *          The Apply Glyph SHOULD not come without a Reset Glyph and vice versa.
     *       2: >
     *          If there are no changes to apply, the Apply Glyph MUST be deactivated (or not be clickable).
     *   style:
     *       1: >
     *          The deactivated state of the Apply Glyph MUST be visually noticeable for the user, i.e. by greying out
     *          the Apply Glyph.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Apply'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function apply(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Search Glyph is used to trigger a search dialog.
     *   composition: >
     *       The Search Glyph uses the glyphicon-search.
     *   effect: >
     *       Clicking this glyph will open a search dialog.
     *       Since the context for the Search Glyph primarily is the Meta Bar,
     *       the according search dialog will be opened as Tool in the Main Bar.
     *
     * context:
     *    - The Search Glyph appears in the Meta Bar.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Search'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function search(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Help Glyph opens a context-sensitive help screen.
     *   composition: >
     *       The Help Glyph uses the glyphicon-question-sign.
     *   effect: >
     *       When clicked, the user is provided with explanations or
     *       instructions for the usage of the current context.
     *       When used in the Meta Bar, the help is displayed as tool in the
     *       Sidebar.
     *
     * context:
     *    - The Help Glyph appears in the Meta Bar.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Help'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function help(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Calendar glyph is used to symbolize date-related actions or alerts.
     *   composition: >
     *      The Calendar Glyph uses the glyphicon-calendar.
     *   effect: >
     *       Clicking the calendar Glyph will usually open a date-picker.
     *
     * context:
     *    - Use in conjunction with Date-Inputs.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Calendar'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function calendar(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Time Glyph is used to symbolize time-related actions or alerts.
     *   composition: >
     *      The Time Glyph uses the glyphicon-time.
     *   effect: >
     *       Clicking the Time Glyph will usually open a time-picker.
     *
     * context:
     *    - Use in conjunction with Date-Inputs.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Time'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function time(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Close Glyph is used to symbolize an action that closes something
     *      or leaves a previously initiated context.
     *   composition: >
     *      The Close Glyph uses the glyphicon-remove.
     *   effect: >
     *       Clicking the Close Glyph will close an overlay or change the view.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Close'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function close(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The More Glyph allows shortening a part of a set of entries that
     *      are too long to be presented fully or would be overwhelming.
     *      The More glyph offers viewing the rest of the shortened set of
     *      entries so that the entire set becomes visible.
     *   composition: >
     *      The More Glyph uses the glyphicon-option-horizontal.
     *   effect: >
     *       Clicking the More Glyph shows the rest of the set of entries.
     *   rivals:
     *      Disclosure Glyph: >
     *         The Disclosure Glyph hides the complete set of entries, wherear the
     *         More Glyph only hides parts of it.
     *      Mini Action Dropdown: >
     *         The Dropdown in the ListGUI without text is used to offer a
     *         set of actions that cannot be displayed directly due to scarce space.
     *         This is different because the set of entries of the More Glyph does not entail actions.
     *      Show More Less Button: >
     *         The Show-More /Show Less Button in Timeline unhides
     *         a full individual entry of a timeline. Entries are caped at a certain
     *         length and Show-More-Buttons allow viewing all the content of this entry.
     *         This is different, because the unhidden entirety is an individual entry
     *         and not a set of entries. The Show-More /Show Less Button in filtered Categories with loads of
     *         objects shows the next x objects in the list GUI. This is different,
     *         because what is shown is not an entirety but a part of an entirety.
     *      The Hamburg Glyph: >
     *         The Hamburg Glyph is an icon introduced on the web,
     *         which in most cases represents a complete main menu. This is different
     *         from More Glyph, which abbreviates part of the menu. The hamburger
     *         icon currently used in the shortened toolbar (on small screens) should
     *         actually be replaced because it doesn't show the entire main menu, but
     *         more actions are displayed when you click on it.
     *
     * context:
     *    - This Glyph is currently used in the responsive view of the Main Bar.
     *
     * rules:
     *   usage:
     *       1: >
     *          The usage of this Glyph SHOULD be avoided if possible.
     *          Invisible components reduce the affordance of a screen.
     *   style:
     *       1: >
     *          Because it has a certain similarity to the Disclose Glyph, it
     *          SHOULD also have a visual similarity, which can be distinguished
     *          from the Disclose Glyph.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Show More'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function more(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Disclose Glyph allows hiding a complete set of entries that
     *      are too long to be presented fully or would be overwhelming.
     *      The Disclosure Glyphs offers viewing the entirety of the hidden set of entries.
     *   composition: >
     *      The Disclosure Glyph uses the glyphicon-option-vertical.
     *   effect: >
     *       Clicking the Disclose Glyph shows the entire set of entries.
     *   rivals:
     *      More Glyph: >
     *         The More Glyph hides part of the set of entries.
     *         This is a difference to the Disclose Glyph, because here the
     *         complete set of entries is collected in a glyph.
     *      Mini Action Dropdown: >
     *         The Dropdown in the ListGUI without text is used to offer a
     *         set of actions that cannot be displayed directly due to scarce space.
     *         This is different because the set of entries of the More Glyph does not entail actions.
     *      Show More Less Button: >
     *         The Show-More /Show Less Button in Timeline unhides
     *         a full individual entry of a timeline. Entries are caped at a certain
     *         length and Show-More-Buttons allow viewing all the content of this entry.
     *         This is different, because the unhidden entirety is an individual entry
     *         and not a set of entries. The Show-More /Show Less Button in filtered Categories with loads of
     *         objects shows the next x objects in the list GUI. This is different,
     *         because what is shown is not an entirety but a part of an entirety.
     *      The Hamburg Glyph: >
     *         The Hamburg Glyph is an icon introduced on the web,
     *         which in most cases represents a complete main menu. This is different
     *         from More Glyph, which abbreviates part of the menu. The hamburger
     *         icon currently used in the shortened toolbar (on small screens) should
     *         actually be replaced because it doesn't show the entire main menu, but
     *         more actions are displayed when you click on it.
     *
     * context:
     *    - This Glyph is currently used in the responsive view of the Meta Bar.
     * rules:
     *   usage:
     *       1: >
     *          The usage of this Glyph SHOULD be avoided if possible.
     *          Invisible components reduce the affordance of a screen.
     *   style:
     *       1: >
     *          Because it has a certain similarity to the More Glyph, it SHOULD
     *          also have a visual similarity, which can be distinguished from the More Glyph.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be „Disclose“.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function disclosure(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Language Glyph is used to indicate the option to switch languages
     *       by some shorthand workflow without navigating to the personal settings.
     *   composition: >
     *       The Language Glyph uses the glyphicon-lang from the il-icons set.
     *   effect: >
     *       When clicked, the user is shown a set of active languages to choose from.
     *   rivals:
     *      Standard Icon: >
     *         The Standard Icon-Set features the Language Icon, which symbolizes
     *         the Service "Language". It is not used in the Meta Bar as trigger
     *         for switching languages, but to visually identify the language as
     *         service (e.g. in the administration).
     *
     *
     * context:
     *    - The Language Glyph appears in the Meta Bar.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Switch Language'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function language(?string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Login Glyph is used to trigger the login interaction.
     *       It is displayed in the Meta Bar of the user is not yet logged in.
     *   composition: >
     *       The Login Glyph uses the login glyph from the il-icons font.
     *   effect: >
     *       Clicking this Glyph will trigger the interaction to authenticate and login.
     *   rivals:
     *       Logout Glyph: The Logout Glyph triggers the logout interaction.
     *
     * context:
     *    - The Login Glyph appears in the Meta Bar.
     *
     * rules:
     *   usage:
     *       1: The Login Glyph MUST be displayed if no user is authenticated.
     *   style:
     *       1: The Login Glyph MUST be placed on the very top right.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Login'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function login(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Logout Glyph is used to trigger the logout interaction.
     *       It is displayed in the Slate triggered by clicking on the User Avatar in the Meta Bar.
     *   composition: >
     *       The Logout Glyph uses the logout glyph from the il-icons font.
     *   effect: >
     *       Clicking this Glyph will trigger the interaction to logout.
     *   rivals:
     *       Login Glyph: The Login Glyph triggers the login interaction.
     *
     * context:
     *    - The Logout Glyph appears in the Slate triggered by clicking on the User Avatar in the Meta Bar.
     *
     * rules:
     *   usage:
     *       1: The Logout Glyph MUST be displayed if the user is logged in.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Logout'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function logout(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Bullet List Glyph is used to indicate the possibility to group related content together
     *       and organize vertically, when you don’t need to convey a specific order for list items.
     *   composition: >
     *       The Bullet List Glyph uses the glyphicon-listbullet.
     *   effect: >
     *       Clicking this glyph will group a list of entries with bullet points.
     *   rivals:
     *       Numbered List Glyph: The Numbered Glyph will group a list of entries with enumeration number.
     *
     * context:
     *    - The Bullet List Glyph appears in the ILIAS Page Editor.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Bullet Point List'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function bulletlist(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Numbered List Glyph is used to indicate the possibility to group related content together
     *       and organize vertically, where you need to convey a priority, hierarchy,
     *       or sequence between list items.
     *   composition: >
     *       The Numbered List Glyph uses the glyphicon-listnumbered.
     *   effect: >
     *       Clicking this glyph will group a list of entries with enumeration number.
     *   rivals:
     *       Bullet List Glyph: The Bullet Glyph will group a list of entries with bullet points.
     *
     * context:
     *    - The Numbered List Glyph appears in the ILIAS Page Editor.
     *
     * rules:
     *   usage:
     *       1: The Logout Glyph MUST be displayed if the user is logged in.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Numbered List'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function numberedlist(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Indent Glyph is used to define the gradation of a structured list.
     *       It leads to an increased indentation and thus gives the impression of a
     *       subordinate level.
     *   composition: >
     *       The Indent List Glyph uses the glyphicon-listindent.
     *   effect: >
     *       Clicking this glyph will intend the content to the next subordinate level of the list.
     *   rivals:
     *       Outdent Glyph: The Outend Glyph will reduce the indent to the next superordinate level of the list.
     *
     * context:
     *    - The Indent Glyph appears in the ILIAS Page Editor.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Increase Indent'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function listindent(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Outdent Glyph is used to define the gradation of a structured list.
     *       It leads to a decreased indentation and thus gives the impression of a superordinate level.
     *   composition: >
     *       The Outdent List Glyph uses the glyphicon-listoutdent.
     *   effect: >
     *       Clicking this glyph will outdent the content to the next superordinate level of the list.
     *   rivals:
     *       Indent Glyph: The Indent Glyph will increase the indentation to the next subordinate level of the list.
     *
     * context:
     *    -  The Outdent List Glyph appears in the ILIAS Page Editor.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Decrease Indent'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function listoutdent(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Filter Glyph is used to trigger a filter action.
     *   composition: >
     *       The Filter Glyph uses the glyphicon-filter.
     *   effect: >
     *       Clicking this glyph will filter a list of entries.
     *   rivals:
     *       Search Glyph: The Search Glyph will open a search dialog  or will generate a list of entries according to the search input.
     *
     * context:
     *    -  The Filter Glyph appears in the Who-is-online-Tool.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Filter'.
     * ---
     * @param	string|null	$action
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function filter(string $action = null): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Collapse Horizontal Glyph is used to trigger the collapsing of
     *       some neighbouring Container Collection (such as a Slate) or to navigate
     *       within a menu where collapsing might mean "switching to a higher level".
     *       The Collapse Horizontal Glyph is used where collapsing is better
     *       indicated by a left-triangle than by a down-triangle.
     *   composition: >
     *       The Collapse Horizontal Glyph is composed of a triangle pointing to the left.
     *   effect: >
     *      Clicking the Collapse Horizontal Glyph hides the display of some Container Collection.
     *      It might simultaneously trigger the display of another Container Collection.
     *   rivals:
     *      Expand Glyph: The Expand Glyphs triggers the display of some Container Collection.
     *      Collapse Glyph: The Collapse Glyph strongly indicates a Container positioned below.
     *      Previous Glyph: The Previous/Next Glyph opens a completely new view. It serves a navigational purpose.
     *
     * context:
     *    -  The Collapse Horizontal Glyph appears in the Drilldown Menu.
     *    -  The Collapse Horizontal Glyph appears in Main Bar to hide Slates.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘collapse/back'.
     * ---
     * @param   string|null $action
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function collapseHorizontal(string $action = null): Glyph;
}
