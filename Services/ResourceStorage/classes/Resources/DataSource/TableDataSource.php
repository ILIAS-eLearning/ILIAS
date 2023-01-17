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

namespace ILIAS\Services\ResourceStorage\Resources\DataSource;

use ILIAS\UI\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface TableDataSource
{
    public function getSortationsMapping(): array;

    public function applyFilterValues(?array $filter_values): void;

    public function getFilterItems(
        Factory $ui_factory,
        \ilLanguage $lng
    ): array;

    public function setOffsetAndLimit(int $offset, int $limit): void;

    /**
     * @see SortDirection for possible values, whis will be an ENUM as soon as possible
     */
    public function setSortDirection(int $sort_direction): void;

    public function getResourceIdentifications(): array;

    public function getFilteredAmountOfItems(): int;

    public function process(): void;
}
