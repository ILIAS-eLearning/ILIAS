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
     *     An Alignment positions Components in relation to each other
     *     and defines breakpoints for changing screensizes. It therefore
     *     does not have an visual manifestation by itself, but rather
     *     groups and arranges Components.
     *   composition: >
     *     Alignment will accept Components implementing the "Block"-Interface.
     *     It will not alter the appearance of the Component.
     *   effect: >
     *     When available screensize changes, the Alignment will arrange Blocks
     *     according to its rules.
     *
     * ----
     * @return  \ILIAS\UI\Component\Layout\Alignment\Factory
     */
    public function alignment(): Alignment\Factory;
}
