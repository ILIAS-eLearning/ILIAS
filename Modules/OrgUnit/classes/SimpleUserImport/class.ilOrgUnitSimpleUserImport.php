<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Modules/OrgUnit/classes/class.ilOrgUnitImporter.php');

/**
 * Class ilOrgUnitSimpleUserImport
 *
 * @author : Oskar Truffer <ot@studer-raimann.ch>
 * @author : Martin Studer <ms@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 *
 */
class ilOrgUnitSimpleUserImport extends ilOrgUnitImporter {

	/**
	 * @param $file_path
	 */
	public function simpleUserImport($file_path) {
		$this->stats = array( 'created' => 0, 'removed' => 0 );
		$a = file_get_contents($file_path, 'r');
		$xml = new SimpleXMLElement($a);

		if (!count($xml->Assignment)) {
			$this->addError('no_assignment', NULL, NULL);

			return;
		}

		foreach ($xml->Assignment as $a) {
			$this->simpleUserImportElement($a);
		}
	}


	/**
	 * @param SimpleXMLElement $a
	 */
	public function simpleUserImportElement(SimpleXMLElement $a) {
		global $DIC;
		$rbacadmin = $DIC['rbacadmin'];

		$attributes = $a->attributes();
		$action = $attributes->action;
		$user_id_type = $a->User->attributes()->id_type;
		$user_id = (string)$a->User;
		$org_unit_id_type = $a->OrgUnit->attributes()->id_type;
		$org_unit_id = (string)$a->OrgUnit;
		$role = (string)$a->Role;

		if (!$user_id = $this->buildUserId($user_id, $user_id_type)) {
			$this->addError('user_not_found', $a->User);

			return;
		}

		if (!$org_unit_id = $this->buildRef($org_unit_id, $org_unit_id_type)) {
			$this->addError('org_unit_not_found', $a->OrgUnit);

			return;
		}
		$org_unit = new ilObjOrgUnit($org_unit_id);

		if ($role == 'employee') {
			$role_id = $org_unit->getEmployeeRole();
		} elseif ($role == 'superior') {
			$role_id = $org_unit->getSuperiorRole();
		} else {
			$this->addError('not_a_valid_role', $user_id);

			return;
		}

		if ($action == 'add') {
			$rbacadmin->assignUser($role_id, $user_id);
			$this->stats['created'] ++;
		} elseif ($action == 'remove') {
			$rbacadmin->deassignUser($role_id, $user_id);
			$this->stats['removed'] ++;
		} else {
			$this->addError('not_a_valid_action', $user_id);
		}
	}


	/**
	 * @param $id
	 * @param $type
	 *
	 * @return bool
	 */
	private function buildUserId($id, $type) {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		if ($type == 'ilias_login') {
			$user_id = ilObjUser::_lookupId($id);

			return $user_id ? $user_id : false;
		} elseif ($type == 'external_id') {
			$user_id = ilObjUser::_lookupObjIdByImportId($id);

			return $user_id ? $user_id : false;
		} elseif ($type == 'email') {
			$q = 'SELECT usr_id FROM usr_data WHERE email = ' . $ilDB->quote($id, 'text');
			$set = $ilDB->query($q);
			$user_id = $ilDB->fetchAssoc($set);

			return $user_id ? $user_id : false;
		} elseif ($type == 'user_id') {
			return $id;
		} else {
			return false;
		}
	}
}

?>