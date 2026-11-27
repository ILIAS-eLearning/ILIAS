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

namespace ILIAS\Forum\Setup\Database\V10;

use ilDatabaseUpdateSteps;
use ilDBInterface;

class RecalculateThreadAndPostCountersHotfix implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulate(
            <<<'SQL'
            UPDATE frm_data d
            LEFT JOIN (SELECT thr_top_fk, COUNT(*) AS c FROM frm_threads GROUP BY thr_top_fk) t
              ON t.thr_top_fk = d.top_pk
            LEFT JOIN (SELECT pos_top_fk, COUNT(*) AS c FROM frm_posts GROUP BY pos_top_fk) p
              ON p.pos_top_fk = d.top_pk
            SET d.top_num_threads = COALESCE(t.c, 0),
                d.top_num_posts = COALESCE(p.c, 0)
            WHERE d.top_num_threads != COALESCE(t.c, 0)
               OR d.top_num_posts != COALESCE(p.c, 0);
            SQL,
        );
    }
}
