<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * This is how a factory for Items looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       This is a standard item to be used in lists or similar contexts.
     *   composition: >
     *       A list item consists of a title and the following optional elements:
     *       description, action drop down, audio player element, properties (name/value),
     *       a text, image, icon or avatar lead, a progress meter chart and a color.
     *       Property values MAY be interactive by using a Shy Buttons or a Link.
     * rules:
     *    accessibility:
     *      1: >
     *       Information MUST NOT be provided by color alone. The same information could
     *       be presented, e.g. in a property to enable screen reader access.
     * ---
     * @param string|\ILIAS\UI\Component\Button\Shy|\ILIAS\UI\Component\Link\Link $title Title of the item
     * @return \ILIAS\UI\Component\Item\Standard
     */
    public function standard($title) : Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *     Shy Items are used to list more decent items which don't acquire much space.
     *   composition: >
     *     A Shy Item contains a title and optional a description, a close action, properties (name/value), an icon as a
     *     lead.
     * rules:
     *   interaction:
     *     1: >
     *        Clicking on the Close Button MUST remove the Shy Item permanently.
     *   accessibility:
     *     1: >
     *       All interactions offered by a Shy Item MUST be accessible by only using the keyboard.
     * ---
     *
     * @param string      $title
     * @return \ILIAS\UI\Component\Item\Shy
     */
    public function shy(string $title) : Shy;

    /**
     * ---
     * description:
     *   purpose: >
     *       An Item Group groups items of a certain type.
     *   composition: >
     *       An Item Group consists of a header with an optional action Dropdown and
     *       a list if Items.
     * ---
     * @param string $title Title of the group
     * @param \ILIAS\UI\Component\Item\Item[] $items items
     * @return \ILIAS\UI\Component\Item\Group
     */
    public function group(string $title, array $items) : Group;

    /**
     * ---
     * description:
     *   purpose: >
     *     Notifications in this context are messages from the system published to the user.
     *     Notification Items are used to bundle information (such as title and description)
     *     about such notifications and possible interactions with them
     *     (such as opening the mail folder containing a new mail).
     *   composition: >
     *     Notification Items always contain a title and an icon, which indicates
     *     the service or module triggering the notification. They also contain a close button.
     *     They might contain meta data such as various properties or a description
     *     and they further contain a set of interactions allowing the user to react
     *     in various ways. The first of those interaction is placed on the title
     *     of the Notification Item. Notification Items might also aggregate information about
     *     a set of related notifications and display them in the form of such an aggregate.
     *   effect: >
     *     The main interaction of the item is placed on the title and will be fired
     *     by clicking on the Notification Items title. If more than one is passed,
     *     they will be listed in a dropdown. The interaction fired by clicking
     *     on the Notification Item's title directs in most cases to
     *     some repository holding the entry which fired the notification.
     *     Clicking on the close button removes the Notification permanently.
     *     Exceptions are Notification Items displaying aggregated information.
     *     In such a case, clicking on the title displays the list of the Notifications
     *     being aggregated and it will only be closed if all Notifications being aggregated are closed.
     * rules:
     *   interaction:
     *     1: >
     *       The main interaction offered by clicking on the Notification Items
     *       title SHOULD open some repository holding the entry which fired
     *       the notification (e.g. Mailbox in case of new Mail).
     *     2: >
     *       Clicking on the title of a Notification Item displaying aggregated
     *       information of other Notification Items will open a Notification
     *       Slate displaying those Notification Items.
     *     3: >
     *        Clicking on the Close Button MUST remove the Notification Item
     *        permanently from the list of Notification Items.
     *     4: >
     *        If the Notification Item aggregates information on other Notification
     *        Items, closing all the aggregates MUST close the aggregating Notification Item as well.
     *   accessibility:
     *     1: >
     *       All interactions offered by a notification item MUST be accessible by only using the keyboard.
     *     2: >
     *       The purpose of each interaction MUST be clearly labeled by text.
     * ---
     * @param                                      $title
     * @param \ILIAS\UI\Component\Symbol\Icon\Icon $lead
     * @return \ILIAS\UI\Component\Item\Notification
     */
    public function notification($title, Icon $lead) : Notification;

    /**
     * ---
     * description:
     *   purpose: >
     *      The contextualisation of an item is prior to the content itself
     *   composition: >
     *      Communication items focus on a message to be communicated.
     *      For a message, its context is of great relevance.
     *      This context is given by information about the sender
     *      and the receiver(s) of the message, as well as the time
     *      at which the message was communicated.
     *      This is followed by the main message content.
     *      This can be supplemented by further information such as
     *      attachments, references, ratings or status informations.
     *   effect: >
     *      Interactions can be present in the contextual information,
     *      the content and metadata.
     * context:
     *     - Contexts can be single messages such as Mails, but also several related messages
     *      such as in forums.
     * rules:
     *   usage:
     *     1: Communincation Items SHOULD be used if it is a message.
     *   ordering:
     *     1: An lead icon or lead avatar can be used
     *     2: Title
     *     3: Context Informations
     *     4: Message content
     *     5: Metadata
     *   accessibility:
     *     1: All interactions offered by a notification item MUST be accessible
     *      by only using the keyboard.
     * ---
     * @param $title
     * @return \ILIAS\UI\Component\Item\Communication
     */
    public function communication($title) : Communication;
}
