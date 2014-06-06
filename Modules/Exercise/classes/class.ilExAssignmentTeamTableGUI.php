<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all team members of an assignment
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExAssignmentTeamTableGUI extends ilTable2GUI
{
	protected $mode; // [int]
	protected $team_id; // [int]
	protected $assignment; // [ilExAssignment]
	protected $member_ids; // [array]	
	protected $read_only; // [bool]	
	
	const MODE_ADD = 1;
	const MODE_EDIT = 2;
	
	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param int $a_mode
	 * @param int $a_team_id
	 * @param ilExAssignment $a_assignment
	 * @param array $a_member_ids
	 * @param bool $a_read_only
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd, $a_mode, $a_team_id, ilExAssignment $a_assignment, array $a_member_ids = null, $a_read_only = false)
	{
		global $ilCtrl;
				
		$this->mode = $a_mode;
		$this->team_id = $a_team_id;
		$this->assignment = $a_assignment;
		$this->member_ids = $a_member_ids;
		$this->read_only = (bool)$a_read_only;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		if(!$this->read_only)
		{
			$this->addColumn("", "", 1);
		}
		$this->addColumn($this->lng->txt("name"), "name");
		
		$this->setDefaultOrderField("name");
						
		$this->setRowTemplate("tpl.exc_team_member_row.html", "Modules/Exercise");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

		if(!$this->read_only)
		{
			if($this->mode == self::MODE_ADD)
			{
				$this->setTitle($this->lng->txt("exc_team_member_container_add"));
				$this->addMultiCommand("addTeamMemberContainerAction", $this->lng->txt("add"));
			}
			else
			{
				$this->setTitle($this->lng->txt("exc_team_members"));
				$this->addMultiCommand("confirmRemoveTeamMember", $this->lng->txt("remove"));
			}
		}
		
		$this->getItems();
	}

	/**
	 * Get all completed tests
	 */
	protected function getItems()
	{			
		if($this->mode == self::MODE_ADD)
		{
			$assigned = $this->assignment->getMembersOfAllTeams();		
		}
		else
		{
			$assigned = array();						
			$this->member_ids = $this->assignment->getTeamMembers($this->team_id);			
		}
	
		include_once "Services/User/classes/class.ilUserUtil.php";
		
		$data = array();
		foreach($this->member_ids as $id)
		{
			if(!in_array($id, $assigned))
			{
				$data[] = array("id" => $id,
					"name" => ilUserUtil::getNamePresentation($id, false, false, "", true));		
			}
		}
		
		$this->setData($data);
	}

	/**
	 * Fill template row
	 * 
	 * @param array $a_set
	 */
	protected function fillRow($a_set)
	{		
		if(!$this->read_only)
		{
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		}
		$this->tpl->setVariable("TXT_NAME", $a_set["name"]);		
	}
}

?>