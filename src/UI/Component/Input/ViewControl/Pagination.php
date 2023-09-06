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

namespace ILIAS\UI\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\Container\ViewControl\ViewControlInput;

/**
 * This describes a Pagination View Control
 */
interface Pagination extends ViewControlInput
{
    /**
     * Optionally provide a list of integers for the page-length selection.
     * @param int[] $options
     */
    public function withLimitOptions(array $options): self;

    /**
     * In order to calculate the sections, the pagination needs to know
     * the total amount of entries.
     */
    public function withTotalCount(?int $total_count): self;

    /**
     * You may alter the amount of sections shown simultanously:
     * there is always the first and last section, and the remaining amount
     * in between. E.g., a Number of visible entries = 5 will give you
     * something like "1 ... 7 8 9 ... 302"
     */
    public function withNumberOfVisibleEntries(int $number_of_entries): self;
}
