<?php

/**
 * @ilCtrl_IsCalledBy ilUserCertificateGUI: ilUserCertificateTableGUI
 *
 * @ingroup ServicesCertificate
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

	public function executeCommand()
	{
		$next_class = $this->controller->getNextClass($this);
		$cmd = $this->controller->getCmd();

		switch ($cmd) {
			case 'download':
				$pdfGenerator = new ilPdfGenerator($this->userCertificateRepository, $this->certificateLogger);
				$pdfScalar = $pdfGenerator->generate((int) $this->request->getQueryParams()['user_certificate_id']);

				ilUtil::deliverData(
					$pdfScalar,
					'Certificate.pdf',
					"application/pdf"
				);
			default:
				$this->show();
				break;
		}

		$this->show();


		return true;
	}

	private function show()
	{
		$certificates = $this->userCertificateRepository->fetchActiveCertificates($this->user->getId());

		$data = array();
		/** @var ilUserCertificate $certificate */
		foreach ($certificates as $certificate) {
			$result['id'] = $certificate->getId();

			$objectId = $certificate->getObjId();
			$object = ilObjectFactory::getInstanceByObjId($objectId);
			$result['title'] = $object->getTitle();

			$result['date'] = ilDatePresentation::formatDate(new ilDateTime($certificate->getAcquiredTimestamp(), IL_CAL_UNIX));
			$data[] = $result;
		}

		$table = new ilUserCertificateTableGUI($this, 'show');
		$table->setData($data);

		$html = $table->getHTML();
		$this->template->setContent($html);
	}
}
