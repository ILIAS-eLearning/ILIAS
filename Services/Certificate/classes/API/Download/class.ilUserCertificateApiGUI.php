<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @ingroup ServicesCertificate
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateApiGUI
{
    public const CMD_DOWNLOAD = 'download';
    private ilLogger $certificateLogger;
    private ServerRequestInterface $request;
    private ilLanguage $language;
    private ilCtrl $ctrl;

    public function __construct(
        ?ilLanguage $language = null,
        ?ServerRequestInterface $request = null,
        ?ilLogger $certificateLogger = null,
        ?ilCtrl $ctrl = null
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

        if ($ctrl === null) {
            $ctrl = $DIC->ctrl();
        }
        $this->ctrl = $ctrl;

        $this->language->loadLanguageModule('cert');
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_DOWNLOAD:
                $this->{$cmd}();
                break;

            default:
                break;
        }
    }

    /**
     * @throws ilException
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
