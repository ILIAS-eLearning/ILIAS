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

namespace ILIAS\Mail\Setup\Migration;

use ilDBConstants;
use ilDBInterface;
use ilDBStatement;
use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ILIAS\Setup\CLI\IOWrapper;
use ilDatabaseUpdatedObjective;
use ReflectionClass;

class MailOutboxMigration implements Migration
{
    public const int NUMBER_OF_STEPS = 10;
    public const int NUMBER_OF_PATHS_PER_STEP = 10;

    private ilDBStatement $ps_in_fold_entry;
    private ilDBStatement $ps_in_tree_entry;
    private ilDBStatement $ps_up_tree_entry;
    private ?IOWrapper $io = null;
    private ilDBInterface $db;

    public function getLabel(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::NUMBER_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->ps_in_fold_entry = $this->db->prepareManip(
            'INSERT INTO mail_obj_data (obj_id, user_id, title, m_type) VALUES(?, ?, ?, ?)',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT]
        );

        $this->ps_in_tree_entry = $this->db->prepareManip(
            'INSERT INTO mail_tree (tree, child, parent, lft, rgt, depth) VALUES(?, ?, ?, ?, ?, ?)',
            [
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER
            ]
        );

        $this->ps_up_tree_entry = $this->db->prepareManip(
            <<<SQL
            UPDATE mail_tree 
            SET lft = CASE WHEN lft > ? THEN lft + 2 ELSE lft END, 
                rgt = CASE WHEN rgt > ? THEN rgt + 2 ELSE rgt END 
            WHERE tree = ?
            SQL,
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER]
        );
    }

    public function step(Environment $environment): void
    {
        $this->db->setLimit(self::NUMBER_OF_PATHS_PER_STEP);
        $sql = <<<SQL
            SELECT mail_obj_data.*, mail_tree.*
            FROM usr_data AS ud
            INNER JOIN  mail_obj_data ON mail_obj_data.user_id = ud.usr_id  AND mail_obj_data.title = %s AND mail_obj_data.m_type = %s
            INNER JOIN mail_tree ON mail_tree.child = mail_obj_data.obj_id AND mail_tree.tree = ud.usr_id
            LEFT JOIN mail_obj_data AS outbox ON outbox.user_id = ud.usr_id  AND outbox.title = %s
            WHERE outbox.obj_id IS NULL
        SQL;

        $res = $this->db->queryF(
            $sql,
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['d_drafts', 'drafts', 'e_outbox']
        );
        while ($draft_folder_row = $this->db->fetchAssoc($res)) {
            $outbox_folder_id = $this->db->nextId('mail_obj_data');
            $this->db->execute(
                $this->ps_in_fold_entry,
                [$outbox_folder_id, (int) $draft_folder_row['user_id'], 'e_outbox', 'outbox']
            );
            $this->inform(
                "Created outbox folder (obj_id: $outbox_folder_id) for user_id: " . $draft_folder_row['user_id']
            );

            $right = $draft_folder_row['rgt'];
            $lft = $right + 1 ;
            $rgt = $right + 2;

            $this->db->execute($this->ps_up_tree_entry, [$right, $right, $draft_folder_row['user_id']]);
            $this->db->execute(
                $this->ps_in_tree_entry,
                [
                    $draft_folder_row['user_id'],
                    $outbox_folder_id,
                    $draft_folder_row['parent'],
                    $lft,
                    $rgt,
                    2
                ]
            );
            $this->inform(
                "Inserted outbox folder (obj_id: $outbox_folder_id) into tree for user_id: " . $draft_folder_row['user_id']
            );
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        $sql = <<<SQL
            SELECT COUNT(*) AS paths
            FROM usr_data AS ud
            INNER JOIN  mail_obj_data ON mail_obj_data.user_id = ud.usr_id  AND mail_obj_data.title = %s AND mail_obj_data.m_type = %s
            INNER JOIN mail_tree ON mail_tree.child = mail_obj_data.obj_id AND mail_tree.tree = ud.usr_id
            LEFT JOIN mail_obj_data AS outbox ON outbox.user_id = ud.usr_id  AND outbox.title = %s
            WHERE outbox.obj_id IS NULL
        SQL;

        $res = $this->db->queryF(
            $sql,
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['d_drafts', 'drafts', 'e_outbox']
        );
        $row = $this->db->fetchAssoc($res);
        $paths = (int) ($row['paths'] ?? 0);
        $num_steps = (int) ceil($paths / self::NUMBER_OF_PATHS_PER_STEP);

        $this->inform(
            "Remaining outbox folders to create: $paths / Estimated steps remaining: $num_steps",
            true
        );

        return $num_steps;
    }

    private function inform(string $text, bool $force = false): void
    {
        if ($this->io === null || (!$force && !$this->io->isVerbose())) {
            return;
        }

        $this->io->inform($text);
    }

    private function error(string $text): void
    {
        if ($this->io === null) {
            return;
        }

        $this->io->error($text);
    }
}
