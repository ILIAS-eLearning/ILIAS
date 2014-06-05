<?php

require_once ("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

class ilCourseMembershipButtonsPlugin extends ilUserInterfaceHookPlugin {
	public function getPluginName() {
		return "CourseMembershipButtons";
	}
}

?>