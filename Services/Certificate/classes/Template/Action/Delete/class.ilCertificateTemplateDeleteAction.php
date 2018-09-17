<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateDeleteAction implements ilCertificateDeleteAction
{
	/**
	 * @var ilCertificateTemplateRepository
	 */
	private $templateRepository;

	/**
	 * @var \ILIAS\Filesystem\Filesystem
	 */
	private $fileSystem;

	/**
	 * @var string
	 */
	private $rootDirectory;

	/**
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param \ILIAS\Filesystem\Filesystem|null $filesystem
	 * @param string $rootDirectory
	 */
	public function __construct(
		ilCertificateTemplateRepository $templateRepository,
		\ILIAS\Filesystem\Filesystem $filesystem = null,
		string $rootDirectory = CLIENT_WEB_DIR
	) {
		$this->templateRepository = $templateRepository;

		if (null ===$filesystem) {
			global $DIC;
			$filesystem = $DIC->filesystem();
		}
		$this->fileSystem = $filesystem;

		$this->rootDirectory = $rootDirectory;
	}

	/**
	 * @param $templateTemplateId
	 * @param $objectId
	 * @return mixed
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 */
	public function delete($templateTemplateId, $objectId)
	{
		$this->templateRepository->deleteTemplate($templateTemplateId, $objectId);
		$previousTemplate = $this->templateRepository->activatePreviousCertificate($objectId);

		$this->overwriteBackgroundImageThumbnail($previousTemplate);
	}

	/**
	 * @param $previousTemplate
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 */
	private function overwriteBackgroundImageThumbnail($previousTemplate)
	{
		$relativePath = $previousTemplate->getBackgroundImagePath();
		$absoluteImagePath = $this->rootDirectory . $relativePath;

		if (null !== $relativePath && '' !== $relativePath) {
			$pathInfo = pathinfo($absoluteImagePath);
			$newFilePath = $pathInfo['dirname'] . '/background.jpg.thumb.jpg';

			$this->fileSystem->copy($absoluteImagePath, $newFilePath);
		}
	}
}
