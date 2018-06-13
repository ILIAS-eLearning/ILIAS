<?php
include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository
 */
class ilObjComponentProviderExampleListGUI extends ilObjectPluginListGUI {
	/**
	 * Init the type of the plugin. Same value as choosen in plugin.php
	 */
	public function initType() {
		$this->setType("xlep");
	}

	/**
	 * Get name of gui class handling the commands
	 */
	function getGuiClass() {
		return "ilObjComponentProviderExampleGUI";
	}

	/**
	 * Get commands
	 */
	function initCommands() {
		$this->info_screen_enabled = true;
		$this->copy_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->timings_enabled = false;

		return array(array("permission" => "read",
							"cmd" => "showContent",
							"default" => true
						),
						array("permission" => "write",
							"cmd" => "editProperties",
							"txt" => $this->txt("edit"),
							"default" => false
						)
				);
	}
}
