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

namespace ILIAS\AdvancedMetaData\Setup;

use ilDBConstants;
use ILIAS\Setup;

class DBUpdateSteps10 implements \ilDatabaseUpdateSteps
{
    private \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('adv_mdf_enum', 'position')) {
            $field_infos = [
                'type' => 'integer',
                'length' => 4,
                'notnull' => false,
                'default' => null
            ];
            $this->db->addTableColumn('adv_mdf_enum', 'position', $field_infos);
        }
    }

    public function step_2(): void
    {
        $table_name = "adv_md_record_files";
        if ($this->db->tableExists($table_name)) {
            return;
        }
        $this->db->createTable($table_name, [
            'object_id' => [
                'type' => ilDBConstants::T_INTEGER,
                'length' => 8,
                'default' => 0,
                'notnull' => true
            ],
            'rid' => [
                'type' => ilDBConstants::T_TEXT,
                'length' => 64,
                'default' => '',
                'notnull' => true
            ],
            'is_global' => [
                'type' => ilDBConstants::T_INTEGER,
                'length' => 1,
                'default' => 0,
                'notnull' => true
            ]
        ]);
        $this->db->addPrimaryKey($table_name, ["object_id", "rid", "is_global"]);
    }
}
