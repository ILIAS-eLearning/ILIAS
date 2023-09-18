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

use ILIAS\Services\ResourceStorage\Resources\Listing\SortDirection;
use ILIAS\UI\Implementation\Component\Input\Container\Filter\Standard;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class CollectionDataSource extends BaseTableDataSource implements TableDataSource
{
    public function __construct(
        string $collection_id
    ) {
        global $DIC;
        parent::__construct(
            $DIC->resourceStorage()->collection()->id($collection_id, 6) // ad hoc collection
        );
    }

    public function getSortationsMapping(): array
    {
        return [
            SortDirection::BY_SIZE_DESC => 'fsd', // Default
            SortDirection::BY_SIZE_ASC => 'fsa',
            SortDirection::BY_TITLE_ASC => 'rta',
            SortDirection::BY_TITLE_DESC => 'rtd',
        ];
    }

    public function handleFilters(Standard $filter): void
    {
        // TODO: Implement handleFilters() method.
    }

    public function process(): void
    {
        switch ($this->sort_direction) {
            case SortDirection::BY_SIZE_DESC:
                $this->collection = $this->sorter->desc()->bySize();
                break;
            case SortDirection::BY_SIZE_ASC:
                $this->collection = $this->sorter->asc()->bySize();
                break;
            case SortDirection::BY_TITLE_ASC:
                $this->collection = $this->sorter->asc()->byTitle();
                break;
            case SortDirection::BY_TITLE_DESC:
                $this->collection = $this->sorter->desc()->byTitle();
                break;
        }
        $this->filtered_amount_of_items = $this->collection->count();
    }

    /**
     * @return \ILIAS\ResourceStorage\Identification\ResourceIdentification[]
     */
    public function getResourceIdentifications(): array
    {
        return $this->irss->collection()->rangeAsArray(
            $this->collection,
            $this->offset,
            $this->limit
        );
    }
}
