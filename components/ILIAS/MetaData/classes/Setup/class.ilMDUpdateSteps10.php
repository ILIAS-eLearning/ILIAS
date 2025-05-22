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

class ilMDUpdateSteps10 implements ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Add a new table for exposed OER metadata.
     */
    public function step_1(): void
    {
        if (!$this->db->tableExists('il_meta_oer_exposed')) {
            $this->db->createTable(
                'il_meta_oer_exposed',
                [
                    'obj_id' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 4
                    ],
                    'identifier' => [
                        'type' => ilDBConstants::T_TEXT,
                        'notnull' => true,
                        'length' => 64
                    ],
                    'datestamp' => [
                        'type' => ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'length' => 8
                    ],
                    'metadata' => [
                        'type' => ilDBConstants::T_CLOB,
                        'notnull' => true
                    ]
                ]
            );
            $this->db->addPrimaryKey('il_meta_oer_exposed', ['obj_id']);
        }
    }
}
