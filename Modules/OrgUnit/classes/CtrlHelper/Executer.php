<?php

namespace ILIAS\Modules\OrgUnit\CtrlHelper;

/**
 * Trait Executer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait Executer {

	public function executeCommand() {
		/**
		 * @var $ilCtrl \ilCtrl
		 */
		$ilCtrl = $GLOBALS["DIC"]["ilCtrl"];
		$cmd = $ilCtrl->getCmd(self::CMD_INDEX);
		switch ($cmd) {
			default:
				if ($this->checkRequestReferenceId()) {
					$this->{$cmd}();
				}
				break;
		}
	}


	protected function checkRequestReferenceId() {
		/**
		 * @var $http     \ILIAS\DI\HTTPServices
		 * @var $ilAccess \ilAccessHandler
		 */
		$http = $GLOBALS["DIC"]->http();
		$queries = $http->request()->getQueryParams();
		if ($queries["ref_id"]) {
			$ilAccess = $GLOBALS["DIC"]["ilAccess"];

			return $ilAccess->checkAccess("read", "", $queries["ref_id"]);
		}

		return true;
	}
}