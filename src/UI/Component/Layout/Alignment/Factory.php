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
     *     An Horizontal Alignment groups some sets of Blocks and displays those
     *     groups horizontally next to each other.
     *   effect: >
     *     Blocks will break to a new line within the groups first;
     *     preferably, the groups will remain next to each other, however, if
     *     space is really scarce, the groups will be vertically aligned.
     *   rivals:
     *     Force Horizontal: >
     *       Force Horizontal will keep the horizontal placement no matter what.
     *       Try to avoid that, though, unless there is a very good reason.
     *     Table: >
     *       Tables may present potetially large sets of uniformly structured data.
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
     * @return  \ILIAS\UI\Component\Layout\Alignment\Vertical
     */
    public function vertical(Block ...$blocks): Vertical;



    /**
     * ---
     * description:
     *   purpose: >
     *     An Horizontal Alignment groups some sets of Blocks and displays those
     *     groups horizontally next to each other.
     *   effect: >
     *     Blocks will break to a new line within the groups first;
     *     preferably, the groups will remain next to each other, however, if
     *     space is really scarce, the groups will be vertically aligned.
     *   rivals:
     *     Force Horizontal: >
     *       Force Horizontal will keep the horizontal placement no matter what.
     *       Try to avoid that, though, unless there is a very good reason.
     *     Table: >
     *       Tables may present potetially large sets of uniformly structured data.
     *       While Tables are not meant to layout Components, Horizontal Alignments are
     *       nothing like a row in a table; do not use multiple Alignments to
     *       mimic a Table.
     *
     * ----
     * @param  Block[] $blocksets
     * @return  \ILIAS\UI\Component\Layout\Alignment\PreferHorizontal
     */
    //public function preferHorizontal(array ...$blocksets): PreferHorizontal;

    /**
     * ---
     * description:
     *   purpose: >
     *     An Horizontal Alignment groups some sets of Blocks and displays those
     *     groups horizontally next to each other.
     *   effect: >
     *     Blocks will break to a new line within the groups; the horizontal
     *     alignment of the groups will remain, though.
     *   rivals:
     *     Prefer Horizontal: >
     *       Most of the time, Prefer Horizontal will lead to a much better
     *       user experience on small screens.
     *       Please consider using "prefer" over "force".
     *     Table: >
     *       Tables may present potentially large sets of uniformly structured data.
     *       While Tables are not meant to layout Components, Horizontal Alignments are
     *       nothing like a row in a table; do not use multiple Alignments to
     *       mimic a Table.
     *
     * ----
     * @param  Block[] $blocksets
     * @return  \ILIAS\UI\Component\Layout\Alignment\ForceHorizontal
     */
    //public function forceHorizontal(array ...$blocksets): ForceHorizontal;
}
