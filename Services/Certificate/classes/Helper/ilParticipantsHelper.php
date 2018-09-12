<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilParticipantsHelper
{
	public function getDateTimeOfPassed($objectId, $userId)
	{
		return ilCourseParticipants::getDateTimeOfPassed($objectId, $userId);
	}
}
