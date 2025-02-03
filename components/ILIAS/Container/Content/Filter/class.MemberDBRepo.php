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

namespace ILIAS\Container\Content\Filter;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class MemberDBRepo
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function filterObjIdsByTutorialSupport(array $obj_ids, string $lastname): array
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT DISTINCT(obj_id) FROM obj_members m JOIN usr_data u ON (u.usr_id = m.usr_id) " .
            " WHERE  " . $db->in("m.obj_id", $obj_ids, false, "integer") .
            " AND " . $db->like("u.lastname", "text", $lastname) .
            " AND m.contact = %s",
            array("integer"),
            array(1)
        );
        $result_obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $result_obj_ids[] = $rec["obj_id"];
        }
        return array_intersect($obj_ids, $result_obj_ids);
    }
}
