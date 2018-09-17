<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;

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
	 * @var string
	 */
	private $rootDirectory;

	/**
	 * @var ilCertificateUtilHelper|null
	 */
	private $utilHelper;

	/**
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param string $rootDirectory
	 * @param ilCertificateUtilHelper|null $utilHelper
	 */
	public function __construct(
		ilCertificateTemplateRepository $templateRepository,
		string $rootDirectory = CLIENT_WEB_DIR,
		ilCertificateUtilHelper $utilHelper = null
	) {
		$this->templateRepository = $templateRepository;

		$this->rootDirectory = $rootDirectory;

		if (null === $utilHelper) {
			$utilHelper = new ilCertificateUtilHelper();
		}
		$this->utilHelper = $utilHelper;
	}

	/**
	 * @param $templateTemplateId
	 * @param $objectId
	 * @return mixed
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 * @throws ilDatabaseException
	 */
	public function delete($templateTemplateId, $objectId)
	{
		$this->templateRepository->deleteTemplate($templateTemplateId, $objectId);
		$previousTemplate = $this->templateRepository->activatePreviousCertificate($objectId);

		$this->overwriteBackgroundImageThumbnail($previousTemplate);
	}

	/**
	 * @param $previousTemplate
	 */
	private function overwriteBackgroundImageThumbnail(ilCertificateTemplate $previousTemplate)
	{
		$relativePath = $previousTemplate->getBackgroundImagePath();

		if (null !== $relativePath && '' !== $relativePath) {
			$pathInfo = pathinfo($relativePath);
			$newFilePath = $pathInfo['dirname'] . '/background.jpg.thumb.jpg';

			$this->utilHelper->convertImage(
				$this->rootDirectory . $relativePath,
				$this->rootDirectory . $newFilePath,
				'JPEG',
				100
			);
		}
	}
}
