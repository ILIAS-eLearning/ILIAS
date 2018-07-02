<?php

class ilPreviewFactory {
	const CONTEXT_TUTOR_MANUAL = "crs_context_tutor_manual";
	const CONTEXT_CRS_AUTOMATIC = "crs_context_automatic"; //ilCourseMailTemplateAutomaticContext
	const CONTEXT_CRS_INVITATION = "crs_context_invitation"; //ilCourseMailTemplateInvitationContext

	public function getPreviewForContext($context) {
		switch($context) {
			case self::CONTEXT_TUTOR_MANUAL:
			/**
			 * cat-tms-patch start
			 */
			case self::CONTEXT_CRS_AUTOMATIC:
			case self::CONTEXT_CRS_INVITATION:
				return $this->getTutorContextPreview();
			 /**
			 * cat-tms-patch end
			 */
			default:
				throw new Exception("Unknown context: ".$context);
		}
	}

	protected function getTutorContextPreview() {
		require_once "Services/Mail/classes/Preview/class.ilCourseMailTemplateTutorContextPreview.php";
		return new ilCourseMailTemplateTutorContextPreview();
	}
}
