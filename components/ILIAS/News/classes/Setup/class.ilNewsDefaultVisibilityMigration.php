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

class ilNewsDefaultVisibilityMigration implements Migration
{
    private ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Remove local default visibility settings for news';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return Migration::INFINITE;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $this->db->manipulateF(
            'DELETE FROM il_block_setting WHERE type = %s AND setting = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            ['news', 'default_visibility']
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        return (int) ($this->db->fetchAssoc(
            $this->db->queryF(
                'SELECT COUNT(*) AS count FROM il_block_setting WHERE type = %s AND setting = %s',
                [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
                ['news', 'default_visibility']
            )
        )['count'] ?? 0);
    }
}
