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

namespace ILIAS\LegalDocuments\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;
use ilDBConstants;

class UpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $database;

    public function prepare(ilDBInterface $database): void
    {
        $this->database = $database;
    }

    public function step_1(): void
    {
        $this->renameTables([
            'tos_documents' => 'ldoc_documents',
            'tos_criterion_to_doc' => 'ldoc_criteria',
            'tos_acceptance_track' => 'ldoc_acceptance_track',
            'tos_versions' => 'ldoc_versions',
        ]);

        $this->ensureColumn('ldoc_documents', 'provider', [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => true,
            'default' => 'tos',
            'length' => 255,
        ]);

        $this->ensureColumn('ldoc_documents', 'hash', [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => true,
            'default' => '',
            'length' => 255,
        ]);

        foreach (['ldoc_documents', 'ldoc_versions'] as $table) {
            $this->ensureColumn($table, 'type', [
                'type' => ilDBConstants::T_TEXT,
                'notnull' => true,
                'default' => 'html',
                'length' => 255,
            ]);
        }
    }

    public function step_2(): void
    {
        $this->database->manipulate('UPDATE ldoc_documents SET `text` = "" WHERE `text` IS NULL');
        $this->database->manipulate('ALTER TABLE ldoc_documents MODIFY COLUMN `text` longtext NOT NULL DEFAULT ""');
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function ensureColumn(string $table, string $name, array $attributes): void
    {
        if (!$this->database->tableColumnExists($table, $name)) {
            $this->database->addTableColumn($table, $name, $attributes);
        }
    }

    /**
     * @param array<string, $string> $tables
     */
    private function renameTables(array $tables): void
    {
        foreach ($tables as $old => $new) {
            if (!$this->database->tableExists($new)) {
                $this->database->renameTable($old, $new);
            }
        }
    }
}
