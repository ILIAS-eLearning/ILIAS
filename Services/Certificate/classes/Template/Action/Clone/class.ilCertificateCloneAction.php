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
	private $certificatePathFactory;

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
	 * @param ilLogger $logger
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificatePathFactory|null $certificatePathFactory
	 */
	public function __construct(
		ilDBInterface $database,
		ilCertificateTemplateRepository $templateRepository,
		\ILIAS\Filesystem\Filesystem $fileSystem = null,
		ilLogger $logger = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificatePathFactory $certificatePathFactory = null
	) {
		$this->database = $database;
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

		if (null === $certificatePathFactory) {
			$certificatePathFactory = new ilCertificatePathFactory();
		}
		$this->certificatePathFactory = $certificatePathFactory;
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

		$templates = $this->templateRepository->fetchCertificateTemplatesByObjId($oldObject->getId());

		/** @var ilCertificateTemplate $template */
		foreach ($templates as $template) {
			$backgroundImagePath = $template->getBackgroundImagePath();
			$backgroundImageFile = basename($backgroundImagePath);
			$backgroundImageThumbnail = dirname($backgroundImagePath) . '/background.jpg.thumb.jpg';

			$newBackgroundImage = $this->certificatePathFactory->createCertificatePath($newObject) . $backgroundImageFile;
			$newBackgroundImageThumbnail = $this->certificatePathFactory->createCertificatePath($newObject) . '/background.jpg.thumb.jpg';

			if ($this->fileSystem->has($backgroundImagePath)) {
				if ($this->fileSystem->has($newBackgroundImage)) {
					$this->fileSystem->delete($newBackgroundImage);
				}

				$this->fileSystem->copy(
					$backgroundImagePath,
					$newBackgroundImage
				);
			}

			if ($this->fileSystem->has($backgroundImageThumbnail)) {
				if ($this->fileSystem->has($newBackgroundImageThumbnail)) {
					$this->fileSystem->delete($newBackgroundImageThumbnail);
				}

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
