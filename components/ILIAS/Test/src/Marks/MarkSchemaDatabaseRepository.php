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

namespace ILIAS\Test\Marks;

class MarksDatabaseRepository implements MarksRepository
{
    private const DB_TABLE = 'tst_mark';


    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function getMarkSchemaFor(int $test_id): MarkSchema
    {
        $schema = new MarkSchema($test_id);
        $schema->createSimpleSchema();
        $result = $this->db->queryF(
            'SELECT * FROM ' . self::DB_TABLE . ' WHERE test_fi = %s ORDER BY minimum_level',
            ['integer'],
            [$test_id]
        );
        if ($this->db->numRows($result) > 0) {
            $schema->flush();
            while ($data = $this->db->fetchAssoc($result)) {
                $schema->addMarkStep($data['short_name'], $data['official_name'], (float) $data['minimum_level'], (int) $data['passed']);
            }
        }
        return $schema;
    }

    public function storeMarkSchema(MarkSchema $mark_schema): void
    {
        if (!$mark_schema->getTestId()) {
            return;
        }
        // Delete all entries
        $this->db->manipulateF(
            'DELETE FROM ' . self::DB_TABLE . ' WHERE test_fi = %s',
            ['integer'],
            [$mark_schema->getTestId()]
        );
        if ($mark_schema->getMarkSteps() === []) {
            return;
        }

        // Write new datasets
        foreach ($mark_schema->getMarkSteps()->toStorage() as $marks) {
            $marks['mark_id'] = ['integer', $this->db->nextId(self::DB_TABLE)];
            $marks['test_fi'] = ['integer', $mark_schema->getTestId()];
            $this->db->insert(
                self::DB_TABLE,
                $marks
            );
        }
    }
}
