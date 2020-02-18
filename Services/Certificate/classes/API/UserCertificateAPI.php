<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace Certificate\API;

use Certificate\API\Data\UserCertificateDto;
use Certificate\API\Filter\UserDataFilter;
use Certificate\API\Repository\UserDataRepository;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateAPI
{
    /** @var UserDataRepository */
    private $userCertificateRepository;

    /**
     * UserCertificateAPI constructor.
     * @param UserDataRepository|null $userCertificateRepository
     */
    public function __construct(UserDataRepository $userCertificateRepository = null)
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
     * @param array $ilCtrlStack - array of ilCtrl-enabled GUI class
     *                             names that are used to create the link,
     *                             if this is an empty array (default) no link
     *                             will be generated
     * @return array<int, UserCertificateDto>
     */
    public function getUserCertificateData(UserDataFilter $filter, array $ilCtrlStack = []) : array
    {
        return $this->userCertificateRepository->getUserData($filter, $ilCtrlStack);
    }


    /**
     * @param UserDataFilter $filter
     *
     * @return int
     */
    public function getUserCertificateDataMaxCount(UserDataFilter $filter) : int
    {
        return $this->userCertificateRepository->getUserCertificateDataMaxCount($filter);
    }
}
