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

namespace ILIAS\Export\HTML\Setup;

use ilDatabaseUpdateSteps;
use ilDBConstants;
use ilDBInterface;

class DBHTMLExportUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Create new export file table
     */
    public function step_1(): void
    {
        if (!$this->db->tableExists("export_files_html")) {
            $this->db->createTable("export_files_html", [
                'object_id' => [
                    'type' => 'integer',
                    'length' => 8,
                    'default' => 0,
                    'notnull' => true
                ],
                'rid' => [
                    'type' => 'text',
                    'length' => 64,
                    'default' => '',
                    'notnull' => true
                ],
                'timestamp' => [
                    'type' => 'timestamp',
                    'notnull' => false
                ],
                'type' => [
                    'type' => 'text',
                    'length' => 10,
                    'default' => '',
                    'notnull' => true
                ],
            ]);
            $this->db->addPrimaryKey("export_files_html", ["object_id", "rid"]);
        }
    }

    public function step_2(): void
    {
    }

    public function step_3(): void
    {
    }
}
