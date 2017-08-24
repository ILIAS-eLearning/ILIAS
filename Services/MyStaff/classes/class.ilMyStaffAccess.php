<?php

/**
 * Class ilMyStaffAccess
 *
 * @author: Martin Studer <ms@studer-raimann.ch>
 */
class ilMyStaffAccess extends ilObjectAccess {

	/**
	 * @var ilMyStaffAccess
	 */
	protected static $instance;
	/**
	 * @var
	 */
	protected static $orgu_users_of_current_user_show_staff_permission;


	/**
	 * @return \ilMyStaffAccess
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @return bool
	 */
	public function hasCurrentUserAccessToMyStaff() {
		if (count($this->getOrguUsersOfCurrentUserWithShowStaffPermission()) > 0) {
			return true;
		}
	}


	/**
	 * @return array
	 */
	public function getOrguUsersOfCurrentUserWithShowStaffPermission() {
		if (is_null(self::$orgu_users_of_current_user_show_staff_permission)) {

			$arr_users = array();
			$arr_orgus_perm = array();
			$arr_orgus_perm_view_lp = array();
			$arr_orgus_perm_view_lp_rec = array();

			$arr_orgus_perm_view_lp = ilObjOrgUnitTree::_getInstance()
			                                          ->getOrgusWhereUserHasPermissionForOperation('view_learning_progress');
			$arr_orgus_perm_view_lp_rec = ilObjOrgUnitTree::_getInstance()
			                                              ->getOrgusWhereUserHasPermissionForOperation('view_learning_progress_rec');

			$arr_orgus_perm = array_merge($arr_orgus_perm_view_lp, $arr_orgus_perm_view_lp_rec);
			$arr_users = self::getUsersByOrgus($arr_orgus_perm);
			self::$orgu_users_of_current_user_show_staff_permission = $arr_users;

			return $arr_users;
		}

		return self::$orgu_users_of_current_user_show_staff_permission;
	}


	/**
	 * @param array $arr_orgu_ref_id
	 *
	 * @return array
	 */
	public static function getUsersByOrgus($arr_orgu_ref_id) {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$users = array();

		$user_query = "select user.usr_id from object_data AS obj
											INNER JOIN object_reference as ref on ref.obj_id = obj.obj_id
											INNER JOIN rbac_fa on rbac_fa.parent = ref.ref_id
											INNER JOIN object_data as role_obj on role_obj.obj_id = rbac_fa.rol_id and (role_obj.title like '%il_orgu_employee%' OR role_obj.title like '%il_orgu_superior%')
											INNER JOIN rbac_ua as user on user.rol_id = role_obj.obj_id
											WHERE
											obj.type = 'orgu'
											AND "
		              . $ilDB->in("ref.ref_id", $arr_orgu_ref_id, false, 'integer');

		$user_set = $ilDB->query($user_query);
		while ($rec = $ilDB->fetchAssoc($user_set)) {
			$users[$rec['usr_id']] = $rec['usr_id'];
		}

		return $users;
	}
}
