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

namespace ILIAS\ItemGroup\Setup;

use ilDatabaseUpdateStepsExecutedObjective;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use RuntimeException;

class ilItemGroupDataTableColumnMigration implements Migration
{
    private ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Removes item group data table columns hide_title and behaviour.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseUpdateStepsExecutedObjective(new ilItemGroupDBUpdateSteps())
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        if ($this->isDataMigrationRequired()) {
            return;
        }

        if ($this->db->tableColumnExists('itgr_data', 'hide_title')) {
            $this->db->dropTableColumn('itgr_data', 'hide_title');
        }

        if ($this->db->tableColumnExists('itgr_data', 'behaviour')) {
            $this->db->dropTableColumn('itgr_data', 'behaviour');
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        if ($this->isDataMigrationRequired()) {
            throw new RuntimeException(sprintf('The %s migration must be executed first.', ilItemGroupDisplayMigration::class));
        }

        return (int) (
            $this->db->tableColumnExists('itgr_data', 'hide_title')
            || $this->db->tableColumnExists('itgr_data', 'behaviour')
        );
    }

    private function isDataMigrationRequired(): bool
    {
        $result = $this->db->queryF(
            'SELECT COUNT(id) AS cnt FROM itgr_data WHERE hide_title <> %s AND behaviour <> %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [ilItemGroupDisplayMigration::MIGRATED_MARKER, ilItemGroupDisplayMigration::MIGRATED_MARKER]
        );

        return ($this->db->fetchObject($result)?->cnt ?? 0) > 0;
    }
}
