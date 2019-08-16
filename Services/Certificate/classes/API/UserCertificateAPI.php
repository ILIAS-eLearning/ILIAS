<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace Certificate\API;

use Certificate\API\Data\ilUserCertificateData;
use Certificate\API\Filter\UserDataFilter;
use Certificate\API\Repository\ilUserDataRepository;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class UserCertificateAPI
{
    /**
     * @var ilUserDataRepository
     */
    private $userCertificateRepository;

    public function __construct(ilUserDataRepository $userCertificateRepository)
    {
        $this->userCertificateRepository = $userCertificateRepository;
    }

    /**
     * @param array          $userIds
     * @param UserDataFilter $filter
     * @return array<int, ilUserCertificateData>
     */
    public function getUserCertificateData(array $userIds, UserDataFilter $filter) : array
    {
        return $this->userCertificateRepository->getUserData($userIds, $filter);
    }
}
