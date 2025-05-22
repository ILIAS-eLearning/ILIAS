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

namespace ILIAS\Taxonomy\Usage;

class UsageDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
    }

    public function getUsageOfObject(int $obj_id, bool $include_titles = false): array
    {
        $set = $this->db->query(
            "SELECT tax_id FROM tax_usage " .
            " WHERE obj_id = " . $this->db->quote($obj_id, "integer")
        );
        $tax = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            if (!$include_titles) {
                $tax[] = (int) $rec["tax_id"];
            } else {
                $tax[] = array("tax_id" => (int) $rec["tax_id"],
                               "title" => \ilObject::_lookupTitle((int) $rec["tax_id"])
                );
            }
        }
        if ($include_titles) {
            $tax = \ilArrayUtil::sortArray($tax, "title");
        }
        return $tax;
    }

}
