<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDateHelper
{
	/**
	 * @param string $date
	 * @return string
	 */
	public function formatDate(string $date): string
	{
		$oldDatePresentationValue = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);

		$date = ilDatePresentation::formatDate(new ilDate($date, IL_CAL_DATETIME));

		ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

		return $date;
	}

	/**
	 * @param string $dateTime
	 * @return string
	 * @throws ilDateTimeException
	 */
	public function formatDateTime(string $dateTime): string
	{
		$oldDatePresentationValue = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);

		$date = ilDatePresentation::formatDate(new ilDateTime($dateTime, IL_CAL_DATETIME));

		ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

		return $date;
	}
}
