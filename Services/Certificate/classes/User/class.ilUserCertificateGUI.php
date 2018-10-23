<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @ingroup ServicesCertificate
 * @author  Niels Theen <ntheen@databay.de>
 * @ilCtrl_IsCalledBy ilUserCertificateGUI: ilLearningHistoryGUI
 */
class ilUserCertificateGUI
{
	/** @var ilTemplate */
	private $template;

	/** @var ilCtrl */
	private $controller;

	/** @var ilLanguage */
	private $language;

	/** @var ilUserCertificateRepository|null */
	private $userCertificateRepository;

	/** @var ilObjUser|null */
	private $user;

	/** @var \GuzzleHttp\Psr7\Request|null|\Psr\Http\Message\ServerRequestInterface */
	private $request;

	/** @var ilLogger */
	private $certificateLogger;

	/** @var ilSetting */
	protected $certificateSettings;

	/** @var Factory */
	protected $uiFactory;

	/** @var Renderer */
	protected $uiRenderer;

	/** @var ilAccessHandler */
	protected $access;
	
	const SORTATION_SESSION_KEY = 'my_certificates_sorting';

	/**
	 * @var array 
	 */
	protected $sortationOptions = [
		'title_ASC' => 'cert_sortable_by_title_asc',
		'title_DESC' => 'cert_sortable_by_title_desc',
		'date_ASC' => 'cert_sortable_by_issue_date_asc',
		'date_DESC' => 'cert_sortable_by_issue_date_desc',
	];

	/** @var string */
	protected $defaultSorting = 'date_DESC';

	/** @var \ILIAS\Filesystem\Filesystem */
	private $filesystem;

	/**
	 * @param ilTemplate|null $template
	 * @param ilCtrl|null $controller
	 * @param ilLanguage|null $language
	 * @param ilObjUser $user
	 * @param ilUserCertificateRepository|null $userCertificateRepository
	 * @param \GuzzleHttp\Psr7\Request|null $request
	 * @param ilLogger $certificateLogger
	 * @param ilSetting|null $certificateSettings
	 * @param Factory|null $uiFactory
	 * @param Renderer|null $uiRenderer
	 * @param \ilAccessHandler|null $access
	 */
	public function __construct(
		ilTemplate $template = null,
		ilCtrl $controller = null,
		ilLanguage $language = null,
		ilObjUser $user = null,
		ilUserCertificateRepository $userCertificateRepository = null,
		GuzzleHttp\Psr7\Request $request = null,
		ilLogger $certificateLogger = null,
		ilSetting $certificateSettings = null,
		Factory $uiFactory = null,
		Renderer $uiRenderer = null,
		\ilAccessHandler $access = null,
		\ILIAS\Filesystem\Filesystem $filesystem = null
	) {
		global $DIC;

		$logger = $DIC->logger()->cert();

		if ($template === null) {
			$template = $DIC->ui()->mainTemplate();
		}
		$this->template = $template;

		if ($controller === null) {
			$controller = $DIC->ctrl();
		}
		$this->controller = $controller;
		
		if ($language === null) {
			$language = $DIC->language();
		}
		$this->language = $language;

		if ($userCertificateRepository === null) {
			$userCertificateRepository = new ilUserCertificateRepository($DIC->database(), $logger);
		}
		$this->userCertificateRepository = $userCertificateRepository;

		if ($user === null) {
			$user = $DIC->user();
		}
		$this->user = $user;

		if ($request === null) {
			$request = $DIC->http()->request();
		}
		$this->request = $request;

		if ($certificateLogger === null) {
			$certificateLogger = $DIC->logger()->cert();
		}
		$this->certificateLogger = $certificateLogger;

		if ($certificateSettings === null) {
			$certificateSettings = new ilSetting("certificate");
		}
		$this->certificateSettings = $certificateSettings;

		if (null === $uiFactory) {
			$uiFactory = $DIC->ui()->factory();
		}
		$this->uiFactory = $uiFactory;

		if (null === $uiRenderer) {
			$uiRenderer = $DIC->ui()->renderer();
		}
		$this->uiRenderer = $uiRenderer;

		if (null === $access) {
			$access = $DIC->access();
		}
		$this->access = $access;

		if (null === $filesystem) {
			$filesystem = $DIC->filesystem()->web();
		}
		$this->filesystem = $filesystem;

		$this->language->loadLanguageModule('cert');
	}

