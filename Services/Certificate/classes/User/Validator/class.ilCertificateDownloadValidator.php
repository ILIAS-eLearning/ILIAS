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
	 * @var ilCertificateActiveValidator|null
	 */
	private $activeValidator;

	/**
	 * @param ilUserCertificateRepository $userCertificateRepository
	 * @param ilCertificateActiveValidator|null $activeValidator
	 */
	public function __construct(ilUserCertificateRepository $userCertificateRepository = null, ilCertificateActiveValidator $activeValidator = null)
	{
		if (null === $userCertificateRepository) {
			global $DIC;
			$database = $DIC->database();
			$logger = $DIC->logger()->cert();

			$userCertificateRepository = new ilUserCertificateRepository($database, $logger);
		}
		$this->userCertificateRepository = $userCertificateRepository;

		if (null === $activeValidator) {
			$activeValidator = new ilCertificateActiveValidator();
		}
		$this->activeValidator = $activeValidator;
	}

	/**
	 * @param int $userId
	 * @param int $objId
	 * @return bool
	 */
	public function isCertificateDownloadable(int $userId, int $objId)
	{
		if (false === $this->activeValidator->validate()) {
			return false;
		}

		try {
			$this->userCertificateRepository->fetchActiveCertificate($userId, $objId);
		} catch (ilException $exception) {
			return false;
		}

		return true;
	}
}
