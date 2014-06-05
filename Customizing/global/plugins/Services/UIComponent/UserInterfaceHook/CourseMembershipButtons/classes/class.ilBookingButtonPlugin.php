<?php

require_once ("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

class ilBookingButtonPlugin extends ilUserInterfaceHookPlugin {
	public function getPluginName() {
		return "BookingButton";
	}
}

?>