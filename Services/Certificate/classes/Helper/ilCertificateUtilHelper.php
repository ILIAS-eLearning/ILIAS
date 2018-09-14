<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Just a wrapper class to create Unit Test for other classes.
 * Can be remove when the static method calls have been removed
 *
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUtilHelper
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
	 * @return string
	 */
	public function prepareFormOutput(string $string) : string
	{
		return ilUtil::prepareFormOutput($string);
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @param string $targetFormat
	 * @param string $geometry
	 * @param string $backgroundColor
	 */
	public function convertImage(
		string $from,
		string $to,
		string $targetFormat = '',
		string $geometry = '',
		string $backgroundColor = ''
	) {
		return ilUtil::convertImage($from, $to, $targetFormat, $geometry, $backgroundColor);
	}

	/**
	 * @param string $string
	 * @return mixed|null|string|string[]
	 */
	public function stripSlashes(string $string) : string
	{
		return ilUtil::stripSlashes($string);
	}

	/**
	 * @param string $exportPath
	 * @param string $zipPath
	 */
	public function zip(string $exportPath, string $zipPath)
	{
		ilUtil::zip($exportPath, $zipPath);
	}

	/**
	 * @param string $zipPath
	 * @param string $zipFileName
	 * @param string $mime
	 */
	public function deliverFile(string $zipPath, string $zipFileName, string $mime)
	{
		ilUtil::deliverFile($zipPath, $zipFileName, $mime);
	}
}
