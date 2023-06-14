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

namespace ILIAS\UI\Component\Layout\Alignment\Horizontal;

use ILIAS\UI\Component\Layout\Alignment\Block;

/**
 * This is what a factory for horizontal alignments layouts looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     X
     *   effect: >
     *     X
     *   rivals:
     *     Force Horizontal: >
     *       X
     *     Table: >
     *       X
     *
     * ----
     * @param  Block[] $blocks
     * @return  \ILIAS\UI\Component\Layout\Alignment\Horizontal\EvenlyDistributed
     */
    public function evenlyDistributed(Block ...$blocks): EvenlyDistributed;

    /**
     * ---
     * description:
     *   purpose: >
     *     X
     *   effect: >
     *     X
     *   rivals:
     *     Force Horizontal: >
     *       X
     *     Table: >
     *       X
     *
     * ----
     * @param  Block[] $blocks
     * @return  \ILIAS\UI\Component\Layout\Alignment\Horizontal\DynamicallyDistributed
     */
    public function dynamicallyDistributed(Block ...$blocks): DynamicallyDistributed;
}
