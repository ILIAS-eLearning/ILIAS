<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDateHelper
{
	/**
	 * @param string $date
	 * @param int $format
	 * @return string
	 */
	public function formatDate(string $date): string
	{
		return ilDatePresentation::formatDate(new ilDate($date, IL_CAL_DATETIME));
	}

	/**
	 * @param string $dateTime
	 * @param int $format
	 * @return string
	 * @throws ilDateTimeException
	 */
	public function formatDateTime(string $dateTime): string
	{
		return ilDatePresentation::formatDate(new ilDateTime($dateTime, IL_CAL_DATETIME));
	}
}
