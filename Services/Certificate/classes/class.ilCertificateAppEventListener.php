<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateAppEventListener
 *
 * @author Niels Theen <ntheen@databay.de>
 * @version $Id:$
 *
 * @package Services/Certificate
 */
class ilCertificateAppEventListener implements ilAppEventListener
{
	/** @var \ilDBInterface */
	protected $db;

	/** @var ilObjectDataCache */
	private $objectDataCache;

	/** @var ilLogger */
	private $logger;

	/** @var string */
	protected $component = '';

	/** @var string */
	protected $event = '';

	/** @var array */
	protected $parameters = [];

	/**
	 * ilCertificateAppEventListener constructor.
	 * @param \ilDBInterface $db
	 * @param \ilObjectDataCache $objectDataCache
	 * @param \ilLogger $logger
	 */
	public function __construct(
		\ilDBInterface $db,
		\ilObjectDataCache $objectDataCache,
		\ilLogger $logger
	) {
		$this->db = $db;
		$this->objectDataCache = $objectDataCache;
		$this->logger = $logger;
	}

	/**
	 * @param string $component
	 * @return \ilCertificateAppEventListener
	 */
	public function withComponent(string $component): self
	{
		$clone = clone $this;

		$clone->component = $component;

		return $clone;
	}

	/**
	 * @param string $event
	 * @return \ilCertificateAppEventListener
	 */
	public function withEvent(string $event): self
	{
		$clone = clone $this;

		$clone->event = $event;

		return $clone;
	}

	/**
	 * @param array $parameters
	 * @return \ilCertificateAppEventListener
	 */
	public function withParameters(array $parameters): self
	{
		$clone = clone $this;

		$clone->parameters = $parameters;

		return $clone;
	}

	/**
	 * @return bool
	 */
	protected function isLearningAchievementEvent(): bool
	{
		return (
			'Services/Tracking' === $this->component &&
			'updateStatus' === $this->event
		);
	}

	/**
	 * @return bool
	 */
	protected function isMigratingCertificateEvent(): bool
	{
		return (
			'Services/Certificate' === $this->component &&
			'migrateUserCertificate' === $this->event
		);
	}

