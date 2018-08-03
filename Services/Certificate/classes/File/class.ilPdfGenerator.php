<?php


class ilPdfGenerator
{
	/**
	 * @var ilUserCertificateRepository
	 */
	private $certificateRepository;

	/**
	 * @var ilLogger
	 */
	private $logger;

	/**
	 * @param ilUserCertificateRepository $userCertificateRepository
	 * @param ilLogger $logger
	 */
	public function __construct(ilUserCertificateRepository $userCertificateRepository, ilLogger $logger)
	{
		$this->certificateRepository = $userCertificateRepository;
		$this->logger                = $logger;
	}

	/**
	 * @param $userCertificateId
	 * @return mixed
	 * @throws ilException
	 */
	public function generate($userCertificateId)
	{
		$certificate = $this->certificateRepository->fetchCertificate($userCertificateId);

		return $this->createPDFScalar($certificate);
	}

	/**
	 * @param $userId
	 * @param $objId
	 * @return mixed
	 * @throws ilException
	 */
	public function generateCurrentActiveCertificate($userId, $objId)
	{
		$certificate = $this->certificateRepository->fetchActiveCertificate($userId, $objId);

		return $this->createPDFScalar($certificate);
	}

	/**
	 * @param $certificate
	 * @return mixed
	 */
	private function createPDFScalar($certificate)
	{
		$certificateContent = $certificate->getCertificateContent();
		$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')
			->ilFO2PDF($certificateContent);

		return $pdf_base64->scalar;
	}
}
