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
        private readonly \ilObjTest $object,
        private readonly \ilDBInterface $db
    ) {
    }

    public function getGeneralToplist(TopListOrder $order_by): \Generator
    {
        $order_stmt = $order_by === TopListOrder::BY_TIME ? 'tst_pass_result.workingtime' : 'percentage';
        $this->db->setLimit($this->object->getHighscoreTopNum(), 0);
        $result = $this->db->query(
            '
			SELECT tst_result_cache.*, round(points/maxpoints*100,2) as percentage, tst_pass_result.workingtime, usr_data.usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $this->db->quote($this->object->getRefId(), 'integer') .
            ' ORDER BY ' . $order_stmt . ' DESC'
        );

        while ($row = $this->db->fetchAssoc($result)) {
            yield $row;
        }
    }

    public function getUserToplistByPercentage(int $a_user_id): \Generator
    {
        $a_test_ref_id = $this->object->getRefId();
        $result = $this->db->query(
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
        );
        $row = $this->db->fetchAssoc($result);
        $better_participants = $row['cnt'];
        $own_placement = $better_participants + 1;

        $result = $this->db->query(
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
        );
        $row = $this->db->fetchAssoc($result);
        $number_total = $row['cnt'];

        $result = $this->db->query(
            '
		SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage ,
			tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
		FROM object_reference
		INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
		INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
		INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
		INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
			AND tst_pass_result.pass = tst_result_cache.pass
		INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi

		WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
		AND tst_active.user_fi = ' . $this->db->quote($a_user_id, 'integer') . '

		UNION(
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
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
			ORDER BY round(reached_points/max_points*100) ASC
			LIMIT 0, ' . $this->db->quote($this->object->getHighscoreTopNum(), 'integer') . '
		)
		UNION(
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
			AND tst_active.user_fi != ' . $this->db->quote($a_user_id, 'integer') . '
			AND round(reached_points/max_points*100) <=
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
			ORDER BY round(reached_points/max_points*100) ASC
			LIMIT 0, ' . $this->db->quote($this->object->getHighscoreTopNum(), 'integer') . '
		)
		ORDER BY round(reached_points/max_points*100) DESC, tstamp ASC
		LIMIT 0, ' . $this->db->quote($this->object->getHighscoreTopNum(), 'integer') . '
		'
        );

        $i = $own_placement - ($better_participants >= $this->object->getHighscoreTopNum()
                ? $this->object->getHighscoreTopNum() : $better_participants);

        if ($i > 1) {
            yield $this->buildEmptyItem();
        }

        while ($row = $this->db->fetchAssoc($result)) {
            $i++;
            yield $row;
        }

        if ($number_total > $i) {
            yield $this->buildEmptyItem();
        }
    }

    public function getUserToplistByWorkingtime(int $a_user_id): \Generator
    {
        $a_test_ref_id = $this->object->getRefId();
        $result = $this->db->query(
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
        );
        $row = $this->db->fetchAssoc($result);
        $better_participants = $row['cnt'];
        $own_placement = $better_participants + 1;

        $result = $this->db->query(
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
        );
        $row = $this->db->fetchAssoc($result);
        $number_total = $row['cnt'];

        $result = $this->db->query(
            '
		SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage ,
			tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
		FROM object_reference
		INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
		INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
		INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
		INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
			AND tst_pass_result.pass = tst_result_cache.pass
		INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi

		WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
		AND tst_active.user_fi = ' . $this->db->quote($a_user_id, 'integer') . '

		UNION(
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
			FROM object_reference
			INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
			INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
			INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
			INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
				AND tst_pass_result.pass = tst_result_cache.pass
			INNER JOIN usr_data ON usr_data.usr_id = tst_active.user_fi
			WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
			AND tst_active.user_fi != ' . $this->db->quote($a_user_id, 'integer') . '
			AND workingtime >=
			(
				SELECT tst_pass_result.workingtime
				FROM object_reference
				INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
				INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
				INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
				INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
					AND tst_pass_result.pass = tst_result_cache.pass
				WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
				AND tst_active.user_fi = ' . $this->db->quote($a_user_id, 'integer') . '
			)
			ORDER BY workingtime DESC
			LIMIT 0, ' . $this->db->quote($this->object->getHighscoreTopNum(), 'integer') . '
		)
		UNION(
			SELECT tst_result_cache.*, round(reached_points/max_points*100) as percentage,
				tst_pass_result.workingtime, usr_id, usr_data.firstname, usr_data.lastname
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
				SELECT tst_pass_result.workingtime
				FROM object_reference
				INNER JOIN tst_tests ON object_reference.obj_id = tst_tests.obj_fi
				INNER JOIN tst_active ON tst_tests.test_id = tst_active.test_fi
				INNER JOIN tst_result_cache ON tst_active.active_id = tst_result_cache.active_fi
				INNER JOIN tst_pass_result ON tst_active.active_id = tst_pass_result.active_fi
					AND tst_pass_result.pass = tst_result_cache.pass
				WHERE object_reference.ref_id = ' . $this->db->quote($a_test_ref_id, 'integer') . '
				AND tst_active.user_fi = ' . $this->db->quote($a_user_id, 'integer') . '
			)
			ORDER BY workingtime DESC
			LIMIT 0, ' . $this->db->quote($this->object->getHighscoreTopNum(), 'integer') . '
		)
		ORDER BY workingtime ASC
		LIMIT 0, ' . $this->db->quote($this->object->getHighscoreTopNum(), 'integer') . '
		'
        );

        $i = $own_placement - (($better_participants >= 3) ? 3 : $better_participants);

        if ($i > 1) {
            yield $this->buildEmptyItem();
        }

        while ($row = $this->db->fetchAssoc($result)) {
            $i++;
            yield $row;
        }

        if ($number_total > $i) {
            yield $this->buildEmptyItem();
        }
    }

    private function buildEmptyItem(): array
    {
        return [
            'rank' => '...',
            'is_actor' => false,
            'participant' => '',
            'achieved' => '',
            'score' => '',
            'percentage' => '',
            'hints' => '',
            'time' => ''
        ];
    }
}
