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
	 * @var ilUtilHelper
	 */
	private $ilUtilHelper;

	/**
	 * @param ilLogger $logger
	 * @param ilPdfGenerator $pdfGenerator
	 * @param ilUtilHelper $ilUtilHelper
	 */
	public function __construct(
		ilLogger $logger,
		ilPdfGenerator $pdfGenerator,
		ilUtilHelper $ilUtilHelper
	) {
		$this->logger       = $logger;
		$this->pdfGenerator = $pdfGenerator;
		$this->ilUtilHelper = $ilUtilHelper;
	}

	/**
	 * @param integer $objectId
	 * @param integer $userId
	 * @return bool
	 * @throws ilException
	 */
	public function createPDF(int $userId, int $objectId)
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
	public function downloadPdf(int $userId, int $objectId)
	{
		$pdfScalar = $this->createPDF($userId, $objectId);

		$this->ilUtilHelper->deliverData(
			$pdfScalar,
			'Certificate.pdf',
			'application/pdf'
		);

		return $pdfScalar;
	}
}
