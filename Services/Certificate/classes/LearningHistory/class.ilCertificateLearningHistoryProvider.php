<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{
	/**
	 * @var ilUserCertificateRepository
	 */
	private $userCertificateRepository;

	/**
	 * @var ilCtrl
	 */
	private $controller;

	/**
	 * @var ilSetting|null
	 */
	private $certificateSettings;

	/**
	 * @param int $user_id
	 * @param ilLearningHistoryFactory $factory
	 * @param ilLanguage $lng
	 * @param \ILIAS\DI\Container|null $dic
	 * @param ilUserCertificateRepository|null $userCertificateRepository
	 * @param ilCtrl $controller
	 * @param ilSetting|null $certificateSettings
	 */
	public function __construct(
		int $user_id,
		ilLearningHistoryFactory $factory,
		ilLanguage $lng,
		\ILIAS\DI\Container $dic = null,
		ilUserCertificateRepository $userCertificateRepository = null,
		ilCtrl $controller = null,
		ilSetting $certificateSettings = null
	) {
		$lng->loadLanguageModule("cert");

		parent::__construct($user_id, $factory, $lng);

		if (null === $dic) {
			global $DIC;
			$dic = $DIC;
		}

		if (null === $userCertificateRepository) {
			$database = $dic->database();
			$looger = $dic->logger()->cert();
			$userCertificateRepository = new ilUserCertificateRepository($database, $looger);
		}
		$this->userCertificateRepository = $userCertificateRepository;

		if (null === $controller) {
			$controller = $dic->ctrl();
		}
		$this->controller = $controller;

		if (null === $certificateSettings) {
			$certificateSettings =  new ilSetting("certificate");
		}
		$this->certificateSettings = $certificateSettings;
	}

	/**
	 * Is active?
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return (bool) $this->certificateSettings->get('active');
	}

	/**
	 * Get entries
	 *
	 * @param int $ts_start
	 * @param int $ts_end
	 * @return ilLearningHistoryEntry[]
	 */
	public function getEntries($ts_start, $ts_end)
	{
		$entries = array();

		$certificates = $this->userCertificateRepository->fetchActiveCertificatesInIntervalForPresentation($this->user_id, $ts_start, $ts_end);

		foreach ($certificates as $certificate) {
			$objectId = $certificate->getUserCertificate()->getObjId();

			$this->controller->setParameterByClass(
				'ilUserCertificateGUI',
				'certificate_id',
				$certificate->getUserCertificate()->getId()
			);
			$link = $this->controller->getLinkTargetByClass('ilUserCertificateGUI', 'download');
			$this->controller->clearParametersByClass('ilUserCertificateGUI');

			$href = str_replace('{LINK}', $link , '<a href="{LINK}">{LINK_TEXT}</a>');
			$href = str_replace('{LINK_TEXT}', $this->lng->txt('certificate'), $href);

			$text = str_replace(
				"$3$",
				$href,
				$this->lng->txt("certificate_achievement")
			);

			$text = str_replace(
				"$1$",
				$certificate->getObjectTitle(),
				$text
			);

			$entries[] = new ilLearningHistoryEntry(
				$text,
				$text,
				ilUtil::getImagePath("icon_cert.svg"),
				$certificate->getUserCertificate()->getAcquiredTimestamp(),
				$objectId
			);
		}

		return $entries;
	}
}
