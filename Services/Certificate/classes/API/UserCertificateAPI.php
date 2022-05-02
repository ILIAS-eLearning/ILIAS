<?php declare(strict_types=1);

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

namespace Certificate\API;

use Certificate\API\Data\UserCertificateDto;
use Certificate\API\Filter\UserDataFilter;
use Certificate\API\Repository\UserDataRepository;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateAPI
{
    private UserDataRepository $userCertificateRepository;

    public function __construct(?UserDataRepository $userCertificateRepository = null)
    {
        if (null === $userCertificateRepository) {
            global $DIC;

            $userCertificateRepository = new UserDataRepository(
                $DIC->database(),
                $DIC->logger()->cert(),
                $DIC->ctrl()
            );
        }
        $this->userCertificateRepository = $userCertificateRepository;
    }

    /**
     * @param UserDataFilter $filter
     * @param string[] $ilCtrlStack An array of ilCtrl-enabled GUI class names that are used to create the link,
     *                              if this is an empty array (default) no link
     *                              will be generated
     * @return array<int, UserCertificateDto>
     */
    public function getUserCertificateData(UserDataFilter $filter, array $ilCtrlStack = []) : array
    {
        return $this->userCertificateRepository->getUserData($filter, $ilCtrlStack);
    }

    public function getUserCertificateDataMaxCount(UserDataFilter $filter) : int
    {
        return $this->userCertificateRepository->getUserCertificateDataMaxCount($filter);
    }
}
