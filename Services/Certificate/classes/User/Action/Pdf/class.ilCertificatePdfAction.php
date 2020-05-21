<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfAction
{
    /**
     * @var ilLogger
     */
    private $logger;

    /**
     * @var ilPdfGenerator
     */
    private $pdfGenerator;

    /**
     * @var ilCertificateUtilHelper
     */
    private $ilUtilHelper;

    /**
     * @var ilErrorHandling
     */
    private $errorHandler;

    /**
     * @var string
     */
    private $translatedErrorText;

    /**
     * @param ilLogger $logger
     * @param ilPdfGenerator $pdfGenerator
     * @param ilCertificateUtilHelper $ilUtilHelper
     * @param ilErrorHandling|null $errorHandler
     * @param string $translatedErrorText
     */
    public function __construct(
        ilLogger $logger,
        ilPdfGenerator $pdfGenerator,
        ilCertificateUtilHelper $ilUtilHelper = null,
        string $translatedErrorText = '',
        ilErrorHandling $errorHandler = null
    ) {
        $this->logger = $logger;
        $this->pdfGenerator = $pdfGenerator;
        if (null == $ilUtilHelper) {
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

    /**
     * @param integer $objectId
     * @param integer $userId
     * @return string
     * @throws ilException
     */
    public function createPDF(int $userId, int $objectId) : string
    {
        $pdfScalar = $this->pdfGenerator->generateCurrentActiveCertificate($userId, $objectId);

        return $pdfScalar;
    }

    /**
     * @param int $userId
     * @param int $objectId
     * @param string $pdfDownloadName
     * @return string
     */
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
