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

namespace ILIAS\ObjectGroup\Repository;

use ilDBConstants;
use ilDBInterface;

class ObjectGroupRepository
{
    public function __construct(
        private readonly ilDBInterface $db,
    ) {
    }

    /**
     * @param int[] $object_ref_ids
     */
    public function removeItems(array $object_ref_ids, ?int $object_group_id = null): void
    {
        if ($object_ref_ids === []) {
            return;
        }

        $query = "DELETE FROM object_group_item WHERE ({$this->db->in('object_ref_id', $object_ref_ids, false, ilDBConstants::T_INTEGER)})";

        if (is_int($object_group_id) && $object_group_id > 0) {
            $query .= " AND object_group_id = {$this->db->quote($object_group_id, ilDBConstants::T_INTEGER)}";
        }

        $this->db->manipulate($query);
    }
}
