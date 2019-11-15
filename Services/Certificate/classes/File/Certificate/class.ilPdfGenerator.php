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
	 * @var ilCertificateScormPdfFilename|null
	 */
	private $scormPdfFilename;

	/**
	 * @var ilCertificatePdfFileNameFactory|null
	 */
	private $pdfFilenameFactory;

	/**
	 * @param ilUserCertificateRepository $userCertificateRepository
	 * @param ilLogger $logger
	 * @param ilCertificateRpcClientFactoryHelper|null $rpcHelper
	 * @param ilCertificatePdfFileNameFactory|null $pdfFileNameFactory
	 */
	public function __construct(
		ilUserCertificateRepository $userCertificateRepository,
		ilLogger $logger,
		ilCertificateRpcClientFactoryHelper $rpcHelper = null,
		ilCertificatePdfFileNameFactory $pdfFileNameFactory = null
	) {
		$this->certificateRepository = $userCertificateRepository;
		$this->logger                = $logger;

		if (null === $rpcHelper) {
			$rpcHelper = new ilCertificateRpcClientFactoryHelper();
		}
		$this->rpcHelper = $rpcHelper;

		if (null === $pdfFileNameFactory) {
			$pdfFileNameFactory = new ilCertificatePdfFileNameFactory();
		}
		$this->pdfFilenameFactory = $pdfFileNameFactory;

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

	public function generateFileName(int $userId, int $objId) : string
	{
		$certificate = $this->certificateRepository->fetchActiveCertificateForPresentation($userId, $objId);

		$user = ilObjectFactory::getInstanceByObjId($userId);
		if (!$user instanceof ilObjUser) {
			throw new ilException(sprintf('The user_id "%s" does NOT reference a user', $userId));
		}

		$pdfFileName = $this->pdfFilenameFactory->create($certificate);

		return $pdfFileName;
	}

	/**
	 * @param $certificate
	 * @return mixed
	 */
	private function createPDFScalar(ilUserCertificate $certificate) : string
	{
		$certificateContent = $certificate->getCertificateContent();

		$certificateContent = str_replace(
			'[BACKGROUND_IMAGE]',
			'[CLIENT_WEB_DIR]' . $certificate->getBackgroundImagePath(),
			$certificateContent
		);
		$certificateContent = str_replace(
			'[CLIENT_WEB_DIR]',
			CLIENT_WEB_DIR,
			$certificateContent
		);

		$pdf_base64 = $this->rpcHelper->ilFO2PDF('RPCTransformationHandler', $certificateContent);

		return $pdf_base64->scalar;
	}
}
