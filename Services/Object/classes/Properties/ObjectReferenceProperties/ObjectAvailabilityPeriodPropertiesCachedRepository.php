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

/**
 *
 * @author Stephan Kergomard
 */
class ObjectAvailabilityPeriodPropertiesCachedRepository
{
    private const TIMINGS_PROPERTIES_TABLE = 'crs_items';

    private array $data_cache = [];

    public function __construct(
        private readonly \ilDBInterface $database,
        private readonly \ilTree $tree
    ) {
    }

    public function preload(array $object_reference_ids): void
    {
        $this->data_cache += $this->retrieveDataForObjectReferenceIds($object_reference_ids);
    }

    public function resetPreloadedData(): void
    {
        $this->data_cache = [];
    }

    public function getFor(?int $object_reference_id): ObjectAvailabilityPeriodProperty
    {
        if ($object_reference_id === null
            || $object_reference_id === 0) {
            return $this->getDefaultObjectReferenceProperties();
        }

        if (!isset($this->data_cache[$object_reference_id])) {
            $this->data_cache[$object_reference_id] = $this->retrieveDataForObjectReferenceId($object_reference_id);
        }

        $data = $this->data_cache[$object_reference_id];
        return new ObjectAvailabilityPeriodProperty(
            $object_reference_id,
            $data['timing_enabled'],
            $data['timing_start'],
            $data['timing_end'],
            $data['visible']
        );
    }

    public function store(ObjectAvailabilityPeriodProperty $property): void
    {
        $object_reference_id = $property->getObjectReferenceId();

        if ($object_reference_id === null) {
            throw new \Exception('The current configuration cannot be saved.');
        }

        $primary = [
            'parent_id' => [\ilDBConstants::T_INTEGER, $this->tree->getParentId($property->getObjectReferenceId())],
            'obj_id' => [\ilDBConstants::T_INTEGER, $object_reference_id]
        ];

        $storage_array = [
            'timing_type' => [\ilDBConstants::T_TEXT, $property->getAvailabilityPeriodEnabled() ? 0 : 1],
            'timing_start' => [\ilDBConstants::T_INTEGER, $property->getAvailabilityPeriodStart()?->getTimestamp() ?? 0],
            'timing_end' => [\ilDBConstants::T_INTEGER, $property->getAvailabilityPeriodEnd()?->getTimestamp() ?? 0],
            'visible' => [\ilDBConstants::T_INTEGER, $property->getVisibleWhenDisabled() ? 1 : 0]
        ];
        $this->database->replace(self::TIMINGS_PROPERTIES_TABLE, $primary, $storage_array);
        $this->data_cache[$object_reference_id] = $this->retrieveDataForObjectReferenceId($object_reference_id);
    }

    private function getDefaultObjectReferenceProperties(): ObjectAvailabilityPeriodProperty
    {
        return new ObjectAvailabilityPeriodProperty();
    }

    /**
     * @return array<mixed>
     */
    protected function retrieveDataForObjectReferenceId(int $object_reference_id): array
    {
        $where = 'WHERE obj_id=' . $this->database->quote($object_reference_id, \ilDBConstants::T_INTEGER);
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
        $where = 'WHERE ' . $this->database->in('obj_id', $object_reference_ids, false, \ilDBConstants::T_INTEGER);
        return $this->retrieveDataForWhereClause($where);
    }

    protected function retrieveDataForWhereClause(string $where): array
    {
        $query = 'SELECT '
            . 'obj_id, timing_type, timing_start, timing_end, visible' . PHP_EOL
            . 'FROM ' . self::TIMINGS_PROPERTIES_TABLE . PHP_EOL
            . $where;

        $statement = $this->database->query($query);
        $num_rows = $this->database->numRows($statement);

        if ($num_rows === 0) {
            return [];
        }

        $data = [];
        while ($row = $this->database->fetchAssoc($statement)) {
            $data[$row['obj_id']] = [
                'timing_enabled' => $row['timing_type'] === \ilObjectActivation::TIMINGS_ACTIVATION,
                'timing_start' => $row['timing_start'] !== 0
                    ? \DateTimeImmutable::createFromFormat('U', (string) $row['timing_start'])
                    : null,
                'timing_end' => $row['timing_end'] !== 0
                    ? \DateTimeImmutable::createFromFormat('U', (string) $row['timing_end'])
                    : null,
                'visible' => $row['visible'] === 1 ? true : false
            ];
        }

        return $data;
    }
}
