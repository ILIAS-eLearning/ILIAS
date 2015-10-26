<?php
include_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

class ilReportBasePlugin extends ilRepositoryObjectPlugin {

	public function getPluginName() {
		return $this->getReportName();
	}

	abstract protected function getReportName();

}