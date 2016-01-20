<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/GEV/Mailing/classes/class.gevCrsMailData.php");
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");


/**
 * Generali mail data for Webinar Reminder
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version $Id$
 */

class gevWebinarMailData extends gevCrsMailData {

	public function __construct($a_recipient,$a_gender) {
		$this->usr_utils = gevUserUtils::getInstance($a_recipient);
		$this->start_timestamp = null;
		$this->end_timestamp = null;
		$this->end_date_str = "";
		$this->gender = $a_gender;
	}
	
	function getRecipientMailAddress() {
		return null;
	}
	function getRecipientFullName() {
		return null;
	}

	function getRecipientUserId() {
		return null;
	}
}