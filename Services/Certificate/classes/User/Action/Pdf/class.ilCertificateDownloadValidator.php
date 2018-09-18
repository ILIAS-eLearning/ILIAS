<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Validates if an active certificate is stored
 * in the database and can be downloaded by the
 * user
 *
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDownloadValidator
{
	/**
	 * @var ilUserCertificateRepository
	 */
	private $userCertificateRepository;

	/**
	 * @param ilUserCertificateRepository $userCertificateRepository
	 */
	public function __construct(ilUserCertificateRepository $userCertificateRepository = null)
	{
		if (null === $userCertificateRepository) {
			global $DIC;
			$database = $DIC->database();
			$logger = $DIC->logger()->cert();

			$userCertificateRepository = new ilUserCertificateRepository($database, $logger);
		}
		$this->userCertificateRepository = $userCertificateRepository;
	}

	public function isCertificateDownloadable(int $userId, int $objId)
	{
		try {
			$this->userCertificateRepository->fetchActiveCertificate($userId, $objId);
		} catch (ilException $exception) {
			return false;
		}

		return true;
	}
}
