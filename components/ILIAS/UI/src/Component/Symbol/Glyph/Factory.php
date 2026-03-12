<?php

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

declare(strict_types=1);

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
     *       The Settings Glyph symbolizes opening a dropdown that allows to edit settings of the displayed block.
     *   composition: >
     *       The Settings Glyph uses the glyphicon-cog.
     *   effect: >
     *      When placed in a Button or Link, clicking triggers the opening of a settings Dropdown.
     *
     * rules:
     *   usage:
     *       1: >
     *          The Settings Glyph MUST only be used in Blocks.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be “Settings”.
     * ---
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function settings(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Collapse Glyph symbolizes the collapsing of
     *       some neighbouring Container Collection, such as the content of a Dropdown or an Accordion currently shown.
     *   composition: >
     *       The Collapse Glyph is composed of a triangle pointing to the bottom indicating that content is currently shown.
     *   effect: >
     *      When placed in a Button or Link, clicking hides the display of some Container Collection.
     *   rivals:
     *      Expand Glyph: When placed in a Button or Link, the Expand Glyph triggers the display of some Container Collection.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function collapse(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Expand Glyph symbolizes the display of
     *       some neighbouring Container Collection, such as the content of a Dropdown or an Accordion currently shown.
     *   composition: >
     *       The Expand Glyph is composed of a triangle pointing to the right indicating that content is currently collapsed.
     *   effect: >
     *      When placed in a Button or Link, clicking displays some Container Collection.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function expand(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Add Glyph serves as a replacement for the respective textual
     *       button in very crowded screens. It allows adding a new item.
     *   composition: >
     *      The Add Glyph uses the glyphicon-plus-sign.
     *   effect: >
     *      When placed in a Button or Link, clicking adds a new input to a form or an event to the calendar.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function add(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Remove Glyph serves as a replacement for the respective textual
     *       button in very crowded screens. It allows removing an item.
     *   composition: >
     *       The Remove Glyph uses the glyphicon-minus-sign.
     *   effect: >
     *       When placed in a Button or Link, clicking deletes an existing input from a form.
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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function remove(): Glyph;

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
     *       When placed in a Button or Link, clicking moves an item up.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function up(): Glyph;

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
     *       When placed in a Button or Link, clicking moves an item down.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function down(): Glyph;

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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function back(): Glyph;

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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function next(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Sorting Glyphs indicate the current sorting direction of a column in a table as ascending (up) or descending (down).
     *       Only one Glyph is shown at a time. When placed in a Button or Link, clicking reverses the sorting direction.
     *   composition: >
     *       The Sort Ascending Glyph uses glyphicon-arrow-up.
     *   effect: >
     *       When placed in a Button or Link, clicking reverses the direction of ordering in a table.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Sort Ascending'.
     * ---
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function sortAscending(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Sorting Glyphs indicate the current sorting direction of a column in a table as ascending (up) or descending (down).
     *       Only one Glyph is shown at a time. When placed in a Button or Link, clicking reverses the sorting direction.
     *   composition: >
     *       The Sort Descending Glyph uses glyphicon-arrow-descending.
     *   effect: >
     *       When placed in a Button or Link, clicking reverses the direction of ordering in a table.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Sort Descending'.
     * ---
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function sortDescending(): Glyph;

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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function briefcase(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The User Glyph symbolizes the “Who is online?” Popover in the Top Navigation.
     *       The User Glyph indicates the number of pending contact requests and users online via the the Novelty Counter and Status Counter respectively.
     *   composition: >
     *       The User Glyph uses the glyphicon-user.
     *   effect: >
     *       When placed in a Button or Link, clicking opens the “Who is online?” Popover.
     *
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Show who is online'.
     * ---
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function user(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Mail Glyph provides a shortcut to the mail service. The Mail Glyph indicates the number of new mails received.
     *   composition: >
     *       The Mail Glyph uses the glyphicon-envelope.
     *   effect: >
     *       When placed in a Button or Link, clicking transfers the user to the full-screen mail service.
     *   rivals:
     *      Mail Icon: The Mail Icon is used to indicate the user is currently located in the Mail service. The Mail Glyph acts as shortcut to the Mail service.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Mail'.
     * ---
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function mail(): Glyph;

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
     * @return 	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function notification(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Tag Glyph is used to indicate the possibility of adding tags to an object.
     *   composition: >
     *       The Tag Glyph uses the glyphicon-tag.
     *   effect: >
     *       When placed in a Button or Link, clicking opens the Round Trip Modal to add new Tags.
     *
     * rules:
     *   composition:
     *       1: >
     *          Novelty and Status Counter MUST show the amount of tags that have been added to a specific object.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Tags'.
     * ---
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function tag(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Note Glyph is used to indicate the possibility of adding notes to an object.
     *   composition: >
     *       The Note Glyph uses the glyphicon-pushpin.
     *   effect: >
     *       When placed in a Button or Link, clicking opens the Round Trip Modal to add new notes.
     *
     * rules:
     *   composition:
     *       1: >
     *          Novelty and Status Counter MUST show the amount of notes that have been added to a specific object.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Notes'.
     * ---
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function note(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Comment Glyph is used to indicate the possibility of adding comments to an object.
     *   composition: >
     *       The Comment Glyph uses the glyphicon-comment.
     *   effect: >
     *       When placed in a Button or Link, clicking opens the Round Trip Modal to add new comments.
     *
     * rules:
     *   composition:
     *       1: >
     *          Novelty and Status Counter MUST show the amount of comments that have been added to a specific object.
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Comments'.
     * ---
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function comment(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Like Glyph symbolizes a user approving an item, e.g. a posting.
     *   composition: >
     *       The Like Glyph uses the "thumbs up" unicode emoji U+1F44D, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        When placed in a Button or Link, the Like Glyph acts as a toggle: A first click adds a Like to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Like away, which is also reflected in colour and counter.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function like(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Love Glyph symbolizes a user adoring an item, e.g. a posting.
     *   composition: >
     *       The Love Glyph uses the "red heart" unicode emoji U+2764, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        When placed in a Button or Link, the Love Glyph acts as a toggle: A first click adds a Love to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Love away, which is also reflected in colour and counter.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function love(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Dislike Glyph symbolizes a user disapproving an item, e.g. a posting.
     *   composition: >
     *       The Dislike Glyph uses the "thumbs down" unicode emoji U+1F44E, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        When placed in a Button or Link, the Dislike Glyph acts as a toggle: A first click adds a Dislike to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Dislike away, which is also reflected in colour and counter.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function dislike(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Laugh Glyph symbolizes a user finding an item hilarious, e.g. a posting.
     *   composition: >
     *       The Laugh Glyph uses the "grinning face with smiling eyes" unicode emoji U+1F604, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        When placed in a Button or Link, the Laugh Glyph acts as a toggle: A first click adds a Laugh to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Laugh away, which is also reflected in colour and counter.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function laugh(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Astounded Glyph symbolizes a user finding an item surprising, e.g. a posting.
     *   composition: >
     *       The Astounded Glyph uses the "face with open mouth" unicode emoji U+1F62E, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        When placed in a Button or Link, the Astounded Glyph acts as a toggle: A first click adds an Astounded to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Astounded away, which is also reflected in colour and counter.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function astounded(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Sad Glyph symbolizes a user finding an item disconcerting, e.g. a posting.
     *   composition: >
     *       The Sad Glyph uses the "sad but relieved face" unicode emoji U+1F625, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        When placed in a Button or Link, the Sad Glyph acts as a toggle: A first click adds a Sad to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Sad away, which is also reflected in colour and counter.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function sad(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Angry Glyph symbolizes a user finding an item outraging, e.g. a posting.
     *   composition: >
     *       The Angry Glyph uses the "angry face" unicode emoji U+1F620, see https://unicode.org/emoji/charts/full-emoji-list.html.
     *   effect: >
     *        When placed in a Button or Link, the Angry Glyph acts as a toggle: A first click adds an Angry to the respective item, which is reflected in the colour of the Glyph and in the counter. A second click takes the Angry away, which is also reflected in colour and counter.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function angry(): Glyph;

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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function eyeclosed(): Glyph;

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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function eyeopen(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Attachment Glyph indicates that a file is attached or can be attached to an object or entity.
     *   composition: >
     *      The Attachment Glyph uses the glyphicon-paperclip.
     *   effect: >
     *       When placed in a Button or Link, clicking executes an action which delivers these attachments to the actor OR initiates a process to add new attachments.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function attachment(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Reset Glyph is used to indicate the possibilty of resetting changes made by the user
     *      within a control back to a previous state.
     *   composition: >
     *      The Reset Glyph uses the glyphicon-repeat.
     *   effect: >
     *       When placed in a Button or Link, clicking reloads the related control immediately and goes back to state
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function reset(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Apply Glyph is used to indicate the possibilty of applying changes which the user has made
     *      within a control, i.e. a filter.
     *   composition: >
     *      The Apply Glyph uses the glyphicon-ok.
     *   effect: >
     *       When placed in a Button or Link, clicking reloads the page immediately with the updated content reflected in the control. In case of
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function apply(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Search Glyph is used to trigger a search dialog.
     *   composition: >
     *       The Search Glyph uses the glyphicon-search.
     *   effect: >
     *       When placed in a Button or Link, clicking opens a search dialog.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function search(): Glyph;

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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function help(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Calendar glyph is used to symbolize date-related actions or alerts.
     *   composition: >
     *      The Calendar Glyph uses the glyphicon-calendar.
     *   effect: >
     *       When placed in a Button or Link, clicking usually opens a date-picker.
     *
     * context:
     *    - Use in conjunction with Date-Inputs.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Calendar'.
     * ---
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function calendar(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Time Glyph is used to symbolize time-related actions or alerts.
     *   composition: >
     *      The Time Glyph uses the glyphicon-time.
     *   effect: >
     *       When placed in a Button or Link, clicking usually opens a time-picker.
     *
     * context:
     *    - Use in conjunction with Date-Inputs.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Time'.
     * ---
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function time(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Close Glyph is used to symbolize an action that closes something
     *      or leaves a previously initiated context.
     *   composition: >
     *      The Close Glyph uses the glyphicon-remove.
     *   effect: >
     *       When placed in a Button or Link, clicking closes an overlay or changes the view.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Close'.
     * ---
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function close(): Glyph;

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
     *       When placed in a Button or Link, clicking shows the rest of the set of entries.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function more(): Glyph;

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
     *       When placed in a Button or Link, clicking shows the entire set of entries.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function disclosure(): Glyph;

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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function language(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Login Glyph is used to trigger the login interaction.
     *       It is displayed in the Meta Bar of the user is not yet logged in.
     *   composition: >
     *       The Login Glyph uses the login glyph from the il-icons font.
     *   effect: >
     *       When placed in a Button or Link, clicking triggers the interaction to authenticate and login.
     *   rivals:
     *       Logout Glyph: The Logout Glyph symbolizes the logout interaction.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function login(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Logout Glyph is used to trigger the logout interaction.
     *       It is displayed in the Slate triggered by clicking on the User Avatar in the Meta Bar.
     *   composition: >
     *       The Logout Glyph uses the logout glyph from the il-icons font.
     *   effect: >
     *       When placed in a Button or Link, clicking triggers the interaction to logout.
     *   rivals:
     *       Login Glyph: The Login Glyph symbolizes the login interaction.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function logout(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Bullet List Glyph is used to indicate the possibility to group related content together
     *       and organize vertically, when you don’t need to convey a specific order for list items.
     *   composition: >
     *       The Bullet List Glyph uses the glyphicon-listbullet.
     *   effect: >
     *       When placed in a Button or Link, clicking groups a list of entries with bullet points.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function bulletlist(): Glyph;

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
     *       When placed in a Button or Link, clicking groups a list of entries with enumeration number.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function numberedlist(): Glyph;

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
     *       When placed in a Button or Link, clicking indents the content to the next subordinate level of the list.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function listindent(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Outdent Glyph is used to define the gradation of a structured list.
     *       It leads to a decreased indentation and thus gives the impression of a superordinate level.
     *   composition: >
     *       The Outdent List Glyph uses the glyphicon-listoutdent.
     *   effect: >
     *       When placed in a Button or Link, clicking outdents the content to the next superordinate level of the list.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function listoutdent(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *       The Filter Glyph is used to trigger a filter action.
     *   composition: >
     *       The Filter Glyph uses the glyphicon-filter.
     *   effect: >
     *       When placed in a Button or Link, clicking filters a list of entries.
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
     * @return	\ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function filter(): Glyph;

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
     *      When placed in a Button or Link, clicking hides the display of some Container Collection.
     *      It might simultaneously trigger the display of another Container Collection.
     *   rivals:
     *      Expand Glyph: When placed in a Button or Link, the Expand Glyph triggers the display of some Container Collection.
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
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function collapseHorizontal(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Heading Glyph indicates the intention of an action in an e.g. link or button, which
     *      transforms some text from or into a heading.
     *   composition: >
     *       The Heading Glyph is composed of the letter H.
     *   effect: >
     *      When placed in a Button or Link, clicking may insert or transform some text into a heading.
     *   rivals:
     *      Bold Glyph: should be used if the transformation should be bold.
     *      Italic Glyph: should be used if the transformation should be italic.
     *      Link Glyph: should be used if the transformation should be a link.
     *
     * context:
     *    -  Appears in the markdown-actions.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Insert Heading'.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function header(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Italic Glyph indicates the intention of an action in an e.g. link or button, which
     *      transforms some text from or into cursive one.
     *   composition: >
     *       The Italic Glyph is composed of the letter I.
     *   effect: >
     *      When placed in a Button or Link, clicking may insert or transform some text into cursive one.
     *   rivals:
     *      Bold Glyph: should be used if the transformation should be bold.
     *      Heading Glyph: should be used if the transformation should be a heading.
     *      Link Glyph: should be used if the transformation should be a link.
     *
     * context:
     *    -  Appears in the markdown-actions.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Insert Italic'.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function italic(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Bold Glyph indicates the intention of an action in an e.g. link or button, which
     *      transforms some text from or into bold one.
     *   composition: >
     *       The Bold Glyph is composed of the letter B.
     *   effect: >
     *      When placed in a Button or Link, clicking may insert or transform some text into bold one.
     *   rivals:
     *      Italic Glyph: should be used if the transformation should be italic.
     *      Heading Glyph: should be used if the transformation should be a heading.
     *      Link Glyph: should be used if the transformation should be a link.
     *
     * context:
     *    -  Appears in the markdown-actions.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Insert Bold'.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function bold(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Link Glyph indicates the intention of an action in an e.g. link or button, which
     *      transforms some text from or into a link.
     *   composition: >
     *       The Link Glyph is composed out of two linked chain-pieces that ilustrate the official
     *       URL symbol.
     *   effect: >
     *      When placed in a Button or Link, clicking may insert or transform some text into a link.
     *   rivals:
     *      Italic Glyph: should be used if the transformation should be italic.
     *      Heading Glyph: should be used if the transformation should be a heading.
     *      Bold Glyph: should be used if the transformation should be bold.
     *
     * context:
     *    -  Appears in the markdown-actions.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be ‘Insert Link'.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function link(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Launch Glyph indicates a process to start, e.g. subscribing to a
     *      Course or triggering a SCORM Module.
     *   composition: >
     *      The Launch Glyph uses the glyphicon plane.
     *   effect: >
     *      When placed in a Button or Link, clicking will immediately start or continue the process; this
     *      may manifest as a Modal to open or the redirection to the appropriate Page.
     * context:
     *    -  The Launch Glyph appears in the Launcher's Bulky Button.
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'launch'.
     *   usage:
     *       1: The LAUNCH Glyph MUST NOT be used for mere navigation; focus is on
     *         a process to start, which means altering a user's relation to some object.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function launch(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Enlarge glyph indicates the possibility of enlarging the content to see more details or to improve the display.
     *   composition: >
     *      The Enlarge Glyph uses the glyphicon-enlarge.
     *   effect: >
     *      When placed in a Button or Link, clicking triggers an interaction that displays an enlarged version of the content just seen.
     *      This can be a modal with an enlarged display of an image.
     *   rivals:
     *      Preview Glyph: >
     *         The Preview Glyph shows a preview and therefore only a section of the content.
     * context:
     *    -  The Enlarge Glyph appears in close proximity to images and graphics.
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Enlarge'.
     *   usage:
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function enlarge(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The List View Glyph displays data stacked on top of each other in a list.
     *      The glyph is suitable for views that are read from top to bottom and where the focus is on text.
     *   composition: >
     *      The List View Glyph uses the glyphicon-ListView.
     *   effect: >
     *      When placed in a Button or Link, clicking displays the collection of data as a list.
     *   rivals:
     *      TileView Glyph: >
     *         The Tile View Glyph will display data in a grid view.
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'List View'.
     *   usage:
     *       1: The List View Glyph SHOULD not come without a Tile View Glyph and vice versa.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function listView(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Preview Glyph indicates the possibility to display a preview or a short preview of a
     *      content before the user performs a final action.
     *   composition: >
     *      The Preview Glyph uses the glyphicon-preview.
     *   effect: >
     *      When a user clicks on the "Preview" icon, a preview of the content is displayed without a permanent
     *      change or a larger display. This can be a modal with several pages of a file preview.
     *   rivals:
     *      Enlarge Glyph: >
     *         The Enlarge Glyph shows more details or improve the display of an information.
     * context:
     *    -  The Preview Glyph appears when previewing documents, files or thumbnails.
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Preview'.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function preview(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Sort Glyph indicates the possibility of changing the order of elements within a list, table or other structured data.
     *   composition: >
     *      The Sort Glyph uses the glyphicon-sort.
     *   effect: >
     *      When a user clicks on the "Sort" icon, all possible sorting options are displayed.
     *      The elements will be reordered based on a specific criterion, such as alphabet, date or size.
     *      The order of the elements is thus adjusted.
     *
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Sort'.
     *   usage:
     *       1: The Sort Glyph SHOULD NOT be used to display the selected sort option.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function sort(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Column Selection Glyph shows the option of displaying or hiding columns in a table.
     *   composition: >
     *      The Column Selection Glyph uses the glyphicon-columnselection.
     *   effect: >
     *      If a user clicks on the Colum Selection symbol, an overview is displayed showing which columns are
     *      already visible and which are hidden.
     * context:
     *    -  The Column Selection Glyph appears in tables.
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Column Selection'.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function columnSelection(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Tile View Glyph displays data in cells arrayed in vertical and horizontal layouts.
     *      The glyph works well for collections that are read from side-to-side and where images are the main focus.
     *   composition: >
     *      The Tile View Glyph uses the glyphicon-TileView.
     *   effect: >
     *      When you click on the glyph, the displayed data is shown in a grid view.
     *   rivals:
     *      ListView Glyph: >
     *         The List View Glyph will display data in a list view.
     * context:
     *    -  The Tile View Glyph appears in combination with other display variants, e.g. List View.
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Grid View'.
     *   usage:
     *       1: The Tile View Glyph SHOULD not come without a ListView Glyph and vice versa.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function tileView(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Drag Handle Glyph indicates that an element can be dragged by clicking or tapping, then holding and
     *      moving the mouse or finger. When the hold is released, it's expected to drop the item at the nearest valid
     *      position.
     *      The glyph works best when there is a background or border indicating the dimension of the element that is
     *      draggable.
     *   composition: >
     *      The cells of the Ordering Table use this glyph.
     *   effect: >
     *      When you click and hold on the glyph, the item it is on can be dragged and dropped.
     *   rivals:
     *      No glyph: >
     *          In some instances the design and context of an element might already sufficiently indicate that it can
     *          be dragged. However, if an element could be confused with a non-draggable counterpart or is draggable
     *          only some of the time, you SHOULD use the glyph to indicate when it is draggable or otherwise change the
     *          appearance to communicate the drag and drop functionality.
     * context:
     *    -  The Drag Glyph communicates the drag and drop feature on the Ordering Table cells.
     * rules:
     *   accessibility:
     *       1: >
     *          The aria-label MUST be 'Draggable element'.
     *   usage:
     *       1: The Drag Glyph SHOULD be positioned near the corners of a draggable element.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function dragHandle(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Checked Glyph indicates a positive status (e.g. approved/complete/ok/yes/finished/passed)
     *   composition: >
     *      The Checked Glyph uses a checkmark.
     * context:
     *    - The Checked Glyph can be used in combination with the Unchecked Glyph to display binary states.
     * rules:
     *   accessibility:
     *      1: >
     *         The aria-label MUST be 'checked'.
     *   style:
     *      1: >
     *         The Checked Glyph SHOULD display a checkmark in the geometric focus of a mono-colored symmetric shape
     *   usage:
     *      1: >
     *         The Checked Glyph SHOULD be used to display a unary state or one option of a binary state.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function checked(): Glyph;

    /**
     * ---
     * description:
     *   purpose: >
     *      The Unchecked Glyph indicates a negative status (e.g. disapproved/blocked/no/failed/rejected)
     *   composition: >
     *      The Unchecked Glyph uses a diagonal cross.
     * context:
     *    - The Unchecked Glyph can be used in combination with the Checked Glyph to display binary states.
     * rules:
     *   accessibility:
     *      1: >
     *         The aria-label MUST be 'unchecked'.
     *   style:
     *      1: >
     *         The Unchecked Glyph SHOULD display a symmetric diagonal cross in the geometric focus of a mono-colored symmetric shape
     *   usage:
     *      1: >
     *         The Unchecked Glyph SHOULD be used to display a unary state or one option of a binary state.
     * ---
     * @return  \ILIAS\UI\Component\Symbol\Glyph\Glyph
     */
    public function unchecked(): Glyph;
}
