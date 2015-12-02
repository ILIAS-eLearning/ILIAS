<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/
//BEGIN PATCH HSLU Postbox
/**
* Class ilPostboxHelper
*
* @author Simon Moor <simon.moor@hslu.ch>
*
* @version $Id: class.ilPostboxHelper.php 26179 2010-10-27 11:00:00Z smoor $
*
*/
class ilPostboxHelper
{
    static function _makePostbox($ref_id){
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';
		$innermost_member_role_id = ilPermissionHelper::_getInnermostRoleId($ref_id,'member');
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();
		$result = true;
		if ($innermost_member_role_id != null) {
			ilPermissionHelper::_removeLocalPolicy($innermost_member_role_id, $ref_id);
			$result = ilPermissionHelper::_setLocalPolicy($innermost_member_role_id, $ref_id,
						'_fold_drop_box','_fold_drop_box');
		}
		if ($result == true && $global_user_role_id != null) {
				if ($innermost_member_role_id == null) {
						ilPermissionHelper::_removeLocalPolicy($global_user_role_id, $ref_id);
						$result = ilPermissionHelper::_setLocalPolicy($global_user_role_id, $ref_id,
								'_fold_drop_box','_fold_drop_box');
				}
		}
		if ($result !== true) {
				//$this->ilias->raiseError($result, $this->ilias->error_obj->MESSAGE);
		}
	}
/*
	static function _makePostboxAll($ref_id,$defaultName){
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';

		$type_array = array('fold');
		$title_array = array('Briefkasten','Drop Box','Postbox','DropBox');
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();
		$innermost_member_role_id = ilPermissionHelper::_getInnermostRoleId($ref_id,'member');
		$resultDropBox = ilPermissionHelper::_setInnermostLocalPolicyInSubtree(
						'member',
						$ref_id,
						$type_array, $title_array,
						'_fold_drop_box', '_fold_drop_box');
		if ($global_user_role_id != null) {
				if ($innermost_member_role_id != null) {
						ilPermissionHelper::_setLocalPolicyInSubtree(
								$global_user_role_id,
								$ref_id,
								$type_array, $title_array,
								'_fold_no_access','_fold_no_access');
				} else {
						ilPermissionHelper::_setLocalPolicyInSubtree(
								$global_user_role_id,
								$ref_id,
								$type_array, $title_array,
								'_fold_drop_box','_fold_drop_box');
				}
		}

		//ilUtil::sendInfo(sprintf($this->lng->txt("fold_drop_box_processed"),$resultDropBox),true);
	}
*/
	static function _makeExchangeFolder($ref_id){
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';
		$innermost_member_role_id = ilPermissionHelper::_getInnermostRoleId($ref_id,'member');
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();
		$result = true;
		if ($innermost_member_role_id != null) {
			ilPermissionHelper::_removeLocalPolicy($innermost_member_role_id, $ref_id);
			$result = ilPermissionHelper::_setLocalPolicy($innermost_member_role_id, $ref_id,
				'_fold_file_exchange_folder', '_fold_file_exchange_folder');
		}
		if ($result == true && $global_user_role_id != null) {
			if ($innermost_member_role_id == null) {
					ilPermissionHelper::_removeLocalPolicy($global_user_role_id, $ref_id);
					$result = ilPermissionHelper::_setLocalPolicy($global_user_role_id, $ref_id,
				'_fold_file_exchange_folder', '_fold_file_exchange_folder');
			}
		}
		
		global $rbacreview;

		$getActiveOperationsOfRole = $rbacreview->getActiveOperationsOfRole($ref_id, (($innermost_member_role_id==null)?$global_user_role_id:$innermost_member_role_id));
		
		if(in_array(4,$getActiveOperationsOfRole) OR in_array(6,$getActiveOperationsOfRole)){
			
			unset($getActiveOperationsOfRole[array_search(4, $getActiveOperationsOfRole)]);
			unset($getActiveOperationsOfRole[array_search(6, $getActiveOperationsOfRole)]);
			
			global $ilDB;
		
			$GLOBALS['ilLog']->write('DROPBOX HELPER ..............................' );
	
			$query = 'UPDATE rbac_pa '.
				'SET ops_id = "'.serialize($getActiveOperationsOfRole).'" '.
				'WHERE ref_id = '.$ilDB->quote($ref_id,'integer').' '.
				'AND rol_id = '.$ilDB->quote((($innermost_member_role_id==null)?$global_user_role_id:$innermost_member_role_id),'integer').' ';
			
			//print $query;
			
			$res = $ilDB->query($query);
			
		}
		
		//print_r($getActiveOperationsOfRole);exit;
		
		if ($result !== true) {
			//$this->ilias->raiseError($result, $this->ilias->error_obj->MESSAGE);
		}
	}
/*
	static function _makeExchangeFolderAll($ref_id){
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';

		$type_array = array('fold');
		$title_array = array('Dateiaustausch','Datenaustausch','File Exchange','Austausch');
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();
		$result = ilPermissionHelper::_setInnermostLocalPolicyInSubtree(
				'member',
				$ref_id,
				$type_array, $title_array,
				'_fold_file_exchange_folder', '_fold_file_exchange_subfolder');
		if ($global_user_role_id != null) {
				if ($innermost_member_role_id != null) {
						ilPermissionHelper::_setLocalPolicyInSubtree(
								$global_user_role_id,
								$ref_id,
								$type_array, $title_array,
								'_fold_no_access','_fold_no_access');
				} else {
						ilPermissionHelper::_setLocalPolicyInSubtree(
								$global_user_role_id,
								$ref_id,
								$type_array, $title_array,
						'_fold_file_exchange_folder', '_fold_file_exchange_subfolder');
				}
		}

		//ilUtil::sendInfo(sprintf($this->lng->txt("fold_file_exchange_processed"),$result),true);
	}
*/      
	static function _makeNormalFolder($ref_id){
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';
		$innermost_member_role_id = ilPermissionHelper::_getInnermostRoleId($ref_id,'member');
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();
		$result = true;
		if ($innermost_member_role_id != null) {
				$result = ilPermissionHelper::_removeLocalPolicy($innermost_member_role_id, $ref_id);
		}
		if ($result == true && $global_user_role_id != null) {
				$result = ilPermissionHelper::_removeLocalPolicy($global_user_role_id, $ref_id);
		}
		global $rbacadmin;
		$rbacadmin->grantPermission((($innermost_member_role_id==null)?$global_user_role_id:$innermost_member_role_id),array(2,3),$ref_id);
		if ($result !== true) {
				//$this->ilias->raiseError($result, $this->ilias->error_obj->MESSAGE);
		}
	}

