<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateLPMarksHelper
{
	/**
	 * @param int $userId
	 * @param int $objectId
	 * @return string
	 */
	public function lookUpMark(int $userId, int $objectId) : string
	{
		return ilLPMarks::_lookupMark($userId, $objectId);
	}
}
