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

namespace ILIAS\Object\Properties\ObjectReferenceProperties;

use ILIAS\UI\NotImplementedException;

class ObjectReferencePropertiesCachedRepository implements ObjectReferencePropertiesRepository
{
    private const REFERENCE_PROPERTIES_TABLE = 'object_reference';

    private array $data_cache = [];

    public function __construct(
        private readonly ObjectAvailabilityPeriodPropertiesCachedRepository $time_based_activation_properties_repository,
        private readonly \ilDBInterface $database
    ) {
    }

    public function getFor(?int $object_reference_id): ObjectReferenceProperties
    {
        if ($object_reference_id === null
            || $object_reference_id === 0) {
            return new ObjectReferenceProperties();
        }

        if (!isset($this->data_cache[$object_reference_id])) {
            $this->data_cache[$object_reference_id] = $this->retrieveDataForObjectReferenceId($object_reference_id);
        }

        $data = $this->data_cache[$object_reference_id];
        return new ObjectReferenceProperties(
            $object_reference_id,
            $data['obj_id'],
            $data['date_of_deletion'],
            $data['deleted_by'],
            $this->time_based_activation_properties_repository->getFor($object_reference_id)
        );
    }

    public function storePropertyAvailabilityPeriod(
        ObjectAvailabilityPeriodProperty $time_based_activation_property
    ): void {
        $this->time_based_activation_properties_repository->store(
            $time_based_activation_property
        );
    }

    /**
     *
     * @param array<int> $object_reference_ids
     */
    public function preload(array $object_reference_ids): void
    {
        $this->data_cache += $this->retrieveDataForObjectReferenceIds($object_reference_ids);
        $this->time_based_activation_properties_repository->preload($object_reference_ids);
    }

    public function resetPreloadedData(): void
    {
        $this->data_cache = [];
        $this->reference_properties_repository->resetPreloadedData();
    }

    /**
     * @return array<mixed>
     */
    protected function retrieveDataForObjectReferenceId(int $object_reference_id): array
    {
        $where = 'WHERE ref_id=' . $this->database->quote($object_reference_id, \ilDBConstants::T_INTEGER);
        $data = $this->retrieveDataForWhereClause($where);

        if ($data === []) {
            throw new \Exception('The object with the following reference_id does not exist: '
                . (string) $object_reference_id);
        }

        return $data[$object_reference_id];
    }

    /**
     * @param array<int> $object_reference_ids
     */
    protected function retrieveDataForObjectReferenceIds(array $object_reference_ids): array
    {
        $where = 'WHERE ' . $this->database->in('ref_id', $object_reference_ids, false, \ilDBConstants::T_INTEGER);
        return $this->retrieveDataForWhereClause($where);
    }

    protected function retrieveDataForWhereClause(string $where): array
    {
        $query = 'SELECT '
            . 'ref_id, obj_id, deleted, deleted_by' . PHP_EOL
            . 'FROM ' . self::REFERENCE_PROPERTIES_TABLE . PHP_EOL
            . $where;

        $statement = $this->database->query($query);
        $num_rows = $this->database->numRows($statement);

        if ($num_rows === 0) {
            return [];
        }

        $data = [];
        while ($row = $this->database->fetchAssoc($statement)) {
            $data[$row['ref_id']] = [
                'obj_id' => $row['obj_id'],
                'date_of_deletion' => $row['deleted'] !== null
                    ? new \DateTimeImmutable($row['deleted'], new \DateTimeZone('UTC'))
                    : null,
                'deleted_by' => $row['deleted_by']
            ];
        }

        return $data;
    }
}
