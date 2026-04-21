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

namespace ILIAS\UI\Component\Listing;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * This is how a factory for listings looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     Unordered Lists are used to display a unordered set of textual elements.
     *   composition: >
     *     Unordered Lists are composed of a set of bullets labeling the listed items.
     * ----
     * @param array $items Set of elements to be rendered (string | Component)[]
     * @return  \ILIAS\UI\Component\Listing\Unordered
     */
    public function unordered(array $items): Unordered;

    /**
     * ---
     * description:
     *   purpose: >
     *     Ordered Lists are used to displayed a numbered set of textual elements. They are used if the order of the
     *     elements is relevant.
     *   composition: >
     *     Ordered Lists are composed of a set of numbers labeling the items enumerated.
     * ----
     * @param array $items Set of elements to be rendered (string | Component)[]
     * @return  \ILIAS\UI\Component\Listing\Ordered
     */
    public function ordered(array $items): Ordered;

    /**
     * ---
     * description:
     *   purpose: >
     *     Inline Lists are used to display a set of elements next to each other when the available
     *     space allows for it. The elements belong to a group of similar items and have about equal
     *     relevance.
     *   composition: >
     *     Inline Lists string up the items horizontally breaking into the next line if necessary.
     *     They are separated by a comma.
     *   rivals:
     *     Unordered List, Ordered Listing: >
     *       If there is enough space for a vertical list, Unordered and Ordered Listing should
     *       be preferred. Line by line items are better suited when the user is expected to be
     *       exploring or engaging with the list for longer than a casual glance.
     *     Property Listing: >
     *       To display key-value pairs in a row, use the Property Listing.
     *
     * context:
     *   - Inline Listings can be used as values in a Property Listing.
     *
     * rules:
     *   usage:
     *     - You MUST use the Inline Listing only when another component around it gives it a
     *       context or headline clarifying what is being listed.
     *     - You MUST only add items belonging to the same group or type.
     *     - The Inline Listing MAY be the value of a property listing item.
     *     - You MUST NOT use this component as a layout tool to force unrelated components next
     *       to each other.
     *     - You MAY change the comma delimiter in your component using CSS.
     * ----
     * @param array<Component\Component|string> $items
     * @return  \ILIAS\UI\Component\Listing\Inline
     */
    public function inline(array $items): Inline;

    /**
     * ---
     * description:
     *   purpose: >
     *     Descriptive Lists are used to display key-value doubles of textual-information.
     *   composition: >
     *     Descriptive Lists are composed of a key acting as title describing the type of
     *     information being displayed underneath.
     *   rivals:
     *      Property Listings: >
     *        In Property Listings, the (visual) focus is on values rather than labels;
     *        labels can also be omitted.
     *        All properties are displayed in one line.
     * ----
     * @param array $items string (key) => (string | Component)[] (value)
     * @return  \ILIAS\UI\Component\Listing\Descriptive
     */
    public function descriptive(array $items): Descriptive;

    /**
     * ---
     * description:
     *   purpose: >
     *      A workflow presents a list of steps that the user should tackle
     *      in a defined order.
     *   composition: >
     *     The workflow has a title and a list of workflow steps.
     *   effect: >
     *     Steps in a workflow reflect their progress (not applicable, not started, in progress, completed).
     *     The currently active step is marked as such.
     *     Clicking the step of a workflow MAY trigger navigation.
     *   rivals:
     *      OrderedListing: >
     *        Items (Steps) in a workflow relate to some task;
     *        they reflect the tasks's progress and may be used to navigate to
     *        respective views.
     *
     * ----
     * @return  \ILIAS\UI\Component\Listing\Workflow\Factory
     */
    public function workflow(): Workflow\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *     Characteristic Value Listings are used to present characteristic values. A characteristic value
     *     is understood here as a value to quantify or describe a state indicated by some key.
     *   composition: >
     *     Characteristic Value Listings are composed of items containing a key labeling the value
     *     being displayed side by side.
     *   rivals:
     *      DescriptiveListing: >
     *        The items for a descriptive listing consists of a key as a title
     *        and a value describing the key.
     * ----
     * @return \ILIAS\UI\Component\Listing\CharacteristicValue\Factory
     */
    public function characteristicValue(): CharacteristicValue\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *     The Entity Listing yields Entities according to a consumer defined concept
     *     and lists them one after the other.
     *     Striking the right balance between providing sufficient information and
     *     avoiding information overload is important for interfaces where we cannot
     *     rely on homogenous mental models and clear user intent - due to of the
     *     huge variety of Entities and user roles/intents.
     *     Consequently, Entities (and their listings) strive to visually reduce/structure
     *     the amount of shown properties without cutting out important information.
     *   composition: >
     *     The Entity Listing will provide Entities.
     *   rivals:
     *     DataTable: >
     *       All fields in a DataTable are displayed with rather equal emphasis;
     *       The semantic groups in Entities structure and focus information.
     *       The purpose of Entity Listings is rather to identify one Entity
     *       instead of comparing or focussing certain attributes.
     *       Data Tables are better suited for administrative user intents.
     *     PresentationTable: >
     *       While both the Entity Listing and the Presentation Table share
     *       an explorative character, the Presentation Table might still list
     *       all kinds of aggregated data; Entity Listings provide solely Entities.
     *       Also, Presentation Table will not display all information at once,
     *       so the Entity Listing will widen the range of anticipated user intents.
     *
     * background: >
     *  ../../docu/UI-Repository-Item_proposal.md,
     *  ../../docu/ux-guide-repository-objects-properties-and-actions.md
     *
     * ----
     * @return \ILIAS\UI\Component\Listing\Entity\Factory
     */
    public function entity(): Entity\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *     Property Listings will list characteristic, labeled values in a space
     *     saving manner. Property listing is ideal when there are many values
     *     of reasonable, but not specific or primarily relevant, importance.
     *   composition: >
     *     Entries are listed as label/value pair in one line.
     *     Since the focus is strongly on the value, which might be
     *     self-explaining, visibility of the label is optional.
     *     The label is a string. A Symbol may be shown in its place.
     *     The value is a string, Links or Legacy Components.
     *     A Symbol may be shown as the value.
     *     Very long value strings will turn into a truncated paragraph
     *     with a clickable Show more/less toggle.
     *   rivals:
     *      Characteristic Value: >
     *        In Characteristic Values, label/value pairs are displayed in a
     *        tabular way; labels cannot be omitted for display.
     *      Descriptive: >
     *        The Descriptive's (visual) emphasis is on the key, not the value.
     *
     * context:
     * - Property Listing is used in Entities
     *
     * rules:
     *   usage:
     *     - You MUST NOT use html code as a value string as it may get truncated in
     *       unexpected ways.
     *     - With more than 6 properties, you SHOULD use multiple Property Listing's
     *       to segment properties into multiple visual groups/lines. Each new
     *       property component starts a new line.
     *     - You SHOULD use properties with short values (e.g. not full paragraphs).
     *       You SHOULD split off long properties into their own Property component so
     *       it will always start a new line.
     *     - When using a Symbol as a label and/or value, the chosen icon
     *       MUST be self-explanatory and easily understood by users.
     *     - When using a Symbol as a label, it SHOULD not have an action.
     *   accessibility:
     *      - When using a Symbol, you still MUST enter a label with a text that can
     *        be understood when read through a screen reader independently of any
     *        visuals. This label is passed onto the Symbol as the aria-label.
     * ----
     * @return \ILIAS\UI\Component\Listing\Property
     */
    public function property(): Property;
}
