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

namespace ILIAS\Survey\Sequence;

use ILIAS\Survey\InternalDataService;

class SequenceDBRepository
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    protected function count(int $survey_id): int
    {
        $set = $this->db->queryF(
            "SELECT count(*) cnt FROM svy_svy_qst " .
            " WHERE survey_fi = %s ",
            ["integer"],
            [$survey_id]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return (int) $rec["cnt"];
        }
        return 0;
    }

    public function insert(int $survey_id, int $svy_question_id): int
    {
        $order_nr = $this->count($survey_id);
        $next_id = $this->db->nextId('svy_svy_qst');
        $this->db->manipulateF(
            "INSERT INTO svy_svy_qst (survey_question_id, survey_fi," .
            "question_fi, sequence, tstamp) VALUES (%s, %s, %s, %s, %s)",
            array('integer', 'integer', 'integer', 'integer', 'integer'),
            array($next_id, $survey_id, $svy_question_id, $order_nr, time())
        );
        return $next_id;
    }
}
