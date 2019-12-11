<?php

/**
 * Class ilMStListCourses
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCourses
{

    /**
     * @param array $arr_usr_ids
     * @param array $options
     *
     * @return array|int
     */
    public static function getData(array $arr_usr_ids = array(), array $options = array())
    {
        global $DIC;

        //Permission Filter
        $operation_access = ilOrgUnitOperation::OP_ACCESS_ENROLMENTS;

        if (!empty($options['filters']['lp_status']) || $options['filters']['lp_status'] === 0) {
            $operation_access = ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS;
        }
        $tmp_table_user_matrix = ilMyStaffAccess::getInstance()->buildTempTableIlobjectsUserMatrixForUserOperationAndContext($DIC->user()
            ->getId(), $operation_access, ilMyStaffAccess::DEFAULT_CONTEXT, ilMyStaffAccess::TMP_DEFAULT_TABLE_NAME_PREFIX_IL_OBJ_USER_MATRIX);

        $_options = array(
            'filters' => array(),
            'sort' => array(),
            'limit' => array(),
            'count' => false,
        );
        $options = array_merge($_options, $options);

        $select = 'SELECT crs_ref.ref_id AS crs_ref_id, crs.title AS crs_title, reg_status, lp_status, usr_data.usr_id AS usr_id, usr_data.login AS usr_login, usr_data.lastname AS usr_lastname, usr_data.firstname AS usr_firstname, usr_data.email AS usr_email  FROM (
	                    SELECT reg.obj_id, reg.usr_id, ' . ilMStListCourse::MEMBERSHIP_STATUS_REGISTERED . ' AS reg_status, lp.status AS lp_status FROM obj_members 
		          AS reg
                        LEFT JOIN ut_lp_marks AS lp on lp.obj_id = reg.obj_id AND lp.usr_id = reg.usr_id
                         WHERE ' . $DIC->database()->in('reg.usr_id', $arr_usr_ids, false, 'integer') . '
		            UNION
	                    SELECT obj_id, usr_id, ' . ilMStListCourse::MEMBERSHIP_STATUS_WAITINGLIST . ' AS reg_status, 0 AS lp_status FROM crs_waiting_list AS waiting
	                    WHERE ' . $DIC->database()->in('waiting.usr_id', $arr_usr_ids, false, 'integer') . '
                    UNION
	                    SELECT obj_id, usr_id, ' . ilMStListCourse::MEMBERSHIP_STATUS_REQUESTED . ' AS reg_status, 0 AS lp_status FROM il_subscribers AS requested
	                  WHERE ' . $DIC->database()->in('requested.usr_id', $arr_usr_ids, false, 'integer') . '  
	                    ) AS memb
	           
                    INNER JOIN object_data AS crs on crs.obj_id = memb.obj_id AND crs.type = ' . $DIC->database()
                ->quote(ilMyStaffAccess::DEFAULT_CONTEXT, 'text') . '
                    INNER JOIN object_reference AS crs_ref on crs_ref.obj_id = crs.obj_id
	                INNER JOIN usr_data on usr_data.usr_id = memb.usr_id AND usr_data.active = 1';

        $select .= static::createWhereStatement($arr_usr_ids, $options['filters'], $tmp_table_user_matrix);

        if ($options['count']) {
            $result = $DIC->database()->query($select);

            return $DIC->database()->numRows($result);
        }

        if ($options['sort']) {
            $select .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
        }

        if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
            $select .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
        }
        $result = $DIC->database()->query($select);
        $crs_data = array();

        while ($crs = $DIC->database()->fetchAssoc($result)) {
            $list_course = new ilMStListCourse();
            $list_course->setCrsRefId($crs['crs_ref_id']);
            $list_course->setCrsTitle($crs['crs_title']);
            $list_course->setUsrRegStatus($crs['reg_status']);
            $list_course->setUsrLpStatus($crs['lp_status']);
            $list_course->setUsrLogin($crs['usr_login']);
            $list_course->setUsrLastname($crs['usr_lastname']);
            $list_course->setUsrFirstname($crs['usr_firstname']);
            $list_course->setUsrEmail($crs['usr_email']);
            $list_course->setUsrId($crs['usr_id']);

            $crs_data[] = $list_course;
        }

        return $crs_data;
    }


    /**
     * Returns the WHERE Part for the Queries using parameter $user_ids AND local variable $filters
     *
     * @param array  $arr_usr_ids
     * @param array  $arr_filter
     * @param string $tmp_table_user_matrix
     *
     * @return string
     */
    protected static function createWhereStatement(array $arr_usr_ids, array $arr_filter, $tmp_table_user_matrix)
    {
        global $DIC;

        $where = array();

        $where[] = '(crs_ref.ref_id, usr_data.usr_id) IN (SELECT * FROM ' . $tmp_table_user_matrix . ')';

        if (count($arr_usr_ids)) {
            $where[] = $DIC->database()->in('usr_data.usr_id', $arr_usr_ids, false, 'integer');
        }

        if (!empty($arr_filter['crs_title'])) {
            $where[] = '(crs.title LIKE ' . $DIC->database()->quote('%' . $arr_filter['crs_title'] . '%', 'text') . ')';
        }

        if ($arr_filter['course'] > 0) {
            $where[] = '(crs_ref.ref_id = ' . $DIC->database()->quote($arr_filter['course'], 'integer') . ')';
        }

        if (!empty($arr_filter['lp_status']) || $arr_filter['lp_status'] === 0) {
            switch ($arr_filter['lp_status']) {
                case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
                    //if a user has the lp status not attempted it could be, that the user hase no records in table ut_lp_marks
                    $where[] = '(lp_status = ' . $DIC->database()->quote($arr_filter['lp_status'], 'integer') . ' OR lp_status is NULL)';
                    break;
                default:
                    $where[] = '(lp_status = ' . $DIC->database()->quote($arr_filter['lp_status'], 'integer') . ')';
                    break;
            }
        }

        if (!empty($arr_filter['memb_status'])) {
            $where[] = '(reg_status = ' . $DIC->database()->quote($arr_filter['memb_status'], 'integer') . ')';
        }

        if (!empty($arr_filter['user'])) {
            $where[] = "(" . $DIC->database()->like("usr_data.login", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
                    ->like("usr_data.firstname", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
                    ->like("usr_data.lastname", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
                    ->like("usr_data.email", "text", "%" . $arr_filter['user'] . "%") . ") ";
        }

        if (!empty($arr_filter['org_unit'])) {
            $where[] = 'usr_data.usr_id IN (SELECT user_id FROM il_orgu_ua WHERE orgu_id = ' . $DIC->database()
                    ->quote($arr_filter['org_unit'], 'integer') . ')';
        }

        if (!empty($where)) {
            return ' WHERE ' . implode(' AND ', $where) . ' ';
        } else {
            return '';
        }
    }
}
