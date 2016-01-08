<?php

require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
* ListGUI implementation for Report object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing  Report Access class.
*/
abstract class ilObjReportBaseListGUI extends ilObjectPluginListGUI {
	/**
	* This is probably more of a hack, since this functions responsibility nothing has to do with GUI properties, as it would seem.
	*/
	public function initType() {
		$this->timings_enabled = false;
	}
}