	static function _makeGroupFolder($ref_id){
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';
		$innermost_member_role_id = ilPermissionHelper::_getInnermostRoleId($ref_id,'member');
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();
		$result = true;
		if ($innermost_member_role_id != null) {
				$result = ilPermissionHelper::_removeLocalPolicy($innermost_member_role_id, $ref_id);
		}
		if ($result == true && $global_user_role_id != null) {
				$result = ilPermissionHelper::_removeLocalPolicy($global_user_role_id, $ref_id);
		}
		global $rbacadmin;
		$rbacadmin->grantPermission((($innermost_member_role_id==null)?$global_user_role_id:$innermost_member_role_id),array(2,3,17),$ref_id);
		if ($result !== true) {
				//$ilias->raiseError($result, $this->ilias->error_obj->MESSAGE);
		}
	}
/*
	static function _setOpenCourseAll($ref_id){
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';
		$type_array = array('crs');
		$title_array = null;
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();
		$result = ilPermissionHelper::_setLocalPolicyInSubtree(
			$global_user_role_id,
			$ref_id,
			$type_array, $title_array,
			'_fold_open_course', '_fold_open_course');
		if ($result !== true) {
				//$ilias->raiseError($result, $this->ilias->error_obj->MESSAGE);
		}
	}
*/
/*
	static function _setReadonlyCourseAll($ref_id){
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';
		$type_array = array('crs');
		$title_array = null;
		$resultCrs = 0;
		$resultGrp = 0;
		$resultFoldFileExchange = 0;
		$resultFoldDropBox = 0;
		$result = true;
		$resultCrs = ilPermissionHelper::_setInnermostLocalPolicyInSubtree(
				'tutor',
				$ref_id,
				$type_array, $title_array,
				'_fold_readonly', '_fold_readonly');
		$result = ilPermissionHelper::_setInnermostLocalPolicyInSubtree(
				'member',
				$ref_id,
				$type_array, $title_array,
				'_fold_readonly', '_fold_readonly');
		$type_array = array('grp');
		$title_array = null;
		$resultGrp = ilPermissionHelper::_setInnermostLocalPolicyInSubtree(
				'admin',
				$ref_id,
				$type_array, $title_array,
				'il_fold_readonly', '_fold_readonly');
		$result = ilPermissionHelper::_setInnermostLocalPolicyInSubtree(
				'member',
				$ref_id,
				$type_array, $title_array,
				'_fold_readonly', '_fold_readonly');
		//--
		$type_array = array('fold');
		$title_array = array('Dateiaustausch','Datenaustausch','File Exchange','Austausch');
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();
		$resultFoldFileExchange = ilPermissionHelper::_setInnermostLocalPolicyInSubtree(
				'member',
				$ref_id,
				$type_array, $title_array,
				'_fold_readonly', '_fold_readonly');
		$result = ilPermissionHelper::_setLocalPolicyInSubtree(
				$global_user_role_id,
				$ref_id,
				$type_array, $title_array,
				'_fold_no_access', '_fold_no_access');
		//--
		$type_array = array('fold');
		$title_array = array('Briefkasten','Drop Box');
		$resultFoldDropBox = ilPermissionHelper::_setInnermostLocalPolicyInSubtree(
				'member',
				$ref_id,
				$type_array, $title_array,
				'_fold_visibleonly', '_fold_visibleonly');
		$result = ilPermissionHelper::_setLocalPolicyInSubtree(
				$global_user_role_id,
				$ref_id,
				$type_array, $title_array,
				'_fold_visibleonly', '_fold_visibleonly');

	}
*/
	
