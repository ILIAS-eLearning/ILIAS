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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfAction
{
    private ilLogger $logger;
    private ilPdfGenerator $pdfGenerator;
    private ilCertificateUtilHelper $ilUtilHelper;
    private ilErrorHandling $errorHandler;
    private string $translatedErrorText;

    public function __construct(
        ilLogger $logger,
        ilPdfGenerator $pdfGenerator,
        ?ilCertificateUtilHelper $ilUtilHelper = null,
        string $translatedErrorText = '',
        ?ilErrorHandling $errorHandler = null
    ) {
        $this->logger = $logger;
        $this->pdfGenerator = $pdfGenerator;
        if (null === $ilUtilHelper) {
            $ilUtilHelper = new ilCertificateUtilHelper();
        }
        $this->ilUtilHelper = $ilUtilHelper;

        if (null === $errorHandler) {
            global $DIC;
            $errorHandler = $DIC['ilErr'];
        }
        $this->errorHandler = $errorHandler;

        $this->translatedErrorText = $translatedErrorText;
    }

    public function createPDF(int $userId, int $objectId) : string
    {
        return $this->pdfGenerator->generateCurrentActiveCertificate($userId, $objectId);
    }

    public function downloadPdf(int $userId, int $objectId) : string
    {
        try {
            $pdfScalar = $this->createPDF($userId, $objectId);

            $fileName = $this->pdfGenerator->generateFileName($userId, $objectId);

            $this->ilUtilHelper->deliverData(
                $pdfScalar,
                $fileName,
                'application/pdf'
            );
        } catch (ilException $clientException) {
            $this->errorHandler->raiseError($this->translatedErrorText, $this->errorHandler->MESSAGE);
            return '';
        }

        return $pdfScalar;
    }
}
