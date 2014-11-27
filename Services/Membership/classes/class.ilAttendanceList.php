<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Base class for attendance lists
 * 
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesMembership 
*/
class ilAttendanceList
{
	protected $parent_obj; // [object]
	protected $participants; // [object]
	protected $waiting_list; // [object]
	protected $callback; // [string|array]
	protected $presets; // [array]	
	protected $role_data; // [array]
	protected $roles; // [array]
	protected $has_local_role; // [bool]
	protected $blank_columns; // [array]
	protected $title; // [string]
	protected $description; // [string]
	protected $pre_blanks; // [array]
	protected $id; // [string]
	protected $include_waiting_list; // [bool]
	protected $include_subscribers;  // [bool]		
	protected $user_filters; // [array]
		
	/**
	 * Constructor
	 * 
	 * @param object $a_parent_obj
	 * @param ilParticipants $a_participants_object
	 * @param ilWaitingList $a_waiting_list
	 */
	function __construct($a_parent_obj, ilParticipants $a_participants_object = null, ilWaitingList $a_waiting_list = null)
	{	
		global $lng;
		
		$this->parent_obj = $a_parent_obj;
		$this->participants = $a_participants_object;
		$this->waiting_list = $a_waiting_list;
		
		// always available
		$this->presets['name'] = array($lng->txt('name'), true);
		$this->presets['login'] = array($lng->txt('login'), true);
		$this->presets['email'] = array($lng->txt('email'));	
		
		$lng->loadLanguageModule('crs');
		
		// roles
		$roles = $this->participants->getRoles();
		foreach($roles as $role_id)
		{
			$title = ilObject::_lookupTitle($role_id);
			switch(substr($title, 0, 8))
			{
				case 'il_crs_a':
				case 'il_grp_a':					
					$this->addRole($role_id, $lng->txt('event_tbl_admins'), 'admin');					
					break;
				
				case 'il_crs_t':					
					$this->addRole($role_id, $lng->txt('event_tbl_tutors'), 'tutor');					
					break;
				
				case 'il_crs_m':
				case 'il_grp_m':
					$this->addRole($role_id, $lng->txt('event_tbl_members'), 'member');
					break;
				
				// local
				default:
					$this->has_local_role = true;
					$this->addRole($role_id, $title, 'local');
					break;
			}			
		}			
	}
	
	/**
	 * Add user field
	 * 
	 * @param string $a_id
	 * @param string $a_caption
	 * @param bool $a_selected 
	 */
	function addPreset($a_id, $a_caption, $a_selected = false)
	{
		$this->presets[$a_id] = array($a_caption, $a_selected);
	}
	
	/**
	 * Add blank column preset
	 * 
	 * @param string $a_caption
	 */
	function addBlank($a_caption)
	{
		$this->pre_blanks[] = $a_caption;
	}
	
	/**
	 * Set titles
	 * 
	 * @param string $a_title
	 * @param string $a_description
	 */
	function setTitle($a_title, $a_description = null)
	{
		$this->title = $a_title;
		$this->description = $a_description;
	}
	
	/**
	 * Add role
	 * 
	 * @param int $a_id
	 * @param string $a_caption
	 * @param string $a_type
	 */
	protected function addRole($a_id, $a_caption, $a_type)
	{
		$this->role_data[$a_id] = array($a_caption, $a_type);
	}
	
	/**
	 * Set role selection
	 * 
	 * @param array $a_role_ids
	 */
	protected function setRoleSelection($a_role_ids)
	{
		$this->roles = $a_role_ids;
	}
	
	/**
	 * Add user filter 
	 * 
	 * @param int $a_id
	 * @param string $a_caption
	 * @param bool $a_checked
	 */
	function addUserFilter($a_id, $a_caption, $a_checked = false)
	{
		$this->user_filters[$a_id] = array($a_caption, $a_checked);
	}
	
	/**
	 * Get user data for subscribers and waiting list
	 * 
	 * @param array &$a_res
	 */
	function getNonMemberUserData(array &$a_res)
	{
		global $lng;
		
		$subscriber_ids = $this->participants->getSubscribers();
		
		$user_ids = $subscriber_ids;		
		
		if($this->waiting_list)
		{
			$user_ids = array_merge($user_ids, $this->waiting_list->getUserIds());
		}
		
		if(sizeof($user_ids))
		{
			foreach(array_unique($user_ids) as $user_id)
			{					
				if(!isset($a_res[$user_id]))
				{
					if($tmp_obj = ilObjectFactory::getInstanceByObjId($user_id, false))
					{
						$a_res[$user_id]['login'] = $tmp_obj->getLogin();
						$a_res[$user_id]['name'] = $tmp_obj->getLastname().', '.$tmp_obj->getFirstname();		
						$a_res[$user_id]['email'] = $tmp_obj->getEmail();		

						if(in_array($user_id, $subscriber_ids))
						{
							$a_res[$user_id]['status'] = $lng->txt('crs_subscriber'); 
						}
						else
						{
							$a_res[$user_id]['status'] = $lng->txt('crs_waiting_list'); 
						}
					}			
				}
			}
		}
	}
	
