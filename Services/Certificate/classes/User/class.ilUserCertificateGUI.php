<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @ilCtrl_IsCalledBy ilUserCertificateGUI: ilUserCertificateTableGUI
 *
 * @ingroup ServicesCertificate
 *
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

	/**
	 * @param ilTemplate|null $template
	 * @param ilCtrl|null $controller
	 * @param ilLanguage|null $language
	 * @param ilObjUser $user
	 * @param ilUserCertificateRepository|null $userCertificateRepository
	 * @param \GuzzleHttp\Psr7\Request|null $request
	 * @param ilLogger $certificateLogger
	 * @param ilSetting|null $certificateSettings
	 */
	public function __construct(
		ilTemplate $template = null,
		ilCtrl $controller = null,
		ilLanguage $language = null,
		ilObjUser $user = null,
		ilUserCertificateRepository $userCertificateRepository = null,
		GuzzleHttp\Psr7\Request $request = null,
		ilLogger $certificateLogger = null,
		ilSetting $certificateSettings = null
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
	 * @param bool $a_migration_started
	 */
	public function listCertificates(bool $a_migration_started = false)
	{
		global $DIC;

		if (!$this->certificateSettings->get('active')) {
			$this->controller->redirect($this);
			return;
		}

		if (!$a_migration_started) {
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

		$table = new ilUserCertificateTableGUI(
			$provider,
			$this->user,
			$this,
			'listCertificates'
		);

		$table->populate();

		$this->template->setContent($table->getHTML());
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
