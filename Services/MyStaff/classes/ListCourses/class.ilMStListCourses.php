<?php

/**
 * Class ilMStListCourses
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilMStListCourses {

	/**
	 * @param array $arr_usr_ids
	 * @param array $options
	 *
	 * @return array|bool|int
	 */
	public static function getData(array $arr_usr_ids = array(), array $options = array()) {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		//Permissions
		/*if (count($arr_usr_ids) == 0) {
			return false;
		}*/

		$_options = array(
			'filters' => array(),
			'sort'    => array(),
			'limit'   => array(),
			'count'   => false,
		);
		$options = array_merge($_options, $options);

		$udf = ilUserDefinedFields::_getInstance();

		$select = 'SELECT crs_ref.ref_id as crs_ref_id, crs.title as crs_title, reg_status, lp_status, usr_data.usr_id as usr_id, usr_data.login as usr_login, usr_data.lastname as usr_lastname, usr_data.firstname as usr_firstname, usr_data.email as usr_email  from (
	                    select reg.obj_id, reg.usr_id, '
		          . ilMStListCourse::MEMBERSHIP_STATUS_REGISTERED . ' as reg_status, lp.status as lp_status from obj_members as reg
                        left join ut_lp_marks as lp on lp.obj_id = reg.obj_id and lp.usr_id = reg.usr_id
		            UNION
	                    select obj_id, usr_id, ' . ilMStListCourse::MEMBERSHIP_STATUS_WAITINGLIST . ' as reg_status, 0 as lp_status from crs_waiting_list as waiting
                    UNION
	                    select obj_id, usr_id, ' . ilMStListCourse::MEMBERSHIP_STATUS_REQUESTED . ' as reg_status, 0 as lp_status from il_subscribers as requested) as memb
                    inner join object_data as crs on crs.obj_id = memb.obj_id and crs.type = "crs"
                    inner join object_reference as crs_ref on crs_ref.obj_id = crs.obj_id
	                inner join usr_data on usr_data.usr_id = memb.usr_id and usr_data.active = 1';

		$select .= static::createWhereStatement(array(), $options['filters']);

		if ($options['count']) {
			$result = $ilDB->query($select);

			return $ilDB->numRows($result);
		}

		if ($options['sort']) {
			$select .= " ORDER BY " . $options['sort']['field'] . " "
			           . $options['sort']['direction'];
		}

		if (isset($options['limit']['start']) && isset($options['limit']['end'])) {
			$select .= " LIMIT " . $options['limit']['start'] . "," . $options['limit']['end'];
		}

		$result = $ilDB->query($select);
		$crs_data = array();

		while ($crs = $ilDB->fetchAssoc($result)) {
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
	 * Returns the WHERE Part for the Queries using parameter $user_ids and local variable $filters
	 *
	 * @param array $arr_usr_ids
	 * @param array $arr_filter
	 *
	 * @return bool|string
	 */
	public static function createWhereStatement($arr_usr_ids, $arr_filter) {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$where = array();

		$where[] = '(crs_ref.ref_id, usr_data.usr_id) IN (SELECT * from tmp_ilobj_user_matrix)';

		//$where[] = $ilDB->in('usr_data.usr_id', $arr_usr_ids, false, 'integer');

		if (!empty($arr_filter['crs_title'])) {
			$where[] = '(crs.title LIKE ' . $ilDB->quote('%' . $arr_filter['crs_title']
			                                             . '%', 'text') . ')';
		}

		if ($arr_filter['course'] > 0) {
			$where[] = '(crs_ref.ref_id = ' . $ilDB->quote($arr_filter['course'], 'integer') . ')';
		}

		if (!empty($arr_filter['lp_status']) or $arr_filter['lp_status'] === 0) {
			$where[] = '(lp_status = ' . $ilDB->quote($arr_filter['lp_status'], 'integer') . ')';
		}

		if (!empty($arr_filter['memb_status'])) {
			$where[] = '(reg_status = ' . $ilDB->quote($arr_filter['memb_status'], 'integer') . ')';
		}

		if (!empty($arr_filter['user'])) {
			$where[] = "(" . $ilDB->like("usr_data.login", "text", "%" . $arr_filter['user'] . "%")
			           . " " . "OR " . $ilDB->like("usr_data.firstname", "text", "%"
			                                                                     . $arr_filter['user']
			                                                                     . "%") . " "
			           . "OR " . $ilDB->like("usr_data.lastname", "text", "%" . $arr_filter['user']
			                                                              . "%") . " " . "OR "
			           . $ilDB->like("usr_data.email", "text", "%" . $arr_filter['user'] . "%")
			           . ") ";
		}

		if (!empty($arr_filter['org_unit'])) {
			$where[] = 'usr_data.usr_id in (SELECT user_id from il_orgu_ua where orgu_id = '
			           . $ilDB->quote($arr_filter['org_unit'], 'integer') . ')';
		}

		if (!empty($where)) {
			return ' WHERE ' . implode(' AND ', $where) . ' ';
		} else {
			return '';
		}
	}
}