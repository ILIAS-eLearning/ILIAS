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

class ilCertificatePdfAction
{
    private readonly ilCertificateUtilHelper $helper;
    private readonly ilErrorHandling $error_handler;
    private ?ilLogger $logger = null;

    public function __construct(
        private readonly ilPdfGenerator $pdf_generator,
        ?ilCertificateUtilHelper $helper = null,
        private readonly string $error_txt = '',
        ?ilErrorHandling $error_handler = null
    ) {
        global $DIC;

        $this->helper = $helper ?? new ilCertificateUtilHelper();
        $this->error_handler = $error_handler ?? $DIC['ilErr'];
    }

    public function withLogger(ilLogger $logger): self
    {
        $clone = clone $this;
        $clone->logger = $logger;

        return $clone;
    }

    public function createPDF(int $userId, int $objectId): string
    {
        return $this->pdf_generator->generateCurrentActiveCertificate($userId, $objectId);
    }

    public function downloadPdf(int $userId, int $objectId): string
    {
        try {
            $pdf_scalar = $this->createPDF($userId, $objectId);

            $filename = $this->pdf_generator->generateFileName($userId, $objectId);

            $this->helper->deliverData(
                $pdf_scalar,
                $filename,
                'application/pdf'
            );
        } catch (Throwable $e) {
            $this->logger?->error(
                'Error while generating or downloading certificate PDF for user {user_id} and object {object_id}: {error}',
                [
                    'user_id' => $userId,
                    'object_id' => $objectId,
                    'error' => $e->getMessage(),
                    'exception' => $e
                ]
            );

            $this->error_handler->raiseError($this->error_txt, $this->error_handler->MESSAGE);
            return '';
        }

        return $pdf_scalar;
    }
}
