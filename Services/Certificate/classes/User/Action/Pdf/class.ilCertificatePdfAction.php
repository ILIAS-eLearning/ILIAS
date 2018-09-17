<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
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
	 * @var ilCertificateUtilHelper
	 */
	private $ilUtilHelper;

	/**
	 * @param ilLogger $logger
	 * @param ilPdfGenerator $pdfGenerator
	 * @param ilCertificateUtilHelper $ilUtilHelper
	 */
	public function __construct(
		ilLogger $logger,
		ilPdfGenerator $pdfGenerator,
		ilCertificateUtilHelper $ilUtilHelper = null
	) {
		$this->logger       = $logger;
		$this->pdfGenerator = $pdfGenerator;
		if (null == $ilUtilHelper) {
			$ilUtilHelper = new ilCertificateUtilHelper();
		}
		$this->ilUtilHelper = $ilUtilHelper;
	}

	/**
	 * @param integer $objectId
	 * @param integer $userId
	 * @return string
	 * @throws ilException
	 */
	public function createPDF(int $userId, int $objectId) : string
	{
		$pdfScalar = $this->pdfGenerator->generateCurrentActiveCertificate($userId, $objectId);

		return $pdfScalar;
	}

	/**
	 * @param $objectId
	 * @param $userId
	 * @return string
	 * @throws ilException
	 * @throws ilInvalidCertificateException
	 */
	public function downloadPdf(int $userId, int $objectId) : string
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
