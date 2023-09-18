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
     *     Evenly Distributed Horizontal Alignments are used to position Blocks
     *     horizontally next to each other with evenly sized "columns", i.e.
     *     giving each block the same available space.
     *   effect: >
     *     All Block will have the same space available.
     *     A Layout with 4 Blocks, e.g., will assign 25% of the available space to
     *     each column. The Blocks' contents will break before wrapping starts in
     *     between them.
     *     Columns will break very late, and when they do, they _all_ will.
     *   rivals:
     *     Dynamically Distributed: >
     *       Distributes available space according to the width of the individual Blocks.
     *       Single columns will break their contents, while others don't.
     * ----
     * @param  Block[] $blocks
     * @return  \ILIAS\UI\Component\Layout\Alignment\Horizontal\EvenlyDistributed
     */
    public function evenlyDistributed(Block ...$blocks): EvenlyDistributed;

    /**
     * ---
     * description:
     *   purpose: >
     *     Dynamically Distributed Horizontal Alignments take care of the individual
     *     Block's width in trying to keep as many Blocks horizontally next to each other
     *     as possible without wrapping its contents.
     *   effect: >
     *     The contents of the Alignment will wrap before the contents of the Blocks.
     *     Not necessarily all columns will break at once: When there are three blocks,
     *     and two of them will still fit in one line, they will, and only the third column
     *     will be displayed underneath (taking the whole width, then).
     *   rivals:
     *     Evenly Distributed : >
     *       All Blocks will have the same available space and will wrap their contents
     *       before breaking in between.
     * ----
     * @param  Block[] $blocks
     * @return  \ILIAS\UI\Component\Layout\Alignment\Horizontal\DynamicallyDistributed
     */
    public function dynamicallyDistributed(Block ...$blocks): DynamicallyDistributed;
}
