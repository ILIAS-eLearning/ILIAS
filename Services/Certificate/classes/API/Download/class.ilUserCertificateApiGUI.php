<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * @ingroup ServicesCertificate
 * @author  Niels Theen <ntheen@databay.de>
 * @ilCtrl_Calls: ilUserCertificateApiGUI:
 */
class ilUserCertificateApiGUI
{
    /** @var ilLogger */
    private $certificateLogger;

    /** @var ServerRequestInterface */
    private $request;

    /** @var ilLanguage */
    private $language;

    /**
     * @param ilLanguage|null $language
     * @param ServerRequestInterface $request
     * @param ilLogger $certificateLogger
     */
    public function __construct(
        ilLanguage $language = null,
        ServerRequestInterface $request = null,
        ilLogger $certificateLogger = null
    ) {
        global $DIC;

        if ($language === null) {
            $language = $DIC->language();
        }
        $this->language = $language;

        if ($request === null) {
            $request = $DIC->http()->request();
        }
        $this->request = $request;

        if ($certificateLogger === null) {
            $certificateLogger = $DIC->logger()->cert();
        }
        $this->certificateLogger = $certificateLogger;


        $this->language->loadLanguageModule('cert');
    }

    /**
     * @throws \ilException
     */
    public function download() : void
    {
        $userCertificateRepository = new ilUserCertificateRepository(null, $this->certificateLogger);
        $pdfGenerator = new ilPdfGenerator($userCertificateRepository, $this->certificateLogger);

        $userCertificateId = (int) $this->request->getQueryParams()['certificate_id'];

        $userCertificate = $userCertificateRepository->fetchCertificate($userCertificateId);

        $pdfAction = new ilCertificatePdfAction(
            $this->certificateLogger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->language->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf($userCertificate->getUserId(), $userCertificate->getObjId());
    }
}
