<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUserForObjectPreloader
{
    /** @var array<int, int[]> */
    private static array $certificates = [];
    private ilUserCertificateRepository $userCertificateRepository;
    private ilCertificateActiveValidator $activeValidator;

    public function __construct(
        ilUserCertificateRepository $userCertificateRepository,
        ilCertificateActiveValidator $activeValidator
    ) {
        $this->userCertificateRepository = $userCertificateRepository;
        $this->activeValidator = $activeValidator;
    }

    public function preLoadDownloadableCertificates(int $objectId) : void
    {
        if (true === $this->activeValidator->validate()) {
            $objectIdsWithUserCertificate = $this->userCertificateRepository->fetchUserIdsWithCertificateForObject($objectId);
            self::$certificates[$objectId] = $objectIdsWithUserCertificate;
        }
    }

    public function isPreloaded(int $objId, int $userId) : bool
    {
        if (false === array_key_exists($objId, self::$certificates)) {
            return false;
        }

        if (true === in_array($userId, self::$certificates[$objId], true)) {
            return true;
        }

        return false;
    }
}
