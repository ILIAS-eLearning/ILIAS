<?php
namespace ILIAS\MyStaff\Courses\ShowUser;
use ILIAS\MyStaff\ListCourses\ilMStListCourses;

/**
 * Class ilMStShowUserCourses
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class ilMStShowUserCourses extends ilMStListCourses {


	/**
	 * @param array  $arr_usr_ids
	 * @param array  $arr_filter
	 * @param string $tmp_table_user_matrix
	 *
	 * @return string
	 */
	protected function createWhereStatement(array $arr_usr_ids, array $arr_filter, $tmp_table_user_matrix) {
		global $DIC;

		if (!$arr_filter['usr_id']) {
			return '';
		}

		$where = parent::createWhereStatement($arr_usr_ids, $arr_filter, $tmp_table_user_matrix);
		$usr_filter = "usr_data.usr_id = " . $DIC->database()->quote($arr_filter['usr_id'], 'integer');

		if (empty($where)) {
			return ' WHERE ' . $usr_filter;
		} else {
			return $where . ' AND ' . $usr_filter;
		}
	}
}