	/**
	 * @return string
	 */
	private function getDefaultCommand(): string 
	{
		return 'listCertificates';
	}

	/**
	 * @return bool
	 * @throws ilDateTimeException
	 * @throws ilException
	 */
	public function executeCommand()
	{
		$nextClass = $this->controller->getNextClass($this);
		$cmd = $this->controller->getCmd();

		if (!$this->certificateSettings->get('active')) {
			$this->controller->returnToParent($this);
		}
		
		$this->template->setTitle($this->language->txt('obj_cert'));

		switch ($nextClass) {
			case 'ilcertificatemigrationgui':
				$cert_migration_gui = new \ilCertificateMigrationGUI();
				$ret = $this->controller->forwardCommand($cert_migration_gui);
				/** @var ilTemplate $tpl */
				$this->template->setMessage(\ilTemplate::MESSAGE_TYPE_SUCCESS, $ret, true);
				$this->listCertificates(true);
				break;

			default:
				if (!method_exists($this, $cmd)) {
					$cmd = $this->getDefaultCommand();
				}
				$this->{$cmd}();
		}

		return true;
	}

	/**
	 * @param bool $migrationWasStarted
	 * @throws ilDateTimeException
	 */
	public function listCertificates(bool $migrationWasStarted = false)
	{
		global $DIC;

		if (!$this->certificateSettings->get('active')) {
			$this->controller->redirect($this);
			return;
		}

		if (!$migrationWasStarted) {
			$cert_ui_elements = new \ilCertificateMigrationUIElements();
			$messageBoxLink = $this->controller->getLinkTargetByClass(['ilCertificateMigrationGUI'], 'startMigration', false, true, false);
			$messageBox = $cert_ui_elements->getMigrationMessageBox($messageBoxLink);

			if (strlen($messageBox) > 0) {
				$this->template->setCurrentBlock('mess');
				$this->template->setVariable('MESSAGE', $messageBox);
				$this->template->parseCurrentBlock('mess');
			}
		}

		$provider = new ilUserCertificateTableProvider(
			$DIC->database(),
			$this->certificateLogger,
			$this->controller,
			$this->language->txt('certificate_no_object_title')
		);

		$sorting = $this->getCurrentSortation();
		$data = $provider->fetchDataSet(
			$this->user->getId(),
			[
				'order_field' => explode('_', $sorting)[0],
				'order_direction' => explode('_', $sorting)[1]
			],
			[]
		);

		$uiComponents = [];

		if (count($data['items']) > 0) {
			$sortationOptions = [];
			$cards = [];

			foreach ($this->sortationOptions as $fieldAndDirection => $lngVariable) {
				$sortationOptions[$fieldAndDirection] = $this->language->txt($lngVariable);
			}

			$sortViewControl = $this->uiFactory
				->viewControl()
				->sortation($sortationOptions)
				->withLabel($this->language->txt($this->sortationOptions[$sorting]))
				->withTargetURL($this->controller->getLinkTarget($this, 'applySortation'), 'sort_by');
			$uiComponents[] = $sortViewControl;

			foreach ($data['items'] as $certificateData) {
				$thumbnailImagePath = $certificateData['thumbnail_image_path'];

				$imagePath = ilUtil::getWebspaceDir(). $thumbnailImagePath;
				if ($thumbnailImagePath === null
					|| $thumbnailImagePath === ''
					|| !$this->filesystem->has($thumbnailImagePath)
				) {
					$imagePath = \ilUtil::getImagePath('icon_cert.svg');
				}

				$cardImage = $this->uiFactory->image()->standard(
					ilWACSignedPath::signFile($imagePath),
					$certificateData['title']
				);

				$listSections = [];

				$this->controller->setParameter($this, 'certificate_id', $certificateData['id']);
				$downloadHref = $this->controller->getLinkTarget($this, 'download');
				$this->controller->clearParameters($this);
				$listSections[$this->language->txt('cert_download_label')] = $this->uiRenderer->render(
					$this->uiFactory->button()->standard('Download', $downloadHref)
				);

				$oldDatePresentationStatus = \ilDatePresentation::useRelativeDates();
				\ilDatePresentation::setUseRelativeDates(true);
				$listSections[$this->language->txt('cert_issued_on_label')] = \ilDatePresentation::formatDate(
					new \ilDateTime($certificateData['date'], \IL_CAL_UNIX)
				);
				\ilDatePresentation::setUseRelativeDates($oldDatePresentationStatus);

				$objectTypeIcon = $this->uiFactory
					->icon()
					->standard($certificateData['obj_type'], $certificateData['obj_type'], 'small');

				$objectTitle = $certificateData['title'];
				$refIds = \ilObject::_getAllReferences($certificateData['obj_id']);
				if (count($refIds) > 0) {
					foreach ($refIds as $refId) {
						if ($this->access->checkAccess('read', '', $refId)) {
							$objectTitle = $this->uiRenderer->render(
								$this->uiFactory->link()->standard($objectTitle, \ilLink::_getLink($refId))
							);
							break;
						}
					}
				}

				$listSections[$this->language->txt('cert_object_label')] = implode('', [
					$this->uiRenderer->render($objectTypeIcon),
					$objectTitle
				]);

				$listSections[$this->language->txt('cert_description_label')] = $certificateData['description'];

				$card = $this->uiFactory
					->card($certificateData['title'], $cardImage)
					->withSections([
						$this->uiFactory->listing()->descriptive($listSections)
					]);

				$cards[] = $card;
			}

			$deck = $this->uiFactory->deck($cards);

			$uiComponents[] = $this->uiFactory->divider()->horizontal();

			$uiComponents[] = $deck;
		} else {
			\ilUtil::sendInfo($this->language->txt('cert_currently_no_certs'));
		}

		$this->template->setContent($this->uiRenderer->render($uiComponents));
	}

