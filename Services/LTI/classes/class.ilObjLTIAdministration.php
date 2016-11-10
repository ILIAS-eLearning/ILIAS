<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Object/classes/class.ilObject.php';

/**
 * Class ilObjLTIAdministration
 * @author Jesús López <lopez@leifos.com>
 *
 * @package ServicesLTI
 */
class ilObjLTIAdministration extends ilObject
{
	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "ltis";
		parent::__construct($a_id,$a_call_by_reference);
	}

	public function getLTIObjectTypes()
	{
		// TODO We need to call getLTIObjectTypesIds and then get the names of this object types
		$validLTIObjectTypes = array(
			'crs' => 'course',
			'sahs' => 'scorm',
			'svy' => "survey",
			'tst' => "test"
		);

		/*
		$obj_def = new ilObjectDefinition();
		$repository_objects = $obj_def->getCreatableSubObjects("root");
		var_dump($repository_objects);
		exit();
		*/

		return $validLTIObjectTypes;

	}

	public function getLTIObjectTypesIds()
	{
		return array('crs', 'sahs', 'svy', 'tst');
	}

	public function getLTIRoles()
	{
		global $rbacreview;

		require_once ("Services/AccessControl/classes/class.ilObjRole.php");

		$global_roles =  $rbacreview->getGlobalRoles();

		$filtered_roles = array_diff($global_roles, array(SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID));

		$roles = array();
		foreach ($filtered_roles as $role)
		{
			$obj_role = new ilObjRole($role);
			$roles[$role] = $obj_role->getTitle();
		}

		return $roles;
	}

	/**
	 * @param array $obj_types
	 * @param int $role
	 */
	public function saveData(array $a_obj_types, $a_role = 0)
	{
		global $ilDB;

		$ilDB->query("DELETE FROM lti_lti");

		$lti_obj_types = $this->getLTIObjectTypesIds();

		$obj_actives = array_intersect($lti_obj_types, $a_obj_types);
		$obj_inactives = array_diff($lti_obj_types, $a_obj_types);

		$query = "INSERT INTO lti_lti (obj_type_id,globalrole_id,active) VALUES (%s, %s, %s)";
		$types = array("text", "integer", "text");
		foreach ($obj_actives as $ot)
		{
			$values = array($ot, $a_role, '1');
			$ilDB->manipulateF($query,$types,$values);
		}
		foreach ($obj_inactives as $ot)
		{
			$values = array($ot, $a_role, '0');
			$ilDB->manipulateF($query,$types,$values);
		}
	}
}