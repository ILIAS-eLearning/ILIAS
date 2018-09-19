<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCron extends \ilCronJob
{
	const DEFAULT_SCHEDULE_HOURS = 1;

	/** @var \ilLanguage */
	protected $lng;

	/** \@var ilCertificateQueueRepository */
	private $queueRepository;

	/** @var \ilCertificateTemplateRepository */
	private $templateRepository;

	/** @var \ilUserCertificateRepository */
	private $userRepository;

	/** @var \ILIAS\DI\LoggingServices|ilLogger logger */
	private $logger;

	/** @var \ilCertificateValueReplacement */
	private $valueReplacement;

	/**
	 * @param ilCertificateQueueRepository $queueRepository
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param ilUserCertificateRepository $userRepository
	 * @param ilCertificateValueReplacement|null $valueReplacement
	 * @param ilLogger|null $logger
	 */
	public function __construct(
		ilCertificateQueueRepository $queueRepository = null,
		ilCertificateTemplateRepository $templateRepository = null,
		ilUserCertificateRepository $userRepository = null,
		ilCertificateValueReplacement $valueReplacement = null,
		ilLogger $logger = null
	)
	{
		global $DIC;

		$this->queueRepository = $queueRepository;
		$this->templateRepository = $templateRepository;
		$this->userRepository = $userRepository;
		$this->valueReplacement = $valueReplacement;
		$this->logger = $logger;

		if ($DIC) {
			if (isset($DIC['lng'])) {
				$this->lng = $DIC->language();
				$this->lng->loadLanguageModule('certificate');
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
		return $this->lng->txt('cert_cron_task_title');
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return $this->lng->txt('cert_cron_task_desc');
	}

	public function init()
	{
		global $DIC;

		$database = $DIC->database();

		if (null === $this->logger) {
			$this->logger = $DIC->logger()->cert();
		}

		if (null === $this->queueRepository) {
			$this->queueRepository = new ilCertificateQueueRepository($database, $this->logger);
		}

		if (null === $this->templateRepository) {
			$this->templateRepository = new ilCertificateTemplateRepository($database, $this->logger);
		}

		if (null === $this->userRepository) {
			$this->userRepository = new ilUserCertificateRepository($database, $this->logger);
		}

		if (null === $this->valueReplacement) {
			$this->valueReplacement = new ilCertificateValueReplacement();
		}
	}

	/**
	 * @ineritdoc
	 */
	public function run()
	{
		$this->init();

		$this->logger->info('START - Begin with cron job to create user certificates from templates');

		$entries = $this->queueRepository->getAllEntriesFromQueue();

		$status = ilCronJobResult::STATUS_OK;

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

				$template = $this->templateRepository->fetchCurrentlyUsedCertificate($objId);

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
				$this->logger->warning('Due the error, the entry will now be removed from the queue.');

				$this->queueRepository->removeFromQueue($entry->getId());

				continue;
			} catch (ilException $exception) {
				$this->logger->warning($exception->getMessage());
				$this->logger->warning('Due the error, the entry will now be removed from the queue.');

				$this->queueRepository->removeFromQueue($entry->getId());
				continue;
			}

			$certificateContent = $this->valueReplacement->replace(
				$placeholderValues,
				$certificateContent,
				CLIENT_WEB_DIR . $template->getBackgroundImagePath()
			);

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

		return new ilCronJobResult($status);
	}

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return 'certificate';
	}

	/**
	 * @inheritdoc
	 */
	public function hasAutoActivation()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function hasFlexibleSchedule()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultScheduleValue()
	{
		return 1;
	}
}
