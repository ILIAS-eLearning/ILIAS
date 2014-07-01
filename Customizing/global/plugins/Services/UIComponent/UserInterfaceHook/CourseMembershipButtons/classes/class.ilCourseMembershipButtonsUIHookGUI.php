<?php

require_once ("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");

class ilCourseMembershipButtonsUIHookGUI extends ilUIHookPluginGUI {
	protected function initGlobals() {
		if (!isset($this->ctrl)) {
			global $ilCtrl;
			$this->ctrl = &$ilCtrl;
		}
		
		if (!isset($this->toolbar)) {
			global $ilToolbar;
			$this->toolbar = &$ilToolbar;
		}
		
		if (!isset($this->lng)) {
			global $lng;
			$this->lng = &$lng;
		}
	}
	
	public function getHTML($a_comp, $a_part, $a_parameters = array()) {
		$this->initGlobals();


		if ($this->ctrl->getCmdClass() == "ilobjcoursegui" 
		and (  in_array($this->ctrl->getCmd(), array("members", "post"))
			or in_array($_GET["fallbackCmd"], array("deleteMembers"))
			)
		and $a_part == "template_load"
		and $a_parameters["tpl_id"] == "Services/UIComponent/Toolbar/tpl.toolbar.html") {
			require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			$utils = gevCourseUtils::getInstance(gevObjectUtils::getObjId($_GET["ref_id"]));

			$this->toolbar->addSeparator();
			$this->toolbar->addButton( $this->lng->txt("gev_member_list")
									 , "ilias.php?ref_id=".$_GET["ref_id"]."&cmd=trainer&baseClass=gevMemberListDeliveryGUI"
									 );
			if ($utils->canBuildDeskDisplays()) {
				$this->toolbar->addButton( $this->lng->txt("gev_desk_displays")
										 , "ilias.php?ref_id=".$_GET["ref_id"]."&baseClass=gevDeskDisplaysDeliveryGUI");
			}
		}
		
		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}
}

?>