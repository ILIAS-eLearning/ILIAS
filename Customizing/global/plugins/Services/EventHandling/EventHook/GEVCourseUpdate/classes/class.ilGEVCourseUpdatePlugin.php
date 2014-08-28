<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilGEVCourseUpdatePlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVCourseUpdate";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		if ($a_component !== "Modules/Course" || $a_event !== "update") {
			return;
		}
		
		global $ilLog;
		
		$this->log = $ilLog;
		
		$this->crs_utils = gevCourseUtils::getInstanceByObj($a_parameter["object"]);
		$this->crs = $a_parameter["object"];
		$this->crs_id = $a_parameter["obj_id"];
		
		if(!$this->crs_utils->isTemplate()) {
			$this->updatedCourse();
		}
		else {
			$this->updateTemplateCourse();
		}
	}

	public function updatedCourse() {
		try {
			$max_participants = intval($this->crs_utils->getMaxParticipants());
			$this->crs->enableWaitingList($this->crs_utils->getWaitingListActive() && $max_participants > 0);
			$this->crs->enableSubscriptionMembershipLimitation($this->crs_utils->getWaitingListActive() && $max_participants > 0);
			$this->crs->setSubscriptionMaxMembers($max_participants);

			if ($max_participants == 0) {
				$this->crs_utils->setWaitingListActive(false, false);
			}

			$this->crs->update(false);
		}
		catch (Exception $e) {
			$this->log->write("Error in GEVCourseUpdate::updatedCourse: ".print_r($e, true));
		}
	}
	
	public function updateTemplateCourse() {
		try {
			$max_participants = intval($this->crs_utils->getMaxParticipants());
			$this->crs->enableWaitingList($this->crs_utils->getWaitingListActive() && $max_participants > 0);
			$this->crs->enableSubscriptionMembershipLimitation($this->crs_utils->getWaitingListActive() && $max_participants > 0);
			$this->crs->setSubscriptionMaxMembers($max_participants);
			
			$this->maybeSetTemplateCustomId();
			$this->crs_utils->updateDerivedCourses();
			
			if ($max_participants == 0) {
				$this->crs_utils->setWaitingListActive(false, false);
			}
		
			$this->crs->update(false);
		}
		catch (Exception $e) {
			$this->log->write("Error in GEVCourseUpdate::updateTemplateCourse: ".print_r($e, true));
		}
	}
	
	protected function maybeSetTemplateCustomId() {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		if ($this->crs_utils->getCustomId()) {
			return;
		}
		
		$tmplt = $this->crs_utils->getTemplateCustomId();
		if (!$tmplt) {
			return;
		}
		$tmplt = explode(" ", $tmplt);
		$tmplt = $tmplt[0];
		
		$custom_id = gevCourseUtils::createNewTemplateCustomId($tmplt);
		$this->crs_utils->setCustomId($custom_id);

	}
}

?>