	static function _createBildName($ref_id,$defaultName,$objIdMode=false){

	if($ref_id==NULL) return $defaultName;

	if(strstr($_SERVER['SCRIPT_URL'],'feed.php') OR $defaultName!='fold' ) return $defaultName;

		if($objIdMode){
			global $ilDB;
			$q = "SELECT ref_id FROM object_reference WHERE obj_id = ".$ilDB->quote($ref_id,'integer');
			$r = $ilDB->query($q);
			while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT)) {
				$ref_id=$row->ref_id;
			}
			
		}
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';
		$innermost_member_role_id = ilPermissionHelper::_getInnermostRoleId($ref_id,'member');
		$global_user_role_id = ilPermissionHelper::_getGlobalUserRoleId();

		global $rbacreview;

		$getActiveOperationsOfRole = $rbacreview->getActiveOperationsOfRole($ref_id, (($innermost_member_role_id==null)?$global_user_role_id:$innermost_member_role_id));

		//TODO: hier noch lugen ob lokale rolle veraendert wurde
		//print $innermost_member_role_id.'<br />';
		if(in_array(2,$getActiveOperationsOfRole)
				AND in_array(3,$getActiveOperationsOfRole)
				AND in_array(25,$getActiveOperationsOfRole)
				AND in_array(26,$getActiveOperationsOfRole)
				AND in_array(50,$getActiveOperationsOfRole) ){
			return 'fexch';
		}
		if(in_array(2,$getActiveOperationsOfRole)
				AND in_array(3,$getActiveOperationsOfRole)
				AND in_array(25,$getActiveOperationsOfRole) ){
			return 'drop';
		}
		if(in_array(2,$getActiveOperationsOfRole)
				AND in_array(3,$getActiveOperationsOfRole)
				AND in_array(17,$getActiveOperationsOfRole) ){
			return 'fldgrp';
		}

		return $defaultName;
	}



}
//END PATCH HSLU Postbox
?>