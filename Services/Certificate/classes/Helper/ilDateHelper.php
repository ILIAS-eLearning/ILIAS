<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilDateHelper
{
	/**
	 * @param string $dateTime
	 * @return string
	 */
	public function formatDate(string $dateTime)
	{
		return ilDatePresentation::formatDate(new ilDate($completionDate, IL_CAL_DATETIME));
	}

	/**
	 * @param string $dateTime
	 * @return string
	 */
	public function formatDateTime(string $dateTime)
	{
		return ilDatePresentation::formatDate(new ilDateTime($dateTime, IL_CAL_DATETIME));
	}
}
