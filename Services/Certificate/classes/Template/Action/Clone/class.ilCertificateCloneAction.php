<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
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
	 * @var \ILIAS\Filesystem\Filesystem|null
	 */
	private $fileSystem;

	/**
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @param ilDBInterface $database
	 * @param ilCertificateFactory $certificateFactory
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param \ILIAS\Filesystem\Filesystem|null $fileSystem
	 * @param illLogger $logger
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param string $rootDirectory
	 */
	public function __construct(
		ilDBInterface $database,
		ilCertificateFactory $certificateFactory,
		ilCertificateTemplateRepository $templateRepository,
		\ILIAS\Filesystem\Filesystem $fileSystem = null,
		illLogger $logger = null,
		ilCertificateObjectHelper $objectHelper = null
	) {
		$this->database = $database;
		$this->certificateFactory = $certificateFactory;
		$this->templateRepository = $templateRepository;

		if (null === $logger) {
			global $DIC;
			$logger = $DIC->logger()->cert();
		}
		$this->logger = $logger;

		if (null === $fileSystem) {
			global $DIC;
			$fileSystem = $DIC->filesystem()->web();
		}
		$this->fileSystem = $fileSystem;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;
	}

	/**
	 * @param ilObject $oldObject
	 * @param ilObject $newObject
	 * @param string $iliasVersion
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 * @throws ilDatabaseException
	 * @throws ilException
	 */
	public function cloneCertificate(
		ilObject $oldObject,
		ilObject $newObject,
		string $iliasVersion = ILIAS_VERSION_NUMERIC
	) {
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
			$backgroundImagePath = $template->getBackgroundImagePath();
			$backgroundImageFile = basename($backgroundImagePath);
			$backgroundImageThumbnail = $oldCertificate->getBackgroundImageThumbPath();

			$newBackgroundImage = $newCertificate->getBackgroundImageDirectory() . $backgroundImageFile;
			$newBackgroundImageThumbnail = $newCertificate->getBackgroundImageThumbPath();

			if ($this->fileSystem->has($backgroundImagePath)) {
				$this->fileSystem->copy(
					$backgroundImagePath,
					$newBackgroundImage
				);
			}

			if ($this->fileSystem->has($backgroundImageThumbnail)) {
				$this->fileSystem->copy(
					$backgroundImageThumbnail,
					$newBackgroundImageThumbnail
				);
			}

			$newTemplate = new ilCertificateTemplate(
				$newObject->getId(),
				$this->objectHelper->lookupObjId($newObject->getId()),
				$template->getCertificateContent(),
				$template->getCertificateHash(),
				$template->getTemplateValues(),
				$template->getVersion(),
				$iliasVersion,
				time(),
				$template->isCurrentlyActive(),
				$newBackgroundImage
			);

			$this->templateRepository->save($newTemplate);
		}

		// #10271
		if($this->readActive($oldObject->getId())) {
			$this->database->replace('il_certificate',
				array('obj_id' => array('integer', $newObject->getId())),
				array()
			);
		}
	}

	/**
	 * @param integer $objectId
	 * @return int
	 */
	private function readActive(int $objectId) : int
	{
		$sql = 'SELECT obj_id FROM il_certificate WHERE obj_id = ' . $this->database->quote($objectId, 'integer');

		$query = $this->database->query($sql);

		return $this->database->numRows($query);
	}
}
