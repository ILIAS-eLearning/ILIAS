<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");

class ilGEVCourseTemplateCreationPlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVCourseTemplateCreation";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		if ($a_component !== "Modules/Course" || $a_event !== "create") {
			return;
		}

		$this->createdCourse($a_parameter["object"], $a_parameter["obj_id"]);
	}

	public function createdCourse(ilObjCourse $a_crs, $a_crs_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Modules/Course/classes/class.ilObjCourse.php");
		require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		try {
			$utils = gevCourseUtils::getInstance($a_crs_id);
			$add_mail_settings = new gevCrsAdditionalMailSettings($a_crs_id);
			$bernried = gevSettings::getInstance()->get(gevSettings::VENUE_BERNRIED);
			$generali = gevSettings::getInstance()->get(gevSettings::PROVIDER_GENERALI);

			// Versanddatum Einladungsmails (Stornofrist - 2)
			$add_mail_settings->setInvitationMailingDate(29);
			// Beitrittsverfahren (Direkter Beitritt)
			$a_crs->setSubscriptionLimitationType(IL_CRS_SUBSCRIPTION_UNLIMITED);
			$a_crs->setSubscriptionType(IL_CRS_SUBSCRIPTION_DIRECT);
			// Mitglieder-Reiter aus
			$a_crs->setShowMembers(false);
			// Mail an Mitglieder (Nur für Administratoren)
			$a_crs->setMailToMembersType(ilCourseConstants::MAIL_ALLOWED_TUTORS);
			// Bergüßungsmails für neue Mitglieder (aus)
			$a_crs->setAutoNotification(false);
			// Auf Schreibtisch legen aus
			$a_crs->setAboStatus(false);
			// Warteliste an
			$utils->setWaitingListActive(true);
			// maximale Teilnehmerzahl 12
			$utils->setMaxParticipants(12);
			// Mindesteilnehmerzahl 6
			$utils->setMinParticipants(6);
			// Stornofrist 31 Tage
			$utils->setCancelDeadline(31);
			// Buchungsfrist 14 Tage
			$utils->setBookingDeadline(14);
			// Anbieter Generali
			if ($generali) {
				$utils->setProviderId($generali);
			}
			// Veranstaltungsort Bernried
			// Übernachtungsort Bernried
			if ($bernried) {
				$utils->setVenueId($bernried);
				$utils->setAccomodationId($bernried);
			}
			
			$add_mail_settings->save();
			$a_crs->update();
		}
		catch (Exception $e) {
			global $ilLog;
			$ilLog->write("Error in ilGEVCourseTemplateCreationPlugin::createdCourse: ".print_r($e, true));
		}
	}
}

?>