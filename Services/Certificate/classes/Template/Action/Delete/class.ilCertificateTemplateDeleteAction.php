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
	 * @var string
	 */
	private $rootDirectory;

	/**
	 * @var ilCertificateUtilHelper|null
	 */
	private $utilHelper;

	/**
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param string $rootDirectory
	 * @param ilCertificateUtilHelper|null $utilHelper
	 * @param ilCertificateObjectHelper|null $objectHelper
	 */
	public function __construct(
		ilCertificateTemplateRepository $templateRepository,
		string $rootDirectory = CLIENT_WEB_DIR,
		ilCertificateUtilHelper $utilHelper = null,
		ilCertificateObjectHelper $objectHelper = null
	) {
		$this->templateRepository = $templateRepository;

		$this->rootDirectory = $rootDirectory;

		if (null === $utilHelper) {
			$utilHelper = new ilCertificateUtilHelper();
		}
		$this->utilHelper = $utilHelper;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;
	}

	/**
	 * @param $templateTemplateId
	 * @param $objectId
	 * @param string $iliasVerion
	 * @return mixed
	 * @throws ilDatabaseException
	 */
	public function delete($templateTemplateId, $objectId, $iliasVerion = ILIAS_VERSION_NUMERIC)
	{
		$template = $this->templateRepository->fetchCurrentlyUsedCertificate($objectId);

		$this->templateRepository->deleteTemplate($templateTemplateId, $objectId);
//		$previousTemplate = $this->templateRepository->activatePreviousCertificate($objectId);

		$certificateTemplate = new ilCertificateTemplate(
			$objectId,
			$this->objectHelper->lookupType($objectId),
			'',
			md5(''),
			'',
			$template->getVersion() + 1,
			$iliasVerion,
			time(),
			false,
			''
		);

		$this->templateRepository->save($certificateTemplate);

		$this->overwriteBackgroundImageThumbnail($certificateTemplate);
	}

	/**
	 * @param $previousTemplate
	 */
	private function overwriteBackgroundImageThumbnail(ilCertificateTemplate $previousTemplate)
	{
		$relativePath = $previousTemplate->getBackgroundImagePath();

		if (null === $relativePath || '' === $relativePath) {
			$relativePath = '/certificates/default/background.jpg';
		}

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
