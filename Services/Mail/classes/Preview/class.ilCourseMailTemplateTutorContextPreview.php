<?php
/**
 * Copyright (c) 2017 ILIAS open source, Extended GPL, see docs/LICENSE
 * CaT Concepts and Training GmbH
 */
include_once './Modules/Course/classes/class.ilCourseMailTemplateTutorContext.php';

/**
 * Preiview context for course mail template
 *
 * @author Stefan Hecken 	<concepts-and-training.de>
 */
class ilCourseMailTemplateTutorContextPreview extends ilCourseMailTemplateTutorContext {
	const ID = 'crs_context_tutor_manual_preview';

	const DEFAULT_COURSE_TITLE = "preview_crs_title";
	const DEFAULT_COURSE_STATUS = "preview_crs_status";
	const DEFAULT_COURSE_MARK = "preview_crs_mark";
	const DEFAULT_COURSE_TIME_SPENT = "3671";

	public function __construct() {
		global $DIC;
		$this->g_lng = $DIC->language();
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolveSpecificPlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient = null, $html_markup = false)
	{
		if(!in_array($placeholder_id, array('crs_title', 'crs_link')))
		{
			return "";
		}

		$this->g_lng->loadLanguageModule('sess');
		$ret = null;
		switch($placeholder_id)
		{
			case 'crs_title':
				$ret = $this->g_lng->txt(self::DEFAULT_COURSE_TITLE);
				break;
			case 'crs_link':
				require_once './Services/Link/classes/class.ilLink.php';
				$ret = ilLink::_getLink($context_parameters['ref_id'], 'crs');;
				break;
			case 'crs_status':
				$ret = $this->g_lng->txt(self::DEFAULT_COURSE_STATUS);
				break;
			case 'crs_mark':
				$ret = $this->g_lng->txt(self::DEFAULT_COURSE_MARK);
				break;
			case 'crs_time_spent':
				if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS))
				{
					require_once("Services/Calendar/classes/class.ilDatePresentation.php");
					$ret = ilDatePresentation::secondsToString(self::DEFAULT_COURSE_TIME_SPENT, true, $this->g_lng);
				}
				break;
			case 'crs_first_access':
				if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS))
				{
					$ret = date("d.m.Y", strtotime("-5 day"));
				}
				break;
			case 'crs_last_access':
				if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS))
				{
					$ret = date("d.m.Y", strtotime("-1 day"));
				}
				break;
			default:
				$ret = "";
		}

		return $ret;
	}
}