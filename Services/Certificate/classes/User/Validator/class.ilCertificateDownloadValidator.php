<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Validates if an active certificate is stored
 * in the database and can be downloaded by the
 * user
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateDownloadValidator
{
    private ilCertificateUserCertificateAccessValidator $userCertificateAccessValidator;
    private ilCertificateActiveValidator $activeValidator;

    public function __construct(
        ?ilCertificateUserCertificateAccessValidator $userCertificateAccessValidator = null,
        ?ilCertificateActiveValidator $activeValidator = null
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

    public function isCertificateDownloadable(int $userId, int $objId) : bool
    {
        if (false === $this->activeValidator->validate()) {
            return false;
        }

        return $this->userCertificateAccessValidator->validate($userId, $objId);
    }
}
