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

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class LSOMigratePageIdsIntro implements Setup\Migration
{
    private const DEFAULT_AMOUNT_OF_STEPS = 1000;
    private const QUERY = "SELECT page_id, parent_id, parent_type, count(page_id) AS cnt" . PHP_EOL
        . "FROM page_object" . PHP_EOL
        . "WHERE page_id = (parent_id * -1)" . PHP_EOL
        . "AND parent_type = 'cont'";

    private ilDBInterface $db;

    public function getLabel(): string
    {
        return "Update IDs of intro pages";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
    }

    /**
     * @throws Exception
     */
    public function step(Environment $environment): void
    {
        $result = $this->db->query(self::QUERY . ' LIMIT 1');
        $row = $this->db->fetchAssoc($result);
        $query = 'UPDATE page_object' . PHP_EOL
            . "SET page_id = parent_id, parent_type = 'lsoi'" . PHP_EOL
            . "WHERE page_id = " . $row['page_id'] . PHP_EOL
            . "AND parent_id = " . $row['parent_id'] . PHP_EOL
            . "AND parent_type = " . $this->db->quote($row['parent_type'], 'text');
        $this->db->manipulate($query);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result = $this->db->query(self::QUERY);
        $row = $this->db->fetchAssoc($result);

        return (int) $row['cnt'];
    }
}
