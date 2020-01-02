<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Validates if an active certificate is stored
 * in the database and can be downloaded by the
 * user
 *
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDownloadValidator
{
    /**
     * @var ilUserCertificateRepository
     */
    private $userCertificateAccessValidator;

    /**
     * @var ilCertificateActiveValidator|null
     */
    private $activeValidator;

    /**
     * @param ilCertificateUserCertificateAccessValidator|null $userCertificateAccessValidator
     * @param ilCertificateActiveValidator|null $activeValidator
     */
    public function __construct(
        ilCertificateUserCertificateAccessValidator $userCertificateAccessValidator = null,
        ilCertificateActiveValidator $activeValidator = null
    ) {
        if (null === $userCertificateAccessValidator) {
            $userCertificateAccessValidator = new ilCertificateUserCertificateAccessValidator();
        }
        $this->userCertificateAccessValidator = $userCertificateAccessValidator;

        if (null === $activeValidator) {
            $activeValidator = new ilCertificateActiveValidator();
        }
        $this->activeValidator = $activeValidator;
    }

    /**
     * @param int $userId
     * @param int $objId
     * @return bool
     */
    public function isCertificateDownloadable(int $userId, int $objId)
    {
        if (false === $this->activeValidator->validate()) {
            return false;
        }

        return $this->userCertificateAccessValidator->validate($userId, $objId);
    }
}
