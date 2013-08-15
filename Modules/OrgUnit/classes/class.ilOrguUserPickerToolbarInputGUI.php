<?php
/**
 * Created by JetBrains PhpStorm.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * Date: 23/07/13
 * Time: 4:39 PM
 * To change this template use File | Settings | File Templates.
 */

require_once("./Modules/OrgUnit/classes/class.ilMultiUserToolbarInputGUI.php");

class ilOrguUserPickerToolbarInputGUI extends ilMultiUserToolbarInputGUI {

	protected $staff;

	public function getToolbarHTML()
	{
		//TODO refactor into template.
		$html = "<form method='post' class='ilOrguUserPicker' action='".$this->getSubmitLink()."'>";
		$html .= $this->render();
		$html .= $this->getSelectHTML();
		$html .= $this->getSubmitButtonHTML();
		$html .= "</form>";
		return $html;
	}

	protected function getSelectHTML(){
		global $lng;
		//todo refactor into template.
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
