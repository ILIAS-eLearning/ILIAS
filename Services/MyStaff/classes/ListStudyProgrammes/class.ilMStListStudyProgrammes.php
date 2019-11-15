<?php
namespace ILIAS\MyStaff\ListStudyProgrammes;
use ILIAS\MyStaff\ilMyStaffAccess;
use ilLPStatus;
use ilOrgUnitOperation;

/**
 * Class ilMStListStudyProgrammes
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListStudyProgrammes {

	/**
	 * @param array $arr_usr_ids
	 * @param array $options
	 *
	 * @return array|int
	 */
	public static function getData(array $arr_usr_ids = array(), array $options = array()) {
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

		//see Services/MyStaff/classes/ListStudyProgrammes/class.ilMStListStudyprogrammesTableGUI.php
		$select = 'SELECT 
    prgrs.id prgrs_id,
    pcp.firstname,
    pcp.lastname,
    pcp.login,
    prgrs.points,
    prgrs.points_cur * ABS(prgrs.status - 3) / (GREATEST(ABS(prgrs.status - 3), 1)) + prgrs.points * (1 - ABS(prgrs.status - 3) / (GREATEST(ABS(prgrs.status - 3), 1))) points_current,
    prgrs.last_change_by,
    prgrs.status,
    blngs.title belongs_to,
    cmpl_usr.login accredited_by,
    cmpl_obj.title completion_by,
    cmpl_obj.type completion_by_type,
    prgrs.completion_by completion_by_id,
    prgrs.assignment_id assignment_id,
    ass.root_prg_id root_prg_id,
    ass.last_change prg_assign_date,
    ass_usr.login prg_assigned_by,
    CONCAT(pcp.firstname, pcp.lastname) name,
    (prgrs.last_change_by IS NOT NULL) custom_plan
FROM
    prg_usr_progress prgrs
        JOIN
    usr_data pcp ON pcp.usr_id = prgrs.usr_id
        JOIN
    prg_usr_assignments ass ON ass.id = prgrs.assignment_id
        JOIN
    object_data blngs ON blngs.obj_id = ass.root_prg_id
        LEFT JOIN
    usr_data ass_usr ON ass_usr.usr_id = ass.last_change_by
        LEFT JOIN
    usr_data cmpl_usr ON cmpl_usr.usr_id = prgrs.completion_by
        LEFT JOIN
    object_data cmpl_obj ON cmpl_obj.obj_id = prgrs.completion_by';

		$select .= static::createWhereStatement($arr_usr_ids, $options['filters'], $tmp_table_user_matrix);

		if ($options['count']) {
			$result = $DIC->database()->query($select);

			return $DIC->database()->numRows($result);
		}

		if ($options['sort']) {
			//$select .= " ORDER BY " . $options['sort']['field'] . " " . $options['sort']['direction'];
		}

		if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
			//$select .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
		}
		$result = $DIC->database()->query($select);
		$crs_data = array();

		while ($row = $DIC->database()->fetchAssoc($result)) {
			$obj = new ilMStListStudyProgramme();
            $obj->setCrsRefId($row['crs_ref_id']);
            $obj->setCrsTitle($row['crs_title']);
            $obj->setUsrRegStatus($row['reg_status']);
            $obj->setUsrLpStatus($row['lp_status']);
            $obj->setUsrLogin($row['usr_login']);
            $obj->setUsrLastname($row['usr_lastname']);
            $obj->setUsrFirstname($row['usr_firstname']);
            $obj->setUsrEmail($row['usr_email']);
            $obj->setUsrId($row['usr_id']);

			$data[] = $obj;
		}

		return $data;
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
	protected static function createWhereStatement(array $arr_usr_ids, array $arr_filter, $tmp_table_user_matrix) {
		global $DIC;

		$where = array();

		//$where[] = '(crs_ref.ref_id, usr_data.usr_id) IN (SELECT * FROM ' . $tmp_table_user_matrix . ')';

		if (count($arr_usr_ids)) {
			//$where[] = $DIC->database()->in('usr_data.usr_id', $arr_usr_ids, false, 'integer');
		}

		if (!empty($arr_filter['crs_title'])) {
			//$where[] = '(crs.title LIKE ' . $DIC->database()->quote('%' . $arr_filter['crs_title'] . '%', 'text') . ')';
		}

		if ($arr_filter['course'] > 0) {
			//$where[] = '(crs_ref.ref_id = ' . $DIC->database()->quote($arr_filter['course'], 'integer') . ')';
		}

		/*if (!empty($arr_filter['lp_status']) || $arr_filter['lp_status'] === 0) {

			switch ($arr_filter['lp_status']) {
				case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
					//if a user has the lp status not attempted it could be, that the user hase no records in table ut_lp_marks
					$where[] = '(lp_status = ' . $DIC->database()->quote($arr_filter['lp_status'], 'integer') . ' OR lp_status is NULL)';
					break;
				default:
					$where[] = '(lp_status = ' . $DIC->database()->quote($arr_filter['lp_status'], 'integer') . ')';
					break;
			}
		}*/

		if (!empty($arr_filter['memb_status'])) {
			//$where[] = '(reg_status = ' . $DIC->database()->quote($arr_filter['memb_status'], 'integer') . ')';
		}
/*
		if (!empty($arr_filter['user'])) {
			$where[] = "(" . $DIC->database()->like("usr_data.login", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
					->like("usr_data.firstname", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
					->like("usr_data.lastname", "text", "%" . $arr_filter['user'] . "%") . " " . "OR " . $DIC->database()
					->like("usr_data.email", "text", "%" . $arr_filter['user'] . "%") . ") ";
		}*/

		/*if (!empty($arr_filter['org_unit'])) {
			$where[] = 'usr_data.usr_id IN (SELECT user_id FROM il_orgu_ua WHERE orgu_id = ' . $DIC->database()
					->quote($arr_filter['org_unit'], 'integer') . ')';
		}*/

		if (!empty($where)) {
			return ' WHERE ' . implode(' AND ', $where) . ' ';
		} else {
			return '';
		}
	}
}
