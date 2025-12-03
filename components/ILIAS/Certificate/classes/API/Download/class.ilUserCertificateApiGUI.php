<?php

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

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;

class ilUserCertificateApiGUI
{
    final public const string CMD_DOWNLOAD = 'download';

    private readonly ilLogger $logger;
    private readonly ServerRequestInterface $request;
    private readonly ilLanguage $language;
    private readonly ilCtrlInterface $ctrl;

    public function __construct(
        ?ilLanguage $language = null,
        ?ServerRequestInterface $request = null,
        ?ilLogger $logger = null,
        ?ilCtrlInterface $ctrl = null
    ) {
        global $DIC;

        $this->language = $language ?? $DIC->language();
        $this->request = $request ?? $DIC->http()->request();
        $this->logger = $logger ?? $DIC->logger()->cert();
        $this->ctrl = $ctrl ?? $DIC->ctrl();

        $this->language->loadLanguageModule('cert');
    }

    public function executeCommand(): void
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

    public function download(): void
    {
        $repo = new ilUserCertificateRepository(null, $this->logger);

        $certificate_id = (int) $this->request->getQueryParams()['certificate_id'];

        $certificate = $repo->fetchCertificate($certificate_id);

        $action = (new ilCertificatePdfAction(
            (new ilPdfGenerator($repo))->withLogger($this->logger),
            new ilCertificateUtilHelper(),
            $this->language->txt('error_creating_certificate_pdf')
        ))->withLogger($this->logger);
        $action->downloadPdf(
            $certificate->getUserId(),
            $certificate->getObjId()
        );
    }
}
