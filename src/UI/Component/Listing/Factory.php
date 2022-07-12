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
     * @param array $items Set of elements to be rendered (string|Component)[]
     * @return  \ILIAS\UI\Component\Listing\Unordered
     */
    public function unordered(array $items) : Unordered;

    /**
     * ---
     * description:
     *   purpose: >
     *     Ordered Lists are used to displayed a numbered set of textual elements. They are used if the order of the
     *     elements is relevant.
     *   composition: >
     *     Ordered Lists are composed of a set of numbers labeling the items enumerated.
     * ----
     * @param array $items Set of elements to be rendered (string|Component)[]
     * @return  \ILIAS\UI\Component\Listing\Ordered
     */
    public function ordered(array $items) : Ordered;

    /**
     * ---
     * description:
     *   purpose: >
     *     Descriptive Lists are used to display key-value doubles of textual-information.
     *   composition: >
     *     Descriptive Lists are composed of a key acting as title describing the type of
     *     information being displayed underneath.
     * ----
     * @param array $items string (key) => (string|Component)[] (value)
     * @return  \ILIAS\UI\Component\Listing\Descriptive
     */
    public function descriptive(array $items) : Descriptive;

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
    public function workflow() : Workflow\Factory;

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
    public function characteristicValue() : CharacteristicValue\Factory;
}
