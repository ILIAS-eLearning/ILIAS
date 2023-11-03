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

use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Services;
use ILIAS\Services\ResourceStorage\Resources\Listing\SortDirection;
use ILIAS\UI\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseTableDataSource implements TableDataSource
{
    protected \ilDBInterface $db;
    protected Services $irss;
    protected ResourceCollection $collection;
    protected \ILIAS\ResourceStorage\Collection\Sorter\Sorter $sorter;
    protected int $filtered_amount_of_items = 0;
    protected int $offset = 0;
    protected int $limit = 50;
    protected int $sort_direction = SortDirection::BY_SIZE_DESC;
    protected ?array $filter_values = null;

    public function __construct(
        ResourceCollectionIdentification $rcid
    ) {
        global $DIC;
        $this->db = $DIC->database();
        $this->irss = $DIC->resourceStorage();
        $this->collection = $this->irss->collection()->get(
            $rcid
        );
        $this->sorter = $this->irss->collection()->sort($this->collection);
    }


    public function setOffsetAndLimit(int $offset, int $limit): void
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function setSortDirection(int $sort_direction): void
    {
        $this->sort_direction = $sort_direction;
    }


    public function getFilteredAmountOfItems(): int
    {
        return $this->filtered_amount_of_items;
    }


    public function getResourceIdentifications(): array
    {
        return $this->collection->getResourceIdentifications();
    }

    public function getFilterItems(
        Factory $ui_factory,
        \ilLanguage $lng
    ): array {
        return [];
    }

    public function applyFilterValues(?array $filter_values): void
    {
        $this->filter_values = $filter_values;
    }
}
