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

namespace ILIAS\Repository\Deletion;

use ilDBInterface;

class TreeDBRepo
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function getTrashedSubtrees(int $ref_id): array
    {
        $db = $this->db;

        // this queries for trash items in the trash of deleted nodes
        $q = 'SELECT tree FROM tree WHERE parent = ' . $db->quote($ref_id, \ilDBConstants::T_INTEGER) .
            ' AND tree < 0 ' .
            ' AND tree = -1 * child';

        $r = $db->query($q);

        $tree_ids = [];
        while ($row = $db->fetchObject($r)) {
            $tree_ids = (int) $row->tree;
        }
        return $tree_ids;
    }

}