	/**
	 * Add blank columns
	 * 
	 * @param array $a_value 
	 */
	function setBlankColumns(array $a_values)
	{
		if(!implode("", $a_values))
		{
			$a_values = array();
		}
		else
		{
			foreach($a_values as $idx => $value)
			{
				$a_values[$idx] = trim($value);
				if($a_values[$idx] == "")
				{
					unset($a_values[$idx]);
				}
			}
		}
		$this->blank_columns = $a_values;
	}

	/**
	 * Set participant detail callback
	 * 
	 * @param string|array $a_callback 
	 */
	function setCallback($a_callback)
	{
		$this->callback = $a_callback;
	}
	
	/**
	 * Set id (used for user form settings)
	 * 
	 * @param string $a_value 
	 */
	function setId($a_value)
	{
		$this->id = (string)$a_value;
	}
	
	/**
	 * Init form
	 *
	 * @param string $a_cmd
	 * @return ilPropertyFormGUI
	 */
	public function initForm($a_cmd = "")
	{
		global $ilCtrl, $lng;
	
		$lng->loadLanguageModule('crs');
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this->parent_obj,$a_cmd));
		$form->setTarget('_blank');
		$form->setPreventDoubleSubmission(false);
		$form->setTitle($lng->txt('sess_gen_attendance_list'));
		
		$title = new ilTextInputGUI($lng->txt('title'), 'title');
		$title->setValue($this->title);
		$form->addItem($title);
		
		$desc = new ilTextInputGUI($lng->txt('description'), 'desc');
		$desc->setValue($this->description);
		$form->addItem($desc);
		
		if(sizeof($this->presets))
		{
			$preset = new ilCheckboxGroupInputGUI($lng->txt('user_detail'), 'preset');		
			$preset_value = array();
			foreach($this->presets as $id => $item)
			{
				$preset->addOption(new ilCheckboxOption($item[0], $id));
				if($item[1])
				{
					$preset_value[] = $id;
				}
			}
			$preset->setValue($preset_value);
			$form->addItem($preset);
		}	
		
		$blank = new ilTextInputGUI($lng->txt('event_blank_columns'), 'blank');
		$blank->setMulti(true);		
		$form->addItem($blank);			
		
		if($this->pre_blanks)
		{
			$blank->setValue($this->pre_blanks);
		}
		
		$part = new ilFormSectionHeaderGUI();
		$part->setTitle($lng->txt('event_participant_selection'));
		$form->addItem($part);
		
		// participants by roles
		foreach($this->role_data as $role_id => $role_data)
		{
			$chk = new ilCheckboxInputGUI($role_data[0], 'role_'.$role_id);			
			$chk->setValue(1);
			$chk->setChecked(1);
			$form->addItem($chk);
		}
				
		// not in sessions
		if($this->waiting_list)
		{
			$chk = new ilCheckboxInputGUI($lng->txt('group_new_registrations'), 'subscr');			
			$chk->setValue(1);		
			$form->addItem($chk);		

			$chk = new ilCheckboxInputGUI($lng->txt('crs_waiting_list'), 'wlist');			
			$chk->setValue(1);
			$form->addItem($chk);
		}
			
		if($this->user_filters)
		{
			foreach($this->user_filters as $sub_id => $sub_item)
			{
				$sub = new ilCheckboxInputGUI($sub_item[0], 'members_'.$sub_id);
				if($sub_item[1])
				{
					$sub->setChecked(true);
				}
				$form->addItem($sub);
			}
		}
		
		$form->addCommandButton($a_cmd,$lng->txt('sess_print_attendance_list'));
		
		if($this->id && $a_cmd)
		{
			include_once "Services/User/classes/class.ilUserFormSettings.php";
			$settings = new ilUserFormSettings($this->id);
			$settings->deleteValue('desc'); // #11340
			$settings->exportToForm($form);
		}
		
		return $form;
	}
	
	/**
	 * Set list attributes from post values
	 */
	public function initFromForm()
	{		
		$form = $this->initForm();
		if($form->checkInput())
		{			
			foreach(array_keys($this->presets) as $id)
			{
				$this->presets[$id][1] = false;
			}
			foreach($form->getInput('preset') as $value)
			{
				if(isset($this->presets[$value]))
				{
					$this->presets[$value][1] = true;
				}
				else
				{
					$this->addPreset($value, $value, true);
				}
			}
			
			$this->setTitle($form->getInput('title'), $form->getInput('desc'));
			$this->setBlankColumns($form->getInput('blank'));	
			
			$roles = array();
			foreach(array_keys($this->role_data) as $role_id)
			{
				if($form->getInput('role_'.$role_id))
				{
					$roles[] = $role_id;
				}
			}
			$this->setRoleSelection($roles);
			
			// not in sessions
			if($this->waiting_list)
			{
				$this->include_subscribers = (bool)$form->getInput('subscr');			
				$this->include_waiting_list = (bool)$form->getInput('wlist');
			}
			
			if($this->user_filters)
			{
				foreach(array_keys($this->user_filters) as $msub_id)
				{
					$this->user_filters[$msub_id][2] = $form->getInput("members_".$msub_id);
				}			
			}				
			
			if($this->id)
			{
				$form->setValuesByPost();
				
				include_once "Services/User/classes/class.ilUserFormSettings.php";
				$settings = new ilUserFormSettings($this->id);
				$settings->deleteValue('desc'); // #11340
				$settings->importFromForm($form);
				$settings->store();
			}
			
		}		
	}
	
	/**
	 * render list in fullscreen mode
	 * 
	 * @return string
	 */
	public function getFullscreenHTML()
	{		
		$tpl = new ilTemplate('tpl.main.html',true,true);
		$tpl->setBodyClass("ilBodyPrint");
		
		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
						
		$tpl->setVariable("BODY_ATTRIBUTES",'onload="window.print()"');
	    $tpl->setVariable("CONTENT", $this->getHTML());
		
		return $tpl->show();
	}
	
	/**
	 * render attendance list
	 *
	 * @return string
	 */
	public function getHTML()
	{				
		$tpl = new ilTemplate('tpl.attendance_list_print.html',true,true,'Services/Membership');

		
		// title
		
		$time = ilFormat::formatUnixTime(time(),true);
		
		$tpl->setVariable('TXT_TITLE', $this->title);
		if($this->description)
		{
			$tpl->setVariable('TXT_DESCRIPTION', $this->description." (".$time.")");
		}
		else
		{
			$tpl->setVariable('TXT_DESCRIPTION', $time);
		}
		
		
		// header 
		
		$tpl->setCurrentBlock('head_item');
		foreach($this->presets as $id => $item)
		{
			if($item[1])
			{
				$tpl->setVariable('TXT_HEAD', $item[0]);
				$tpl->parseCurrentBlock();
			}
		}
		
		if($this->blank_columns)
		{
			foreach($this->blank_columns as $blank)
			{
				$tpl->setVariable('TXT_HEAD', $blank);
				$tpl->parseCurrentBlock();				
			}
		}

		
		// handle members
	
		$valid_user_ids = $filters = array();
		
		if($this->roles)
		{			
			if($this->has_local_role)
			{				
				$members = array();
				foreach($this->participants->getMembers() as $member_id)
				{
					foreach($this->participants->getAssignedRoles($member_id) as $role_id)
					{
						$members[$role_id][] = $member_id;
					}				
				}							
			}
			else
			{
				$members = $this->participants->getMembers();
			}
		
			foreach($this->roles as $role_id)
			{
				switch($this->role_data[$role_id][1])
				{
					case "admin":
						$valid_user_ids = array_merge($valid_user_ids, $this->participants->getAdmins());
						break;
					
					case "tutor":
						$valid_user_ids = array_merge($valid_user_ids, $this->participants->getTutors());
						break;
					
					// member/local
					default:
						if(!$this->has_local_role)
						{	
							$valid_user_ids = array_merge($valid_user_ids, (array)$members);
						}
						else
						{
							$valid_user_ids = array_merge($valid_user_ids, (array)$members[$role_id]);
						}
						break;
				}								
			}							
		}
		
		if($this->include_subscribers)
		{
			$valid_user_ids = array_merge($valid_user_ids, $this->participants->getSubscribers()); 			
		}
		
		if($this->include_waiting_list)
		{
			$valid_user_ids = array_merge($valid_user_ids, $this->waiting_list->getUserIds()); 			
		}
			
		if($this->user_filters)
		{
			foreach($this->user_filters as $sub_id => $sub_item)
			{
				$filters[$sub_id] = (bool)$sub_item[2];
			}
		}

		$valid_user_ids = ilUtil::_sortIds(array_unique($valid_user_ids),'usr_data','lastname','usr_id');						
		
		
		// rows 
		
		foreach($valid_user_ids as $user_id)
		{
			if($this->callback)
			{
				$user_data = call_user_func_array($this->callback, array($user_id, $filters));	
				if(!$user_data)
				{
					continue;
				}
				
				$tpl->setCurrentBlock("row_preset");
				foreach($this->presets as $id => $item)
				{
					if($item[1])
					{
						switch($id)
						{
							case "name":
								if(!$user_data[$id])
								{
									$name = ilObjUser::_lookupName($user_id);
									$value = $name["lastname"].", ".$name["firstname"];
									break;
								}
								
							
							case "email":
								if(!$user_data[$id])
								{
									$value = ilObjUser::_lookupEmail($user_id);
									break;
								}
								
							
							case "login":
								if(!$user_data[$id])
								{
									$value = ilObjUser::_lookupLogin($user_id);
									break;
								}							

							default:
								$value = (string)$user_data[$id];
								break;
						}
						$tpl->setVariable("TXT_PRESET", $value);
						$tpl->parseCurrentBlock();
					}
				}								
			}

			if($this->blank_columns)
			{
				for($loop = 0; $loop < sizeof($this->blank_columns); $loop++)
				{
					$tpl->touchBlock('row_blank');
				}
			}
			
			$tpl->touchBlock("member_row");
		}
		
		return $tpl->get();
	}
}

?>