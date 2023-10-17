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
     *     The value is a string, or one or several Symbols, Links or Legacy Components.
     *   rivals:
     *      Characteristic Value: >
     *        In Charakteristic Values, label/value pairs are displayed in a
     *        tabular way; labels cannot be omitted for display.
     *      Descriptive: >
     *        The Descriptive's (visual) emphasis is on the key, not the value.
     * context:
     *   - Property Listing is used in Entities
     *
     * ----
     * @return \ILIAS\UI\Component\Listing\Property
     */
    public function property(): Property;
}
