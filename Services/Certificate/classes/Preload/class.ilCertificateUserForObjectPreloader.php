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
     * @var ilCertificateActiveValidator
     */
    private $activeValidator;

    /**
     * @param ilUserCertificateRepository $userCertificateRepository
     * @param ilCertificateActiveValidator $activeValidator
     */
    public function __construct(ilUserCertificateRepository $userCertificateRepository, ilCertificateActiveValidator $activeValidator)
    {
        $this->userCertificateRepository = $userCertificateRepository;
        $this->activeValidator = $activeValidator;
    }

    /**
     * @param int $objectId
     * @param array $userIds
     */
    public function preLoadDownloadableCertificates(int $objectId)
    {
        if (true === $this->activeValidator->validate()) {
            $objectIdsWithUserCertificate = $this->userCertificateRepository->fetchUserIdsWithCertificateForObject($objectId);
            self::$certificates[$objectId] = $objectIdsWithUserCertificate;
        }
    }

    /**
     * @param int $objId
     * @param int $userId
     * @return bool
     */
    public function isPreloaded(int $objId, int $userId)
    {
        if (false === array_key_exists($objId, self::$certificates)) {
            return false;
        }

        if (true === in_array($userId, self::$certificates[$objId])) {
            return true;
        }

        return false;
    }
}
