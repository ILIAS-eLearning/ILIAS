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
	protected $callback; // [string|array]
	protected $presets; // [array]	
	protected $show_admins; // [bool]
	protected $show_tutors; // [bool]
	protected $show_members; // [bool]
	protected $blank_columns; // [array]
	protected $title; // [string]
	protected $description; // [string]
	protected $pre_blanks; // [array]
	protected $id; // [string]
		
	/**
	 * Constructor
	 * 
	 * @param object $a_parent_obj
	 * @param object $a_participants_object
	 */
	function __construct($a_parent_obj, $a_participants_object = null)
	{	
		global $lng;
		
		$this->parent_obj = $a_parent_obj;
		$this->participants = $a_participants_object;
		
		// always available
		$this->presets['name'] = array($lng->txt('name'), true);
		$this->presets['login'] = array($lng->txt('login'), true);
		$this->presets['email'] = array($lng->txt('email'));		
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
	 * Include admins 
	 * 
	 * @param bool $a_value 
	 */
	function showAdmins($a_value = true)
	{
		$this->show_admins = (bool)$a_value;
	}
	
	/**
	 * Include tutors 
	 * 
	 * @param bool $a_value 
	 */
	function showTutors($a_value = true)
	{
		$this->show_tutors = (bool)$a_value;
	}

	/**
	 * Include members 
	 * 
	 * @param bool $a_value 
	 */
	function showMembers($a_value = true)
	{
		$this->show_members = (bool)$a_value;
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
		
		// Admins
		$admin = new ilCheckboxInputGUI($lng->txt('event_tbl_admins'),'show_admins');
		$admin->setOptionTitle($lng->txt('event_inc_admins'));
		$admin->setValue(1);
		$form->addItem($admin);
		
		// Tutors
		$tutor = new ilCheckboxInputGUI($lng->txt('event_tbl_tutors'),'show_tutors');
		$tutor->setOptionTitle($lng->txt('event_inc_tutors'));
		$tutor->setValue(1);
		$form->addItem($tutor);

		// Members
		$member = new ilCheckboxInputGUI($lng->txt('event_tbl_members'),'show_members');
		$member->setOptionTitle($lng->txt('event_inc_members'));
		$member->setValue(1);
		$member->setChecked(true);
		$form->addItem($member);
		
		$form->addCommandButton($a_cmd,$lng->txt('sess_print_attendance_list'));
		
		if($this->id && $a_cmd)
		{
			include_once "Services/User/classes/class.ilUserFormSettings.php";
			$settings = new ilUserFormSettings($this->id);
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
			$this->showAdmins($form->getInput('show_admins'));
			$this->showTutors($form->getInput('show_tutors'));
			$this->showMembers($form->getInput('show_members'));	
			
			if($this->id)
			{
				$form->setValuesByPost();
				
				include_once "Services/User/classes/class.ilUserFormSettings.php";
				$settings = new ilUserFormSettings($this->id);
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
		
		$member_ids = array();
		if($this->show_admins)
		{
			$member_ids = array_merge((array)$member_ids,$this->participants->getAdmins());
		}
		if($this->show_tutors)
		{
			$member_ids = array_merge((array)$member_ids,$this->participants->getTutors());
		}
		if($this->show_members)
		{
			$member_ids = array_merge((array)$member_ids,$this->participants->getMembers());
		}				
		$member_ids = ilUtil::_sortIds((array) $member_ids,'usr_data','lastname','usr_id');
				
		
		// rows 
		
		foreach($member_ids as $user_id)
		{
			if($this->callback)
			{
				$user_data = call_user_func_array($this->callback, array($user_id));		
				
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