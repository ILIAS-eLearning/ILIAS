<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateVerificationFileService
{
    /**
     * @var ilLanguage
     */
    private $language;

    /**
     * @var ilDBInterface
     */
    private $database;

    /**
     * @var ilLogger
     */
    private $logger;

    /**
     * @var ilCertificateVerificationClassMap
     */
    private $classMap;

    /**
     * @param ilLanguage $language
     * @param ilDBInterface $database
     * @param ilLogger $logger
     * @param ilCertificateVerificationClassMap $classMap
     */
    public function __construct(
        ilLanguage $language,
        ilDBInterface $database,
        ilLogger $logger,
        ilCertificateVerificationClassMap $classMap
    ) {
        $this->language = $language;
        $this->database = $database;
        $this->logger = $logger;
        $this->classMap = $classMap;
    }

    public function createFile(ilUserCertificatePresentation $userCertificatePresentation)
    {
        $userCertificate = $userCertificatePresentation->getUserCertificate();
        $objectType = $userCertificate->getObjType();

        $this->language->loadLanguageModule('cert');

        $verificationObjectType = $this->classMap->getVerificationTypeByType($objectType);

        $verificationObject = new ilCertificateVerificationObject($verificationObjectType);
        $verificationObject->setTitle($userCertificatePresentation->getObjectTitle());
        $verificationObject->setDescription($userCertificatePresentation->getObjectDescription());

        $objectId = $userCertificate->getObjId();
        $userId = $userCertificate->getUserId();

        $issueDate = new ilDate($userCertificate->getAcquiredTimestamp(), IL_CAL_UNIX);

        $verificationObject->setProperty('issued_on', $issueDate);

        $ilUserCertificateRepository = new ilUserCertificateRepository($this->database, $this->logger);
        $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository, $this->logger);

        $pdfAction = new ilCertificatePdfAction(
            $this->logger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->language->txt('error_creating_certificate_pdf')
        );

        $certificateScalar = $pdfAction->createPDF($userId, $objectId);

        if ($certificateScalar) {
            // we need the object id for storing the certificate file
            $verificationObject->create();

            $path = $this->initStorage($verificationObject->getId(), 'certificate');

            $fileName = $objectType . '_' . $objectId . '_' . $userId . '.pdf';

            if (file_put_contents($path . $fileName, $certificateScalar)) {
                $verificationObject->setProperty('file', $fileName);
                $verificationObject->update();

                return $verificationObject;
            }

            $this->logger->info('File could not be created');
            $verificationObject->delete();
        }
    }

    /**
     * @param int $objectId
     * @param string $subDirectory
     * @return string
     */
    public function initStorage(int $objectId, string $subDirectory = '')
    {
        $storage = new ilVerificationStorageFile($objectId);
        $storage->create();

        $path = $storage->getAbsolutePath() . "/";

        if ($subDirectory !== '') {
            $path .= $subDirectory . "/";

            if (!is_dir($path)) {
                mkdir($path);
            }
        }

        return $path;
    }
}
