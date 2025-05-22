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

namespace ILIAS\Test\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class CloneIntroductionAndClosingRemarksMigration implements Migration
{
    private const TESTS_PER_STEP = 100;

    private \ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Fix missing clones for Introduction and Concluding Remarks.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $select_page_statement = $this->db->prepare(
            'SELECT * FROM page_object WHERE parent_type = "tst" AND page_id = ?',
            [\ilDBConstants::T_INTEGER]
        );

        $max_steps = $this->migrateIntroductions($select_page_statement);
        $this->migrateConcludingRemarks($select_page_statement, $max_steps);

        $this->db->free($select_page_statement);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result_intro = $this->db->query('
            SELECT COUNT(test_id) as cnt
            FROM tst_tests
            WHERE NOT introduction_page_id IS NULL
                AND introduction_page_id IN
                (SELECT introduction_page_id FROM tst_tests GROUP BY introduction_page_id HAVING COUNT(introduction_page_id) > 1)
        ');
        $row_intro = $this->db->fetchObject($result_intro);

        $result_conclusion = $this->db->query('
            SELECT COUNT(test_id) as cnt
            FROM tst_tests
            WHERE NOT concluding_remarks_page_id IS NULL
                AND concluding_remarks_page_id  in
                (SELECT concluding_remarks_page_id FROM tst_tests GROUP BY concluding_remarks_page_id HAVING COUNT(concluding_remarks_page_id) > 1)
        ');
        $row_conclusion = $this->db->fetchObject($result_conclusion);

        return (int) ceil(($row_intro->cnt + $row_conclusion->cnt) / self::TESTS_PER_STEP);
    }

    private function migrateIntroductions(\ilDBStatement $select_page_statement): int
    {
        $result = $this->db->query(
            '
            SELECT test_id, obj_fi, introduction_page_id
            FROM tst_tests
            WHERE NOT introduction_page_id IS NULL
                AND introduction_page_id IN
                (SELECT introduction_page_id FROM tst_tests GROUP BY introduction_page_id HAVING COUNT(introduction_page_id) > 1)
            ORDER BY introduction_page_id
            LIMIT ' . self::TESTS_PER_STEP
        );

        $first_row = $this->db->fetchObject($result);
        if ($first_row === null) {
            return self::TESTS_PER_STEP;
        }

        $introduction_to_clone = $this->db->fetchObject(
            $this->db->execute(
                $select_page_statement,
                [$first_row->introduction_page_id]
            )
        );
        while (($row = $this->db->fetchObject($result)) !== null) {
            if ($row->introduction_page_id !== $introduction_to_clone?->page_id) {
                $introduction_to_clone = $this->db->fetchObject(
                    $this->db->execute(
                        $select_page_statement,
                        [$row->introduction_page_id]
                    )
                );
                continue;
            }

            $new_page_id = $this->createPageWithNextId($row->obj_fi, $introduction_to_clone);
            $this->db->update(
                'tst_tests',
                [
                    'introduction_page_id' => [\ilDBConstants::T_INTEGER, $new_page_id]
                ],
                [
                    'test_id' => [\ilDBConstants::T_INTEGER, $row->test_id]
                ]
            );
        }

        return self::TESTS_PER_STEP - $result->numRows();
    }

    private function migrateConcludingRemarks(\ilDBStatement $select_page_statement, int $max_steps): void
    {
        $result = $this->db->query(
            '
            SELECT test_id, obj_fi, concluding_remarks_page_id
            FROM tst_tests
            WHERE NOT concluding_remarks_page_id IS NULL
                AND concluding_remarks_page_id IN
                (SELECT concluding_remarks_page_id FROM tst_tests GROUP BY concluding_remarks_page_id HAVING COUNT(concluding_remarks_page_id) > 1)
            ORDER BY concluding_remarks_page_id
            LIMIT ' . $max_steps
        );

        $first_row = $this->db->fetchObject($result);
        if ($first_row === null) {
            return;
        }

        $concluding_remarks_to_clone = $this->db->fetchObject(
            $this->db->execute(
                $select_page_statement,
                [$first_row->concluding_remarks_page_id]
            )
        );
        while (($row = $this->db->fetchObject($result)) !== null) {
            if ($row->concluding_remarks_page_id !== $concluding_remarks_to_clone?->page_id) {
                $concluding_remarks_to_clone = $this->db->fetchObject(
                    $this->db->execute(
                        $select_page_statement,
                        [$row->concluding_remarks_page_id]
                    )
                );
                continue;
            }

            $new_page_id = $this->createPageWithNextId($row->obj_fi, $concluding_remarks_to_clone);
            $this->db->update(
                'tst_tests',
                [
                    'concluding_remarks_page_id' => [\ilDBConstants::T_INTEGER, $new_page_id]
                ],
                [
                    'test_id' => [\ilDBConstants::T_INTEGER, $row->test_id]
                ]
            );
        }
    }

    private function createPageWithNextId(int $test_obj_id, \stdClass $row): int
    {
        $query = $this->db->query('SELECT max(page_id) as last_id FROM page_object WHERE parent_type="tst"');
        $last_row = $this->db->fetchObject($query);
        try {
            $this->db->insert(
                'page_object',
                [
                    'page_id' => ['integer', $last_row->last_id + 1],
                    'parent_id' => ['integer', $test_obj_id],
                    'lang' => ['text', $row->lang],
                    'content' => ['clob', $row->content],
                    'parent_type' => ['text', $row->parent_type],
                    'create_user' => ['integer', $row->create_user],
                    'last_change_user' => ['integer', $row->last_change_user],
                    'active' => ['integer', $row->active],
                    'activation_start' => ['timestamp', $row->activation_start],
                    'activation_end' => ['timestamp', $row->activation_end],
                    'show_activation_info' => ['integer', $row->show_activation_info],
                    'inactive_elements' => ['integer', $row->inactive_elements],
                    'int_links' => ['integer', $row->int_links],
                    'created' => ['timestamp', \ilUtil::now()],
                    'last_change' => ['timestamp', \ilUtil::now()],
                    'is_empty' => ['integer', $row->is_empty]
                ]
            );
        } catch (ilDatabaseException $e) {
            $this->createPageWithNextId($row);
        }

        return $last_row->last_id + 1;
    }
}
