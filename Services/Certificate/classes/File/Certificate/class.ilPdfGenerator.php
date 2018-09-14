<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
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
	 * @var ilCertificateRpcClientFactoryHelper|null
	 */
	private $rpcHelper;

	/**
	 * @param ilUserCertificateRepository $userCertificateRepository
	 * @param ilLogger $logger
	 * @param ilCertificateRpcClientFactoryHelper|null $rpcHelper
	 */
	public function __construct(
		ilUserCertificateRepository $userCertificateRepository,
		ilLogger $logger,
		ilCertificateRpcClientFactoryHelper $rpcHelper = null
	) {
		$this->certificateRepository = $userCertificateRepository;
		$this->logger                = $logger;

		if (null === $rpcHelper) {
			$rpcHelper = new ilCertificateRpcClientFactoryHelper();
		}
		$this->rpcHelper = $rpcHelper;
	}

	/**
	 * @param $userCertificateId
	 * @return mixed
	 * @throws ilException
	 */
	public function generate(int $userCertificateId)
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
	public function generateCurrentActiveCertificate(int $userId, int $objId) : string
	{
		$certificate = $this->certificateRepository->fetchActiveCertificate($userId, $objId);

		return $this->createPDFScalar($certificate);
	}

	/**
	 * @param $certificate
	 * @return mixed
	 */
	private function createPDFScalar(ilUserCertificate $certificate) : string
	{
		$certificateContent = $certificate->getCertificateContent();
		$pdf_base64 = $this->rpcHelper->ilFO2PDF('RPCTransformationHandler', $certificateContent);

		return $pdf_base64->scalar;
	}
}
