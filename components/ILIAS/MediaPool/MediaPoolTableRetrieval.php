<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MediaPool;

use ILIAS\AdvancedMetaData\Services\ServicesInterface;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class MediaPoolTableRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilObjMediaPool $media_pool,
        protected int $current_folder,
        protected string $mode,
        protected bool $all_objects,
        protected MediaPoolRepository $pool_repo,
        protected ServicesInterface $advanced_metadata_service,
        protected array $filter
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        if ($this->all_objects) {
            $data = $this->pool_repo->getItems(
                $this->media_pool->getId(),
                (string) ($this->filter['title'] ?? ''),
                (string) ($this->filter['format'] ?? ''),
                (string) ($this->filter['keyword'] ?? ''),
                (string) ($this->filter['caption'] ?? ''),
                $this->filter,
                $this->media_pool->getRefId()
            );
        } else {
            $folders = $this->media_pool->getChilds($this->current_folder, 'fold');
            $folders = $this->sortByTitle($folders);

            if ($this->mode === 'select') {
                $items = $this->media_pool->getChilds($this->current_folder, 'mob');
            } elseif ($this->mode === 'selectc') {
                $items = $this->media_pool->getChilds($this->current_folder, 'pg');
            } else {
                $items = $this->media_pool->getChildsExceptFolders($this->current_folder);
            }

            $data = array_merge($folders, $this->sortByTitle($items));
        }

        if ($this->all_objects) {
            $advanced_metadata = $this->advanced_metadata_service
                ->forSubObjects('mep', $this->media_pool->getRefId(), 'mob', 'mpg')
                ->inDataTable();
            $sub_object_ids = [];
            foreach ($data as $index => $row) {
                $type = (string) ($row['type'] ?? '');
                if ($type === 'mob' && (int) ($row['foreign_id'] ?? 0) > 0) {
                    $sub_object_ids[$index] = $this->advanced_metadata_service->getSubObjectID(
                        0,
                        (int) $row['foreign_id'],
                        'mob'
                    );
                } elseif ($type === 'pg' && (int) ($row['child'] ?? 0) > 0) {
                    $sub_object_ids[$index] = $this->advanced_metadata_service->getSubObjectID(
                        $this->media_pool->getId(),
                        (int) $row['child'],
                        'mpg'
                    );
                }
            }
            if ($sub_object_ids !== []) {
                $advanced_data = $advanced_metadata->getData(...array_values($sub_object_ids));
                foreach ($sub_object_ids as $index => $sub_object_id) {
                    foreach ($advanced_data->dataForSubObject($sub_object_id) as $key => $value) {
                        $data[$index][$key] = $value;
                    }
                }
            }
        }

        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            $row['id'] = (int) ($row['child'] ?? $row['obj_id'] ?? 0);
            yield $row;
        }
    }

    public function count(array $filter, array $parameters): int
    {
        $count = 0;
        foreach ($this->getData([], null, null, $filter, $parameters) as $row) {
            $count++;
        }
        return $count;
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ['child', 'obj_id', 'foreign_id'], true);
    }

    protected function sortByTitle(array $data): array
    {
        usort(
            $data,
            static fn(array $left, array $right): int => strcasecmp(
                (string) ($left['title'] ?? ''),
                (string) ($right['title'] ?? '')
            )
        );
        return $data;
    }
}
