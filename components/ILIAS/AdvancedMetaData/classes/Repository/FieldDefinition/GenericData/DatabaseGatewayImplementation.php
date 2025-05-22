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

namespace ILIAS\AdvancedMetaData\Repository\FieldDefinition\GenericData;

use ILIAS\AdvancedMetaData\Data\FieldDefinition\GenericData\GenericData;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\GenericData\GenericDataImplementation;
use ILIAS\AdvancedMetaData\Data\FieldDefinition\Type;
use ILIAS\AdvancedMetaData\Repository\Exception;

class DatabaseGatewayImplementation implements Gateway
{
    public function __construct(
        protected \ilDBInterface $db
    ) {
    }

    public function create(GenericData $data): int
    {
        return $this->insert($data, false, false);
    }

    public function createFromScratch(GenericData $data): int
    {
        return $this->insert($data, true, true);
    }

    public function createWithNewPosition(GenericData $data): int
    {
        return $this->insert($data, true, false);
    }

    public function insert(
        GenericData $data,
        bool $increment_position,
        bool $generate_import_id
    ): int {
        $next_id = $this->db->nextId('adv_mdf_definition');

        $position = $increment_position ?
            $this->getNextPositionInRecord($data->getRecordID()) :
            $data->getPosition();

        $import_id = $generate_import_id ?
            $this->generateUniqueImportId($next_id) :
            $data->getImportID();

        $this->db->insert(
            'adv_mdf_definition',
            [
                'field_id' => [\ilDBConstants::T_INTEGER, $next_id],
                'field_type' => [\ilDBConstants::T_INTEGER, $data->type()->value],
                'record_id' => [\ilDBConstants::T_INTEGER, $data->getRecordID()],
                'import_id' => [\ilDBConstants::T_TEXT, $import_id],
                'title' => [\ilDBConstants::T_TEXT, $data->getTitle()],
                'description' => [\ilDBConstants::T_TEXT, $data->getDescription()],
                'position' => [\ilDBConstants::T_INTEGER, $data->getPosition()],
                'searchable' => [\ilDBConstants::T_INTEGER, $data->isSearchable()],
                'required' => [\ilDBConstants::T_INTEGER, $data->isRequired()],
                'field_values' => [\ilDBConstants::T_TEXT, serialize($data->getFieldValues())]
            ]
        );

        return $next_id;
    }

    public function readByID(int $field_id): ?GenericData
    {
        $query = 'SELECT * FROM adv_mdf_definition WHERE field_id = ' .
            $this->db->quote($field_id, \ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            return $this->dataFromRow($row);
        }
        return null;
    }

    /**
     * @return GenericData[]
     */
    public function readByIDs(int ...$field_ids): \Generator
    {
        if (empty($field_ids)) {
            return;
        }

        $query = 'SELECT * FROM adv_mdf_definition WHERE ' .
            $this->db->in('field_id', $field_ids, false, \ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        $data = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $data[$row['field_id']] = $this->dataFromRow($row);
        }

        // Return data in the order the field IDs where passed.
        foreach ($field_ids as $field_id) {
            yield $data[$field_id];
        }
    }

    /**
     * @return GenericData[]
     */
    public function readByRecords(bool $only_searchable, int ...$record_ids): \Generator
    {
        if (empty($record_ids)) {
            return;
        }

        $query = 'SELECT * FROM adv_mdf_definition WHERE ' .
            $this->db->in('record_id', $record_ids, false, \ilDBConstants::T_INTEGER);

        if ($only_searchable) {
            $query .= ' AND searchable = 1';
        }

        $query .= ' ORDER BY position';

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            yield $this->dataFromRow($row);
        }
    }

    public function readByImportID(string $import_id): ?GenericData
    {
        $query = 'SELECT * FROM adv_mdf_definition WHERE import_id = ' .
            $this->db->quote($import_id, \ilDBConstants::T_TEXT);

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            return $this->dataFromRow($row);
        }
        return null;
    }

    public function update(GenericData $data): void
    {
        if (!$data->isPersisted() || !$data->containsChanges()) {
            return;
        }

        $this->db->update(
            'adv_mdf_definition',
            [
                'field_type' => [\ilDBConstants::T_INTEGER, $data->type()->value],
                'record_id' => [\ilDBConstants::T_INTEGER, $data->getRecordID()],
                'import_id' => [\ilDBConstants::T_TEXT, $data->getImportID()],
                'title' => [\ilDBConstants::T_TEXT, $data->getTitle()],
                'description' => [\ilDBConstants::T_TEXT, $data->getDescription()],
                'position' => [\ilDBConstants::T_INTEGER, $data->getPosition()],
                'searchable' => [\ilDBConstants::T_INTEGER, $data->isSearchable()],
                'required' => [\ilDBConstants::T_INTEGER, $data->isRequired()],
                'field_values' => [\ilDBConstants::T_TEXT, serialize($data->getFieldValues())]
            ],
            [
                'field_id' => [\ilDBConstants::T_INTEGER, $data->id()]
            ]
        );
    }

    public function delete(int ...$field_ids): void
    {
        if (empty($field_ids)) {
            return;
        }

        $query = 'DELETE FROM adv_mdf_definition WHERE ' .
            $this->db->in('field_id', $field_ids, false, \ilDBConstants::T_INTEGER);

        $this->db->manipulate($query);
    }

    protected function getNextPositionInRecord(int $record_id): int
    {
        $query = 'SELECT MAX(position) max_pos FROM adv_mdf_definition WHERE record_id = ' .
            $this->db->quote($record_id, \ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            return $row['max_pos'] + 1;
        }
        return 0;
    }

    protected function generateUniqueImportId(int $field_id): string
    {
        return 'il_' . IL_INST_ID . '_adv_md_field_' . $field_id;
    }

    protected function dataFromRow(array $row): GenericData
    {
        if (!isset($row['field_type']) || is_null($type = Type::tryFrom((int) $row['field_type']))) {
            throw new Exception(
                ($row['field_type'] ?? 'Null') . ' is invalid as field definition type'
            );
        }

        $field_values = unserialize((string) $row['field_values']);
        if (!is_array($field_values)) {
            $field_values = [];
        }

        return new GenericDataImplementation(
            $type,
            (int) $row['record_id'],
            (string) $row['import_id'],
            (string) $row['title'],
            (string) $row['description'],
            (int) $row['position'],
            (bool) $row['searchable'],
            (bool) $row['required'],
            $field_values,
            (int) $row['field_id']
        );
    }
}
