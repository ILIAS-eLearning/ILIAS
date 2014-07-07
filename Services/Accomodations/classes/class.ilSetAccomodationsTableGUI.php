<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for accomodations
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesAccomodations
*/
class ilSetAccomodationsTableGUI extends ilTable2GUI
{		
	protected $accomodotations; // [ilAccomodations]
	protected $permissions; // [ilAccomodationsPermissions]
	protected $user_ids; // [array]
	protected $nights; // [array]
	
	/**
	 * Constructor
	 * 
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param string $a_action_cmd
	 * @param string $a_abort_cmd
	 * @param ilObjCourse $a_course
	 * @param ilAccomodations $a_accomodations
	 * @param array $a_user_ids
	 * @param ilAccomodationsPermissions $a_permissions
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_action_cmd, $a_abort_cmd, ilObjCourse $a_course, ilAccomodations $a_accomodations, array $a_user_ids = null, ilAccomodationsPermissions $a_permissions = null)
	{
		global $ilCtrl, $lng;
		
		if(!$a_permissions)
		{
			require_once "./Services/Accomodations/classes/class.ilAccomodationsPermissions.php";
			$a_permissions = ilAccomodationsPermissions::getInstance($a_course);			
		}
		
		if(!$a_user_ids)
		{
			$a_user_ids = $a_accomodations->getValidUserIds();
		}
			
		$this->accomodotations = $a_accomodations;
		$this->permissions = $a_permissions;
		$this->user_ids = $a_user_ids;
		
		$this->setId("crsaccolist");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setShowRowsSelector(true);
		$this->setTitle($lng->txt("acco_tab_list_accomodations"));
		
		$this->addColumn($lng->txt("name"), "name");		
		
		ilDatePresentation::setUseRelativeDates(false);
		
		$this->nights = $a_accomodations->getPossibleAccomodationNights();
		foreach($this->nights as $idx => $night)
		{
			if($idx == sizeof($this->nights)-1)
			{
				$col = ilDatePresentation::formatDate($night)."<br />".
					$lng->txt("acco_period_input_to_last");				
			}
			else
			{			
				$next = clone $night;
				$next->increment(IL_CAL_DAY, 1);

				if(!$idx)
				{
					$col = $lng->txt("acco_period_input_from_first")."<br />".
						ilDatePresentation::formatDate($next);
				}			
				else
				{
					$col = ilDatePresentation::formatDate($night)."<br />".
						ilDatePresentation::formatDate($next);
				}
			}
			$this->addColumn($col);				
		}
				
		$this->setRowTemplate("tpl.accomodations_row.html", "Services/Accomodations");

		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		
		$has_editable = $this->getItems($this->user_ids);				
		if($has_editable)
		{			
			$this->addCommandButton($a_action_cmd, $lng->txt("save"));
		}
		$this->addCommandButton($a_abort_cmd, $lng->txt("cancel"));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_action_cmd));				
	}

	function getItems($a_user_ids)
	{
		global $ilUser;
		
		$has_editable = false;
		
		$data = array();
				
		if($a_user_ids)
		{															
			$edit_own = $this->permissions->setOwnAccomodations();
			$edit_others = $this->permissions->setOthersAccomodations();					
			$user_nights = $this->accomodotations->getAccomodationsOfUsers($a_user_ids);
			
			foreach(ilAccomodations::getUserNames($a_user_ids) as $user_id => $name)
			{
				$editable = false;
				if(($user_id == $ilUser->getId() && $edit_own) ||
					$edit_others)
				{
					$has_editable = true;
					$editable = true;
				}			
				
				$data[$user_id] = array(
					"user_id" => $user_id
					,"name" => $name
					,"editable" => $editable
					,"nights" => array()
				);			

				foreach((array)$user_nights[$user_id] as $night)
				{
					$data[$user_id]["nights"][] = $night->get(IL_CAL_DATE);
				}
			}								
		}
		
		$this->setData($data);
		
		return $has_editable;
	}

	protected function fillRow($a_set)
	{				
		global $ilCtrl;
		
		// :TODO: test edit single user
		$ilCtrl->setParameter($this->getParentObject(), "uid", $a_set["user_id"]);
		$a_set["name"] .= ' [<a href="'.$ilCtrl->getLinkTarget($this->getParentObject(), "editUserAccomodations").'">edit test</a>]';
		$ilCtrl->setParameter($this->getParentObject(), "uid", "");
		
		$this->tpl->setVariable("VAL_NAME", $a_set["name"]);
		
		$this->tpl->setCurrentBlock("night_bl");
		foreach($this->nights as $night)
		{
			$date = $night->get(IL_CAL_DATE);
			
			$this->tpl->setVariable("USER_ID", $a_set["user_id"]);
			$this->tpl->setVariable("NIGHT_ID", $date);
			
			if(in_array($date, $a_set["nights"]))
			{
				$this->tpl->setVariable("NIGHT_CHECKED", ' checked="checked"');
			}
			
			if(!$a_set["editable"])
			{
				$this->tpl->setVariable("NIGHT_DISABLED", ' disabled="disabled"');
			}
			
			$this->tpl->parseCurrentBlock();
		}		
	}
		
	public function processPostVars()
	{
		global $ilUser;
		
		$data = $_POST["acco"];
		
		$edit_own = $this->permissions->setOwnAccomodations();
		$edit_others = $this->permissions->setOthersAccomodations();	
		if(!$edit_own &&
			!$edit_others)
		{
			return false;
		}
		
		foreach($this->user_ids as $user_id)
		{
			if(!($user_id == $ilUser->getId() && $edit_own) &&
				!$edit_others)
			{
				continue;
			}
			
			if(is_array($data) && array_key_exists($user_id, $data))
			{				
				$nights = array();
				foreach(array_keys($data[$user_id]) as $night)
				{
					$nights[] = new ilDate($night, IL_CAL_DATE);
				}
				$this->accomodotations->setAccomodationsOfUser($user_id, $nights);
			}
			else
			{
				$this->accomodotations->deleteAccomodations($user_id);
			}
		}		
		
		return true;
	}
}

?>