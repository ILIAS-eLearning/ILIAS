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

namespace ILIAS\ILIASObject\Setup;

class DBUpdateSteps11 implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('object_translation', 'master_lang')) {
            $this->db->addTableColumn(
                'object_translation',
                'lang_master',
                [
                    'type' => \ilDBConstants::T_INTEGER,
                    'notnull' => true,
                    'length' => 1,
                    'default' => 0
                ]
            );
        }

        $this->db->modifyTableColumn(
            'object_translation',
            'lang_code',
            [
                'type' => \ilDBConstants::T_TEXT,
                'notnull' => true,
                'length' => 64
            ]
        );
    }
}
