<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
 * Membership notification settings
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesMembership
 */
class ilMembershipNotifications
{
	protected $ref_id; // [int]	
	protected $mode; // [int]
	protected $custom; // [array]
	protected $participants; // [ilParticipants]
	
	const VALUE_OFF = 0;
	const VALUE_ON = 1;
	const VALUE_BLOCKED = 2;
	
	const MODE_SELF = 1;
	const MODE_ALL = 2;
	const MODE_ALL_BLOCKED = 3;
	const MODE_CUSTOM = 4;
	
	/**
	 * Constructor
	 * 
	 * @param int $a_ref_id
	 * @return self
	 */
	public function __construct($a_ref_id)
	{
		$this->ref_id = (int)$a_ref_id;						
		$this->custom = array();
		$this->setMode(self::MODE_SELF);		
		
		if($this->ref_id)
		{
			$this->read();
		}	
	}
		
	/**
	 * Is feature active?
	 * 
	 * @return bool
	 */
	public static function isActive()
	{
		global $DIC;

		$ilSetting = $DIC['ilSetting'];
					
		return ($ilSetting->get("block_activated_news") &&
			$ilSetting->get("crsgrp_ntf"));		
	}
	
	/**
	 * Read from DB
	 */
	protected function read()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$set = $ilDB->query("SELECT nmode mode".
			" FROM member_noti".
			" WHERE ref_id = ".$ilDB->quote($this->ref_id, "integer"));
		if($ilDB->numRows($set))
		{
			$row = $ilDB->fetchAssoc($set);
			$this->setMode($row["mode"]);
				
			if($row["mode"] == self::MODE_CUSTOM)
			{
				$set = $ilDB->query("SELECT *".
					" FROM member_noti_user".
					" WHERE ref_id = ".$ilDB->quote($this->ref_id, "integer"));
				while($row = $ilDB->fetchAssoc($set))
				{
					$this->custom[$row["user_id"]] = $row["status"];
				}
			}
		}
	}	
	
	
	//
	// MODE
	//
	
	/**
	 * Get mode
	 * 
	 * @return int
	 */
	public function getMode()
	{
		return $this->mode;
	}
	
	/**
	 * Set mode	 
	 * 
	 * @param int $a_value
	 */
	protected function setMode($a_value)
	{		
		if($this->isValidMode($a_value))
		{
			$this->mode = $a_value;
		}
	}
	
	/**
	 * Is given mode valid?
	 * 
	 * @param int $a_value
	 * @return bool
	 */
	protected function isValidMode($a_value)
	{
		$valid = array(
			self::MODE_SELF
			,self::MODE_ALL
			,self::MODE_ALL_BLOCKED
			// ,self::MODE_CUSTOM currently used in forum
		);
		return in_array($a_value, $valid);
	}
	
	/**
	 * Switch mode for object
	 * 
	 * @param int $a_new_mode
	 * @return bool
	 */
	public function switchMode($a_new_mode)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if(!$this->ref_id)
		{
			return;
		}
				
		if($this->mode &&
			$this->mode != $a_new_mode &&
			$this->isValidMode($a_new_mode))
		{		
			$ilDB->manipulate("DELETE FROM member_noti".
				" WHERE ref_id = ".$ilDB->quote($this->ref_id, "integer"));				

			// no custom data
			if($a_new_mode != self::MODE_CUSTOM)
			{
				$ilDB->manipulate("DELETE FROM member_noti_user".
					" WHERE ref_id = ".$ilDB->quote($this->ref_id, "integer"));
			}
			
			// mode self is default
			if($a_new_mode != self::MODE_SELF)
			{
				$ilDB->insert("member_noti", array(
					"ref_id" => array("integer", $this->ref_id),
					"nmode" => array("integer", $a_new_mode)
				));
			}	
			
			// remove all user settings (all active is preset, optional opt out)
			if($a_new_mode == self::MODE_ALL)
			{
				$ilDB->manipulate("DELETE FROM usr_pref".
					" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_".$this->ref_id));
			}
		}
		
		$this->setMode($a_new_mode);
	}	
	
	
	//
	// ACTIVE USERS
	//
	
	/**
	 * Init participants for current object
	 *
	 * @return ilParticipants
	 */
	protected function getParticipants()
	{
		global $DIC;

		$tree = $DIC['tree'];
		
		if($this->participants === null)
		{		
			$this->participants = false;
			
			$grp_ref_id = $tree->checkForParentType($this->ref_id, "grp");
			if($grp_ref_id)
			{			
				include_once "Modules/Group/classes/class.ilGroupParticipants.php";
				$this->participants = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjId($grp_ref_id));			
			}

			if(!$this->participants)
			{
				$crs_ref_id = $tree->checkForParentType($this->ref_id, "crs");
				if($crs_ref_id)
				{			
					include_once "Modules/Course/classes/class.ilCourseParticipants.php";
					$this->participants =  ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjId($crs_ref_id));
				}
			}				
		}
		
		return $this->participants;
	}	
	
	/**
	 * Get active notifications for current object
	 *
	 * @return array
	 */
	public function getActiveUsers()
	{		
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$users = $all = array();
		
		$part_obj = $this->getParticipants();
		if($part_obj)
		{		
			$all = $part_obj->getParticipants();
		}
		if(!sizeof($all))
		{
			return array();
		}
		
		switch($this->getMode())
		{
			// users decide themselves
			case self::MODE_SELF:						
				$set = $ilDB->query("SELECT usr_id".
					" FROM usr_pref".
					" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_".$this->ref_id).
					" AND value = ".$ilDB->quote(self::VALUE_ON, "text"));
				while($row = $ilDB->fetchAssoc($set))
				{					
					$users[] = $row["usr_id"];			
				}
				break;
			
			// all members, mind opt-out
			case self::MODE_ALL:
				// users who did opt-out
				$inactive = array();
				$set = $ilDB->query("SELECT usr_id".
					" FROM usr_pref".
					" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_".$this->ref_id).
					" AND value = ".$ilDB->quote(self::VALUE_OFF, "text"));
				while($row = $ilDB->fetchAssoc($set))
				{					
					$inactive[] = $row["usr_id"];			
				}								
				$users = array_diff($all, $inactive);
				break;
			
			// all members, no opt-out
			case self::MODE_ALL_BLOCKED:				
				$users = $all;			
				break;
			
			// custom settings
			case self::MODE_CUSTOM:	
				foreach($this->custom as $user_id => $status)
				{
					if($status != self::VALUE_OFF)
					{
						$users[] = $user_id;
					}
				}				
				break;
		}
		
		// only valid participants
		return  array_intersect($all, $users);
	}
	
	
	//
	// USER STATUS
	//
	
	/**
	 * Activate notification for user
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function activateUser($a_user_id = null)
	{
		return $this->toggleUser(true, $a_user_id);	
	}
	
	/**
	 * Deactivate notification for user
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function deactivateUser($a_user_id = null)
	{
		return $this->toggleUser(false, $a_user_id);		
	}
	
	/**
	 * Init user instance
	 *
	 * @param int $a_user_id
	 * @return ilUser
	 */
	protected function getUser($a_user_id = null)
	{
		global $DIC;

		$ilUser = $DIC['ilUser'];
		
		if($a_user_id === null ||
			$a_user_id == $ilUser->getId())
		{
			$user = $ilUser;
		}
		else
		{			
			$user = new ilUser($a_user_id);		
		}
		
		if($user->getId() &&
			$user->getId() != ANONYMOUS_USER_ID)
		{		
			return $user;
		}
	}
	
	/**
	 * Toggle user notification status
	 * 
	 * @param bool $a_status
	 * @param int $a_user_id
	 * @return boolean
	 */
	protected function toggleUser($a_status, $a_user_id = null)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if(!self::isActive())
		{
			return;
		}
	
		switch($this->getMode())
		{
			case self::MODE_ALL:				
			case self::MODE_SELF:				
				// current user!					
				$user = $this->getUser();
				if($user)
				{
					// blocked value not supported in user pref!
					$user->setPref("grpcrs_ntf_".$this->ref_id, (int)(bool)$a_status);	
					$user->writePrefs();
					return true;
				}
				break;
			
			case self::MODE_CUSTOM:
				$user = $this->getUser($a_user_id);
				if($user)
				{
					$user_id = $user->getId();
					
					// did status change at all?
					if(!array_key_exists($user_id, $this->custom) ||
						$this->custom[$user_id != $a_status])
					{
						$this->custom[$user_id] = $a_status;

						$ilDB->replace("member_noti_user",
							array(
								"ref_id" => array("integer", $this->ref_id),
								"user_id" => array("integer", $user_id),
							),
							array(
								"status" => array("integer", $a_status)
							)
						);		
					}
					return true;		
				}
				break;
				
			case self::MODE_ALL_BLOCKED:
				// no individual settings
				break;			
		}
				
		return false;
	}
	
	
	//
	// CURRENT USER
	//
	
	/**
	 * Get user notification status
	 * 
	 * @return boolean
	 */
	public function isCurrentUserActive()
	{		
		global $DIC;

		$ilUser = $DIC['ilUser'];
		
		return in_array($ilUser->getId(), $this->getActiveUsers());
	}
	
	/**
	 * Can user change notification status?
	 * 
	 * @return boolean
	 */
	public function canCurrentUserEdit()
	{
		global $DIC;

		$ilUser = $DIC['ilUser'];
		
		$user_id = $ilUser->getId();
		if($user_id == ANONYMOUS_USER_ID)
		{
			return false;
		}
		
		switch($this->getMode())
		{
			case self::MODE_SELF:
			case self::MODE_ALL:
				return true;
				
			case self::MODE_ALL_BLOCKED:
				return false;
				
			case self::MODE_CUSTOM:
				return !(array_key_exists($user_id, $this->custom) &&
					$this->custom[$user_id] == self::VALUE_BLOCKED);			
		}
	}
	
	
	//
	// CRON
	//
	
	/**
	 * Get active notifications for all objects
	 * 
	 * @return array
	 */
	public static function getActiveUsersforAllObjects()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		$tree = $DIC['tree'];

		$log = ilLoggerFactory::getLogger("mmbr");


		$res = array();
				
		if(self::isActive())
		{			
			$objects = array();
			
			// user-preference data (MODE_SELF)
			$log->debug("read usr_pref");
			$set = $ilDB->query("SELECT DISTINCT(keyword) keyword".
				" FROM usr_pref".
				" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_%").
				" AND value = ".$ilDB->quote("1", "text"));
			while($row = $ilDB->fetchAssoc($set))
			{
				$ref_id = substr($row["keyword"], 11);					
				$objects[(int)$ref_id] = (int)$ref_id;
			}			
			
			// all other modes
			$log->debug("read member_noti");
			$set = $ilDB->query("SELECT ref_id".
				" FROM member_noti");
			while($row = $ilDB->fetchAssoc($set))
			{					
				$objects[(int)$row["ref_id"]] = (int)$row["ref_id"];
			}
			
			// this might be slow but it is to be used in CRON JOB ONLY!
			foreach(array_unique($objects) as $ref_id)
			{
				// :TODO: enough checking?
				if(!$tree->isDeleted($ref_id))
				{
					$log->debug("get active users");
					$noti = new self($ref_id);
					$active = $noti->getActiveUsers();
					if(sizeof($active))
					{
						$res[$ref_id] = $active;
					}
				}
			}
		}
		
		return $res;		
	}
	
	
	//
	// (OBJECT SETTINGS) FORM
	//
	
	/**
	 * Add notification settings to form 
	 *
	 * @param int $a_ref_id
	 * @param ilPropertyFormGUI $a_form
	 * @param ilPropertyFormGUI $a_input
	 */
	public static function addToSettingsForm($a_ref_id, ilPropertyFormGUI $a_form = null, ilFormPropertyGUI $a_input = null)
	{
		global $DIC;

		$lng = $DIC['lng'];
	
		if(self::isActive() &&
			$a_ref_id)
		{				
			$lng->loadLanguageModule("membership");			
			$noti = new self($a_ref_id);
			
			$force_noti = new ilRadioGroupInputGUI($lng->txt("mem_force_notification"), "force_noti");
			$force_noti->setRequired(true);
			if($a_form)
			{
				$a_form->addItem($force_noti);
			}
			else
			{
				$a_input->addSubItem($force_noti);
			}
			
			if($noti->isValidMode(self::MODE_SELF))
			{
				$option = new ilRadioOption($lng->txt("mem_force_notification_mode_self"), self::MODE_SELF);				
				$force_noti->addOption($option);
			}
			if($noti->isValidMode(self::MODE_ALL_BLOCKED))
			{
				$option = new ilRadioOption($lng->txt("mem_force_notification_mode_blocked"), self::MODE_ALL_BLOCKED);				
				$force_noti->addOption($option);	
				
				if($noti->isValidMode(self::MODE_ALL))
				{				
					$changeable = new ilCheckboxInputGUI($lng->txt("mem_force_notification_mode_all_sub_blocked"), "force_noti_allblk");			
					$option->addSubItem($changeable);
				}					
			}
			else if($noti->isValidMode(self::MODE_ALL))
			{				
				$option = new ilRadioOption($lng->txt("mem_force_notification_mode_all"), self::MODE_ALL);				
				$force_noti->addOption($option);
			}	
			/* not supported in GUI
			if($noti->isValidMode(self::MODE_CUSTOM))
			{
				$option = new ilRadioOption($lng->txt("mem_force_notification_mode_custom"), self::MODE_CUSTOM);
				$option->setInfo($lng->txt("mem_force_notification_mode_custom_info"));
				$force_noti->addOption($option);	
			}
			*/			
			
			// set current mode
			$current_mode = $noti->getMode();
			$has_changeable_cb = ($noti->isValidMode(self::MODE_ALL_BLOCKED) &&
				$noti->isValidMode(self::MODE_ALL));			
			if(!$has_changeable_cb)
			{
				$force_noti->setValue($current_mode);
			}
			else 
			{
				switch($current_mode)
				{
					case self::MODE_SELF:
						$force_noti->setValue($current_mode);
						$changeable->setChecked(true); // checked as "default" on selection of parent
						break;
					
					case self::MODE_ALL_BLOCKED:
						$force_noti->setValue($current_mode);
						break;
					
					case self::MODE_ALL:
						$force_noti->setValue(self::MODE_ALL_BLOCKED);
						$changeable->setChecked(true);
						break;
				}				
			}					
		}
	}
	
	/**
	 * Import notification settings from form 
	 *
	 * @param int $a_ref_id
	 * @param ilPropertyFormGUI $a_form
	 */
	public static function importFromForm($a_ref_id, ilPropertyFormGUI $a_form = null)
	{		
		if(self::isActive() &&
			$a_ref_id)
		{			
			$noti = new self($a_ref_id);
			$has_changeable_cb = ($noti->isValidMode(self::MODE_ALL_BLOCKED) &&
				$noti->isValidMode(self::MODE_ALL));
			$changeable = null;
			if(!$a_form)
			{
				$mode = (int)$_POST["force_noti"];
				if($has_changeable_cb)
				{
					$changeable = (int)$_POST["force_noti_allblk"];
				}
			}
			else
			{
				$mode = $a_form->getInput("force_noti");
				if($has_changeable_cb)
				{
					$changeable = $a_form->getInput("force_noti_allblk");
				}
			}							
			// checkbox (all) is subitem of all_blocked
			if($changeable &&
				$mode == self::MODE_ALL_BLOCKED)
			{
				$mode = self::MODE_ALL;
			}			
			$noti->switchMode($mode);			
		}
	}

	/**
	 * Clone notification object settings
	 *
     * @param $new_ref_id
     */
	public function cloneSettings($new_ref_id)
	{
	    global $ilDB;

	    $set = $ilDB->queryF("SELECT * FROM member_noti ".
	    	" WHERE ref_id = %s ",
	    	array("integer"),
	    	array($this->ref_id)
	    	);
	    while ($rec = $ilDB->fetchAssoc($set))
	    {
            $ilDB->insert("member_noti", array(
                "ref_id" => array("integer", $new_ref_id),
                "nmode" => array("integer", $rec["nmode"])
            ));
	    }


    }

}