<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateValueReplacement
{
	/**
	 * @var string
	 */
	private $clientWebDirectory;

	public function __construct(string $clientWebDirectory = CLIENT_WEB_DIR)
	{
		$this->clientWebDirectory = $clientWebDirectory;
	}

	/**
	 * Replaces placeholder in the certificate content with actual values
	 *
	 * @param array $placeholderValues
	 * @param string $certificateContent
	 * @param string $backgroundPath
	 * @return string
	 */
	public function replace(array $placeholderValues, string $certificateContent, string $backgroundPath) : string
	{

		foreach ($placeholderValues as $placeholder => $value) {
			$certificateContent = str_replace('[' . $placeholder . ']', $value, $certificateContent);
		}

		$certificateContent = str_replace('[BACKGROUND_IMAGE]',  $backgroundPath, $certificateContent);
		$certificateContent = str_replace('[CLIENT_WEB_DIR]',  $this->clientWebDirectory, $certificateContent);

		$certificateContent = preg_replace("/<\?xml[^>]+?>/", "", $certificateContent);
		$certificateContent = str_replace("&#xA0;", "<br />", $certificateContent);
		$certificateContent = str_replace("&#160;", "<br />", $certificateContent);

		return $certificateContent;
	}
}
