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

namespace ILIAS\Test\Results\Toplist;

class TestTopListRepository
{
    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function getGeneralToplist(\ilObjTest $object, TopListOrder $order_by): \Generator
    {
        $order_by_query = $order_by === TopListOrder::BY_TIME
            ? 'tst_pass_result.workingtime ASC'
            : 'percentage DESC';

        $this->db->setLimit($object->getHighscoreTopNum(), 0);
        $result = $this->db->queryF(
            "
                SELECT tst_result_cache.*, round(points/maxpoints*100,2) as percentage, tst_pass_result.workingtime, usr_data.usr_id, usr_data.firstname, usr_data.lastname, tst_active.active_id
                FROM object_reference
                INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
                INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
                INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
                INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi AND tst_pass_result.pass = tst_result_cache.pass
                INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
                WHERE object_reference.ref_id = %s
                ORDER BY {$order_by_query}, tstamp ASC
            ",
            [\ilDBConstants::T_INTEGER],
            [$object->getRefId()]
        );

        $i = 1;
        while ($row = $this->db->fetchAssoc($result)) {
            $row['rank'] = $i;
            $i++;
            yield $row;
        }
    }

    public function getUserToplistByPercentage(\ilObjTest $object, int $a_user_id): \Generator
    {
        $a_test_ref_id = $object->getRefId();
        $better_participants = $this->db->fetchObject(
            $this->db->query(
                '
                SELECT COUNT(tst_pass_result.workingtime) cnt
                FROM object_reference
                INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
                INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
                INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
                INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
                    AND tst_pass_result.pass = tst_result_cache.pass
                INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
                WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
                AND tst_active.user_fi != ' . $this->db->quote($a_user_id, 'integer') . '
                AND round(reached_points/max_points*100) >=
                (
                    SELECT round(reached_points/max_points*100)
                    FROM object_reference
                    INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
                    INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
                    INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
                    INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
                        AND tst_pass_result.pass = tst_result_cache.pass
                    WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
                    AND tst_active.user_fi = ' . $this->db->quote($a_user_id, 'integer') . '
                )
            '
            )
        )->cnt;

        $total_participants = $this->db->fetchObject(
            $this->db->query("
                SELECT COUNT(tst_pass_result.workingtime) cnt
                FROM object_reference
                INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
                INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
                INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
                INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
                    AND tst_pass_result.pass = tst_result_cache.pass
                INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
                WHERE object_reference.ref_id = {$this->db->quote($a_test_ref_id, 'integer')}
            ")
        )->cnt;

        [$offset, $amount] = $this->calculateLimits(
            $object->getHighscoreTopNum(),
            $better_participants,
            $total_participants
        );

        $result = $this->db->query("
            SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage ,
                tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname, tst_active.active_id
            FROM object_reference
            INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
            INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
            INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
            INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
                AND tst_pass_result.pass = tst_result_cache.pass
            INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
            WHERE object_reference.ref_id = {$this->db->quote($a_test_ref_id, 'integer')}
            ORDER BY round(reached_points/max_points*100) DESC, tstamp ASC
            LIMIT {$amount} OFFSET {$offset}
        ");

        if ($offset > 0) {
            yield $this->buildEmptyItem();
        }

        $i = $offset + 1;
        while ($row = $this->db->fetchAssoc($result)) {
            $row['rank'] = $i;
            $i += 1;
            yield $row;
        }

        if ($total_participants > $offset + $amount) {
            yield $this->buildEmptyItem();
        }
    }

    public function getUserToplistByWorkingtime(\ilObjTest $object, int $a_user_id): \Generator
    {

        $a_test_ref_id = $object->getRefId();
        $better_participants = $this->db->fetchObject(
            $this->db->query(
                '
                SELECT COUNT(tst_pass_result.workingtime) cnt
                FROM object_reference
                INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
                INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
                INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
                INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
                    AND tst_pass_result.pass = tst_result_cache.pass
                INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
                WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
                AND tst_active.user_fi != ' . $this->db->quote($a_user_id, 'integer') . '
                AND workingtime <
                (
                    SELECT workingtime
                    FROM object_reference
                    INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
                    INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
                    INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
                    INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
                        AND tst_pass_result.pass = tst_result_cache.pass
                    WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
                    AND tst_active.user_fi = ' . $this->db->quote($a_user_id, 'integer') . '
                )
            '
            )
        )->cnt;

        $total_participants = $this->db->fetchObject(
            $this->db->query(
                '
                SELECT COUNT(tst_pass_result.workingtime) cnt
                FROM object_reference
                INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
                INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
                INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
                INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
                    AND tst_pass_result.pass = tst_result_cache.pass
                INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
                WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer')
            )
        )->cnt;

        [$offset, $amount] = $this->calculateLimits(
            $object->getHighscoreTopNum(),
            $better_participants,
            $total_participants
        );

        $result = $this->db->query("
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname, tst_active.active_id
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
            WHERE object_reference.ref_id =  {$this->db->quote($a_test_ref_id, 'integer')}
			ORDER BY workingtime ASC
            LIMIT {$amount} OFFSET {$offset}
		");

        if ($offset > 0) {
            yield $this->buildEmptyItem();
        }

        $i = $offset + 1;
        while ($row = $this->db->fetchAssoc($result)) {
            $row['rank'] = $i;
            $i += 1;
            yield $row;
        }

        if ($total_participants > $offset + $amount) {
            yield $this->buildEmptyItem();
        }
    }

    private function calculateLimits(
        int $pax_to_show,
        int $better_pax,
        int $total_pax
    ): array {
        if ($total_pax < $pax_to_show) {
            return [0, $total_pax];
        }

        $pax_to_show_on_each_side = floor($pax_to_show / 2);
        $offset = $better_pax - $pax_to_show_on_each_side;
        if ($offset < 0) {
            $offset = 0;
        }
        $end = $offset + $pax_to_show;

        if ($end < $total_pax) {
            return [$offset, $pax_to_show];
        }

        return [$total_pax - $pax_to_show, $pax_to_show];
    }

    private function buildEmptyItem(): array
    {
        return [
            'rank' => '...',
            'is_actor' => false,
            'participant' => '',
            'achieved' => '',
            'score' => '',
            'percentage' => null,
            'time' => ''
        ];
    }
}
