<?php declare(strict_types=1);
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
     * @param UserDataFilter $filter
     * @param array $ilCtrlStack - array of ilCtrl-enabled GUI class
     *                             names that are used to create the link,
     *                             if this is an empty array (default) no link
     *                             will be generated
     * @return array<int, ilUserCertificateData>
     */
    public function getUserCertificateData(UserDataFilter $filter, array $ilCtrlStack = array()) : array
    {
        return $this->userCertificateRepository->getUserData($filter, $ilCtrlStack);
    }
}
