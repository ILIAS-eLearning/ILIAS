<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handle user timings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesCourse
 */
class ilTimingsUser
{
	private static $instances = array();
	
	private $container_obj_id = 0;
	private $container_ref_id = 0;
	
	private $initialized = FALSE;
	private $item_ids = array();


	
	
	/**
	 * Singleton constructor
	 */
	protected function __construct($a_container_obj_id)
	{
		$this->container_obj_id = $a_container_obj_id;
		
		$refs = ilObject::_getAllReferences($a_container_obj_id);
		$this->container_ref_id = end($refs);
	}
	
	/**
	 * Get instance by container id
	 * @param int $a_container_obj_id
	 * @return ilTimingsUser
	 */
	public static function getInstanceByContainerId($a_container_obj_id)
	{
		if(array_key_exists($a_container_obj_id, self::$instances))
		{
			return self::$instances[$a_container_obj_id];
		}
		return self::$instances[$a_container_obj_id] = new self($a_container_obj_id);
	}
	
	/**
	 * Get container obj id
	 */
	public function getContainerObjId()
	{
		return $this->container_obj_id;
	}
	
	/**
	 * Get container ref_id
	 */
	public function getContainerRefId()
	{
		return $this->container_ref_id;
	}
	
	public function getItemIds()
	{
		return $this->item_ids;
	}
	
	/**
	 * Init activation items
	 */
	public function init()
	{
		if($this->initialized)
		{
			return TRUE;
		}
		$this->item_ids = $GLOBALS['tree']->getSubTreeIds($this->getContainerRefId());
		
		include_once './Services/Object/classes/class.ilObjectActivation.php';
		ilObjectActivation::preloadData($this->item_ids);
		
		$this->initialized = TRUE;
	}


	/**
	 * @param int $a_usr_id
	 * @param ilDateTime $sub_date
	 * @throws ilDateTimeException
	 */
	public function handleNewMembership($a_usr_id, ilDateTime $sub_date)
	{
		foreach($this->getItemIds() as $item_ref_id)
		{
			include_once './Services/Object/classes/class.ilObjectActivation.php';
			$item = ilObjectActivation::getItem($item_ref_id);
			
			if($item['timing_type'] != ilObjectActivation::TIMINGS_PRESETTING)
			{
				continue;
			}
			
			include_once './Modules/Course/classes/Timings/class.ilTimingUser.php';
			$user_item = new ilTimingUser($item['obj_id'], $a_usr_id);
			
			$user_start = clone $sub_date;
			$user_start->increment(IL_CAL_DAY, $item['suggestion_start_rel']);
			$user_item->getStart()->setDate($user_start->get(IL_CAL_UNIX),IL_CAL_UNIX);
			
			$user_end = clone $sub_date;
			$user_end->increment(IL_CAL_DAY, $item['suggestion_end_rel']);
			$user_item->getEnd()->setDate($user_end->get(IL_CAL_UNIX), IL_CAL_UNIX);
			
			$user_item->update();
		}
	}
	
	/**
	 * Handle unsubscribe
	 * @param type $a_usr_id
	 */
	public function handleUnsubscribe($a_usr_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$query = 'DELETE FROM crs_timings_user WHERE '. $ilDB->in('ref_id',$this->item_ids, FALSE, 'integer').' '.
				'AND usr_id = '.$ilDB->quote($a_usr_id,'integer');
				
		$ilDB->manipulate($query);
		
	}
	
}
?>
