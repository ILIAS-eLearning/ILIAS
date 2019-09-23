<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateParticipantsHelper
{
	/**
	 * @param int $objectId
	 * @param int $userId
	 * @return string
	 */
	public function getDateTimeOfPassed(int $objectId, int $userId) : string
	{
		return (string) ilCourseParticipants::getDateTimeOfPassed($objectId, $userId);
	}
}
