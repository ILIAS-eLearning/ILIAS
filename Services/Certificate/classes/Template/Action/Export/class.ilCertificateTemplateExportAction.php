<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateExportAction
{
	/**
	 * @var int
	 */
	private $objectId;

	/**
	 * @var string
	 */
	private $certificatePath;

	/**
	 * @var ilCertificateTemplateRepository
	 */
	private $templateRepository;

	/**
	 * @param integer $objectId
	 * @param string $certificatePath
	 * @param ilCertificateTemplateRepository $templateRepository
	 */
	public function __construct(
		int $objectId,
		string $certificatePath,
		ilCertificateTemplateRepository $templateRepository
	) {
		$this->objectId = $objectId;
		$this->certificatePath = $certificatePath;
		$this->templateRepository = $templateRepository;
	}

	/**
	 * Creates an downloadable file via the browser
	 */
	public function export()
	{
		global $DIC;

		$time = time();
		$web = $DIC->filesystem()->web();

		$type = ilObject::_lookupType($this->objectId);
		$certificateId = $this->objectId;

		$exportPath = $this->certificatePath . $time . '__' . IL_INST_ID . '__' . $type . '__' . $certificateId . '__certificate/';

		$web->createDir($exportPath, \ILIAS\Filesystem\Visibility::PUBLIC_ACCESS);

		$template = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		$xslContent = $template->getCertificateContent();

		$web->put($exportPath . 'certificate.xml', $xslContent);

		$backgroundImagePath = $template->getBackgroundImagePath();
		if ($backgroundImagePath !== null && $backgroundImagePath !== '') {
			$web->copy($backgroundImagePath, $exportPath . 'background.jpg');
		}

		$objectType = ilObject::_lookupType($this->objectId);
		$zipFileName = $time . '__' . IL_INST_ID . '__' . $objectType . '__' . $this->objectId . '__certificate.zip';


		$zipPath = CLIENT_WEB_DIR . $this->certificatePath . $zipFileName;
		ilUtil::zip($exportPath, $zipPath);
		$web->deleteDir($exportPath);

		ilUtil::deliverFile($zipPath, $zipFileName, "application/zip");
	}

	/**
	 * Saves the XSL-FO code to a file
	 *
	 * @param string $xslfo XSL-FO code
	 * @param string $filename
	 */
	private function createCertificateFile(string $xslfo, string $filename = '')
	{
		if (!file_exists($this->certificatePath)) {
			ilUtil::makeDirParents($this->certificatePath);
		}

		if (strlen($filename) == 0) {
			$filename = $this->getXSLPath();
		}

		$fileHandle = fopen($filename, "w");
		fwrite($fileHandle, $xslfo);
		fclose($fileHandle);
	}

	/**
	 * Returns the filesystem path of the XSL-FO file
	 *
	 * @return string The filesystem path of the XSL-FO file
	 */
	private function getXSLPath() : string
	{
		return $this->certificatePath . $this->getXSLName();
	}

	/**
	 * Returns the filename of the XSL-FO file
	 *
	 * @return string The filename of the XSL-FO file
	 */
	private function getXSLName() : string
	{
		return 'certificate.xml';
	}
}
