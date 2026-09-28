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

namespace ILIAS\Logging\Setup\Steps;

use ilDatabaseUpdateSteps;
use ilDBInterface;

class DBUpdateSteps12 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * Remove default entries, all components/plugins now get a log level.
     */
    public function step_1(): void
    {
        if ($this->db->tableExists('log_components')) {
            $this->db->manipulate(
                "DELETE FROM log_components WHERE log_level = 0 OR log_level IS NULL"
            );
        }
    }

    /**
     * Remove the root logger log level.
     */
    public function step_2(): void
    {
        if ($this->db->tableExists('log_components')) {
            $this->db->manipulate(
                "DELETE FROM log_components WHERE component_id = 'log_root'"
            );
        }
    }
}
