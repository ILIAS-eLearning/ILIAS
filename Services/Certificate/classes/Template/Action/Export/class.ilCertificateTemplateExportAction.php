<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

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
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * @param integer $objectId
	 * @param string $certificatePath
	 * @param Filesystem $filesystem
	 * @param ilCertificateTemplateRepository $templateRepository
	 */
	public function __construct(
		int $objectId,
		string $certificatePath,
		ilCertificateTemplateRepository $templateRepository,
		Filesystem $filesystem
	) {
		$this->objectId           = $objectId;
		$this->certificatePath    = $certificatePath;
		$this->templateRepository = $templateRepository;
		$this->filesystem         = $filesystem;
	}

	/**
	 * Creates an downloadable file via the browser
	 */
	public function export()
	{
		$time = time();

		$type = ilObject::_lookupType($this->objectId);
		$certificateId = $this->objectId;

		$exportPath = $this->certificatePath . $time . '__' . IL_INST_ID . '__' . $type . '__' . $certificateId . '__certificate/';

		$this->filesystem->createDir($exportPath, \ILIAS\Filesystem\Visibility::PUBLIC_ACCESS);

		$template = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		$xslContent = $template->getCertificateContent();

		$this->filesystem->put($exportPath . 'certificate.xml', $xslContent);

		$backgroundImagePath = $template->getBackgroundImagePath();
		if ($backgroundImagePath !== null && $backgroundImagePath !== '') {
			$this->filesystem->copy($backgroundImagePath, $exportPath . 'background.jpg');
		}

		$objectType = ilObject::_lookupType($this->objectId);
		$zipFileName = $time . '__' . IL_INST_ID . '__' . $objectType . '__' . $this->objectId . '__certificate.zip';


		$zipPath = CLIENT_WEB_DIR . $this->certificatePath . $zipFileName;
		ilUtil::zip($exportPath, $zipPath);
		$this->filesystem->deleteDir($exportPath);

		ilUtil::deliverFile($zipPath, $zipFileName, "application/zip");
	}
}
