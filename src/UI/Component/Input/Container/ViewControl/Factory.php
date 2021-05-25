<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
     *      The Standard View Control Container is used as the binding element of a collection of Control Inputs
     *      for one visualization.
     *   effect: >
     *      The View Control Container is responsible for aligning request-parameters for all contained View Controls
     *      as well as receiving and distributing values accordingly.
     * ---
     *
     * @param array<string,\ILIAS\UI\Component\Input\ViewControl\ViewControl> $controls
     * @return \ILIAS\UI\Component\Input\Container\ViewControl\Standard
     */
    public function standard(array $controls) : Standard;
}
