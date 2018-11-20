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

	/** @var ilCertificateUtilHelper|null */
	private $utilHelper;

	/** @var ilAccess|ilAccessHandler|null */
	private $access;

	/** @var ilCertificateObjectHelper|null */
	private $objectHelper;

	/**
	 * @param int $user_id
	 * @param ilLearningHistoryFactory $factory
	 * @param ilLanguage $lng
	 * @param ilTemplate|null $template
	 * @param \ILIAS\DI\Container|null $dic
	 * @param ilUserCertificateRepository|null $userCertificateRepository
	 * @param ilCtrl $controller
	 * @param ilSetting|null $certificateSettings
	 * @param Factory|null $uiFactory
	 * @param Renderer|null $uiRenderer
	 * @param ilCertificateUtilHelper|null $utilHelper
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilAccess|null $access
	 */
	public function __construct(
		int $user_id,
		ilLearningHistoryFactory $factory,
		ilLanguage $lng,
		ilTemplate $template = null,
		\ILIAS\DI\Container $dic = null,
		ilUserCertificateRepository $userCertificateRepository = null,
		ilCtrl $controller = null,
		ilSetting $certificateSettings = null,
		Factory $uiFactory = null,
		Renderer $uiRenderer = null,
		ilCertificateUtilHelper $utilHelper = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilAccess $access = null
	) {
		$lng->loadLanguageModule("cert");

		parent::__construct($user_id, $factory, $lng, $template);

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

		if (null === $utilHelper) {
			$utilHelper = new ilCertificateUtilHelper();
		}
		$this->utilHelper = $utilHelper;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $access) {
			$access = $dic->access();
		}
		$this->access = $access;
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

			$certificateDownloadHref = $this->controller->getLinkTargetByClass('ilUserCertificateGUI', 'download');
			$this->controller->clearParametersByClass('ilUserCertificateGUI');

			$displayText = $this->createDisplayText($objectId, $certificate, $certificateDownloadHref);

			$entries[] = new ilLearningHistoryEntry(
				$displayText,
				$displayText,
				$this->utilHelper->getImagePath("icon_cert.svg"),
				$certificate->getUserCertificate()->getAcquiredTimestamp(),
				$objectId
			);
		}

		return $entries;
	}

	/**
	 * Get name of provider (in user language)
	 * @return string
	 */
	public function getName(): string
	{
		return $this->lng->txt('certificates');
	}


	/**
	 * @param $objectId
	 * @param $certificate
	 * @param $certificateDownloadHref
	 * @return string
	 */
	protected function createDisplayText(int $objectId, ilUserCertificatePresentation $certificate, string $certificateDownloadHref): string
	{
		$allRefIds = $this->objectHelper->getAllReferences($objectId);
		$ref_id = (int) array_shift($allRefIds);

		if ($this->access->checkAccess("read", "", $ref_id)) {
			return $this->createDisplayTextForKnownObject($ref_id, $certificate, $certificateDownloadHref);
		}

		return $this->createDisplayTextForUnknownObject($certificate->getObjectTitle(), $certificateDownloadHref);
	}

	/**
	 * @param $ref_id
	 * @param $certificate
	 * @param $certificateDownloadHref
	 * @return string
	 */
	private function createDisplayTextForKnownObject(int $ref_id, ilUserCertificatePresentation $certificate, string $certificateDownloadHref)
	{
		$objectUrl = ilLink::_getLink($ref_id);

		$label = sprintf('%1$s', $certificate->getObjectTitle());
		$objectLink = $this->uiFactory->link()->standard($label, $objectUrl);
		$objectLink = $this->uiRenderer->render($objectLink);

		$certificateLink = $this->uiFactory->link()->standard($this->lng->txt('certificate'), $certificateDownloadHref);
		$certificateLink = $this->uiRenderer->render($certificateLink);

		$text = sprintf(
			$this->lng->txt('certificate_achievement_object_exists'),
			$certificateLink,
			$this->getEmphasizedTitle($objectLink)
		);

		return $text;
	}

	/**
	 * @param $objectText
	 * @param $certificateDownloadHref
	 * @return string
	 */
	protected function createDisplayTextForUnknownObject($objectText, $certificateDownloadHref): string
	{
		$text = sprintf(
			$this->lng->txt('certificate_achievement_sub_obj'),
			$this->getEmphasizedTitle($objectText)
		);

		$link = $this->uiFactory->link()->standard($text, $certificateDownloadHref);
		$link = $this->uiRenderer->render($link);

		$displayText = sprintf(
			$this->lng->txt('certificate_achievement'),
			$link
		);
		return $displayText;
	}

}
