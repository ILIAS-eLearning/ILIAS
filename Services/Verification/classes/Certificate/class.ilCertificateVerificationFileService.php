<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateVerificationFileService
{
    private ilLanguage $language;
    private ilDBInterface $database;
    private ilLogger $logger;
    private ilCertificateVerificationClassMap $classMap;

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

    /**
     * @throws ilException
     */
    public function createFile(ilUserCertificatePresentation $userCertificatePresentation) : ?ilCertificateVerificationObject
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
        return null;
    }

    public function initStorage(int $objectId, string $subDirectory = '') : string
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
