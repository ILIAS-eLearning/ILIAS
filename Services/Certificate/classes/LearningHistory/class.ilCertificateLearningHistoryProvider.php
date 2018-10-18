<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

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
	
	/** @var Factory */
	protected $uiFactory;

	/** @var Renderer */
	protected $uiRenderer;

	/**
	 * @param int $user_id
	 * @param ilLearningHistoryFactory $factory
	 * @param ilLanguage $lng
	 * @param \ILIAS\DI\Container|null $dic
	 * @param ilUserCertificateRepository|null $userCertificateRepository
	 * @param ilCtrl $controller
	 * @param ilSetting|null $certificateSettings
	 * @param Factory|null $uiFactory
	 * @param Renderer|null $uiRenderer
	 */
	public function __construct(
		int $user_id,
		ilLearningHistoryFactory $factory,
		ilLanguage $lng,
		\ILIAS\DI\Container $dic = null,
		ilUserCertificateRepository $userCertificateRepository = null,
		ilCtrl $controller = null,
		ilSetting $certificateSettings = null,
		Factory $uiFactory = null,
		Renderer $uiRenderer = null
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

		if (null === $uiFactory) {
			$uiFactory = $dic->ui()->factory();
		}
		$this->uiFactory = $uiFactory;

		if (null === $uiRenderer) {
			$uiRenderer = $dic->ui()->renderer();
		}
		$this->uiRenderer = $uiRenderer;
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
			$href = $this->controller->getLinkTargetByClass('ilUserCertificateGUI', 'download');
			$this->controller->clearParametersByClass('ilUserCertificateGUI');

			$text = sprintf(
				$this->lng->txt('certificate_achievement_sub_obj'),
				$this->getEmphasizedTitle($certificate->getObjectTitle())
			);

			$link = $this->uiFactory->link()->standard($text, $href);
			$link = $this->uiRenderer->render($link);

			$text = sprintf(
				$this->lng->txt('certificate_achievement'),
				$link
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
