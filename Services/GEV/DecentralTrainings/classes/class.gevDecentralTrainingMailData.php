<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

/**
 * Generali mail data for courses
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevDecentralTrainingMailData extends ilMailData {
	protected $rec_email;
	protected $rec_fullname;
	protected $rec_user_id;
	protected $crs_utils;
	protected $usr_utils;
	protected $cache;
	
	public function __construct(gevDecentralTrainingCreationRequest $a_request) {
		$this->request = $a_request;
		$this->usr_utils = gevUserUtils::getInstance($a_request->userId());
		
		if ($this->request->createdObjId()) {
			$crs_utils = gevCourseUtils::getInstance($this->request->createdObjId());
			$this->title = $crs_utils->getTitle();
			$this->start_date = $crs_utils->getFormattedStartDate();
			$this->end_date = $crs_utils->getFormattedEndDate();
			$this->start_time = $crs_utils->getFormattedStartTime();
			$this->end_time = $crs_utils->getFormattedEndTime();
			
			require_once("Services/Link/classes/class.ilLink.php");
			$this->booking_link = ilLink::_getLink($this->request->createdObjId(), 'gevcrsbookingtrainer');
			$this->booking_link = str_replace( "orange.cat06.de"
											 , "www.generali-onlineakademie.de"
											 , $this->booking_link);
		}
		else {
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			require_once("Services/GEV/Utils/classes/class.gevSettings.php");
			require_once("Services/Calendar/classes/class.ilDatePresentation.php");

			$tpl_ref_id = gevObjectUtils::getRefId($this->request->templateObjId());
			$settings = gevSettings::getInstance();
			$is_flexible =  ($tpl_ref_id == $settings->getDctTplFlexPresenceId())
						 || ($tpl_ref_id == $settings->getDctTplFlexWebinarId());
			
			$settings = $this->request->settings();
			
			if ($is_flexible) {
				$this->title = $settings->title();
			}
			else{
				$crs_utils = gevCourseUtils::getInstance($this->request->templateObjId());
				$this->title = $crs_utils->getTitle();
			}
			
			$start_dt = $settings->start();
			$end_dt = $settings->end();
			
			$start_formatted = explode(", ", ilDatePresentation::formatDate($start_dt));
			$end_formatted = explode(", ", ilDatePresentation::formatDate($end_dt));
			
			$this->start_date = $start_formatted[0];
			$this->end_date = $end_formatted[0];
			$this->start_time = $start_formatted[1];
			$this->end_time = $end_formatted[1];
			$this->booking_link = null;
		}
	}
	
	function getRecipientMailAddress() {
		return $this->usr_utils->getEmail();
	}
	
	function getRecipientFullName() {
		return $this->usr_utils->getFullName();
	}
	
	function getRecipientUserId() {
		return $this->usr_utils->getId();
	}
	
	function hasCarbonCopyRecipients() {
		return false;
	}
	
	function getCarbonCopyRecipients() {
		return array();
	}
	
	function hasBlindCarbonCopyRecipients() {
		return false;
	}
	
	function getBlindCarbonCopyRecipients() {
		return array();
	}
	
	function maybeFormatEmptyField($val) {
		if ($val === null) {
			return "-";
		}
		else {
			return $val;
		}
	}
	
	function getPlaceholderLocalized($a_placeholder_code, $a_lng, $a_markup = false) {
		$val = null;
		
		switch ($a_placeholder_code) {
			case "TITEL":
				$val = $this->title;
				break;
			case "STARTDATUM":
				$val = $this->start_date;
				break;
			case "STARTZEIT":
				$val = $this->start_time;
				break;
			case "ENDDATUM":
				$val = $this->end_date;
				break;
			case "ENDZEIT":
				$val = $this->end_time;
				break;
			case "BUCHUNGSLINK":
				if ($this->booking_link !== null) {
					$val = "<a href='".$this->booking_link."'>".$this->booking_link."</a>";
				}
				else {
					$val = "";
				}
				break;
			default:
				return $a_placeholder_code;
		}
		
		$val = $this->maybeFormatEmptyField($val);
		if (!$a_markup) 
			$val = str_replace("<br />", "\n", $val);
		
		return $val;
	}

	// Phase 2: Attachments via Maildata
	function hasAttachments() {
		return false;
	}
	
	function getAttachments($a_lng) {
		return array();
	}
}

?>