<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilObjPersistentCertificateVerificationGUI
{
    /**
     * @var
     */
    private $dic;

    /**
     * @var ilPortfolioCertificateFileService
     */
    private $fileService;

    /**
     * @var ilLanguage
     */
    private $language;

    public function __construct(
        \ILIAS\DI\Container $dic = null,
        ilPortfolioCertificateFileService $fileService = null,
        ilLanguage $language = null
    ) {
        if (null === $dic) {
            global $DIC;
            $dic = $DIC;
        }
        $this->dic = $dic;

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
     * @param int $objectId
     * @param int $userId
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function downloadFromPortfolioPage(ilPortfolioPage $a_page, int $objectId, int $userId)
    {
        if (ilPCVerification::isInPortfolioPage($a_page, 'crta', (int) $objectId)) {
            $this->fileService->deliverCertificate((int) $userId, (int) $objectId);
        }

        throw new ilException($this->language->txt('permission_denied'));
    }
}
