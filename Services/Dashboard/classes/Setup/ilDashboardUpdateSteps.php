<?php declare(strict_types=1);

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

namespace ILIAS\Dashboard\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;

class ilDashboardUpdateSteps implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db) : void
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        $this->db->manipulateF('DELETE FROM settings WHERE keyword = %s', ['text'], ['enable_block_moving']);
        $this->db->manipulate('DELETE FROM il_block_setting WHERE ' . $this->db->like('type', 'text', 'pd%'));
    }
}
