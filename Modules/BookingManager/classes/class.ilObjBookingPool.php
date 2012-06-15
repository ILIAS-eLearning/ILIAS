<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjBookingPool
* 
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
*/
class ilObjBookingPool extends ilObject
{
	protected $offline;			// bool
	protected $public_log;		// bool
	protected $schedule_type;	// int
	protected $slots_no;		// int
	
	const TYPE_FIX_SCHEDULE = 1;
	const TYPE_NO_SCHEDULE = 2;
	
	/**
	* Constructor
	* @param	int		$a_id					reference_id or object_id
	* @param	bool	$a_call_by_reference	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "book";
		$this->setScheduleType(self::TYPE_FIX_SCHEDULE);
		$this->ilObject($a_id,$a_call_by_reference);
	}
	
	/**
	 * Parse properties for sql statements 
	 */
	protected function getDBFields()
	{
		$fields = array(
			"schedule_type" => array("integer", $this->getScheduleType()),
			"pool_offline" => array("integer", $this->isOffline()),
			"public_log" => array("integer", $this->hasPublicLog()),
			"slots_no" => array("integer", $this->getNumberOfSlots())			
		);
		
		return $fields;
	}

	/**
	* create object
	* @return	integer
	*/
	function create()
	{
		global $ilDB;
		
		$new_id = parent::create();
		
		$fields = $this->getDBFields();
		$fields["booking_pool_id"] = array("integer", $new_id);

		$ilDB->insert("booking_settings", $fields);

		return $new_id;
	}

	/**
	* update object data
	* @return	boolean
	*/
	function update()
	{
		global $ilDB;
		
		if (!parent::update())
		{			
			return false;
		}

		// put here object specific stuff
		if($this->getId())
		{			
			$ilDB->update("booking_settings", $this->getDBFields(),
				array("booking_pool_id" => array("integer", $this->getId())));			
		}

		return true;
	}

	function read()
	{
		global $ilDB;
		
		parent::read();

		// put here object specific stuff
		if($this->getId())
		{
			$set = $ilDB->query('SELECT * FROM booking_settings'.
				' WHERE booking_pool_id = '.$ilDB->quote($this->getId(), 'integer'));
			$row = $ilDB->fetchAssoc($set);
			$this->setOffline($row['pool_offline']);
			$this->setPublicLog($row['public_log']);
			$this->setNumberOfSlots($row['slots_no']);
			$this->setScheduleType($row['schedule_type']);
		}
	}

	/**
	* delete object and all related data	
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB;

		$id = $this->getId();

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// put here your module specific stuff
		
		$ilDB->manipulate('DELETE FROM booking_settings'.
				' WHERE booking_pool_id = '.$ilDB->quote($id, 'integer'));

		$ilDB->manipulate('DELETE FROM booking_schedule'.
				' WHERE pool_id = '.$ilDB->quote($id, 'integer'));
		
		$objects = array();
		$set = $ilDB->query('SELECT booking_object_id FROM booking_object'.
			' WHERE pool_id = '.$ilDB->quote($id, 'integer'));
		while($row = $ilDB->fetchAssoc($set))
		{
			$objects[] = $row['booking_object_id'];
		}

		if(sizeof($objects))
		{
			$ilDB->manipulate('DELETE FROM booking_reservation'.
					' WHERE '.$ilDB->in('object_id', $objects, '', 'integer'));
		}

		$ilDB->manipulate('DELETE FROM booking_object'.
			' WHERE pool_id = '.$ilDB->quote($id, 'integer'));

		return true;
	}
	
	/**
	* init default roles settings
	* 
	* If your module does not require any default roles, delete this method 
	* (For an example how this method is used, look at ilObjForum)
	*
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;
		
		// create a local role folder
		//$rfoldObj = $this->createRoleFolder("Local roles","Role Folder of forum obj_no.".$this->getId());

		// create moderator role and assign role to rolefolder...
		//$roleObj = $rfoldObj->createRole("Moderator","Moderator of forum obj_no.".$this->getId());
		//$roles[] = $roleObj->getId();

		//unset($rfoldObj);
		//unset($roleObj);

		return $roles ? $roles : array();
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
	*
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;
		
		switch ($a_event)
		{
			case "link":
				
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "cut":
				
				//echo "Module name ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;
				
			case "copy":
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":
				
				//echo "Module name ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "new":
				
				//echo "Module name ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}

		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}
		
		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}

	/**
	 * Toggle offline property
	 * @param bool $a_value
	 */
	function setOffline($a_value = true)
    {
		$this->offline = (bool)$a_value;
	}

	/**
	 * Get offline property
	 * @return bool
	 */
	function isOffline()
	{
		return (bool)$this->offline;
	}

	/**
	 * Toggle public log property
	 * @param bool $a_value
	 */
	function setPublicLog($a_value = true)
    {
		$this->public_log = (bool)$a_value;
	}

	/**
	 * Get public log property
	 * @return bool
	 */
	function hasPublicLog()
	{
		return (bool)$this->public_log;
	}

	/**
	 * Set number of slots in schedules
	 * @param int $a_value
	 */
	function setNumberOfSlots($a_value = true)
    {
		$this->slots_no = (int)$a_value;
	}

	/**
	 * Get number of slots in schedules
	 * @return int
	 */
	function getNumberOfSlots()
	{
		return (int)$this->slots_no;
	}
	
	/**
	 * Set schedule type
	 * @param int $a_value
	 */
	function setScheduleType($a_value)
    {
		$this->schedule_type = (int)$a_value;
	}

	/**
	 * Get schedule type
	 * @return int
	 */
	function getScheduleType()
	{
		return $this->schedule_type;
	}
}

?>