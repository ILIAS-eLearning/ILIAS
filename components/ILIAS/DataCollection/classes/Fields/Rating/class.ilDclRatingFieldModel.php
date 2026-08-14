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

class ilDclRatingFieldModel extends ilDclBaseFieldModel
{
    public function getRecordQuerySortObject(
        string $direction = "asc",
        bool $sort_by_status = false
    ): ilDclRecordQueryObject {
        $join_str = "LEFT JOIN (SELECT (ROUND(AVG(rating), 1) * 10000 + COUNT(rating)) as rating, obj_id FROM il_rating GROUP BY obj_id) AS average ON average.obj_id = record.id";
        $select_str = " average.rating AS field_{$this->getId()},";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setSelectStatement($select_str);
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setOrderStatement("field_{$this->getId()} " . $direction . ", ID ASC");

        return $sql_obj;
    }

    public function getRecordQueryFilterObject(
        mixed $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {
        if (!$sort_field instanceof $this) {
            $join_str = "LEFT JOIN (SELECT (ROUND(AVG(rating), 1) * 10000 + COUNT(rating)) as rating, obj_id FROM il_rating GROUP BY obj_id) AS average ON average.obj_id = record.id";
        }
        $where_additions = " AND average.rating >= " . $this->db->quote($filter_value * 10000, 'integer');

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setWhereStatement($where_additions);

        if (isset($join_str)) {
            $sql_obj->setJoinStatement($join_str);
        }

        return $sql_obj;
    }
}
