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

use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilForumThreadAndPostCounterMigration implements Migration
{
    private ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Recalculation of forum thread and posting counters, which may be incorrect in copied forums.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $this->db->setLimit(1);
        $res = $this->db->query(
            'SELECT top_pk '
            . 'FROM frm_data '
            . 'WHERE frm_data.top_num_threads != (SELECT COUNT(*) FROM frm_threads WHERE frm_threads.thr_top_fk = frm_data.top_pk) '
            . 'OR frm_data.top_num_posts != (SELECT COUNT(*) FROM frm_posts WHERE frm_posts.pos_top_fk = frm_data.top_pk)',
        );

        $row = $this->db->fetchAssoc($res);
        if (!isset($row['top_pk'])) {
            return;
        }

        $this->db->manipulateF(
            'UPDATE frm_data SET ' .
            'top_num_threads = (SELECT COUNT(*) FROM frm_threads WHERE frm_threads.thr_top_fk = frm_data.top_pk), ' .
            'top_num_posts = (SELECT COUNT(*) FROM frm_posts WHERE frm_posts.pos_top_fk = frm_data.top_pk) ' .
            'WHERE top_pk = %s',
            ['integer'],
            [(int) $row['top_pk']],
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $res = $this->db->query(
            'SELECT COUNT(*) cnt '
            . 'FROM frm_data '
            . 'WHERE frm_data.top_num_threads != (SELECT COUNT(*) FROM frm_threads WHERE frm_threads.thr_top_fk = frm_data.top_pk) '
            . 'OR frm_data.top_num_posts != (SELECT COUNT(*) FROM frm_posts WHERE frm_posts.pos_top_fk = frm_data.top_pk)',
        );
        $row = $this->db->fetchAssoc($res);

        return (int) ($row['cnt'] ?? 0);
    }
}
