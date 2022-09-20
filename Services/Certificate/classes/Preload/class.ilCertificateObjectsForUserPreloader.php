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
class ilCertificateObjectsForUserPreloader
{
    /** @var array<int, int[]> */
    private static array $certificates = [];

    public function __construct(private ilUserCertificateRepository $userCertificateRepository)
    {
    }

    /**
     * @param int[] $objIds
     */
    public function preLoad(int $userId, array $objIds): void
    {
        if (!array_key_exists($userId, self::$certificates)) {
            self::$certificates[$userId] = [];
        }

        $objectIdsWithUserCertificate = $this->userCertificateRepository->fetchObjectIdsWithCertificateForUser(
            $userId,
            array_diff($objIds, self::$certificates[$userId])
        );

        self::$certificates[$userId] = array_unique(array_merge(
            $objectIdsWithUserCertificate,
            self::$certificates[$userId]
        ));
    }

    public function isPreloaded(int $userId, int $objId): bool
    {
        if (!array_key_exists($userId, self::$certificates)) {
            return false;
        }

        return in_array($objId, self::$certificates[$userId], true);
    }
}
