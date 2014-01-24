<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Modules/OrgUnit/classes/class.ilMultiUserToolbarInputGUI.php");
/**
 * Class ilOrguUserPickerToolbarInputGUI
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilOrguUserPickerToolbarInputGUI extends ilMultiUserToolbarInputGUI {

	protected $staff;

	public function getToolbarHTML()
	{
		$html = "<form method='post' class='ilOrguUserPicker' action='".$this->getSubmitLink()."'>";
		$html .= $this->render();
		$html .= $this->getSelectHTML();
		$html .= $this->getSubmitButtonHTML();
		$html .= "</form>";
		return $html;
	}

	protected function getSelectHTML(){
		global $lng;
		$html = "
		<select name='".$this->searchPostVar()."_role"."'>
			<option value='employee'>".$lng->txt("employee")."</option>
			<option value='superior'>".$lng->txt("superior")."</option>
		</select>
		";
		return $html;
	}

	public function setValueByArray($array){
		parent::setValueByArray($array);
		$this->staff = $array[$this->searchPostVar()."_role"];
	}

	public function getStaff(){
		return $this->staff;
	}
}
?>