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
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateUtilHelper
	 */
	private $utilHelper;

	/**
	 * @var string
	 */
	private $installationID;

	/**
	 * @param integer $objectId
	 * @param string $certificatePath
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilLogger $logger
	 * @param Filesystem|null $filesystem
	 * @param ilCertificateTemplateRepository|null $templateRepository
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificateUtilHelper|null $utilHelper
	 * @param ilDBInterface|null $database
	 * @param string $installationID
	 */
	public function __construct(
		int $objectId,
		string $certificatePath,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilLogger $logger,
		Filesystem $filesystem,
		ilCertificateTemplateRepository $templateRepository = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateUtilHelper $utilHelper = null,
		ilDBInterface $database = null
	) {
		$this->objectId = $objectId;
		$this->certificatePath = $certificatePath;

		$this->logger = $logger;
		if (null === $database) {
			global $DIC;
			$database = $DIC->database();
		}

		$this->filesystem = $filesystem;

		$this->placeholderDescriptionObject = $placeholderDescriptionObject;

		if (null === $templateRepository) {
			$templateRepository = new ilCertificateTemplateRepository($database, $logger);
		}
		$this->templateRepository = $templateRepository;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $utilHelper) {
			$utilHelper = new ilCertificateUtilHelper();
		}
		$this->utilHelper = $utilHelper;
	}

	/**
	 * @param string $zipFile
	 * @param string $filename
	 * @param string $rootDir
	 * @param string $iliasVerision
	 * @param string $installationID
	 * @return bool
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 * @throws ilDatabaseException
	 * @throws ilException
	 */
	public function import(
		string $zipFile,
		string $filename,
		string $rootDir = CLIENT_WEB_DIR,
		string $iliasVerision = ILIAS_VERSION_NUMERIC,
		string $installationID = IL_INST_ID
	) {
		$importPath = $this->createArchiveDirectory($installationID);

		$result = $this->utilHelper->moveUploadedFile($zipFile, $filename, $rootDir . $importPath . $filename);

		if (!$result) {
			$this->filesystem->deleteDir($importPath);
			return false;
		}

		$this->utilHelper->unzip(
			$rootDir . $importPath . $filename,
			true
		);

		$subDirectoryName = str_replace('.zip', '', strtolower($filename)) . '/';
		$subDirectoryAbsolutePath = $rootDir . $importPath . $subDirectoryName;

		$copyDirectory = $importPath;
		if (is_dir($subDirectoryAbsolutePath)) {
			$copyDirectory = $subDirectoryAbsolutePath;
		}

		$directoryInformation = $this->utilHelper->getDir($copyDirectory);

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

		$certificate = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

		$currentVersion = (int) $certificate->getVersion();
		$newVersion = $currentVersion + 1;
		$backgroundImagePath = $certificate->getBackgroundImagePath();
		$cardThumbnailImagePath = $certificate->getThumbnailImagePath();

		$xsl = $certificate->getCertificateContent();

		foreach ($directoryInformation as $file) {
			if (strcmp($file['type'], 'file') == 0) {
				$filePath = $importPath . $subDirectoryName . $file['entry'];
				if (strpos($file['entry'], '.xml') !== false) {
					$xsl = $this->filesystem->read($filePath);
					// as long as we cannot make RPC calls in a given directory, we have
					// to add the complete path to every url
					$xsl = preg_replace_callback("/url\([']{0,1}(.*?)[']{0,1}\)/", function(array $matches) use ($rootDir) {
						$basePath = rtrim(dirname($this->getBackgroundImageDirectory($rootDir)), '/');
						$fileName = basename($matches[1]);

						if (strlen($basePath) > 0) {
							$basePath .= '/';
						}

						return 'url(' . $basePath . $fileName . ')';
					}, $xsl);
				} elseif (strpos($file['entry'], '.jpg') !== false) {
					$newBackgroundImageName = 'background_' . $newVersion . '.jpg';
					$newPath = $this->certificatePath . $newBackgroundImageName;
					$this->filesystem->copy($filePath, $newPath);

					$backgroundImagePath = $this->certificatePath . $newBackgroundImageName;
					// upload of the background image, create a thumbnail

					$backgroundImageThumbPath = $this->getBackgroundImageThumbnailPath();

					$thumbnailImagePath = $rootDir . $backgroundImageThumbPath;

					$originalImagePath = $rootDir . $newPath;
					$this->utilHelper->convertImage(
						$originalImagePath,
						$thumbnailImagePath,
						'JPEG',
						100
					);
				} elseif (strpos($file['entry'], '.svg') !== false) {
					$newCardThumbnailName = 'thumbnail_' . $newVersion . '.svg';
					$newPath = $this->certificatePath . $newCardThumbnailName;

					$this->filesystem->copy($filePath, $newPath);

					$cardThumbnailImagePath = $this->certificatePath . $newCardThumbnailName;
				}
			}
		}

		$jsonEncodedTemplateValues = json_encode($this->placeholderDescriptionObject->getPlaceholderDescriptions());

		$newHashValue = hash(
			'sha256',
			implode('', array(
				$xsl,
				$backgroundImagePath,
				$jsonEncodedTemplateValues,
				$cardThumbnailImagePath
			))
		);

		$template = new ilCertificateTemplate(
			$this->objectId,
			$this->objectHelper->lookupType($this->objectId),
			$xsl,
			$newHashValue,
			$jsonEncodedTemplateValues,
			$newVersion,
			$iliasVerision,
			time(),
			true,
			$backgroundImagePath,
			$cardThumbnailImagePath
		);

		$this->templateRepository->save($template);

		$this->utilHelper->delDir($importPath);

		return true;
	}

	/**
	 * Creates a directory for a zip archive containing multiple certificates
	 *
	 * @param string $installationID
	 * @return string The created archive directory
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 */
	private function createArchiveDirectory(string $installationID) : string
	{
		$type = $this->objectHelper->lookupType($this->objectId);
		$certificateId = $this->objectId;

		$dir = $this->certificatePath . time() . '__' . $installationID . '__' . $type . '__' . $certificateId . '__certificate/';
		$this->filesystem->createDir($dir);

		return $dir;
	}


	/**
	 * @param string $rootDir
	 * @return mixed|string
	 */
	private function getBackgroundImageDirectory(string $rootDir) : string
	{
		return str_replace(
			array($rootDir, '//'),
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
