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

namespace ILIAS\Help\Setup;

class ilHelpDB10HotfixSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->modifyTableColumn('help_map', 'component', [
            'type' => 'text',
            'length' => 10,
            'default' => ""
        ]);
        $this->db->modifyTableColumn('help_map', 'screen_id', [
            'type' => 'text',
            'length' => 100,
            'default' => ""
        ]);
        $this->db->modifyTableColumn('help_map', 'screen_sub_id', [
            'type' => 'text',
            'length' => 100,
            'default' => ""
        ]);
    }

}
