<?php


class ilCertificateCloneAction
{
	/**
	 * @var ilLogger
	 */
	private $logger;

	/**
	 * @var ilCertificateFactory
	 */
	private $certificateFactory;

	/**
	 * @var ilCertificateTemplateRepository
	 */
	private $templateRepository;

	/**
	 * @var ilDBInterface
	 */
	private $database;

	/**
	 * @param ilLogger $logger
	 * @param ilDBInterface $database
	 * @param ilCertificateFactory $certificateFactory
	 * @param ilCertificateTemplateRepository $templateRepository
	 */
	public function __construct(
		ilDBInterface $database,
		ilCertificateFactory $certificateFactory,
		ilCertificateTemplateRepository $templateRepository,
		illLogger $logger = null
	) {
		$this->database = $database;
		$this->certificateFactory = $certificateFactory;
		$this->templateRepository = $templateRepository;

		if (null === $logger) {
			$logger = ilLoggerFactory::getLogger('cert');
		}
		$this->logger = $logger;
	}

	/**
	 * @param ilObject $oldObject
	 * @param ilObject $newObject
	 * @throws ilException
	 */
	public function cloneCertificate(ilObject $oldObject, ilObject $newObject)
	{
		$oldType = $oldObject->getType();
		$newType = $newObject->getType();

		if ($oldType !== $newType) {
			throw new ilException(sprintf(
				'The types "%s" and "%s" for cloning  does not match',
				$oldType,
				$newType
			));
		}

		$oldCertificate = $this->certificateFactory->create($oldObject);

		$newCertificate = $this->certificateFactory->create($newObject);

		$templates = $this->templateRepository->fetchCertificateTemplatesByObjId($oldObject->getId());

		/** @var ilCertificateTemplate $template */
		foreach ($templates as $template) {
			$backgroundImagePath = CLIENT_WEB_DIR . $template->getBackgroundImagePath();
			$backgroundImageFile = basename($backgroundImagePath);
			$backgroundImageThumbnail = $oldCertificate->getBackgroundImageThumbPath();

			$newBackgroundImage = CLIENT_WEB_DIR . $newCertificate->getBackgroundImageDirectory() . $backgroundImageFile;
			$newBackgroundImageThumbnail = $newCertificate->getBackgroundImageThumbPath();

			if (@file_exists($backgroundImagePath)) {
				@copy($backgroundImagePath, $newBackgroundImage);
			}

			if (@file_exists($backgroundImageThumbnail)) {
				@copy($backgroundImageThumbnail, $newBackgroundImageThumbnail);
			}

			$newTemplate = new ilCertificateTemplate(
				$newObject->getId(),
				ilObject::_lookupType($newObject->getId()),
				$template->getCertificateContent(),
				$template->getCertificateHash(),
				$template->getTemplateValues(),
				$template->getVersion(),
				ILIAS_VERSION_NUMERIC,
				time(),
				$template->isCurrentlyActive(),
				$newBackgroundImage
			);

			$this->templateRepository->save($newTemplate);
		}

		// #10271
		if($this->readActive($oldObject->getId())) {
			$this->database->replace('il_certificate',
				array("obj_id" => array("integer", $newObject->getId())),
				array()
			);
		}
	}

	/**
	 * @param integer $objectId
	 * @return int
	 */
	private function readActive($objectId)
	{
		$sql = 'SELECT obj_id FROM il_certificate WHERE obj_id = ' . $this->database->quote($objectId, 'integer');

		$query = $this->database->query($sql);

		return $this->database->numRows($query);
	}
}
