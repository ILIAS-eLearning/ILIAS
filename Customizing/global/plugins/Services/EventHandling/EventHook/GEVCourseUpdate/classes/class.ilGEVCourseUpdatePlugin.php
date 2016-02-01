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
		
		global $ilLog, $lng, $ilCtrl;
		
		$this->gLog = $ilLog;
		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		
		$this->crs_utils = gevCourseUtils::getInstanceByObj($a_parameter["object"]);
		$this->crs = $a_parameter["object"];
		$this->crs_id = $a_parameter["obj_id"];
		
		if(!$this->crs_utils->isTemplate()) {
			$this->updatedCourse($a_parameter);
		}
		else {
			$this->updateTemplateCourse();
		}
	}

	public function updatedCourse($a_parameter) {
		try {
			$max_participants = intval($this->crs_utils->getMaxParticipants());
			$this->crs->enableWaitingList($this->crs_utils->getWaitingListActive() && $max_participants > 0);
			$this->crs->enableSubscriptionMembershipLimitation($max_participants > 0);
			$this->crs->setSubscriptionMaxMembers($max_participants);

			if ($max_participants == 0) {
				$this->crs_utils->setWaitingListActive(false, false);
			}

			$this->crs->update(false);
			
			$this->crs_utils->moveAccomodations();

			if ($this->crs_utils->isVirtualTraining()) {
				$this->crs_utils->checkVirtualTrainingForPossibleVCAssignment();
			}

			$this->crs_utils->fillFreePlacesFromWaitingList();

			$this->crs_utils->adjustVCAssignment();

			if(isset($a_parameter["comparison"]) && count($a_parameter["comparison"] == 2)) {
				$this->compareCourse($a_parameter["comparison"]);
			}
		}
		catch (Exception $e) {
			$this->gLog->write("Error in GEVCourseUpdate::updatedCourse: ".print_r($e, true));
		}
	}
	
	public function updateTemplateCourse() {
		try {
			$max_participants = intval($this->crs_utils->getMaxParticipants());
			$this->crs->enableWaitingList($this->crs_utils->getWaitingListActive() && $max_participants > 0);
			$this->crs->enableSubscriptionMembershipLimitation($this->crs_utils->getWaitingListActive() && $max_participants > 0);
			$this->crs->setSubscriptionMaxMembers($max_participants);
			if ($this->crs_utils->getRefId() && $_GET["ref_id"] && $this->crs_utils->getRefId() == $_GET["ref_id"]) {
				$this->crs_utils->warningIfTemplateWithDates();
			}
			$this->maybeSetTemplateCustomId();

			$this->crs_utils->updateDerivedCourses();

			if ($max_participants == 0) {
				$this->crs_utils->setWaitingListActive(false, false);
			}
			$this->crs->update(false);
		}
		catch (Exception $e) {
			$this->gLog->write("Error in GEVCourseUpdate::updateTemplateCourse: ".print_r($e, true));
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

	protected function compareCourse(array $crs_to_compare) {
		require_once("Services/UICore/classes/class.ilTemplateHTMLITX.php");
		require_once("Services/PEAR/lib/HTML/Template/ITX.php");
		require_once("Services/PEAR/lib/HTML/Template/IT.php");
		require_once("Services/UICore/classes/class.ilTemplate.php");

		$old = $crs_to_compare["bevor_update"];
		$new = $crs_to_compare["after_update"];

		if($old->compareWith($new)) {
			$this->gLog->write("ilGEVCourseUpdatePlugin::compareCourse:compared courseInfos are different!");
			
			$this->gCtrl->setParameterByClass("gevCrsMailingGUI","ref_id", $new->refId());
			$this->gCtrl->setParameterByClass("gevCrsMailingGUI","auto_mail_id", "invitation");
			$link = $this->gCtrl->getLinkTargetByClass(array("ilRepositoryGUI", "ilObjCourseGUI", "gevCrsMailingGUI"),"sendAutoMail");
			$this->gCtrl->clearParametersByClass("gevCrsMailingGUI");

			$tpl = new ilTemplate("tpl.gev_resend_mail_info.html", true, true, "Services/GEV/Course");
			$tpl->setVariable("MESSAGE", $this->gLng->txt("gev_crs_resend_invitation_info"));
			$tpl->setVariable("HREF_LINK", $link);
			$tpl->setVariable("HREF_TEXT", $this->gLng->txt("gev_crs_resend_invitation"));

			ilUtil::sendInfo($tpl->get(), true);
		}
	}
}