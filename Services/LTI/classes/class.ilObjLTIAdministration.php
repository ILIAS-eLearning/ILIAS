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

	public function saveConsumerObjectTypes($a_consumer_id, array $a_obj_types)
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM lti_ext_consumer_otype WHERE consumer_id = ".$ilDB->quote($a_consumer_id, "integer"));

		$lti_obj_types = $this->getLTIObjectTypesIds();

		$obj_actives = array_intersect($lti_obj_types, $a_obj_types);

		$query = "INSERT INTO lti_ext_consumer_otype (consumer_id, object_type) VALUES (%s, %s)";
		$types = array("integer", "text");
		foreach ($obj_actives as $ot)
		{
			$values = array($a_consumer_id, $ot);
			$ilDB->manipulateF($query,$types,$values);
		}
	}

	/**
	 * @param integer $a_consumer_id
	 * @return array consumer active objects
	 */
	public function getActiveObjectTypes($a_consumer_id)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$result = $ilDB->query("SELECT object_type FROM lti_ext_consumer_otype WHERE consumer_id = ".$ilDB->quote($a_consumer_id, "integer"));

		$obj_ids = array();
		while ($record = $ilDB->fetchAssoc($result))
		{
			array_push($obj_ids, $record['object_type']);
		}
		return $obj_ids;
	}
}