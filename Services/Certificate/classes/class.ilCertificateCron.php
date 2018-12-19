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

	/** @var ilCertificateObjectHelper|null */
	private $objectHelper;

	/** @var \ILIAS\DI\Container */
	private $dic;

	/**
	 * @param ilCertificateQueueRepository $queueRepository
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param ilUserCertificateRepository $userRepository
	 * @param ilCertificateValueReplacement|null $valueReplacement
	 * @param ilLogger|null $logger
	 * @param \ILIAS\DI\Container|null $dic
	 * @param ilLanguage|null $language
	 * @param ilCertificateObjectHelper|null $objectHelper
	 */
	public function __construct(
		ilCertificateQueueRepository $queueRepository = null,
		ilCertificateTemplateRepository $templateRepository = null,
		ilUserCertificateRepository $userRepository = null,
		ilCertificateValueReplacement $valueReplacement = null,
		ilLogger $logger = null,
		\ILIAS\DI\Container $dic = null,
		ilLanguage $language = null,
		ilCertificateObjectHelper $objectHelper = null
	) {
		if (null === $dic) {
			global $DIC;
			$dic = $DIC;
		}
		$this->dic = $dic;

		$this->queueRepository = $queueRepository;
		$this->templateRepository = $templateRepository;
		$this->userRepository = $userRepository;
		$this->valueReplacement = $valueReplacement;
		$this->logger = $logger;
		$this->objectHelper = $objectHelper;

		if ($dic) {
			if (isset($dic['lng'])) {
				$language = $dic->language();
				$language->loadLanguageModule('certificate');
			}
		}

		$this->lng = $language;
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
		if (null === $this->dic) {
			global $DIC;
			$this->dic = $DIC;
		}

		$database = $this->dic->database();

		if (null === $this->logger) {
			$this->logger = $this->dic->logger()->cert();
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

		if (null === $this->objectHelper) {
			$this->objectHelper = new ilCertificateObjectHelper();
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
				$templateId = $entry->getTemplateId();

				$this->logger->debug(sprintf(
					'Fetch certificate template for user id: "%s" and object id: "%s" and template id: "%s"',
					$userId,
					$objId,
					$templateId
				));

				$template = $this->templateRepository->fetchTemplate($templateId);

				$object = $this->objectHelper->getInstanceByObjId($objId, false);
				$type = $object->getType();

				$userObject = $this->objectHelper->getInstanceByObjId($userId, false);
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

			$thumbnailImagePath = (string)$template->getThumbnailImagePath();
			$userCertificate = new ilUserCertificate(
				$template->getId(),
				$objId,
				$type,
				$userId,
				$userObject->getFullname(),
				(int)$entry->getStartedTimestamp(),
				$certificateContent,
				json_encode($placeholderValues),
				null,
				$template->getVersion(),
				ILIAS_VERSION_NUMERIC,
				true,
				$template->getBackgroundImagePath(),
				$thumbnailImagePath
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
