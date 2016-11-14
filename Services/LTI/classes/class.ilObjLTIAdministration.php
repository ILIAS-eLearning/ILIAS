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
	/**
	 * @var array
	 */
	protected $obj_types_available;

	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "ltis";

		$this->obj_types_available = array(
			'crs',
			'sahs',
			'svy',
			'tst'
		);
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	 * @return array object type id => object type class name
	 */
	public function getLTIObjectTypes()
	{
		$obj_def = new ilObjectDefinition();
		$repository_objects = $obj_def->getCreatableSubObjects("root");
		$id_types = array();
		foreach ($repository_objects as $key => $value)
		{
			array_push($id_types, $key);
		}
		$match_array = array_intersect($id_types, $this->obj_types_available);

		$validLTIObjectTypes = array();
		foreach ($match_array as $obj_type)
		{
			$validLTIObjectTypes[$obj_type] = $obj_def->getClassName($obj_type);
		}

		return $validLTIObjectTypes;

	}

	/**
	 * @return array object type ids available for LTI
	 */
	public function getLTIObjectTypesIds()
	{
		return $this->obj_types_available;
	}

	/**
	 * @return array available roles for LTI
	 */
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
	 * @param integer $a_consumer_id
	 * @param array $a_obj_types
	 */
	public function saveConsumerObjectTypes($a_consumer_id, $a_obj_types)
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM lti_ext_consumer_otype WHERE consumer_id = ".$ilDB->quote($a_consumer_id, "integer"));

		if($a_obj_types)
		{
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