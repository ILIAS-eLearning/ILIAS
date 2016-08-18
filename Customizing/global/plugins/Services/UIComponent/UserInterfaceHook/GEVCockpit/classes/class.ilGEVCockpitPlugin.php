<?php

/* Copyright (c) 2016, Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

/**
 * Creates a submenu for the Cockpit of the GEV.
 */
class ilGEVCockpitPlugin extends ilUserInterfaceHookPlugin {
	function getPluginName() {
		return "GEVCockpit";
	}
}

