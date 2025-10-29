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

namespace ILIAS\Test\Scoring\Marks;

class MarksDatabaseRepository implements MarksRepository
{
    private const DB_TABLE = 'tst_mark';

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly MarkSchemaFactory $factory
    ) {
    }

    public function getMarkSchemaFor(int $test_id): MarkSchema
    {
        $result = $this->db->queryF(
            'SELECT * FROM ' . self::DB_TABLE . ' WHERE test_fi = %s ORDER BY minimum_level',
            [\ilDBConstants::T_INTEGER],
            [$test_id],
        );

        return $this->factory->createMarkSchemaFromDBRow($this->db->fetchAll($result), $test_id);
    }

    public function getMarkSchemaBySteps(array $step_ids): MarkSchema
    {
        $where_part = $this->db->in('mark_id', $step_ids, false, \ilDBConstants::T_INTEGER);
        $result = $this->db->query('SELECT * FROM ' . self::DB_TABLE . ' WHERE ' . $where_part . ' ORDER BY minimum_level');

        return $this->factory->createMarkSchemaFromDBRow($this->db->fetchAll($result), -1);
    }

    public function storeMarkSchema(MarkSchema $mark_schema): array
    {
        if (!$mark_schema->getTestId()) {
            return [];
        }

        if ($mark_schema->getTestId() > 0) {
            // Delete all entries
            $this->db->manipulateF(
                'DELETE FROM ' . self::DB_TABLE . ' WHERE test_fi = %s',
                ['integer'],
                [$mark_schema->getTestId()]
            );
        }

        if ($mark_schema->getMarkSteps() === []) {
            return [];
        }

        // Write new datasets
        $mark_ids = [];
        foreach ($mark_schema->getMarkSteps() as $mark) {
            $mark_id = $this->db->nextId(self::DB_TABLE);

            $mark_array = $mark->toStorage();
            $mark_array['mark_id'] = ['integer', $mark_id];
            $mark_array['test_fi'] = ['integer', $mark_schema->getTestId()];

            $this->db->insert(
                self::DB_TABLE,
                $mark_array
            );
            $mark_ids[] = $mark_id;
        }

        return $mark_ids;
    }

    public function deleteSteps(array $step_ids): void
    {
        $where_part = $this->db->in('mark_id', $step_ids, false, \ilDBConstants::T_INTEGER);
        $this->db->manipulate('DELETE FROM ' . self::DB_TABLE . ' WHERE ' . $where_part);
    }
}
