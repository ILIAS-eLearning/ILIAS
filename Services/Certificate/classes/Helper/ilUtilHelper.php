<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Just a wrapper class to create Unit Test for other classes.
 * Can be remove when the static method calls have been removed
 *
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUtilHelper
{
	/**
	 * @param string $data
	 * @param string $fileName
	 * @param string $mimeType
	 */
	public function deliverData(string $data, string $fileName, string $mimeType)
	{
		ilUtil::deliverData(
			$data,
			$fileName,
			$mimeType
		);
	}

	/**
	 * @param string $string
	 */
	public function prepareFormOutput(string $string)
	{
		return ilUtil::prepareFormOutput($string);
	}
}
