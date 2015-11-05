<?php

require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

abstract class ilObjReportBaseListGUI extends ilObjectPluginListGUI {
	/**
	* This is probably more of a hack, since this functions responsibility nothing has to do with GUI properties, as it would seem.
	*/
	public function initType() {
		$this->timings_enabled = false;
	}
}