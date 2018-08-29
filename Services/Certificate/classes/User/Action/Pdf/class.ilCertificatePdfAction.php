<?php


class ilCertificatePdfAction
{
	/**
	 * @var ilLogger
	 */
	private $logger;

	/**
	 * @var ilPdfGenerator
	 */
	private $pdfGenerator;

	/**
	 * @param ilLogger $logger
	 * @param ilUserCertificateRepository $userCertificateRepository
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param ilPdfGenerator $pdfGenerator
	 */
	public function __construct(
		ilLogger $logger,
		ilPdfGenerator $pdfGenerator
	) {
		$this->logger = $logger;
		$this->pdfGenerator          = $pdfGenerator;
	}

	/**
	 * @param integer $objectId
	 * @param integer $userId
	 * @return bool
	 */
	public function createPDF($userId, $objectId)
	{
		$pdfScalar = $this->pdfGenerator->generateCurrentActiveCertificate($userId, $objectId);

		return $pdfScalar;
	}

	/**
	 * @param $objectId
	 * @param $userId
	 * @return bool
	 * @throws ilException
	 * @throws ilInvalidCertificateException
	 */
	public function downloadPdf($userId, $objectId)
	{
		$pdfScalar = $this->createPDF($userId, $objectId);

		ilUtil::deliverData(
			$pdfScalar,
			'Certificate.pdf',
			'application/pdf'
		);

		return $pdfScalar;
	}
}
