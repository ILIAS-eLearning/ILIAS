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

namespace ILIAS\UI\Component\Input\Container\ViewControl;

/**
 * This is what a factory for View Control Containers looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      The Standard View Control Container is used as the binding element
     *      of a collection of View Control Inputs concerning one visualization.
     *   composition: >
     *      A View Control Container holds one ore more View Controls; it is
     *      otherwise transparent to the user and does not add "own" elements.
     *   effect: >
     *      The View Control Container is responsible for aligning request-parameters
     *      for all contained View Controls as well as receiving and distributing
     *      values accordingly.
     *      When operating a contained View Control, the location is amended
     *      with parameters of all contained View Controls and reloaded.
     *      * rules:
     * rules:
     *   usage:
     *     1: The Standard View Control Container MUST be provided with a Request before rendering.
     * ---
     * @param array<string,\ILIAS\UI\Component\Input\Container\ViewControl\ViewControl> $controls
     * @return \ILIAS\UI\Component\Input\Container\ViewControl\Standard
     */
    public function standard(array $controls): Standard;
}
