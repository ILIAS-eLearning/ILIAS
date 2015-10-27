<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilGEVCourseUpdatePlugin extends ilEventHookPlugin
{
	const VC_TYPE_CSN = "CSN";
	const VC_TYPE_WEBEX = "Webex";
	const VC_RECIPIENT = "Mitglied";

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

			if($this->crs_utils->isFlexibleDecentrallTraining()) {
				require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
				require_once("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");
				$attachments = array(gevCrsMailAttachments::ICAL_ENTRY
									,gevCrsMailAttachments::SCHEDULE
									);
				$crs_inv_mail_set = new gevCrsInvitationMailSettings($this->crs_utils->getCourse()->getId());

				require_once("Services/GEV/Utils/classes/class.gevSettings.php");
				$settings = gevSettings::getInstance();

				if($this->crs_utils->isWebinar()) {
					$vc_type = $this->crs_utils->getVirtualClassType();

					if($vc_type && $vc_type == self::VC_TYPE_CSN) {
						$crs_inv_mail_set->setSettingsFor(self::VC_RECIPIENT,$settings->getCSNMailTemplateId(),$attachments);
						$crs_inv_mail_set->save();
					}

					if($vc_type && $vc_type == self::VC_TYPE_WEBEX) {
						$crs_inv_mail_set->setSettingsFor(self::VC_RECIPIENT,$settings->getWebExMailTemplateId(),$attachments);
						$crs_inv_mail_set->save();
					}
				}

				if($this->crs_utils->isPraesenztraining()) {
					$crs_inv_mail_set->setSettingsFor(self::VC_RECIPIENT,$settings->getDecentralTrainingMailTemplateId(),$attachments);
					$crs_inv_mail_set->save();
				}
			}

			$this->crs_utils->adjustVCAssignment();
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