<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateObjectsForUserPreloader
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

	/**
	 * @param int $userId
	 * @param array $objIds
	 */
	public function preLoad(int $userId, array $objIds)
	{
		if (!array_key_exists($userId, self::$certificates)) {
			self::$certificates[$userId] = [];
		} 

		$objectIdsWithUserCertificate = $this->userCertificateRepository->fetchObjectWithCertificateForUser($userId, $objIds);
		self::$certificates[$userId] = array_unique(array_merge(
			$objectIdsWithUserCertificate,
			self::$certificates[$userId]
		));
	}

	/**
	 * @param int $userId
	 * @param int $objId
	 * @return bool
	 */
	public function isPreloaded(int $userId, int $objId)
	{
		if (false === array_key_exists($userId, self::$certificates)) {
			return false;
		}

		if (true === in_array($objId, self::$certificates[$userId])) {
			return true;
		}
		return false;
	}
}
