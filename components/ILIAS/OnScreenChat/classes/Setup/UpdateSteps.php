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

namespace ILIAS\OnScreenChat\Setup;

use ilDatabaseUpdateSteps;
use ilDBInterface;
use ilDBConstants;

class UpdateSteps implements ilDatabaseUpdateSteps
{
    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $replace = [
            '&lt;' => '<',
            '&gt;' => '>',
            '&amp;' => '&',
            '&quot;' => '"',
        ];

        $replaced_message = 'message';
        foreach ($replace as $from => $to) {
            $replaced_message = sprintf(
                'REPLACE(%s, %s, %s)',
                $replaced_message,
                $this->db->quote($from, ilDBConstants::T_TEXT),
                $this->db->quote($to, ilDBConstants::T_TEXT)
            );
        }

        $this->db->manipulate('UPDATE osc_messages SET message = ' . $replaced_message);
    }
}
