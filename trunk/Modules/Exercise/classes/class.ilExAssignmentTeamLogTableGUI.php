<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all log entries of team 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExAssignmentTeamLogTableGUI extends ilTable2GUI
{
	protected $team_id; // [int]
	
	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd	
	 * @param int $a_team_id 
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd, $a_team_id)
	{
		global $ilCtrl;
						
		$this->team_id = $a_team_id;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setTitle($this->lng->txt("exc_team_log"));

		$this->addColumn($this->lng->txt("date"), "tstamp");
		$this->addColumn($this->lng->txt("user"), "user");
		$this->addColumn($this->lng->txt("details"), "details");
		
		$this->setDefaultOrderField("tstamp");
		$this->setDefaultOrderDirection("desc");
						
		$this->setRowTemplate("tpl.exc_team_log_row.html", "Modules/Exercise");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->getItems();
	}

	/**
	 * Get all completed tests
	 */
	protected function getItems()
	{			
		$data = array();

		foreach(ilExAssignment::getTeamLog($this->team_id) as $item)
		{;
			switch($item["action"])
			{
				case ilExAssignment::TEAM_LOG_CREATE_TEAM:
					$mess = "create_team";
					break;
				
				case ilExAssignment::TEAM_LOG_ADD_MEMBER:					
					$mess = "add_member";
					break;
				
				case ilExAssignment::TEAM_LOG_REMOVE_MEMBER:					
					$mess = "remove_member";
					break;	
				
				case ilExAssignment::TEAM_LOG_ADD_FILE:					
					$mess = "add_file";
					break;	
				
				case ilExAssignment::TEAM_LOG_REMOVE_FILE:					
					$mess = "remove_file";
					break;	
			}
			
			$details = $this->lng->txt("exc_team_log_".$mess);
			if($item["details"])
			{
				$details = sprintf($details, $item["details"]);
			}			
			
			$data[] = array(
				"tstamp" => $item["tstamp"],
				"user" => ilObjUser::_lookupFullname($item["user_id"]),
				"details" => $details
			);
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
		$date = ilDatePresentation::formatDate(new ilDateTime($a_set["tstamp"], IL_CAL_UNIX));
		
		$this->tpl->setVariable("TSTAMP", $date);
		$this->tpl->setVariable("TXT_USER", $a_set["user"]);		
		$this->tpl->setVariable("TXT_DETAILS", $a_set["details"]);		
	}
}

?>