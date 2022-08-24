<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    public function validate(int $userId, int $objId): bool
    {
        try {
            $this->userCertificateRepository->fetchActiveCertificate($userId, $objId);
        } catch (ilException $exception) {
            return false;
        }

        return true;
    }
}
