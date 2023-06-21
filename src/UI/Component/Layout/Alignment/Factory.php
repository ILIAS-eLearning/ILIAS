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

namespace ILIAS\UI\Component\Layout\Alignment;

/**
 * This is what a factory for alignment layouts looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     An Horizontal Alignment groups Blocks and displays those groups
     *     horizontally next to each other.
     *   effect: >
     *     Blocks will break to a new line within the groups first;
     *     preferably, the groups will remain next to each other, however, if
     *     space decreases, the groups will be vertically aligned.
     *   rivals:
     *     Vertical: >
     *       Not a real rival, but rather the counterpart: Vertical Alignemnts
     *       will position the groups vertically aligned.
     *     Table: >
     *       Tables may present potentially large sets of uniformly structured data.
     *       While Tables are not meant to layout Components, Horizontal Alignments are
     *       nothing like a row in a table; do not use multiple Alignments to
     *       mimic a Table.
     *
     * ----
     * @return  \ILIAS\UI\Component\Layout\Alignment\Horizontal\Factory
     */
    public function horizontal(): Horizontal\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *     A Vertical Alignment groups Blocks and displays those groups
     *     vertically aligned, i.e. below each other.
     *   effect: >
     *     Blocks are diplayed below each other.
     *   rivals:
     *     Force Horizontal: >
     *       Not a real rival, but rather the counterpart: Horizontal Alignemnts
     *       will position the groups (preferably) next to each other.
     *     Table: >
     *       Tables may present potentially large sets of uniformly structured data.
     *       While Tables are not meant to layout Components, Vertical Alignments are
     *       nothing to mimic a tablelike behavior.
     *
     * ----
     * @param  Block[] $blocks
     * @return  \ILIAS\UI\Component\Layout\Alignment\Vertical
     */
    public function vertical(Block ...$blocks): Vertical;
}
