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
	 * @var ilUserCertificateTemplateRepository
	 */
	private $userRepository;

	/**
	 * @param ilCertificateQueueRepository $queueRepository
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param ilUserCertificateTemplateRepository $userRepository
	 */
	public function __construct(
		ilCertificateQueueRepository $queueRepository,
		ilCertificateTemplateRepository $templateRepository,
		ilUserCertificateTemplateRepository $userRepository
	) {
		$this->queueRepository = $queueRepository;
		$this->templateRepository = $templateRepository;
		$this->userRepository = $userRepository;
	}

	public function run()
	{
		$entries = $this->queueRepository->getAllEntriesFromQueue();

		foreach ($entries as $entry) {
			/** @var $entry ilCertificateQueueEntry */
			$class = $entry->getAdapterClass();
			$adapter = new $class();
			if (!$adapter instanceof ilCertificateCronAdapter) {
				throw new ilException('The given class ' . $class . ' MUST be an instance of ilCertificateCronAdapter.');
			}

			$objId = $entry->getObjId();
			$userId = $entry->getUserId();

			$template = $this->templateRepository->fetchCurrentlyActiveCertificate($objId);

			$object = ilObjectFactory::getInstanceByObjId($objId, false);
			$type = $object->getType();

			$userObject = ilObjectFactory::getInstanceByObjId($userId, false);
			if (!$userObject || !($userObject instanceof \ilObjUser)) {
				throw new ilException('The given user id"' . $userId . '" could not be referred to an actual user');
			}

			$certificateContent = $template->getCertificateContent();

			$placeholderValues = $adapter->getPlaceholderValues($userId, $objId);
			foreach ($placeholderValues as $placeholder => $value) {
				$certificateContent = str_replace('[' . $placeholder . ']', $value, $certificateContent );
			}

			$userCertificate = new ilUserCertificateTemplate(
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
				true
			);

			$this->userRepository->save($userCertificate);
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
