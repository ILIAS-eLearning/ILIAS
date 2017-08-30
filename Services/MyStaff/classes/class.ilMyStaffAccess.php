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
		global $DIC;
		$ilUser = $DIC['ilUser'];

		$operation = ilOrgUnitOperationQueries::findByOperationString(ilOrgUnitOperation::OP_ACCESS_ENROLMENTS, 'crs');
		if(!$operation) {
			return false;
		}
		if ($this->countOrgusOfUserWithOperationAndContext($ilUser->getId(), $operation->getOperationId())
		    > 0) {
			return true;
		}
	}


	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public function countOrgusOfUserWithAtLeastOneOperation($user_id) {
		global $DIC;
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$q = "select count(orgu_ua.orgu_id) as 'cnt' from il_orgu_permissions AS perm
				INNER JOIN il_orgu_ua AS orgu_ua ON orgu_ua.position_id = perm.position_id
				INNER JOIN il_orgu_op_contexts as contexts on contexts.id = perm.context_id and contexts.context is not NULL
				where orgu_ua.user_id = " . $ilDB->quote($user_id, 'integer')
		     . " and perm.operations is not NULL and perm.parent_id = -1";

		$set = $ilDB->query($q);
		$rec = $ilDB->fetchAssoc($set);

		return $rec['cnt'];
	}


	/**
	 * @param int    $user_id
	 * @param int    $operation_id
	 * @param string $context
	 *
	 * @return mixed
	 */
	public function countOrgusOfUserWithOperationAndContext($user_id, $operation_id = 1, $context = 'crs') {
		global $DIC;
		$ilUser = $DIC['ilUser'];
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$q = "select count(orgu_ua.orgu_id) as cnt from il_orgu_permissions AS perm
				INNER JOIN il_orgu_ua AS orgu_ua ON orgu_ua.position_id = perm.position_id
				INNER JOIN il_orgu_op_contexts as contexts on contexts.id = perm.context_id and contexts.context = '"
		     . $context . "'
				and orgu_ua.user_id = " . $ilDB->quote($user_id, 'integer') . " and perm.operations LIKE  '%\"$operation_id\"%'
				where perm.parent_id = -1";

		$set = $ilDB->query($q);
		$rec = $ilDB->fetchAssoc($set);

		return $rec['cnt'];
	}


	public function getUsersForUserOperationAndContext($user_id, $operation_id = 1, $context = 'crs') {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->buildTempTableIlobjectsUserMatrixForUserOperationAndContext($user_id, $operation_id, $context);

		$q = 'SELECT usr_id FROM tmp_ilobj_user_matrix';

		$user_set = $ilDB->query($q);

		$arr_users = array();

		while ($rec = $ilDB->fetchAssoc($user_set)) {
			$arr_users[$rec['usr_id']] = $rec['usr_id'];
		}

		return $arr_users;
	}


	public function getUsersForUser($user_id) {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->buildTempTableOrguMembers();

		$q = "select  tmp_orgu_members.user_id as usr_id
        		from 
				tmp_orgu_members
				INNER JOIN il_orgu_ua as orgu_ua_current_user on orgu_ua_current_user.user_id = "
		     . $ilDB->quote($user_id, 'integer') . "
				INNER JOIN il_orgu_authority AS auth ON auth.position_id = orgu_ua_current_user.position_id
				where
				(
				/* Identische OrgUnit wie Current User; Nicht Rekursiv; Fixe Position */
					(orgu_ua_current_user.orgu_id = tmp_orgu_members.orgu_id and auth.scope = 1
						AND auth.over = tmp_orgu_members.user_position_id AND auth.over <> -1
					)
					OR
					/* Identische OrgUnit wie Current User; Nicht Rekursiv; Position egal */
					(orgu_ua_current_user.orgu_id = tmp_orgu_members.orgu_id and auth.scope = 1 and auth.over = -1)
					OR
					/* Kinder OrgUnit wie Current User */
					(
						(
							tmp_orgu_members.orgu_id = orgu_ua_current_user.orgu_id OR
							tmp_orgu_members.tree_path LIKE CONCAT(\"%.\",orgu_ua_current_user.orgu_id ,\".%\")
							OR
							tmp_orgu_members.tree_path LIKE  CONCAT(\"%.\",orgu_ua_current_user.orgu_id )
						)
						AND 
						(
							(
								(
									/* Gleiche Position */
									auth.over = tmp_orgu_members.user_position_id AND auth.over <> -1
								)
								OR
								(
									/* Position Egal */
									auth.over <> -1
								)
							)
							AND auth.scope = 2
						)
					)
				)";

		$user_set = $ilDB->query($q);

		$arr_users = array();

		while ($rec = $ilDB->fetchAssoc($user_set)) {
			$arr_users[$rec['usr_id']] = $rec['usr_id'];
		}

		return $arr_users;
	}


	public function getIlobjectsAndUsersForUserOperationAndContext($user_id, $operation_id = 1, $context = 'crs') {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->buildTempTableIlobjectsUserMatrixForUserOperationAndContext($user_id, $operation_id, $context);

		$q = 'SELECT * FROM tmp_ilobj_user_matrix';

		$user_set = $ilDB->query($q);

		$arr_user_obj = array();

		while ($rec = $ilDB->fetchAssoc($user_set)) {
			$arr_user_obj[] = $rec;
		}

		return $arr_user_obj;
	}


	public function buildTempTableIlobjectsUserMatrixForUserOperationAndContext($user_id, $operation_id = 1, $context = 'crs', $temporary_table_name = 'tmp_ilobj_user_matrix') {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->buildTempTableIlobjectsSpecificPermissionSetForOperationAndContext($operation_id,$context);
		$this->buildTempTableIlobjectsDefaultPermissionSetForOperationAndContext($operation_id,$context);
		$this->buildTempTableIlorgunitDefaultPermissionSetForOperationAndContext($operation_id,$context);
		$this->buildTempTableCourseMembers();
		$this->buildTempTableOrguMembers();
		$this->buildTempTableOrguMembers('tmp_orgu_members_path');

		$this->dropTempTable($temporary_table_name);

		$q = "CREATE TEMPORARY TABLE IF NOT EXISTS " . $temporary_table_name . " AS (
				SELECT DISTINCT user_perm_matrix.perm_for_ref_id, user_perm_matrix.usr_id FROM
				(
				 SELECT crs.*,tmp_crs_members.ref_id,tmp_crs_members.usr_id FROM
					(
						select * from tmp_ilobj_spec_permissions
							UNION
						select * from tmp_ilobj_default_permissions
					) as crs
					inner join tmp_crs_members on tmp_crs_members.ref_id = crs.perm_for_ref_id 
					and (
							(
							tmp_crs_members.orgu_id = crs.perm_for_orgu_id and tmp_crs_members.position_id = crs.perm_over_user_with_position and perm_orgu_scope = 1
							)
							or perm_orgu_scope = 2
						)
				UNION
					select tmp_ilorgu_default_permissions.*, tmp_orgu_members.orgu_id as ref_id, tmp_orgu_members.user_id from tmp_ilorgu_default_permissions
					inner join tmp_orgu_members on tmp_orgu_members.orgu_id = tmp_ilorgu_default_permissions.perm_for_ref_id
					and (
							(
							tmp_orgu_members.orgu_id = tmp_ilorgu_default_permissions.perm_for_orgu_id and tmp_orgu_members.user_position_id = tmp_ilorgu_default_permissions.perm_over_user_with_position and perm_orgu_scope = 1
							)
							or perm_orgu_scope = 2
						)
					
				) as user_perm_matrix  
				INNER JOIN tmp_orgu_members_path as path on path.user_id = user_perm_matrix.usr_id
				
				INNER JOIN il_orgu_ua as orgu_ua_current_user on orgu_ua_current_user.user_id = "
		     . $ilDB->quote($user_id, 'integer') . "
				INNER JOIN il_orgu_permissions AS perm on perm.position_id = orgu_ua_current_user.position_id and perm.parent_id = -1
				INNER JOIN il_orgu_op_contexts as contexts on contexts.id = perm.context_id and contexts.context =  '$context'
				and perm.operations  LIKE '%\"$operation_id\"%'
				
				AND
				( 
					/* Identische OrgUnit wie Current User; Nicht Rekursiv; Fixe Position */
					(orgu_ua_current_user.orgu_id = user_perm_matrix.perm_for_orgu_id and user_perm_matrix.perm_orgu_scope = 1
					AND orgu_ua_current_user.position_id = user_perm_matrix.perm_for_position_id AND user_perm_matrix.perm_over_user_with_position <> -1
					)
				OR
					/* Identische OrgUnit wie Current User; Nicht Rekursiv; Position egal */
					(orgu_ua_current_user.orgu_id = user_perm_matrix.perm_for_orgu_id and user_perm_matrix.perm_orgu_scope = 1 and user_perm_matrix.perm_over_user_with_position = -1)
				OR
					/* Kinder OrgUnit wie Current User */
					(
						orgu_ua_current_user.orgu_id = user_perm_matrix.perm_for_orgu_id
						AND
						(
							path.orgu_id = user_perm_matrix.perm_for_orgu_id OR
							path.tree_path LIKE CONCAT(\"%.\",user_perm_matrix.perm_for_orgu_id ,\".%\")
							OR
							path.tree_path LIKE  CONCAT(\"%.\",user_perm_matrix.perm_for_orgu_id )
						)
						AND 
						(
							(
								(
									/* Gleiche Position */
									orgu_ua_current_user.position_id = user_perm_matrix.perm_for_position_id AND user_perm_matrix.perm_over_user_with_position <> -1
								)
								OR
								(
									/* Position Egal */
									user_perm_matrix.perm_over_user_with_position = -1
								)
							)
							AND user_perm_matrix.perm_orgu_scope = 2
						)
					)
				)	
			);";

		$ilDB->manipulate($q);

		return true;
	}


	public function buildTempTableIlobjectsSpecificPermissionSetForOperationAndContext($operation_id = 1, $context = 'crs', $temporary_table_name = 'tmp_ilobj_spec_permissions') {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->dropTempTable($temporary_table_name);

		$q = "CREATE TEMPORARY TABLE IF NOT EXISTS " . $temporary_table_name . " AS (
				 SELECT 
					obj_ref.ref_id as perm_for_ref_id,
					orgu_ua.orgu_id as perm_for_orgu_id,
			        auth.scope as perm_orgu_scope,
					orgu_ua.position_id as perm_for_position_id,
					auth.over as perm_over_user_with_position
					FROM
					il_orgu_permissions AS perm
					INNER JOIN il_orgu_ua AS orgu_ua ON orgu_ua.position_id = perm.position_id
					INNER JOIN il_orgu_authority AS auth ON auth.position_id = orgu_ua.position_id
					INNER JOIN object_reference AS obj_ref ON obj_ref.ref_id =  perm.parent_id
					INNER JOIN object_data AS obj ON obj.obj_id = obj_ref.obj_id and obj.type = '$context'
					INNER JOIN il_orgu_op_contexts as contexts on contexts.id = perm.context_id and contexts.context = '$context'
					WHERE
				    perm.operations LIKE '%\"$operation_id\"%'
			);";;

		$ilDB->manipulate($q);

		return true;
	}


	public function buildTempTableIlobjectsDefaultPermissionSetForOperationAndContext($operation_id = 1, $context = 'crs', $temporary_table_name = 'tmp_ilobj_default_permissions') {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->dropTempTable($temporary_table_name);

		$q = "CREATE TEMPORARY TABLE IF NOT EXISTS " . $temporary_table_name . " AS (
					SELECT 
					obj_ref.ref_id as perm_for_ref_id,
					orgu_ua.orgu_id as perm_for_orgu_id,
			        auth.scope as perm_orgu_scope,
					orgu_ua.position_id as perm_for_position_id,
					auth.over as perm_over_user_with_position
				    FROM
				    object_data AS obj
				    INNER JOIN object_reference AS obj_ref ON obj_ref.obj_id = obj.obj_id
				    INNER JOIN il_orgu_permissions AS perm ON perm.operations LIKE '%\""
		     . $operation_id . "\"%' AND perm.parent_id = -1
				    INNER JOIN il_orgu_op_contexts as contexts on contexts.id = perm.context_id and contexts.context = '"
		     . $context . "'
				    INNER JOIN il_orgu_ua AS orgu_ua ON orgu_ua.position_id = perm.position_id
				    INNER JOIN il_orgu_authority AS auth ON auth.position_id = orgu_ua.position_id
				    
				    WHERE
				        obj.type = '" . $context . "'
				            AND (obj_ref.ref_id , orgu_ua.position_id) 
				            
				            NOT IN (SELECT 
				                perm.parent_id, orgu_ua.position_id
				            FROM
				                il_orgu_permissions AS perm
				            INNER JOIN il_orgu_ua AS orgu_ua ON orgu_ua.position_id = perm.position_id
				            INNER JOIN il_orgu_op_contexts as contexts on contexts.id = perm.context_id and contexts.context = '"
		     . $context . "'
				            WHERE perm.parent_id <> -1)
							);";

		$ilDB->manipulate($q);

		//echo $q;

		return true;
	}


	public function buildTempTableIlorgunitDefaultPermissionSetForOperationAndContext($operation_id = 1, $context = 'crs', $temporary_table_name = 'tmp_ilorgu_default_permissions') {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->dropTempTable($temporary_table_name);

		$q = "CREATE TEMPORARY TABLE IF NOT EXISTS " . $temporary_table_name . " AS (
					SELECT 
		            orgu_ua.orgu_id as perm_for_ref_id, /* Table has to be identical to the other Permission For Operation And Context-Tables! */
					orgu_ua.orgu_id as perm_for_orgu_id,
			        auth.scope as perm_orgu_scope,
					orgu_ua.position_id as perm_for_position_id,
					auth.over as perm_over_user_with_position
				    FROM
					il_orgu_permissions AS perm
				    INNER JOIN il_orgu_ua AS orgu_ua ON orgu_ua.position_id = perm.position_id and perm.parent_id = -1
				    INNER JOIN il_orgu_authority AS auth ON auth.position_id = orgu_ua.position_id
				    INNER JOIN il_orgu_op_contexts as contexts on contexts.id = perm.context_id and contexts.context = '"
		     . $context . "'
				    WHERE
				    perm.operations LIKE '%\"" . $operation_id . "\"%'
							);";

		$ilDB->manipulate($q);

		//echo $q;

		return true;
	}


	public function buildTempTableCourseMembers($temporary_table_name = 'tmp_crs_members') {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->dropTempTable($temporary_table_name);

		$q = "CREATE TEMPORARY TABLE IF NOT EXISTS " . $temporary_table_name . " AS (
					SELECT crs_members_crs_ref.ref_id, crs_members.usr_id, orgu_ua.position_id, orgu_ua.orgu_id
						FROM (
							select obj_id, usr_id from obj_members where member = 1
						UNION
							select obj_id, usr_id from crs_waiting_list
						UNION
							select obj_id, usr_id from il_subscribers
						) as crs_members
						INNER JOIN object_reference as crs_members_crs_ref on crs_members_crs_ref.obj_id = crs_members.obj_id
						INNER JOIN il_orgu_ua as orgu_ua on orgu_ua.user_id = crs_members.usr_id
			  );";

		$ilDB->manipulate($q);

		//echo $q;

		return true;
	}


	public function buildTempTableOrguMembers($temporary_table_name = 'tmp_orgu_members') {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$this->dropTempTable($temporary_table_name);

		$q = "CREATE TEMPORARY TABLE IF NOT EXISTS " . $temporary_table_name . " AS (
					SELECT  orgu_ua.orgu_id as orgu_id,
							tree_orgu.path as tree_path,
							tree_orgu.child as tree_child,
							tree_orgu.parent as tree_parent,
							tree_orgu.lft as tree_lft,
							tree_orgu.rgt as tree_rgt,
							orgu_ua.position_id as user_position_id,
							orgu_ua.user_id as user_id
							from
							il_orgu_ua AS orgu_ua
							LEFT JOIN tree AS tree_orgu ON tree_orgu.child = orgu_ua.orgu_id
			  );";

		$ilDB->manipulate($q);

		//echo $q;

		return true;
	}



	/**
	 * @return array
	 */
	/*
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
	}*/

	/**
	 * @param array $arr_orgu_ref_id
	 *
	 * @return array
	 */
	/*
	public static function getUsersByOrgus($arr_orgu_ref_id) {

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
	}*/

	/**
	 * @param $temporary_table_name
	 *
	 * @return bool
	 */
	public function dropTempTable($temporary_table_name) {
		/**
		 * @var $ilDB \ilDBInterface
		 */
		$ilDB = $GLOBALS['DIC']->database();

		$q = "DROP TABLE IF EXISTS " . $temporary_table_name;
		//$ilDB->manipulate($q);

		//self::$temporary_table_name = null;

		return true;
	}
}
