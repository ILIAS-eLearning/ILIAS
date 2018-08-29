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
	 * @param ilPdfGenerator $pdfGenerator
	 */
	public function __construct(ilLogger $logger, ilPdfGenerator $pdfGenerator)
	{
		$this->logger = $logger;
		$this->pdfGenerator = $pdfGenerator;
	}

	public function downloadPdf($userId, $objectId)
	{
		$this->logger->info(sprintf('Start download certificate PDF for user: "%s" object id; "%s"', $userId, $objectId));

		$pdfScalar = $this->pdfGenerator->generateCurrentActiveCertificate($userId, $objectId);

		ilUtil::deliverData(
			$pdfScalar,
			'certificate.pdf',
			"application/pdf"
		);
	}
}
