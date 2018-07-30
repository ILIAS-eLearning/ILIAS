<?php


class ilCertificateCron extends ilCronJob
{
	const DEFAULT_SCHEDULE_HOURS = 1;

	/**
	 * @var ilCertificateQueueRepository
	 */
	private $queueRepository;

	/**
	 * @var ilCertificateTemplateRepository
	 */
	private $templateRepository;

	/**
	 * @var ilUserCertificateRepository
	 */
	private $userRepository;

	/**
	 * @var \ILIAS\DI\LoggingServices|ilLogger logger
	 */
	private $logger;

	/**
	 * @param ilCertificateQueueRepository $queueRepository
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param ilUserCertificateRepository $userRepository
	 * @param ilLogger|null $logger
	 */
	public function __construct(
		ilCertificateQueueRepository $queueRepository = null,
		ilCertificateTemplateRepository $templateRepository = null,
		ilUserCertificateRepository $userRepository = null,
		ilLogger $logger = null
	) {
		global $DIC;

		$database = $DIC->database();

		if (null === $queueRepository) {
			$queueRepository = new ilCertificateQueueRepository($database, $DIC->logger()->root());
		}
		$this->queueRepository = $queueRepository;

		if (null === $templateRepository) {
			$templateRepository = new ilCertificateTemplateRepository($database);
		}
		$this->templateRepository = $templateRepository;

		if (null === $userRepository) {
			$userRepository = new ilUserCertificateRepository($database, $DIC->logger()->root());
		}
		$this->userRepository = $userRepository;

		if (null === $logger) {
			$logger = $DIC->logger();
		}
		$this->logger = $logger;
	}

	public function run()
	{
		$this->logger->info('Begin with cron job to create user certificates from templates');

		$entries = $this->queueRepository->getAllEntriesFromQueue();

		foreach ($entries as $entry) {
			try {
				$this->logger->debug('Entry found will start of processing the entry');

				/** @var $entry ilCertificateQueueEntry */
				$class = $entry->getAdapterClass();
				$this->logger->debug('Adapter class to be executed "' . $class . '"');

				$placeholderValueObject = new $class();
				if (!$placeholderValueObject instanceof ilCertificatePlaceholderValues) {
					throw new ilException('The given class ' . $class . ' MUST be an instance of ilCertificateCronAdapter and MUST have an accessible namespace. The class map MAY be reloader.');
				}

				$objId = $entry->getObjId();
				$userId = $entry->getUserId();

				$this->logger->debug(sprintf(
					'Fetch currently active certificate for user id: "%s" and object id: "%s"',
					$userId,
					$objId
				));

				$template = $this->templateRepository->fetchCurrentlyActiveCertificate($objId);

				$object = ilObjectFactory::getInstanceByObjId($objId, false);
				$type = $object->getType();

				$userObject = ilObjectFactory::getInstanceByObjId($userId, false);
				if (!$userObject || !($userObject instanceof \ilObjUser)) {
					throw new ilException('The given user id"' . $userId . '" could not be referred to an actual user');
				}

				$this->logger->debug(sprintf(
					'Object type: "%s"',
					$type
				));

				$certificateContent = $template->getCertificateContent();

				$placeholderValues = $placeholderValueObject->getPlaceholderValues($userId, $objId);

				$this->logger->debug(sprintf(
					'Values for placeholders: "%s"',
					json_encode($placeholderValues)
				));
			} catch (ilInvalidCertificateException $exception) {
				$this->logger->warning($exception->getMessage());
				$this->logger->warning('The user MAY not be able to achieve the certificate based on the adapters settings');
				$this->logger->warning('Due the error, the entry will now be remove from the queue.');

				$this->queueRepository->removeFromQueue($entry->getId());

				continue;
			} catch (ilException $exception) {
				$this->logger->warning($exception->getMessage());
				$this->logger->warning('Due the error, the entry will now be remove from the queue.');

				$this->queueRepository->removeFromQueue($entry->getId());
				continue;
			}

			foreach ($placeholderValues as $placeholder => $value) {
				$certificateContent = str_replace('[' . $placeholder . ']', $value, $certificateContent);
			}

			$certificateContent = str_replace('[CLIENT_WEB_DIR]', CLIENT_WEB_DIR, $certificateContent);

			$userCertificate = new ilUserCertificate(
				$template->getId(),
				$objId,
				$type,
				$userId,
				$userObject->getFullname(),
				$entry->getStartedTimestamp(),
				$certificateContent,
				json_encode($placeholderValues),
				null,
				$template->getVersion(),
				ILIAS_VERSION_NUMERIC,
				true,
				$template->getBackgroundImagePath()
			);

			$this->userRepository->save($userCertificate);

			$this->queueRepository->removeFromQueue($entry->getId());
		}
	}

	/**
	 * Get id
	 *
	 * @return string
	 */
	public function getId()
	{
		return 'certificate';
	}

	/**
	 * Is to be activated on "installation"
	 *
	 * @return boolean
	 */
	public function hasAutoActivation()
	{
		return false;
	}

	/**
	 * Can the schedule be configured?
	 *
	 * @return boolean
	 */
	public function hasFlexibleSchedule()
	{
		return true;
	}

	/**
	 * Get schedule type
	 *
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_HOURS;
	}

	/**
	 * Get schedule value
	 *
	 * @return int|array
	 */
	function getDefaultScheduleValue()
	{
		return self::DEFAULT_SCHEDULE_HOURS;
	}
}
