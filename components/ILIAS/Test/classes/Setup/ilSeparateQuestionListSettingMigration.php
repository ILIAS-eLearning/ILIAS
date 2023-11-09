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
use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ilDBInterface;
use Exception;
use ILIAS\Setup\CLI\IOWrapper;

class ilSeparateQuestionListSettingMigration implements Setup\Migration
{
    private const DEFAULT_AMOUNT_OF_STEPS = 1;
    private ilDBInterface $db;

    /**
     * @var IOWrapper
     */
    private mixed $io;

    public function getLabel(): string
    {
        return "Update QuestionList Settings";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective()
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
        $this->db->manipulate(
            'UPDATE tst_tests SET show_questionlist = 1 WHERE usr_pass_overview_mode > 0'
        );

        $this->db->manipulate(
            'UPDATE tst_tests SET show_questionlist = 0 WHERE usr_pass_overview_mode = 0'
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $result = $this->db->query(
            "SELECT count(*) as cnt FROM tst_tests WHERE show_questionlist is NULL"
        );
        $row = $this->db->fetchAssoc($result);

        return (int) $row['cnt'] ?? 0;
    }
}
