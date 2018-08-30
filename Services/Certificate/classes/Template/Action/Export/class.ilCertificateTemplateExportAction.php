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
		$objectId,
		$certificatePath,
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
		$exportpath = $this->createArchiveDirectory();
		ilUtil::makeDir($exportpath);
		$time = time();

		$template = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		$xslExport = $template->getCertificateContent();
		$this->createCertificateFile($xslExport, $exportpath . 'certificate.xml');
		$backgroundImagePath = $template->getBackgroundImagePath();

		if ($backgroundImagePath !== null && $backgroundImagePath !== '') {
			copy($backgroundImagePath, $exportpath . 'background.jpg');
		}

		$objectType = ilObject::_lookupType($this->objectId);
		$zipFileName = $time . "__" . IL_INST_ID . "__" . $objectType . "__" . $this->objectId . "__certificate.zip";

		ilUtil::zip($exportpath, $this->certificatePath . $zipFileName);

		ilUtil::delDir($exportpath);
		ilUtil::deliverFile($this->certificatePath . $zipFileName, $zipFileName, "application/zip");
	}

	/**
	 * Creates a directory for a zip archive containing multiple certificates
	 *
	 * @return string The created archive directory
	 */
	public function createArchiveDirectory()
	{
		$type = ilObject::_lookupType($this->objectId);
		$certificateId = $this->objectId;

		$dir = $this->certificatePath . time() . "__" . IL_INST_ID . "__" . $type . "__" . $certificateId . "__certificate/";
		ilUtil::makeDirParents($dir);
		return $dir;
	}

	/**
	 * Saves the XSL-FO code to a file
	 *
	 * @param string $xslfo XSL-FO code
	 */
	private function createCertificateFile($xslfo, $filename = '')
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
	private function getXSLPath()
	{
		return $this->certificatePath . $this->getXSLName();
	}

	/**
	 * Returns the filename of the XSL-FO file
	 *
	 * @return string The filename of the XSL-FO file
	 */
	private function getXSLName()
	{
		return "certificate.xml";
	}
}
