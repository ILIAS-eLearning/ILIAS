<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUserForObjectPreloader
{
	/**
	 * @var array
	 */
	private static $certificates = array();

	/**
	 * @var ilUserCertificateRepository
	 */
	private $userCertificateRepository;

	/**
	 * @param ilUserCertificateRepository $userCertificateRepository
	 */
	public function __construct(ilUserCertificateRepository $userCertificateRepository)
	{
		$this->userCertificateRepository = $userCertificateRepository;
	}

	/**
	 * @param int $objectId
	 * @param array $userIds
	 */
	public function preLoad(int $objectId)
	{
		$objectIdsWithUserCertificate = $this->userCertificateRepository->fetchUserIdsWithCertificateForObject($objectId);
		ilCertificateUserForObjectPreloader::$certificates[$objectId] = $objectIdsWithUserCertificate;
	}

	/**
	 * @param int $objId
	 * @param int $userId
	 * @return bool
	 */
	public function isPreloaded(int $objId, int $userId)
	{
		if (false === array_key_exists($objId, ilCertificateUserForObjectPreloader::$certificates)) {
			return false;
		}

		if (true === in_array($userId, ilCertificateUserForObjectPreloader::$certificates[$objId])) {
			return true;
		}

		return false;
	}
}
