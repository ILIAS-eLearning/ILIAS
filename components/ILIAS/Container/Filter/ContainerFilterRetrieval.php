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

namespace ILIAS\Container\Filter;

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\RetrievalBase;

class ContainerFilterRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilContainerFilterService $container_filter_service,
        protected int $ref_id
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $service = $this->container_filter_service;
        $items = array_map(static function (\ilContainerFilterField $i) use ($service): array {
            return [
                "id" => $i->getRecordSetId() . ":" . $i->getFieldId(),
                "record_set_id" => $i->getRecordSetId(),
                "record_title" => $service->util()->getContainerRecordTitle($i->getRecordSetId()),
                "field_title" => $service->util()->getContainerFieldTitle($i->getRecordSetId(), $i->getFieldId())
            ];
        }, $service->data()->getFilterSetForRefId($this->ref_id)->getFields());

        $items = $this->applyOrder($items, $order);
        $items = $this->applyRange($items, $range);

        foreach ($items as $item) {
            yield $item;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        $service = $this->container_filter_service;
        return count($service->data()->getFilterSetForRefId($this->ref_id)->getFields());
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ["record_set_id"]);
    }
}
