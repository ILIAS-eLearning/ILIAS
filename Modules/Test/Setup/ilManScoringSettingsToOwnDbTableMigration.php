<?php declare(strict_types=1);
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

namespace ILIAS\Test\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ilDBInterface;
use Exception;
use ILIAS\Setup\CLI\IOWrapper;

/**
 * Class ilManScoringSettingsToOwnDbTableMigration
 * @package ILIAS\Test\Setup
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilManScoringSettingsToOwnDbTableMigration implements Setup\Migration
{
    private ilDBInterface $db;
    private const TABLE_NAME = "manscoring_done";
    /**
     * @var IOWrapper
     */
    private mixed $io;

    private function manScoringDoneEntryExists(int $activeId) : bool
    {
        $result = $this->db->queryF(
            "SELECT active_id FROM manscoring_done WHERE active_id = %s",
            ["integer"],
            [$activeId]
        );

        return $result->numRows() === 1;
    }

    public function getLabel() : string
    {
        return "Migrate manual scoring done setting from ilSettings db table to own table for improved performance";
    }

    public function getDefaultAmountOfStepsPerRun() : int
    {
        return 10;
    }

    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function prepare(Environment $environment) : void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $this->io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
    }

    /**
     * @throws Exception
     */
    public function step(Environment $environment) : void
    {
        $result = $this->db->query("SELECT keyword, value FROM `settings` WHERE keyword LIKE 'manscoring_done_%'");

        /**
         * @var array<int, string>
         *     <active_id, reason for failure>
         */
        $failed = [];

        /**
         * @var array<int, int>
         *     <index, active_id>y
         */
        $success = [];
        $totalCount = 0;
        while ($row = $this->db->fetchAssoc($result)) {
            $totalCount++;

            $keyword = $row["keyword"];
            $match = [];
            if (!preg_match('/manscoring_done_(\d+)$/', $keyword, $match)) {
                continue;
            }
            $activeId = $match[1];
            if (!is_numeric($activeId)) {
                continue;
            }

            $activeId = (int) $activeId;

            if ($this->manScoringDoneEntryExists($activeId)) {
                $failed[$activeId] = "Entry with active_id '$activeId' already exists in table '" . self::TABLE_NAME . "'.";
                continue;
            }

            if ((int) $this->db->manipulateF("INSERT INTO " . self::TABLE_NAME . " (active_id, done) VALUES (%s, %s)",
                    ["integer", "integer"],
                    [$activeId, (int) $row["value"]]
                ) !== 1) {
                $failed[$activeId] = "Error occurred while trying to insert manscoring done status into new table ' " . self::TABLE_NAME . "'.";
                continue;
            }

            if ((int) $this->db->manipulateF("DELETE FROM `settings` WHERE keyword = %s",
                    ["text"],
                    [$keyword]
                ) !== 1) {
                $failed[$activeId] = "Error occurred while trying to delete manscoring done status '$keyword' from old table 'settings'.";
                continue;
            }

            $success[] = $activeId;
        }

        //To get into new line for cleaner error reporting.
        $this->io->text("");
        foreach ($failed as $active => $reason) {
            $this->io->error($reason);
        }

        $successCount = count($success);
        $failedCount = count($failed);

        $this->io->success("Successfully migrated $successCount of $totalCount ($failedCount failed) entries from table 'settings' to table '" . self::TABLE_NAME . "'.");
    }

    public function getRemainingAmountOfSteps() : int
    {
        return 1;
    }
}