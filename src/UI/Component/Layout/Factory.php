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

namespace ILIAS\UI\Component\Layout;

/**
 * This is what a factory for layouts looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Page is the user's view upon ILIAS in total.
     *
     * ----
     * @return  \ILIAS\UI\Component\Layout\Page\Factory
     */
    public function page(): Page\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *     An Alignment positions content blocks in relation to each other
     *     and defines breakpoints for changing screensizes. It therefore
     *     does not have an visual manifestation by itself, but rather
     *     groups and arranges content.
     *     Alignments do not carry any deeper semantics then those of the positioning.
     *     Their public usage hence is restricted to situations where the UI framework
     *     cannot feasibly know any deeper semantics for the relation of the contained
     *     components.
     *     Examples for these situations could be:
     *     - content that is completely created and arranged by users, such as pages from the page editor
     *     - content that is heavy on text, images or data presentations, such as reports, i.e. classic print layout situations
     *     - content where legacy components need to be included for the moment
     *
     *     From the perspective of the UI framework it is always better to have
     *     meaningful semantics for components.
     *     Decisions like: How should this be arranged? How should this be styled?
     *     How should this be treated on small screens? are a lot easier then.
     *     This should be kept in mind when Alignments are used:
     *     Could there be a way to use a component with richer semantics?
     *     Could we create a component with richer semantics?
     *
     *   composition: >
     *     Alignment will accept Components implementing the "Block"-Interface.
     *     It will not alter the appearance of the Component.
     *   effect: >
     *     When available screensize changes, the Alignment will arrange Blocks
     *     according to its rules.
     * rules:
     *   usage:
     *     1: >
     *       Alignments SHOULD only be used when there is no other component that fits the requirements (see purpose).
     * ----
     * @return  \ILIAS\UI\Component\Layout\Alignment\Factory
     */
    public function alignment(): Alignment\Factory;
}
