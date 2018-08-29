<?php

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
	 * @param integer $objectId
	 * @param string $certificatePath
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilCertificateTemplateRepository|null $templateRepository
	 * @param ilCertificateTemplateImportAction|null $importAction
	 */
	public function __construct(
		$objectId,
		$certificatePath,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilCertificateTemplateRepository $templateRepository = null,
		ilCertificateTemplateImportAction $importAction = null
	) {
		global $DIC;

		$this->objectId = $objectId;
		$this->certificatePath = $certificatePath;

		$database = $DIC->database();

		$this->placeholderDescriptionObject = $placeholderDescriptionObject;
		if (null === $templateRepository) {
			$templateRepository = new ilCertificateTemplateRepository($database);
		}
		$this->templateRepository = $templateRepository;
	}

	/**
	 * @param $zipFile
	 * @param $filename
	 * @return bool
	 * @throws ilException
	 */
	public function import($zipFile, $filename)
	{
		$importpath = $this->createArchiveDirectory();
		if (!ilUtil::moveUploadedFile($zipFile, $filename, $importpath . $filename))
		{
			ilUtil::delDir($importpath);
			return FALSE;
		}

		ilUtil::unzip($importpath . $filename, TRUE);

		$subDirectoryName = str_replace(".zip", "", strtolower($filename)) . "/";
		$subDirectoryAbsolutePath = $importpath . $subDirectoryName;

		$copydir = $importpath;
		if (is_dir($subDirectoryAbsolutePath)) {
			$copydir = $subDirectoryAbsolutePath;
		}
		$dirinfo = ilUtil::getDir($copydir);

		$xmlfiles = 0;
		foreach ($dirinfo as $file) {
			if (strcmp($file['type'], 'file') == 0) {
				if (strpos($file['entry'], '.xml') !== FALSE) {
					$xmlfiles++;
				}
			}
		}

		if (0 === $xmlfiles) {
			ilUtil::delDir($importpath);
			return false;
		}

		$certificate = $this->templateRepository->fetchCurrentlyActiveCertificate($this->objectId);

		$currentVersion = (int) $certificate->getVersion();

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

					if ($newBackgroundImageName !== '') {
						$xsl = preg_replace('/background_{0,1}[0-9]+\\.jpg/', $newBackgroundImageName, $xsl);
					}

					$template = new ilCertificateTemplate(
						$this->objectId,
						$xsl,
						md5($xsl),
						json_encode($this->placeholderDescriptionObject->getPlaceholderDescriptions()),
						$currentVersion + 1,
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
					@copy($copydir . $file['entry'], $newPath);

					$backgroundImagePath = $newPath;
					// upload of the background image, create a thumbnail

					$backgroundImageThumbPath = $this->getBackgroundImageThumbPath();
					ilUtil::convertImage(
						$newPath,
						$backgroundImageThumbPath,
						'JPEG',
						100
					);
				}
			}
		}

		ilUtil::delDir($importpath);
		return true;
	}

	/**
	 * Creates a directory for a zip archive containing multiple certificates
	 *
	 * @return string The created archive directory
	 */
	private function createArchiveDirectory()
	{
		$type = ilObject::_lookupType($this->objectId);
		$certificateId = $this->objectId;

		$dir = $this->certificatePath . time() . '__' . IL_INST_ID . '__' . $type . '__' . $certificateId . '__certificate/';
		ilUtil::makeDirParents($dir);
		return $dir;
	}

	private function getBackgroundImageDirectory($asRelative = false, $backgroundImagePath = '')
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

	private function getBackgroundImageThumbPath()
	{
		return $this->certificatePath . $this->getBackgroundImageName() . '.thumb.jpg';
	}

	public function getBackgroundImageName()
	{
		return "background.jpg";
	}

}
