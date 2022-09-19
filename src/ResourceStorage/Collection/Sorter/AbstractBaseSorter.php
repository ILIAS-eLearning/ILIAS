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

use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;

/**
 * Class AbstractBaseSorter
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
abstract class AbstractBaseSorter implements CollectionSorter
{
    protected ResourceBuilder $resource_builder;
    protected int $direction = SORT_ASC;

    public function __construct(ResourceBuilder $resource_builder, int $direction = SORT_ASC)
    {
        $this->resource_builder = $resource_builder;
        $this->direction = $direction;
    }

    abstract protected function sortResourceIdentification(array $identifications): array;


    public function sort(ResourceCollection $collection): ResourceCollection
    {
        $identifications = $collection->getResourceIdentifications();
        $collection->clear();
        $sorted = $this->sortResourceIdentification($identifications);
        if ($this->direction == SORT_DESC) {
            $sorted = array_reverse($sorted);
        }
        foreach ($sorted as $identification) {
            $collection->add($identification);
        }

        return $collection;
    }
}
