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
	protected $event_start; // [ilDateTime]
	protected $event_end; // [ilDateTime]
	protected $event_title; // [string]
	protected $show_mark; // [bool]
	protected $show_comment; // [bool]
	protected $show_signature; // [bool]
	protected $show_admins; // [bool]
	protected $show_tutors; // [bool]
	protected $show_members; // [bool]
	protected $blank_columns; // [int]
	protected $id_field; // [int]
	protected $callback; // [string|array]
	
	/**
	 * Constructor
	 * 
	 * @param object $a_parent_obj
	 * @param object $a_participants_object
	 * @param string $a_title
	 */
	function __construct($a_parent_obj, $a_participants_object)
	{	
		$this->parent_obj = $a_parent_obj;
		$this->participants = $a_participants_object;
		
		$this->showMark();
		$this->showComment();
		$this->showSignature();
		$this->showAdmins();
		$this->showTutors();
		$this->showMembers();
		$this->setBlankColumns();
		$this->setId();
	}
	
	/**
	 * Set event details
	 * 
	 * @param ilDateTime $a_start
	 * @param ilDateTime $a_end
	 * @param string $a_title 
	 */
	function setEvent($a_start, $a_end, $a_title = null)
	{
		$this->event_start = $a_start;
		$this->event_end = $a_end;
		$this->event_title = $a_title;
	}
	
	/**
	 * Toogle mark 
	 * 
	 * @param bool $a_value 
	 */
	function showMark($a_value = true)
	{
		$this->show_mark = (bool)$a_value;
	}
	
	/**
	 * Toogle comment 
	 * 
	 * @param bool $a_value 
	 */
	function showComment($a_value = true)
	{
		$this->show_comment = (bool)$a_value;
	}
	
	/**
	 * Toogle signature 
	 * 
	 * @param bool $a_value 
	 */
	function showSignature($a_value = true)
	{
		$this->show_signature = (bool)$a_value;
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
	 * @param int $a_value 
	 */
	function setBlankColumns($a_value = 0)
	{
		$this->blank_columns = (int)$a_value;
	}

	/**
	 * Add id field to member name
	 * 
	 * @param string $a_value
	 */
	function setId($a_value = "login")
	{
		$this->id_field = (string)$a_value;
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
	 * Init form
	 *
	 * @param string $a_cmd
	 * @return ilPropertyFormGUI
	 */
	public function initForm($a_cmd = "")
	{
		global $ilCtrl, $lng;
		
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this->parent_obj,$a_cmd));
		$form->setTarget('_blank');
		$form->setTitle($lng->txt('sess_gen_attendance_list'));
		
		$mark = new ilCheckboxInputGUI($lng->txt('trac_mark'),'show_mark');
		$mark->setOptionTitle($lng->txt('sess_gen_mark_title'));
		$mark->setValue(1);
		$form->addItem($mark);
		
		$comment = new ilCheckboxInputGUI($lng->txt('trac_comment'),'show_comment');
		$comment->setOptionTitle($lng->txt('sess_gen_comment'));
		$comment->setValue(1);
		$form->addItem($comment);
		
		$signature = new ilCheckboxInputGUI($lng->txt('sess_signature'),'show_signature');
		$signature->setOptionTitle($lng->txt('sess_gen_signature'));
		$signature->setValue(1);
		$form->addItem($signature);
		
		$id = new ilSelectInputGUI($lng->txt('id'), 'id');
		$id->setOptions(array('login' => $lng->txt('login'),
			'email' => $lng->txt('email')));
		$form->addItem($id);
		
		$blank = new ilNumberInputGUI($lng->txt('event_blank_columns'), 'blank');
		$blank->setSize(3);
		$form->addItem($blank);
		
		
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
			$this->showMark($form->getInput('show_mark'));
			$this->showComment($form->getInput('show_comment'));
			$this->showSignature($form->getInput('show_signature'));
			$this->showAdmins($form->getInput('show_admins'));
			$this->showTutors($form->getInput('show_tutors'));
			$this->showMembers($form->getInput('show_members'));
			$this->setId($form->getInput('id'));
			$this->setBlankColumns($form->getInput('blank'));
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
		
		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
					
		$tpl->setVariable("CONTENT",$this->getHTML());
		$tpl->setVariable("BODY_ATTRIBUTES",'onload="window.print()"');
		return $tpl->show();
	}
	
	/**
	 * render attendance list
	 *
	 * @return string
	 */
	public function getHTML()
	{		
		global $lng;
		
		$tpl = new ilTemplate('tpl.attendance_list_print.html',true,true,'Services/Membership');

		$tpl->setVariable("ATTENDANCE_LIST",$lng->txt('sess_attendance_list'));
		
		if($this->event_title)
		{
			$tpl->setVariable("EVENT_NAME",$this->event_title);
		}
		if($this->event_start && $this->event_end)
		{
			ilDatePresentation::setUseRelativeDates(false);
			$tpl->setVariable("DATE",ilDatePresentation::formatPeriod($this->event_start,$this->event_end));
			ilDatePresentation::setUseRelativeDates(true);
		}
		
		$tpl->setVariable("TXT_NAME",$lng->txt('name'));
		if($this->show_mark)
		{
			$tpl->setVariable("TXT_MARK",$lng->txt('trac_mark'));
		}						  
		if($this->show_comment)
		{
			$tpl->setVariable("TXT_COMMENT",$lng->txt('trac_comment'));	
		}
		if($this->show_signature)
		{
			$tpl->setVariable("TXT_SIGNATURE",$lng->txt('sess_signature'));	
		}
		
		if($this->blank_columns)
		{
			for($loop = 0; $loop < $this->blank_columns; $loop++)
			{
				$tpl->touchBlock('head_blank');
			}
		}
		
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
				
		foreach($member_ids as $user_id)
		{
			if($this->callback && ($this->show_mark || $this->show_comment))
			{
				$user_data = call_user_func_array($this->callback, array($user_id));				
				if($this->show_mark)
				{
					$tpl->setVariable("MARK",$user_data['mark'] ? $user_data['mark'] : ' ');
				}
				if($this->show_comment)
				{
					$tpl->setVariable("COMMENT",$user_data['comment'] ? $user_data['comment'] : ' ');
				}
			}

			if($this->show_signature)
			{
				$tpl->touchBlock('row_signature');
			}

			if($this->blank_columns)
			{
				for($loop = 0; $loop < $this->blank_columns; $loop++)
				{
					$tpl->touchBlock('row_blank');
				}
			}
			
			$tpl->setCurrentBlock("member_row");
			
			$name = ilObjUser::_lookupName($user_id);
			$tpl->setVariable("LASTNAME",$name['lastname']);
			$tpl->setVariable("FIRSTNAME",$name['firstname']);
			
			switch($this->id_field)
			{
				case 'login':
					$id = ilObjUser::_lookupLogin($user_id);
					break;
				
				case 'email':
					$id = ilObjUser::_lookupEmail($user_id);
					break;
			}			
			$tpl->setVariable("LOGIN", $id);
			
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->show();
	}
}

?>