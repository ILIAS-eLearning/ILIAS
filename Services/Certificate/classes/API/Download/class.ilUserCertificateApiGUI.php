<?php declare(strict_types=1);

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

use Psr\Http\Message\ServerRequestInterface;

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
    private ilCtrlInterface $ctrl;

    public function __construct(
        ?ilLanguage $language = null,
        ?ServerRequestInterface $request = null,
        ?ilLogger $certificateLogger = null,
        ?ilCtrlInterface $ctrl = null
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
