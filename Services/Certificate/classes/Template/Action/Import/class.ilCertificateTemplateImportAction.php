<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateImportAction
{
	/**
	 * @var integer
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
	 * @var ilCertificatePlaceholderDescription
	 */
	private $placeholderDescriptionObject;

	/**
	 * @var ilLogger
	 */
	private $logger;

	/**
	 * @param integer $objectId
	 * @param string $certificatePath
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilCertificateTemplateRepository|null $templateRepository
	 * @param ilCertificateTemplateImportAction|null $importAction
	 * @param ilLogger $logger
	 */
	public function __construct(
		int $objectId,
		string $certificatePath,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilLogger $logger,
		ilCertificateTemplateRepository $templateRepository = null,
		ilCertificateTemplateImportAction $importAction = null
	) {
		global $DIC;

		$this->objectId = $objectId;
		$this->certificatePath = $certificatePath;

		$this->logger = $logger;
		$database = $DIC->database();

		$this->placeholderDescriptionObject = $placeholderDescriptionObject;
		if (null === $templateRepository) {
			$templateRepository = new ilCertificateTemplateRepository($database, $logger);
		}
		$this->templateRepository = $templateRepository;
	}

	/**
	 * @param $zipFile
	 * @param $filename
	 * @return bool
	 * @throws ilException
	 */
	public function import(string $zipFile, string $filename)
	{
		$importPath = $this->createArchiveDirectory();
		if (!ilUtil::moveUploadedFile($zipFile, $filename, $importPath . $filename)) {
			ilUtil::delDir($importPath);
			return false;
		}

		ilUtil::unzip($importPath . $filename, true);

		$subDirectoryName = str_replace('.zip', '', strtolower($filename)) . '/';
		$subDirectoryAbsolutePath = $importPath . $subDirectoryName;

		$copydir = $importPath;
		if (is_dir($subDirectoryAbsolutePath)) {
			$copydir = $subDirectoryAbsolutePath;
		}

		$dirinfo = ilUtil::getDir($copydir);

		$xmlFiles = 0;
		foreach ($dirinfo as $file) {
			if (strcmp($file['type'], 'file') == 0) {
				if (strpos($file['entry'], '.xml') !== false) {
					$xmlFiles++;
				}
			}
		}

		if (0 === $xmlFiles) {
			ilUtil::delDir($importPath);
			return false;
		}

		$certificate = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		$currentVersion = (int) $certificate->getVersion();
		$newVersion = $currentVersion;
		$backgroundImagePath = '';
		$newBackgroundImageName = '';
		foreach ($dirinfo as $file) {
			if (strcmp($file['type'], 'file') == 0) {
				if (strpos($file['entry'], '.xml') !== false) {
					$xsl = file_get_contents($copydir . $file['entry']);
					// as long as we cannot make RPC calls in a given directory, we have
					// to add the complete path to every url
					$xsl = preg_replace_callback("/url\([']{0,1}(.*?)[']{0,1}\)/", function(array $matches) {
						$basePath = rtrim(dirname($this->getBackgroundImageDirectory(true)), '/');
						$fileName = basename($matches[1]);

						return 'url(' . $basePath . '/' . $fileName . ')';
					}, $xsl);

//					if ($newBackgroundImageName !== '') {
//						$xsl = preg_replace('/background_{0,1}[0-9]+\\.jpg/', $newBackgroundImageName, $xsl);
//					}

					$template = new ilCertificateTemplate(
						$this->objectId,
						ilObject::_lookupType($this->objectId),
						$xsl,
						md5($xsl),
						json_encode($this->placeholderDescriptionObject->getPlaceholderDescriptions()),
						$newVersion,
						ILIAS_VERSION_NUMERIC,
						time(),
						true,
						$backgroundImagePath
					);

					$this->templateRepository->save($template);
				}
				else if (strpos($file['entry'], '.jpg') !== false) {
					$newVersion = $currentVersion + 1;
					$newBackgroundImageName = 'background_' . $newVersion . '.jpg';
					$newPath = CLIENT_WEB_DIR . $this->certificatePath . $newBackgroundImageName;
					@copy($copydir . $file['entry'], $newPath);

					$backgroundImagePath = $this->certificatePath . $newBackgroundImageName;
					// upload of the background image, create a thumbnail

					$backgroundImageThumbPath = $this->getBackgroundImageThumbPath();
					ilUtil::convertImage(
						$newPath,
						CLIENT_WEB_DIR . $backgroundImageThumbPath,
						'JPEG',
						100
					);
				}
			}
		}

		ilUtil::delDir($importPath);
		return true;
	}

	/**
	 * Creates a directory for a zip archive containing multiple certificates
	 *
	 * @return string The created archive directory
	 */
	private function createArchiveDirectory() : string
	{
		$type = ilObject::_lookupType($this->objectId);
		$certificateId = $this->objectId;

		$dir = CLIENT_WEB_DIR . $this->certificatePath . time() . '__' . IL_INST_ID . '__' . $type . '__' . $certificateId . '__certificate/';
		ilUtil::makeDirParents($dir);
		return $dir;
	}

	/**
	 * @param bool $asRelative
	 * @param string $backgroundImagePath
	 * @return mixed|string
	 */
	private function getBackgroundImageDirectory(bool $asRelative = false, string $backgroundImagePath = '') : string
	{
		if($asRelative) {
			return str_replace(
				array(CLIENT_WEB_DIR, '//'),
				array('[CLIENT_WEB_DIR]', '/'),
				$backgroundImagePath
			);
		}

		return $this->certificatePath;
	}

	/**
	 * @return string
	 */
	private function getBackgroundImageThumbPath() : string
	{
		return $this->certificatePath . $this->getBackgroundImageName() . '.thumb.jpg';
	}

	/**
	 * @return string
	 */
	public function getBackgroundImageName() : string
	{
		return 'background.jpg';
	}

}
