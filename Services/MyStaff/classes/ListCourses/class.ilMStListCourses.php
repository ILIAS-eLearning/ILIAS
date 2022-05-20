<?php
declare(strict_types=1);

namespace ILIAS\MyStaff\ListCourses;

use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilLPStatus;
use ilOrgUnitOperation;
use ILIAS\Services\MyStaff\Utils\ListFetcherResult;

/**
 * Class ilMStListCourses
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCourses
{
    protected Container $dic;

    /**
     * ilMStListCourses constructor.
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    final public function getData(array $arr_usr_ids = array(), array $options = array()) : ListFetcherResult
    {
        //Permission Filter
        $operation_access = ilOrgUnitOperation::OP_ACCESS_ENROLMENTS;

        if (!empty($options['filters']['lp_status']) || $options['filters']['lp_status'] === 0) {
            $operation_access = ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS;
        }
        /*$tmp_table_user_matrix = ilMyStaffAccess::getInstance()->buildTempTableIlobjectsUserMatrixForUserOperationAndContext($this->dic->user()
            ->getId(), $operation_access, ilMyStaffAccess::DEFAULT_CONTEXT, ilMyStaffAccess::TMP_DEFAULT_TABLE_NAME_PREFIX_IL_OBJ_USER_MATRIX);*/

        $_options = array(
            'filters' => array(),
            'sort' => array(),
            'limit' => array(),
            'count' => false,
        );
        $options = array_merge($_options, $options);

        $query = 'SELECT crs_ref.ref_id AS crs_ref_id, crs.title AS crs_title, reg_status, lp_status, usr_data.usr_id AS usr_id, usr_data.login AS usr_login, usr_data.lastname AS usr_lastname, usr_data.firstname AS usr_firstname, usr_data.email AS usr_email  FROM (
	                    SELECT reg.obj_id, reg.usr_id, ' . ilMStListCourse::MEMBERSHIP_STATUS_REGISTERED . ' AS reg_status, lp.status AS lp_status FROM obj_members 
		          AS reg
                        LEFT JOIN ut_lp_marks AS lp on lp.obj_id = reg.obj_id AND lp.usr_id = reg.usr_id
                         WHERE ' . $this->dic->database()->in('reg.usr_id', $arr_usr_ids, false, 'integer') . ' AND (reg.admin = 1 OR reg.tutor = 1 OR reg.member = 1)
		            UNION
	                    SELECT obj_id, usr_id, ' . ilMStListCourse::MEMBERSHIP_STATUS_WAITINGLIST . ' AS reg_status, 0 AS lp_status FROM crs_waiting_list AS waiting
	                    WHERE ' . $this->dic->database()->in('waiting.usr_id', $arr_usr_ids, false, 'integer') . '
                    UNION
	                    SELECT obj_id, usr_id, ' . ilMStListCourse::MEMBERSHIP_STATUS_REQUESTED . ' AS reg_status, 0 AS lp_status FROM il_subscribers AS requested
	                  WHERE ' . $this->dic->database()->in('requested.usr_id', $arr_usr_ids, false, 'integer') . '  
	                    ) AS memb
	           
                    INNER JOIN object_data AS crs on crs.obj_id = memb.obj_id AND crs.type = ' . $this->dic->database()
                                                                                                           ->quote(
                                                                                                               ilMyStaffAccess::DEFAULT_CONTEXT,
                                                                                                               'text'
                                                                                                           ) . '
                    INNER JOIN object_reference AS crs_ref on crs_ref.obj_id = crs.obj_id AND crs_ref.deleted IS NULL
	                INNER JOIN usr_data on usr_data.usr_id = memb.usr_id AND usr_data.active = 1';

        $data = [];
        $users_per_position = ilMyStaffAccess::getInstance()->getUsersForUserPerPosition($this->dic->user()->getId());

        if (empty($users_per_position)) {
            return new ListFetcherResult([], 0);
        }

        $arr_query = [];
        foreach ($users_per_position as $position_id => $users) {
            $obj_ids = ilMyStaffAccess::getInstance()->getIdsForUserAndOperation(
                $this->dic->user()->getId(),
                $operation_access
            );
            $arr_query[] = $query . " AND " . $this->dic->database()->in(
                'crs.obj_id',
                $obj_ids,
                false,
                'integer'
            ) . " AND " . $this->dic->database()->in('usr_data.usr_id', $users, false, 'integer');
        }

        $union_query = "SELECT * FROM ((" . implode(') UNION (', $arr_query) . ")) as a_table";

        $union_query .= static::createWhereStatement($options['filters']);

        $result = $this->dic->database()->query($union_query);
        $numRows = $this->dic->database()->numRows($result);

        if ($options['sort']) {
            $union_query .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
        }

        if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
            $union_query .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
        }
        $result = $this->dic->database()->query($union_query);
        $crs_data = array();

        while ($crs = $this->dic->database()->fetchAssoc($result)) {
            $list_course = new ilMStListCourse();
            $list_course->setCrsRefId(intval($crs['crs_ref_id']));
            $list_course->setCrsTitle($crs['crs_title']);
            $list_course->setUsrRegStatus(intval($crs['reg_status']));
            $list_course->setUsrLpStatus(intval($crs['lp_status']));
            $list_course->setUsrLogin($crs['usr_login']);
            $list_course->setUsrLastname($crs['usr_lastname']);
            $list_course->setUsrFirstname($crs['usr_firstname']);
            $list_course->setUsrEmail($crs['usr_email']);
            $list_course->setUsrId(intval($crs['usr_id']));

            $crs_data[] = $list_course;
        }

        return new ListFetcherResult($crs_data, $numRows);
    }

    /**
     * Returns the WHERE Part for the Queries using parameter $user_ids AND local variable $filters
     */
    protected function createWhereStatement(array $arr_filter) : string
    {
        $where = array();

        if (!empty($arr_filter['crs_title'])) {
            $where[] = '(crs_title LIKE ' . $this->dic->database()->quote(
                '%' . $arr_filter['crs_title'] . '%',
                'text'
            ) . ')';
        }

        if ($arr_filter['course'] > 0) {
            $where[] = '(crs_ref_id = ' . $this->dic->database()->quote($arr_filter['course'], 'integer') . ')';
        }

        if (!empty($arr_filter['lp_status']) || $arr_filter['lp_status'] === 0) {
            switch ($arr_filter['lp_status']) {
                case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
                    //if a user has the lp status not attempted it could be, that the user hase no records in table ut_lp_marks
                    $where[] = '(lp_status = ' . $this->dic->database()->quote(
                        $arr_filter['lp_status'],
                        'integer'
                    ) . ' OR lp_status is NULL)';
                    break;
                default:
                    $where[] = '(lp_status = ' . $this->dic->database()->quote(
                        $arr_filter['lp_status'],
                        'integer'
                    ) . ')';
                    break;
            }
        }

        if (!empty($arr_filter['memb_status'])) {
            $where[] = '(reg_status = ' . $this->dic->database()->quote($arr_filter['memb_status'], 'integer') . ')';
        }

        if (!empty($arr_filter['user'])) {
            $where[] = "(" . $this->dic->database()->like(
                "usr_login",
                "text",
                "%" . $arr_filter['user'] . "%"
            ) . " " . "OR " . $this->dic->database()
                                                                               ->like(
                                                                                   "usr_firstname",
                                                                                   "text",
                                                                                   "%" . $arr_filter['user'] . "%"
                                                                               ) . " " . "OR " . $this->dic->database()
                                                                                                                                              ->like(
                                                                                                                                                  "usr_lastname",
                                                                                                                                                  "text",
                                                                                                                                                  "%" . $arr_filter['user'] . "%"
                                                                                                                                              ) . " " . "OR " . $this->dic->database()
                                                                                                                                                                                                             ->like(
                                                                                                                                                                                                                 "usr_email",
                                                                                                                                                                                                                 "text",
                                                                                                                                                                                                                 "%" . $arr_filter['user'] . "%"
                                                                                                                                                                                                             ) . ") ";
        }

        if (!empty($arr_filter['org_unit'])) {
            $where[] = 'usr_id IN (SELECT user_id FROM il_orgu_ua WHERE orgu_id = ' . $this->dic->database()
                                                                                                ->quote(
                                                                                                    $arr_filter['org_unit'],
                                                                                                    'integer'
                                                                                                ) . ')';
        }

        if (isset($arr_filter['usr_id']) && is_numeric($arr_filter['usr_id'])) {
            $where[] = 'usr_id = ' . $this->dic->database()->quote($arr_filter['usr_id'], \ilDBConstants::T_INTEGER);
        }

        if (isset($arr_filter['usr_id']) && is_numeric($arr_filter['usr_id'])) {
            $where[] = 'usr_id = ' . $this->dic->database()->quote($arr_filter['usr_id'], \ilDBConstants::T_INTEGER);
        }

        if (!empty($where)) {
            return ' WHERE ' . implode(' AND ', $where) . ' ';
        } else {
            return '';
        }
    }
}
