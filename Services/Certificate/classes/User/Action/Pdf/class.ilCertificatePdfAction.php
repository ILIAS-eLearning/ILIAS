<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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
