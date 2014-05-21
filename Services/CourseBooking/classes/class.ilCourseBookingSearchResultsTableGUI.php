<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all users from search result
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */
class ilCourseBookingSearchResultsTableGUI extends ilTable2GUI
{
	protected $default_option; // [int]
	
	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param array $a_user_ids
	 * @param int $a_default_option
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd, array $a_user_ids, $a_default_option = null)
	{
		global $ilCtrl;
		
		$this->default_option = $a_default_option;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("login"), "login");
		$this->addColumn($this->lng->txt("objs_orgu"), "org");
		$this->addColumn($this->lng->txt("crsbook_admin_status"), "");
		
		$this->setDefaultOrderField("name");
		$this->setRowTemplate("tpl.search_results_row.html", "Services/CourseBooking");
		$this->setTitle($this->lng->txt("crsbook_admin_add_participants"));
		
		$this->getItems($a_user_ids);
	}

	/**
	 * Get user data
	 * 
	 * @param array $a_user_ids
	 */
	protected function getItems(array $a_user_ids)
	{					
		$data = array();
		
		$orgu = ilCourseBookingHelper::getUsersOrgUnitData($a_user_ids);
			
		foreach($a_user_ids as $user_id)
		{
			$name = ilObjUser::_lookupName($user_id);
			
		
			$data[] = array(
				"id" => $user_id
				,"name" => $name["lastname"].", ".$name["firstname"]
				,"login" => $name["login"]
				,"org" => $orgu[$user_id][0]
				,"org_txt" => $orgu[$user_id][1]
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
		$this->tpl->setVariable("TXT_NAME", $a_set["name"]);		
		$this->tpl->setVariable("TXT_LOGIN", $a_set["login"]);		
		$this->tpl->setVariable("TXT_ORG", $a_set["org_txt"]);		
		
		$options = array(
			"" => $this->lng->txt("crsbook_admin_do_not_add")
			,ilCourseBooking::STATUS_BOOKED => $this->lng->txt("crsbook_admin_status_booked")
			,ilCourseBooking::STATUS_WAITING => $this->lng->txt("crsbook_admin_status_waiting")
		);		
		$status = ilUtil::formSelect($this->default_option, "usr_srch[".$a_set["id"]."]", $options, false, true);
		
		$this->tpl->setVariable("TXT_STATUS", $status);		
	}
}

?>