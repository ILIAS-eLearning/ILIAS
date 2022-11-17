<?php

declare(strict_types=1);

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

namespace ILIAS\ResourceStorage\Collection\Sorter;

use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;

/**
 * Class Sorter
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class Sorter
{
    protected const SORT_ASC = SORT_ASC;
    protected const SORT_DESC = SORT_DESC;

    protected int $sort_direction = self::SORT_ASC;
    protected ResourceBuilder $resource_builder;
    protected ResourceCollection $collection;
    private bool $sort_and_save = false;
    protected CollectionBuilder $collection_builder;

    public function __construct(
        ResourceBuilder $resource_builder,
        CollectionBuilder $collection_builder,
        ResourceCollection $collection
    ) {
        $this->resource_builder = $resource_builder;
        $this->collection_builder = $collection_builder;
        $this->collection = $collection;
    }

    public function andSave(): self
    {
        $this->sort_and_save = true;
        return $this;
    }

    public function asc(): self
    {
        $this->sort_direction = self::SORT_ASC;
        return $this;
    }

    public function desc(): self
    {
        $this->sort_direction = self::SORT_DESC;
        return $this;
    }

    public function byTitle(): ResourceCollection
    {
        return $this->custom(new ByTitle($this->resource_builder, $this->sort_direction));
    }

    public function bySize(): ResourceCollection
    {
        return $this->custom(new BySize($this->resource_builder, $this->sort_direction));
    }

    public function byCreationDate(): ResourceCollection
    {
        return $this->custom(new ByCreationDate($this->resource_builder, $this->sort_direction));
    }

    public function custom(CollectionSorter $sorter): ResourceCollection
    {
        $collection = $sorter->sort($this->collection);
        if ($this->sort_and_save) {
            $this->collection_builder->store($collection);
        }
        return $collection;
    }
}
