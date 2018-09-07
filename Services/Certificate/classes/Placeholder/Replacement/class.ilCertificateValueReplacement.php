<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateValueReplacement
{

	/**
	 * Replaces placeholder in the certificate content with actual values
	 *
	 * @param array $placeholderValues
	 * @param string $certificateContent
	 * @param string $backgroundPath
	 * @return string
	 */
	public function replace(array $placeholderValues, string $certificateContent, string $backgroundPath)
	{
		foreach ($placeholderValues as $placeholder => $value) {
			$certificateContent = str_replace('[' . $placeholder . ']', $value, $certificateContent);
		}

		$certificateContent = str_replace('[BACKGROUND_IMAGE]',  $backgroundPath, $certificateContent);

		return $certificateContent;
	}
}
