<?php
require_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

abstract class ilReportBasePlugin extends ilRepositoryObjectPlugin {

	public function getPluginName() {
		return $this->getReportName();
	}

	abstract protected function getReportName();

	static public function _getIcon($a_type, $a_size) {
		return 'Services/ReportsRepository/images/icon.png';
	}

}