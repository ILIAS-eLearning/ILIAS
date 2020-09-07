<?php

/**
 * Class ilDclRatingFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRatingFieldModel extends ilDclBaseFieldModel
{

    /**
     * Returns a query-object for building the record-loader-sql-query
     *
     * @param string  $direction
     * @param boolean $sort_by_status The specific sort object is a status field
     *
     * @return null|ilDclRecordQueryObject
     */
    public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false)
    {
        // FSX Bugfix 0015735: The average is multiplied with 10000 and added to the amount of votes
        $join_str = "LEFT JOIN (SELECT (ROUND(AVG(rating), 1) * 10000 + COUNT(rating)) as rating, obj_id FROM il_rating GROUP BY obj_id) AS average ON average.obj_id = record.id";
        $select_str = " average.rating AS field_{$this->getId()},";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setSelectStatement($select_str);
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setOrderStatement("field_{$this->getId()} " . $direction);

        return $sql_obj;
    }


    /**
     * Returns a query-object for building the record-loader-sql-query
     *
     * @param string $filter_value
     *
     * @return null|ilDclRecordQueryObject
     */
    public function getRecordQueryFilterObject($filter_value = "", ilDclBaseFieldModel $sort_field = null)
    {
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
