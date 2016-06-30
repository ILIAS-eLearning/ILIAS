<?php
require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");

class ilGEVUserUpdatePlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVUserUpdate";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		
		if ($a_component !== "Services/User" || $a_event !== "afterUpdate") {
			return;
		}

		global $ilLog;

		$this->gLog = $ilLog;
		$this->user_utils = gevUserUtils::getInstanceByObj($a_parameter["user_obj"]);
		$this->wbd = gevWBD::getInstanceByObj($a_parameter["user_obj"]);

		$this->updateUser();
	}

	public function updateUser() {
		try {
			if($report_after = $this->wbd->getReportPointsFrom()) {
				$this->wbd->setTrainingWBDRelevantAfter($report_after);
			}
		}
		catch (Exception $e) {
			$this->gLog->write("Error in GEVUserUpdate::updatedUer: ".print_r($e, true));
		}
	}
}