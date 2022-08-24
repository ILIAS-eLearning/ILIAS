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
 ********************************************************************
 */

/**
 * Class ilDclRatingFieldModel
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRatingFieldModel extends ilDclBaseFieldModel
{
    public function getRecordQuerySortObject(
        string $direction = "asc",
        bool $sort_by_status = false
    ): ilDclRecordQueryObject {
        // FSX Bugfix 0015735: The average is multiplied with 10000 and added to the amount of votes
        $join_str = "LEFT JOIN (SELECT (ROUND(AVG(rating), 1) * 10000 + COUNT(rating)) as rating, obj_id FROM il_rating GROUP BY obj_id) AS average ON average.obj_id = record.id";
        $select_str = " average.rating AS field_{$this->getId()},";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setSelectStatement($select_str);
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setOrderStatement("field_{$this->getId()} " . $direction);

        return $sql_obj;
    }

    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!$sort_field instanceof $this) {
            $join_str = "LEFT JOIN (SELECT (ROUND(AVG(rating), 1) * 10000 + COUNT(rating)) as rating, obj_id FROM il_rating GROUP BY obj_id) AS average ON average.obj_id = record.id";
        }
        // FSX Bugfix 0015735: The average is multiplied with 10000 and added to the amount of votes
        $where_additions = " AND average.rating >= " . $ilDB->quote($filter_value * 10000, 'integer');

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setWhereStatement($where_additions);
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }
}
