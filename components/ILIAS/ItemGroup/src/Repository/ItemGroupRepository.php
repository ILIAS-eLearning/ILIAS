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

namespace ILIAS\ItemGroup\Repository;

use ilDBConstants;
use ilDBInterface;

class ItemGroupRepository
{
    public function __construct(
        private readonly ilDBInterface $db,
    ) {
    }

    /**
     * @param int[] $item_ref_ids
     */
    public function removeItems(array $item_ref_ids, ?int $item_group_id = null): void
    {
        if ($item_ref_ids === []) {
            return;
        }

        $query = "DELETE FROM item_group_item WHERE ({$this->db->in('item_ref_id', $item_ref_ids, false, ilDBConstants::T_INTEGER)})";

        if (is_int($item_group_id) && $item_group_id > 0) {
            $query .= " AND item_group_id = {$this->db->quote($item_group_id, ilDBConstants::T_INTEGER)}";
        }

        $this->db->manipulate($query);
    }
}
