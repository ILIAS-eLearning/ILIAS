<?php

/* Copyright (c) 2016, Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

/**
 * Create an announcement in an overlay.
 */
class ilAnnouncementPlugin extends ilUserInterfaceHookPlugin {
	function getPluginName() {
		return "Announcement";
	}
}

