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

namespace ILIAS\Tracking\View\ProgressBlock\Settings;

use ilDBInterface;
use ilDBConstants;

class Repository implements RepositoryInterface
{
    public function __construct(protected ilDBInterface $db)
    {
    }

    public function isBlockShownForObject(int $obj_id): bool
    {
        $query = 'SELECT show_block FROM ut_progress_block WHERE obj_id = ' .
            $this->db->quote($obj_id, ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            return (bool) ($row['show_block'] ?? false);
        }
        return false;
    }

    public function setShowBlockForObject(int $obj_id, bool $show): void
    {
        $query = 'INSERT INTO ut_progress_block (obj_id, show_block) VALUES (' .
            $this->db->quote($obj_id, ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($show, ilDBConstants::T_INTEGER) .
            ') ON DUPLICATE KEY UPDATE show_block = ' .
            $this->db->quote($show, ilDBConstants::T_INTEGER);

        $this->db->manipulate($query);
    }
}
