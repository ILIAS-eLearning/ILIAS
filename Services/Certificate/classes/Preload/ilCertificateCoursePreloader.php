<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCoursePreloader
{
	/**
	 * @var array
	 */
	private static $certificates = array();

	/**
	 * @var ilUserCertificateRepository
	 */
	private $userCertificateRepository;

	public function __construct(ilUserCertificateRepository $userCertificateRepository)
	{
		$this->userCertificateRepository = $userCertificateRepository;
	}

	public function preLoad(int $userId, array $objIds)
	{
		$objectIdsWithUserCertificate = $this->userCertificateRepository->fetchObjectWithCertificateForUser($userId, $objIds);
		ilCertificateCoursePreloader::$certificates[$userId] = $objectIdsWithUserCertificate;
	}

	public function isPreloaded(int $userId, int $objId)
	{
		if (false === array_key_exists($userId, ilCertificateCoursePreloader::$certificates)) {
			return false;
		}

		if (true === in_array($objId, ilCertificateCoursePreloader::$certificates[$userId])) {
			return true;
		}
		return false;
	}
}
