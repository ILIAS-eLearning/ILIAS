<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise assignment team
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExAssignmentTeam
{	
	protected $id; // [int]
	protected $assignment_id; // [int]
	protected $members = array(); // [array]
	
	const TEAM_LOG_CREATE_TEAM = 1;
	const TEAM_LOG_ADD_MEMBER = 2;
	const TEAM_LOG_REMOVE_MEMBER = 3;
	const TEAM_LOG_ADD_FILE = 4;
	const TEAM_LOG_REMOVE_FILE = 5;		
	
	public function __construct($a_id = null)
	{
		if($a_id)
		{
			$this->read($a_id);			
		}
	}
	
	public static function getInstanceByUserId($a_assignment_id, $a_user_id, $a_create_on_demand = false)
	{
		$id = self::getTeamId($a_assignment_id, $a_user_id, $a_create_on_demand);
		return new self($id);
	}
	
	public static function getInstancesFromMap($a_assignment_id)
	{
		$teams = array();		
		foreach(self::getAssignmentTeamMap($a_assignment_id) as $user_id => $team_id)
		{
			$teams[$team_id][] = $user_id;
		}
		
		$res = array();
		foreach($teams as $team_id => $members)
		{
			$team = new self();
			$team->id = $team_id;
			$team->members = $members;
			$res[$team_id] = $team;
		}
		
		return $res;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	protected function read($a_id)
	{
		global $ilDB;
		
		// #18094
		$this->members = array();
		
		$sql = "SELECT * FROM il_exc_team".
			" WHERE id = ".$ilDB->quote($a_id, "integer");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			$this->id = $a_id;
		
			while($row = $ilDB->fetchAssoc($set))
			{
				$this->assignment_id = $row["ass_id"];
				$this->members[] = $row["user_id"];
			}	
		}
	}
	
	/**
	 * Get team id for member id
	 * 
	 * team will be created if no team yet
	 * 
	 * @param int $a_user_id
	 * @param bool $a_create_on_demand
	 * @return int 
	 */
	public static function getTeamId($a_assignment_id, $a_user_id, $a_create_on_demand = false)
	{
		global $ilDB;
		
		$sql = "SELECT id FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($a_assignment_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");
		$set = $ilDB->query($sql);
		$row = $ilDB->fetchAssoc($set);
		$id = $row["id"];
		
		if(!$id && $a_create_on_demand)
		{
			$id = $ilDB->nextId("il_exc_team");
			
			$fields = array("id" => array("integer", $id),
				"ass_id" => array("integer", $a_assignment_id),
				"user_id" => array("integer", $a_user_id));			
			$ilDB->insert("il_exc_team", $fields);		
			
			self::writeTeamLog($id, self::TEAM_LOG_CREATE_TEAM);						
			self::writeTeamLog($id, self::TEAM_LOG_ADD_MEMBER, 
				ilObjUser::_lookupFullname($a_user_id));
		}
		
		return $id;
	}
	
	/**
	 * Get members of assignment team
	 *  
	 * @return array
	 */
	public function getMembers()
	{
		return $this->members;
	}
	
	/**
	 * Get members for all teams of assignment
	 * 
	 * @return array 
	 */
	function getMembersOfAllTeams()
	{
		global $ilDB;
		
		$ids = array();
		
		$sql = "SELECT user_id".
			" FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($this->assignment_id, "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$ids[] = $row["user_id"];
		}
		
		return $ids;
	}
	
	/**
	 * Add new member to team
	 * 
	 * @param int $a_user_id 
	 * @param int $a_exc_ref_id 
	 */
	function addTeamMember($a_user_id, $a_exc_ref_id = null)
	{
		global $ilDB;
		
		if(!$this->id)
		{
			return false;
		}
		
		// must not be in any team already
		if(!in_array($a_user_id, $this->getMembersOfAllTeams()))
		{			
			$fields = array("id" => array("integer", $this->id),
				"ass_id" => array("integer", $this->assignment_id),
				"user_id" => array("integer", $a_user_id));			
			$ilDB->insert("il_exc_team", $fields);		
			
			if($a_exc_ref_id)
			{
				$this->sendNotification($a_exc_ref_id, $a_user_id, "add");
			}
			
			$this->writeLog(self::TEAM_LOG_ADD_MEMBER, 
				ilObjUser::_lookupFullname($a_user_id));
			
			$this->read($this->id);
			
			return true;
		}	
		
		return false;
	}
	
	/**
	 * Remove member from team
	 * 
	 * @param int $a_user_id 
	 * @param int $a_exc_ref_id 
	 */
	function removeTeamMember($a_user_id, $a_exc_ref_id = null)
	{
		global $ilDB;
		
		if(!$this->id)
		{
			return;
		}
		
		$sql = "DELETE FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($this->assignment_id, "integer").
			" AND id = ".$ilDB->quote($this->id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer");			
		$ilDB->manipulate($sql);		
			
		if($a_exc_ref_id)
		{
			$this->sendNotification($a_exc_ref_id, $a_user_id, "rmv");
		}
		
		$this->writeLog(self::TEAM_LOG_REMOVE_MEMBER, 
			ilObjUser::_lookupFullname($a_user_id));
		
		$this->read($this->id);
	}
	
	/**
	 * Get team structure for assignment 
	 * 
	 * @param int $a_ass_id
	 * @return array 
	 */
	public static function getAssignmentTeamMap($a_ass_id)
	{
		global $ilDB;
		
		$map = array();
		
		$sql = "SELECT * FROM il_exc_team".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$map[$row["user_id"]] = $row["id"];
		}
		
		return $map;
	}
	
	public function writeLog($a_action, $a_details = null)
	{
		self::writeTeamLog($this->id, $a_action, $a_details);
	}

	/**
	 * Add entry to team log
	 * 
	 * @param int $a_team_id
	 * @param int $a_action
	 * @param string $a_details 
	 */
	public static function writeTeamLog($a_team_id, $a_action, $a_details = null)
	{
		global $ilDB, $ilUser;
		
		$fields = array(
			"team_id" => array("integer", $a_team_id),
			"user_id" => array("integer", $ilUser->getId()),
			"action" => array("integer", $a_action),
			"details" => array("text", $a_details),
			"tstamp" => array("integer", time())
		);
		
		$ilDB->insert("il_exc_team_log", $fields);
	}
	
	/**
	 * Get all log entries for team
	 * 
	 * @param int $a_team_id
	 * @return array 
	 */
	public function getLog()
	{
		global $ilDB;
		
		$this->cleanLog();
		
		$res = array();
		
		$sql = "SELECT * FROM il_exc_team_log".
			" WHERE team_id = ".$ilDB->quote($this->id, "integer").
			" ORDER BY tstamp DESC";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		return $res;
	}			
	
	/**
	 * Remove obsolete log entries 
	 * 
	 * As there is no proper team deletion event, we are doing it this way
	 */
	protected function cleanLog()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT DISTINCT(log.team_id)".
			" FROM il_exc_team_log log".
			" LEFT JOIN il_exc_team team ON (team.id = log.team_id)".
			" WHERE team.id IS NULL");
		while($row = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("DELETE FROM il_exc_team_log".
				" WHERE team_id = ".$ilDB->quote($row["team_id"], "integer"));
		}
	}
	
	/**
	 * Send notification about team status
	 * 
	 * @param int $a_exc_ref_id
	 * @param int $a_user_id
	 * @param string $a_action
	 */
	public function sendNotification($a_exc_ref_id, $a_user_id, $a_action)
	{
		global $ilUser;
		
		// no need to notify current user
		if(!$a_exc_ref_id ||
			$ilUser->getId() == $a_user_id)
		{
			return;
		}		
		
	    $ass = new ilExAssignment($this->assignment_id);
				
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();
		$ntf->setLangModules(array("exc"));
		$ntf->setRefId($a_exc_ref_id);
		$ntf->setChangedByUserId($ilUser->getId());
		$ntf->setSubjectLangId('exc_team_notification_subject_'.$a_action);
		$ntf->setIntroductionLangId('exc_team_notification_body_'.$a_action);
		$ntf->addAdditionalInfo("exc_assignment", $ass->getTitle());	
		$ntf->setGotoLangId('exc_team_notification_link');				
		$ntf->setReasonLangId('exc_team_notification_reason');				
		$ntf->sendMail(array($a_user_id));		
	}
	
	
	public static function getAdoptableTeamAssignments($a_exercise_id, $a_exclude_ass_id = null, $a_user_id = null)
	{
		$res = array();
		
		$data = ilExAssignment::getAssignmentDataOfExercise($a_exercise_id);
		foreach($data as $row)
		{
			if($a_exclude_ass_id && $row["id"] == $a_exclude_ass_id)
			{
				continue;
			}
			
			if($row["type"] == ilExAssignment::TYPE_UPLOAD_TEAM)
			{
				$map = self::getAssignmentTeamMap($row["id"]);
				
				if($a_user_id && !array_key_exists($a_user_id, $map))
				{
					continue;
				}
				
				if(sizeof($map))
				{		
					$user_team = null;
					if($a_user_id)
					{
						$user_team_id = $map[$a_user_id];
						$user_team = array();
						foreach($map as $user_id => $team_id)
						{
							if($user_id != $a_user_id && 
								$user_team_id == $team_id)
							{
								$user_team[] = $user_id;
							}
						}							
					}
					
					if(!$a_user_id ||
						sizeof($user_team))
					{
						$res[$row["id"]] = array(
							"title" => $row["title"],
							"teams" => sizeof(array_flip($map)),
						);
						
						if($a_user_id)
						{
							$res[$row["id"]]["user_team"] = $user_team;
						}
					}					
				}
			}			
		}
		
		return ilUtil::sortArray($res, "title", "asc", false, true);
	}
	
	public static function adoptTeams($a_source_ass_id, $a_target_ass_id, $a_user_id = null, $a_exc_ref_id = null)
	{
		$teams = array();
		
		$old_team = null;
		foreach(self::getAssignmentTeamMap($a_source_ass_id) as $user_id => $team_id)
		{			
			$teams[$team_id][] = $user_id;
						
			if($a_user_id && $user_id == $a_user_id)
			{
				$old_team = $team_id;
			}		
		}
		
		if($a_user_id)
		{			 			
			// no existing team (in source) or user already in team (in current)
			if(!$old_team || 
				self::getInstanceByUserId($a_target_ass_id, $a_user_id)->getId())
			{
				return;
			}
		}
		
		$current_map = self::getAssignmentTeamMap($a_target_ass_id);
		
		foreach($teams as $team_id => $user_ids)
		{
			if(!$old_team || $team_id == $old_team)
			{
				// only not assigned users
				$missing = array();
				foreach($user_ids as $user_id)
				{
					if(!array_key_exists($user_id, $current_map))
					{
						$missing[] = $user_id;
					}
				}
				
				if(sizeof($missing))
				{
					// create new team
					$first = array_shift($missing);			
					$new_team = self::getInstanceByUserId($a_target_ass_id, $first, true);

					if($a_exc_ref_id)
					{	
						// getTeamId() does NOT send notification
						$new_team->sendNotification($a_exc_ref_id, $first, "add");
					}						
				
					foreach($missing as $user_id)
					{
						$new_team->addTeamMember($user_id, $a_exc_ref_id);
					}	
				}
			}
		}
	}
	
	// 
	// GROUPS
	//
	
	public static function getAdoptableGroups($a_exc_ref_id)
	{
		global $tree;
		
		$res = array();
				
		$parent_ref_id = $tree->getParentId($a_exc_ref_id);
		if($parent_ref_id)
		{
			foreach($tree->getChildsByType($parent_ref_id, "grp") as $group)
			{
				$res[] = $group["obj_id"];
			}
		}
		
		return $res;
	}
	
	public static function getGroupMembersMap($a_exc_ref_id)
	{					
		$res = array();
		
		include_once "Modules/Group/classes/class.ilGroupParticipants.php";
		foreach(self::getAdoptableGroups($a_exc_ref_id) as $grp_obj_id)
		{
			$members_obj = new ilGroupParticipants($grp_obj_id);
			
			$res[$grp_obj_id] = array(
				"title" => ilObject::_lookupTitle($grp_obj_id)
				,"members" =>  $members_obj->getMembers()
			);			
		}
		
		return ilUtil::sortArray($res, "title", "asc", false, true);
	}
}

