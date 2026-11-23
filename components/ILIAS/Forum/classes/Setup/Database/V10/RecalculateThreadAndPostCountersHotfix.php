<?php
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
            'UPDATE frm_data '
            . 'INNER JOIN ('
            . 'SELECT d.top_pk, '
            . '(SELECT COUNT(*) FROM frm_threads WHERE thr_top_fk = d.top_pk) AS num_threads, '
            . '(SELECT COUNT(*) FROM frm_posts WHERE pos_top_fk = d.top_pk) AS num_posts '
            . 'FROM frm_data d'
            . ') counted ON counted.top_pk = frm_data.top_pk '
            . 'SET frm_data.top_num_threads = counted.num_threads, '
            . 'frm_data.top_num_posts = counted.num_posts '
            . 'WHERE frm_data.top_num_threads != counted.num_threads '
            . 'OR frm_data.top_num_posts != counted.num_posts',
        );
    }
}
