<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @ilCtrl_IsCalledBy ilUserCertificateGUI: ilUserCertificateTableGUI
 *
 * @ingroup ServicesCertificate
 *
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateGUI
{
	/**
	 * @var ilTemplate
	 */
	private $template;

	/**
	 * @var ilCtrl
	 */
	private $controller;

	/**
	 * @var ilLanguage
	 */
	private $language;

	/**
	 * @var ilUserCertificateRepository|null
	 */
	private $userCertificateRepository;

	/**
	 * @var ilObjUser|ilUser|null
	 */
	private $user;

	/**
	 * @var \GuzzleHttp\Psr7\Request|null|\Psr\Http\Message\ServerRequestInterface
	 */
	private $request;

	/**
	 * @var ilLogger
	 */
	private $certificateLogger;

	/**
	 * @param ilTemplate|null $template
	 * @param ilCtrl|null $controller
	 * @param ilLanguage|null $language
	 * @param ilObjUser $user
	 * @param ilUserCertificateRepository|null $userCertificateRepository
	 * @param \GuzzleHttp\Psr7\Request|null $request
	 * @param ilLogger $certificateLogger
	 */
	public function __construct(
		ilTemplate $template = null,
		ilCtrl $controller = null,
		ilLanguage $language = null,
		ilObjUser $user = null,
		ilUserCertificateRepository $userCertificateRepository = null,
		GuzzleHttp\Psr7\Request $request = null,
		ilLogger $certificateLogger = null
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
	}

	/**
	 * @return string
	 */
	private function getDefaultCommand(): string 
	{
		return 'show';
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

		switch ($nextClass) {
			default:
				if (!method_exists($this, $cmd)) {
					$cmd = $this->getDefaultCommand();
				}
				$this->{$cmd}();
		}

		$this->show();


		return true;
	}

	/**
	 * @throws ilException
	 */
	private function download()
	{
		$pdfGenerator = new ilPdfGenerator($this->userCertificateRepository, $this->certificateLogger);
		$pdfScalar = $pdfGenerator->generate((int) $this->request->getQueryParams()['user_certificate_id']);

		ilUtil::deliverData(
			$pdfScalar,
			'Certificate.pdf',
			"application/pdf"
		);
	}

	/**
	 * @throws ilDateTimeException
	 */
	private function show()
	{
	}
}
