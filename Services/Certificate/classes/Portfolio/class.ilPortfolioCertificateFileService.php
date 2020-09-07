<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPortfolioCertificateFileService
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ilLogger|Logger|null
     */
    private $logger;

    /**
     * @param Filesystem|null $filesystem
     * @param Logger|null $logger
     */
    const PERSISTENT_CERTIFICATES_DIRECTORY = 'PersistentCertificates/';

    const CERTIFICATE_FILENAME = 'certificate.pdf';

    public function __construct(Filesystem $filesystem = null, Logger $logger = null)
    {
        global $DIC;

        if (null === $filesystem) {
            $filesystem = $DIC->filesystem()->storage();
        }
        $this->filesystem = $filesystem;

        if (null === $logger) {
            $logger = $DIC->logger()->root();
        }
        $this->logger = $logger;
    }

    /**
     * @param int $userId
     * @param int $objectId
     * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilException
     */
    public function createCertificateFile(int $userId, int $objectId)
    {
        $userCertificateRepository = new ilUserCertificateRepository();

        $userCertificate = $userCertificateRepository->fetchActiveCertificate($userId, $objectId);

        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId;
        if (false === $this->filesystem->hasDir($dirPath)) {
            $this->filesystem->createDir($dirPath);
        }

        $pdfGenerator = new ilPdfGenerator($userCertificateRepository, $this->logger);

        $pdfScalar = $pdfGenerator->generate($userCertificate->getId());

        $completePath = $dirPath . '/' . $objectId . '_' . self::CERTIFICATE_FILENAME;
        if ($this->filesystem->has($completePath)) {
            $this->filesystem->delete($completePath);
        }

        $this->filesystem->write($completePath, $pdfScalar);
    }

    /**
     * @param $userId
     * @param $objectId
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function deliverCertificate(int $userId, int $objectId)
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId;
        $fileName = $objectId . '_' . self::CERTIFICATE_FILENAME;

        $completePath = $dirPath . '/' . $fileName;
        if ($this->filesystem->has($completePath)) {
            $userCertificateRepository = new ilUserCertificateRepository();

            $userCertificate = $userCertificateRepository->fetchActiveCertificateForPresentation($userId, $objectId);

            $downloadFilePath = CLIENT_DATA_DIR . '/' . $completePath;
            $delivery = new \ilFileDelivery($downloadFilePath);
            $delivery->setMimeType(\ilMimeTypeUtil::APPLICATION__PDF);
            $delivery->setConvertFileNameToAsci(true);
            $delivery->setDownloadFileName(\ilFileUtils::getValidFilename($userCertificate->getObjectTitle() . '.pdf'));

            $delivery->deliver();
        }
    }

    /**
     * @param int $userId
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function deleteUserDirectory(int $userId)
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId;

        if (true === $this->filesystem->hasDir($dirPath)) {
            $this->filesystem->deleteDir($dirPath);
        }
    }

    /**
     * @param int $userId
     * @param int $objectId
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function deleteCertificateFile(int $userId, int $objectId)
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId;

        $completePath = $dirPath . '/' . $objectId . '_' . self::CERTIFICATE_FILENAME;

        if ($this->filesystem->has($completePath)) {
            $this->filesystem->delete($completePath);
        }
    }


    /**
     * @param int $userId
     * @param int $objectId
     * @return string
     * @throws ilException
     */
    public function createCertificateFilePath(int $userId, int $objectId)
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId . '/';
        $fileName = $objectId . '_' . self::CERTIFICATE_FILENAME;
        $completePath = $dirPath . $fileName;
        if ($this->filesystem->has($completePath)) {
            return CLIENT_DATA_DIR . '/' . $completePath;
        }

        throw new ilException(sprintf('Certificate File does not exist in "%"', $completePath));
    }
}
