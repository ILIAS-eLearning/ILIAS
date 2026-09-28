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

namespace ILIAS\Repository\Trash;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class TrashRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
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
        $filter = $this->normalizeFilter($filter);
        $max_entries = 0;
        $order_data = $order?->get() ?? ['title' => 'ASC'];
        $order_field = (string) array_key_first($order_data);
        $order_direction = (string) $order_data[$order_field];
        $range ??= new Range(0, 10);

        $filter['deleted'] ??= [];
        $filter['deleted'] += ['from' => null, 'to' => null];

        $reader = new \ilTreeTrashQueries();
        foreach ($reader->getTrashNodeForContainer(
            $this->ref_id,
            $filter,
            $max_entries,
            $order_field,
            $order_direction,
            $range->getLength(),
            $range->getStart()
        ) as $item) {
            $deleted_by_id = $item->getDeletedBy();
            $deleted_by = \ilObjUser::_lookupLogin($deleted_by_id);

            yield [
                'id' => $item->getRefId(),
                'obj_id' => $item->getObjId(),
                'type' => $item->getType(),
                'title' => \ilUtil::stripSlashes($item->getTitle()),
                'description' => \ilUtil::stripSlashes($item->getDescription()),
                'path' => $this->getPath(),
                'deleted_by' => $deleted_by ?: $this->getUnknownUserLabel(),
                'deleted' => $item->getDeleted(),
                'num_subs' => $reader->getNumberOfTrashedNodesForTrashedContainer($item->getRefId())
            ];
        }
    }

    public function count(array $filter, array $parameters): int
    {
        $filter = $this->normalizeFilter($filter);
        $max_entries = 0;
        $filter['deleted'] ??= [];
        $filter['deleted'] += ['from' => null, 'to' => null];

        (new \ilTreeTrashQueries())->getTrashNodeForContainer(
            $this->ref_id,
            $filter,
            $max_entries,
            '',
            '',
            0,
            0
        );

        return $max_entries;
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ['id', 'obj_id', 'num_subs'], true);
    }

    protected function getPath(): array
    {
        return $this->domain->repositoryTree()->getNodePath($this->ref_id, ROOT_FOLDER_ID);
    }

    protected function getUnknownUserLabel(): string
    {
        return $this->domain->lng()->txt('rep_trash_deleted_by_unknown');
    }

    protected function normalizeFilter(array $filter): array
    {
        foreach (['type', 'title', 'deleted_by'] as $key) {
            if (trim((string) ($filter[$key] ?? '')) === '') {
                unset($filter[$key]);
            }
        }

        $deleted = $filter['deleted'] ?? [];
        if (!is_array($deleted)) {
            $deleted = [];
        }

        $filter['deleted'] = [
            'from' => $this->toDate($deleted['start'] ?? $deleted['from'] ?? null),
            'to' => $this->toDate($deleted['end'] ?? $deleted['to'] ?? null)
        ];

        return $filter;
    }

    protected function toDate(mixed $value): ?\ilDate
    {
        if (!$value instanceof \DateTimeInterface) {
            return null;
        }

        return new \ilDate($value->format('Y-m-d'), \IL_CAL_DATE);
    }
}
