<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateUserCertificateAccessValidator
{
    private ilUserCertificateRepository $userCertificateRepository;

    public function __construct(?ilUserCertificateRepository $userCertificateRepository = null)
    {
        if (null === $userCertificateRepository) {
            global $DIC;
            $database = $DIC->database();
            $logger = $DIC->logger()->cert();

            $userCertificateRepository = new ilUserCertificateRepository($database, $logger);
        }
        $this->userCertificateRepository = $userCertificateRepository;
    }

    public function validate(int $userId, int $objId) : bool
    {
        try {
            $this->userCertificateRepository->fetchActiveCertificate($userId, $objId);
        } catch (ilException $exception) {
            return false;
        }

        return true;
    }
}
