<?php


class ilCertificateTemplateDeleteAction implements ilCertificateDeleteAction
{
	/**
	 * @var ilCertificateTemplateRepository
	 */
	private $templateRepository;

	/**
	 * @param ilCertificateTemplateRepository $templateRepository
	 */
	public function __construct(ilCertificateTemplateRepository $templateRepository)
	{
		$this->templateRepository = $templateRepository;
	}

	/**
	 * @param $templateTemplateId
	 * @param $objectId
	 * @return mixed
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
	private function overwriteBackgroundImageThumbnail($previousTemplate)
	{
		$backgroundImagePath = $previousTemplate->getBackgroundImagePath();

		$pathInfo = pathinfo($backgroundImagePath);
		$newFilePath = $pathInfo['dirname'] . '/background.jpg.thumb.jpg';

		copy($backgroundImagePath, $newFilePath);
	}
}
