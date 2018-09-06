<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

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
	 * @var Filesystem|null
	 */
	private $filesystem;

	/**
	 * @param integer $objectId
	 * @param string $certificatePath
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilLogger $logger
	 * @param Filesystem|null $filesystem
	 * @param ilCertificateTemplateRepository|null $templateRepository
	 * @param ilCertificateTemplateImportAction|null $importAction
	 */
	public function __construct(
		int $objectId,
		string $certificatePath,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilLogger $logger,
		Filesystem $filesystem,
		ilCertificateTemplateRepository $templateRepository = null,
		ilCertificateTemplateImportAction $importAction = null
	) {
		global $DIC;

		$this->objectId = $objectId;
		$this->certificatePath = $certificatePath;

		$this->logger = $logger;
		$database = $DIC->database();

		$this->filesystem = $filesystem;

		$this->placeholderDescriptionObject = $placeholderDescriptionObject;
		if (null === $templateRepository) {
			$templateRepository = new ilCertificateTemplateRepository($database, $logger);
		}
		$this->templateRepository = $templateRepository;
	}

	/**
	 * @param string $zipFile
	 * @param string $filename
	 * @return bool
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 * @throws ilDatabaseException
	 * @throws ilException
	 */
	public function import(string $zipFile, string $filename)
	{
		$importPath = $this->createArchiveDirectory();
		if (!ilUtil::moveUploadedFile($zipFile, $filename, CLIENT_WEB_DIR . $importPath . $filename)) {
			$this->filesystem->deleteDir($importPath);
			return false;
		}

		ilUtil::unzip(CLIENT_WEB_DIR . $importPath . $filename, true);

		$subDirectoryName = str_replace('.zip', '', strtolower($filename)) . '/';
		$subDirectoryAbsolutePath = CLIENT_WEB_DIR . $importPath . $subDirectoryName;

		$copyDirectory = $importPath;
		if (is_dir($subDirectoryAbsolutePath)) {
			$copyDirectory = $subDirectoryAbsolutePath;
		}

		$directoryInformation = ilUtil::getDir($copyDirectory);

		$xmlFiles = 0;
		foreach ($directoryInformation as $file) {
			if (strcmp($file['type'], 'file') == 0) {
				if (strpos($file['entry'], '.xml') !== false) {
					$xmlFiles++;
				}
			}
		}

		if (0 === $xmlFiles) {
			$this->filesystem->deleteDir($importPath);
			return false;
		}

		$certificate = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		$currentVersion = (int) $certificate->getVersion();
		$newVersion = $currentVersion;
		$backgroundImagePath = '';

		foreach ($directoryInformation as $file) {
			if (strcmp($file['type'], 'file') == 0) {
				$filePath = $importPath . $subDirectoryName . $file['entry'];
				if (strpos($file['entry'], '.xml') !== false) {
					$xsl = $this->filesystem->read($filePath);
					// as long as we cannot make RPC calls in a given directory, we have
					// to add the complete path to every url
					$xsl = preg_replace_callback("/url\([']{0,1}(.*?)[']{0,1}\)/", function(array $matches) {
						$basePath = rtrim(dirname($this->getBackgroundImageDirectory()), '/');
						$fileName = basename($matches[1]);

						return 'url(' . $basePath . '/' . $fileName . ')';
					}, $xsl);

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
					$newPath = $this->certificatePath . $newBackgroundImageName;
					$this->filesystem->copy($filePath, $newPath);

					$backgroundImagePath = $this->certificatePath . $newBackgroundImageName;
					// upload of the background image, create a thumbnail

					$backgroundImageThumbPath = $this->getBackgroundImageThumbnailPath();
					ilUtil::convertImage(
						CLIENT_WEB_DIR . $newPath,
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
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 */
	private function createArchiveDirectory() : string
	{
		$type = ilObject::_lookupType($this->objectId);
		$certificateId = $this->objectId;

		$dir = $this->certificatePath . time() . '__' . IL_INST_ID . '__' . $type . '__' . $certificateId . '__certificate/';
		$this->filesystem->createDir($dir);

		return $dir;
	}


	/**
	 * @param bool $asRelative
	 * @param string $backgroundImagePath
	 * @return mixed|string
	 */
	private function getBackgroundImageDirectory() : string
	{
		return str_replace(
			array(CLIENT_WEB_DIR, '//'),
			array('[CLIENT_WEB_DIR]', '/'),
			''
		);
	}

	/**
	 * @return string
	 */
	private function getBackgroundImageThumbnailPath() : string
	{
		return $this->certificatePath . 'background.jpg.thumb.jpg';
	}
}
