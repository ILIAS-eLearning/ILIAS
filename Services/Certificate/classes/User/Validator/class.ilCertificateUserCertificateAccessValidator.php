<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUserCertificateAccessValidator
{
    /**
     * @var ilUserCertificateRepository
     */
    private $userCertificateRepository;

    /**
     * ilCertificateHasUserCertificateValidator constructor.
     * @param ilUserCertificateRepository|null $userCertificateRepository
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

    /**
     * @param int $userId
     * @param int $objId
     * @return bool
     */
    public function validate(int $userId, int $objId)
    {
        try {
            $this->userCertificateRepository->fetchActiveCertificate($userId, $objId);
        } catch (ilException $exception) {
            return false;
        }

        return true;
    }
}