	/**
	 *
	 */
	public function handle()
	{
		try {
			if ($this->isLearningAchievementEvent()) {
				$this->handleLPUpdate();
			} else {
				if ($this->isMigratingCertificateEvent()) {
					$this->handleNewUserCertificate();
				}
			}
		} catch (\ilException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @inheritdoc
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		global $DIC;

		$listener = new static(
			$DIC->database(),
			$DIC['ilObjDataCache'],
			$DIC->logger()->cert()
		);

		$listener
			->withComponent($a_component)
			->withEvent($a_event)
			->withParameters($a_parameter)
			->handle();
	}

	/**
	 * @throws \ilException
	 */
	private function handleLPUpdate()
	{
		$status = $this->parameters['status'] ?? \ilLpStatus::LP_STATUS_NOT_ATTEMPTED_NUM;

		if ($status == \ilLPStatus::LP_STATUS_COMPLETED_NUM) {
			$certificateQueueRepository = new \ilCertificateQueueRepository($this->db, $this->logger);
			$certificateClassMap = new \ilCertificateTypeClassMap();
			$templateRepository = new \ilCertificateTemplateRepository($this->db, $this->logger);

			$objectId = $this->parameters['obj_id'] ?? 0;
			$userId = $this->parameters['usr_id'] ?? 0;

			$type  = $this->objectDataCache->lookupType($objectId);

			if ($certificateClassMap->typeExistsInMap($type)) {
				$template = $templateRepository->fetchCurrentlyActiveCertificate($objectId);
				if (true === $template->isCurrentlyActive()) {
					$className = $certificateClassMap->getPlaceHolderClassNameByType($type);

					$entry = new \ilCertificateQueueEntry(
						$objectId,
						$userId,
						$className,
						\ilCronConstants::IN_PROGRESS,
						$template->getId(),
						time()
					);

					$certificateQueueRepository->addToQueue($entry);
				}
			}

			foreach (\ilObject::_getAllReferences($objectId) as $refId) {
				$templateRepository = new \ilCertificateTemplateRepository($this->db, $this->logger);
				$progressEvaluation = new \ilCertificateCourseLearningProgressEvaluation($templateRepository);

				$completedCourses = $progressEvaluation->evaluate($refId, $userId);
				foreach ($completedCourses as $courseObjId) {
					// We do not check if we support the type anymore, because the type 'crs' is always supported
					$courseTemplate = $templateRepository->fetchCurrentlyActiveCertificate($courseObjId);

					if (true === $courseTemplate->isCurrentlyActive()) {
						$type = $this->objectDataCache->lookupType($courseObjId);

						$className = $certificateClassMap->getPlaceHolderClassNameByType($type);

						$entry = new \ilCertificateQueueEntry(
							$courseObjId,
							$userId,
							$className,
							\ilCronConstants::IN_PROGRESS,
							time()
						);

						$certificateQueueRepository->addToQueue($entry);
					}
				}
			}
		}
	}

	/**
	 * @throws \ilDatabaseException
	 * @throws \ilException
	 */
	private function handleNewUserCertificate()
	{
		$this->logger->info('Try to create new certificates based on event');

		if (false === array_key_exists('obj_id', $this->parameters)) {
			$this->logger->error('Object ID is not added to the event. Abort.');
			return;
		}

		if (false === array_key_exists('user_id', $this->parameters)) {
			$this->logger->error('User ID is not added to the event. Abort.');
			return;
		}

		if (false === array_key_exists('background_image_path', $this->parameters)) {
			$this->logger->error('Background Image Path is not added to the event. Abort.');
			return; 
		}

		if (false === array_key_exists('acquired_timestamp', $this->parameters)) {
			$this->logger->error('Acquired Timestamp is not added to the event. Abort.');
			return;
		}

		if (false === array_key_exists('ilias_version', $this->parameters)) {
			$this->logger->error('ILIAS version is not added to the event. Abort.');
			return;
		}

		$objId = $this->parameters['obj_id'] ?? 0;
		$userId = $this->parameters['user_id'] ?? ß;
		$backgroundImagePath = $this->parameters['background_image_path'] ?? '';
		$acquiredTimestamp = $this->parameters['acquired_timestamp'] ?? '';
		$iliasVersion = $this->parameters['ilias_version'] ?? '';

		$templateRepository = new \ilCertificateTemplateRepository($this->db, $this->logger);
		$template = $templateRepository->fetchFirstCreatedTemplate($objId);

		$userCertificateRepository = new \ilUserCertificateRepository($this->db, $this->logger);

		$type = $this->objectDataCache->lookupType($objId);

		$classMap = new ilCertificateTypeClassMap();
		if (!$classMap->typeExistsInMap($type)) {
			$this->logger->error(sprintf('Migrations for type "%s" not supported. Abort.', $type));
			return;
		}

		$className = $classMap->getPlaceHolderClassNameByType($type);
		$placeholderValuesObject = new $className();
		$placeholderValues = $placeholderValuesObject->getPlaceholderValues($userId, $objId);

		$replacementService = new \ilCertificateValueReplacement();
		$certificateContent = $replacementService->replace(
			$placeholderValues,
			$template->getCertificateContent(),
			$backgroundImagePath
		);

		$user = \ilObjectFactory::getInstanceByObjId($userId, false);
		if (!$user || !($user instanceof \ilObjUser)) {
			throw new \ilException(sprintf('The given user ID("%s") is not a user', $userId));
		}

		$userCertificate = new \ilUserCertificate(
			$template->getId(),
			$objId,
			$type,
			$userId,
			$user->getFullname(),
			$acquiredTimestamp,
			$certificateContent,
			'',
			null,
			1,
			$iliasVersion,
			true,
			$backgroundImagePath
		);

		$userCertificateRepository->save($userCertificate);
	}
}
