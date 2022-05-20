<?php

namespace ILIAS\MyStaff\Courses\ShowUser;

use ILIAS\MyStaff\ListCourses\ilMStListCourses;

/**
 * Class ilMStShowUserCourses
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStShowUserCourses extends ilMStListCourses
{
    /**
     * @param array  $arr_filter
     * @return string
     */
    protected function createWhereStatement(array $arr_filter) : string
    {
        global $DIC;

        if (!$arr_filter['usr_id']) {
            return '';
        }

        $where = parent::createWhereStatement($arr_filter);
        $usr_filter = "a_table.usr_id = " . $DIC->database()->quote($arr_filter['usr_id'], 'integer');

        if (empty($where)) {
            return ' WHERE ' . $usr_filter;
        } else {
            return $where . ' AND ' . $usr_filter;
        }
    }
}
