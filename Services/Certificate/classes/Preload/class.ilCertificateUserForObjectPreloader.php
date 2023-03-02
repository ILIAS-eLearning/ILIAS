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
class ilCertificateUserForObjectPreloader
{
    /** @var array<int, int[]> */
    private static array $certificates = [];

    public function __construct(private readonly ilUserCertificateRepository $userCertificateRepository, private readonly ilCertificateActiveValidator $activeValidator)
    {
    }

    public function preLoadDownloadableCertificates(int $objectId): void
    {
        if ($this->activeValidator->validate()) {
            $objectIdsWithUserCertificate = $this->userCertificateRepository->fetchUserIdsWithCertificateForObject($objectId);
            self::$certificates[$objectId] = $objectIdsWithUserCertificate;
        }
    }

    public function isPreloaded(int $objId, int $userId): bool
    {
        if (!array_key_exists($objId, self::$certificates)) {
            return false;
        }

        return in_array($userId, self::$certificates[$objId], true);
    }
}
