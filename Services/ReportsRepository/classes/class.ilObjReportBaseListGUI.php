<?php

require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

class ilObjReportBaseListGUI extends ilObjectPluginListGUI {
	abstract public function initType();
	abstract public function getGuiClass();
	abstract public function initCommands();

}