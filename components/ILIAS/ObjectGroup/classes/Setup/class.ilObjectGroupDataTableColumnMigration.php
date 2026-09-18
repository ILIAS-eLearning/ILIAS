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

namespace ILIAS\ObjectGroup\Setup;

use ilDatabaseUpdateStepsExecutedObjective;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use RuntimeException;

class ilObjectGroupDataTableColumnMigration implements Migration
{
    private ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Removes object group data table columns hide_title and behaviour.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseUpdateStepsExecutedObjective(new ilObjectGroupDBUpdateSteps()),
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

        $table = $this->dataTableName();

        if ($this->db->tableColumnExists($table, 'hide_title')) {
            $this->db->dropTableColumn($table, 'hide_title');
        }

        if ($this->db->tableColumnExists($table, 'behaviour')) {
            $this->db->dropTableColumn($table, 'behaviour');
        }
    }

    public function getRemainingAmountOfSteps(): int
    {
        if ($this->isDataMigrationRequired()) {
            throw new RuntimeException(sprintf('The %s migration must be executed first.', ilObjectGroupDisplayMigration::class));
        }

        $table = $this->dataTableName();

        return (int) (
            $this->db->tableColumnExists($table, 'hide_title')
            || $this->db->tableColumnExists($table, 'behaviour')
        );
    }

    private function isDataMigrationRequired(): bool
    {
        if (
            !$this->db->tableColumnExists($this->dataTableName(), 'hide_title')
            || !$this->db->tableColumnExists($this->dataTableName(), 'behaviour')
        ) {
            return false;
        }

        $result = $this->db->queryF(
            'SELECT COUNT(id) AS cnt FROM ' . $this->dataTableName() . ' WHERE hide_title <> %s AND behaviour <> %s',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [ilObjectGroupDisplayMigration::MIGRATED_MARKER, ilObjectGroupDisplayMigration::MIGRATED_MARKER]
        );

        return ($this->db->fetchObject($result)?->cnt ?? 0) > 0;
    }

    private function dataTableName(): string
    {
        return $this->db->tableExists('obgr_data') ? 'obgr_data' : 'itgr_data';
    }
}
