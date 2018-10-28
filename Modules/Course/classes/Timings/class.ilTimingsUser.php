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
	
	/**
	 * lookup users, references with exceeded timings
	 * @global type $ilDB
	 * @param array $usr_ids
	 */
	public static function lookupTimingsExceeded(array $usr_ids)
	{
		global $ilDB;
		
		// get all relevant courses
		include_once './Services/Membership/classes/class.ilParticipants.php';
		$course_obj_ids = ilParticipants::_getMembershipByType($usr_ids, 'crs', TRUE);

		$GLOBALS['ilLog']->write(__METHOD__.': '. print_r($course_obj_ids,TRUE));
		
		$exceeded = array();
		foreach($course_obj_ids as $crs_obj_id)
		{
			// lookup timing settings
			include_once './Modules/Course/classes/class.ilCourseConstants.php';
			$query = 'SELECT timing_mode from crs_settings '.
					'WHERE obj_id = '.$ilDB->quote($crs_obj_id,'integer').' '.
					'AND view_mode = '.$ilDB->quote(IL_CRS_VIEW_TIMING,'integer').' '.
					'AND timing_mode = '.$ilDB->quote(1, 'integer');
			$res = $ilDB->query($query);

			$relative_timings = FALSE;
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$relative_timings = TRUE;
			}
			if(!$relative_timings)
			{
				continue;
			}
			// get ref_id of course
			$course_ref_ids = ilObject::_getAllReferences($crs_obj_id);
			$course_ref_id = end($course_ref_ids);
			
			$subtree_query = $GLOBALS['tree']->getSubTreeQuery(
					$course_ref_id, 
					array('child'), 
					'', 
					FALSE
			);
			
			$users_exceeded = 'SELECT ctu.ref_id, ctu.usr_id FROM crs_timings_user ctu '.
					'JOIN object_reference obr ON ctu.ref_id = obr.ref_id '.
					'JOIN ut_lp_marks ulm ON obr.obj_id = ulm.obj_id AND ctu.usr_id = ulm.usr_id '.
					'WHERE ctu.ref_id IN ('.$subtree_query.') '.
					'AND status != '.$ilDB->quote(2,'integer').' '.
					'AND ssend <= '.$ilDB->quote(time(),'integer').' '.
					'AND '.$ilDB->in('ctu.usr_id',$usr_ids,FALSE, 'integer');
			
			$GLOBALS['ilLog']->write(__METHOD__.' '. $users_exceeded);
			
			$res = $ilDB->query($users_exceeded);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$exceeded[$row->usr_id][] = $row->ref_id;
			}
		}
		
		$GLOBALS['ilLog']->write(print_r($exceeded,TRUE));
		
		return $exceeded;
	}
}
?>
