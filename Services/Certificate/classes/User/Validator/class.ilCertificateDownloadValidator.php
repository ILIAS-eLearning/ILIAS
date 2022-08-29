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

    public function isCertificateDownloadable(int $userId, int $objId): bool
    {
        if (!$this->activeValidator->validate()) {
            return false;
        }

        return $this->userCertificateAccessValidator->validate($userId, $objId);
    }
}
