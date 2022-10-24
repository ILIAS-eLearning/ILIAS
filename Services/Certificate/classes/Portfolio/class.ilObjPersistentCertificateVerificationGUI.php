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

use ILIAS\DI\Container;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilObjPersistentCertificateVerificationGUI
{
    private ilPortfolioCertificateFileService $fileService;
    private ilLanguage $language;

    public function __construct(
        ?Container $dic = null,
        ?ilPortfolioCertificateFileService $fileService = null,
        ?ilLanguage $language = null
    ) {
        if (null === $dic) {
            global $DIC;
            $dic = $DIC;
        }

        if (null === $fileService) {
            $fileService = new ilPortfolioCertificateFileService();
        }
        $this->fileService = $fileService;

        if (null === $language) {
            $language = $dic->language();
        }
        $this->language = $language;
    }

    /**
     * @param ilPortfolioPage $a_page
     * @param int             $objectId
     * @param int             $userId
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function downloadFromPortfolioPage(ilPortfolioPage $a_page, int $objectId, int $userId): void
    {
        if (ilPCVerification::isInPortfolioPage($a_page, 'crta', $objectId)) {
            $this->fileService->deliverCertificate($userId, $objectId);
        }

        throw new ilException($this->language->txt('permission_denied'));
    }
}