	/**
	 * @return string
	 */
	protected function getCurrentSortation(): string
	{
		$sorting = \ilSession::get(self::SORTATION_SESSION_KEY);
		if (!array_key_exists($sorting, $this->sortationOptions)) {
			$sorting = $this->defaultSorting;
		}

		return $sorting;
	}

	/**
	 * 
	 */
	protected function applySortation()
	{
		$sorting = $this->request->getQueryParams()['sort_by'] ?? $this->defaultSorting;
		if (!array_key_exists($sorting, $this->sortationOptions)) {
			$sorting = $this->defaultSorting;
		}
		\ilSession::set(self::SORTATION_SESSION_KEY, $sorting);

		$this->listCertificates();
	}

	/**
	 * @throws \ilException
	 */
	public function download()
	{
		global $DIC;

		$user = $DIC->user();
		$language = $DIC->language();

		$userCertificateRepository = new ilUserCertificateRepository(null, $this->certificateLogger);
		$pdfGenerator = new ilPdfGenerator($userCertificateRepository, $this->certificateLogger);

		$userCertificateId = (int)$this->request->getQueryParams()['certificate_id'];

		try {
			$userCertificate = $userCertificateRepository->fetchCertificate($userCertificateId);
			if ((int) $userCertificate->getUserId() !== (int) $user->getId()) {
				throw new ilException(sprintf('User "%s" tried to access certificate: "%s"', $user->getLogin(), $userCertificateId));
			}
		} catch (ilException $exception) {
			$this->certificateLogger->warning($exception->getMessage());
			ilUtil::sendFailure($language->txt('cert_error_no_access'));
			$this->listCertificates();
			return;
		}

		$pdfAction = new ilCertificatePdfAction(
			$this->certificateLogger,
			$pdfGenerator,
			new ilCertificateUtilHelper(),
			$this->language->txt('error_creating_certificate_pdf')
		);

		$pdfAction->downloadPdf($userCertificate->getUserId(), $userCertificate->getObjId());

		$this->listCertificates();
	}
}
