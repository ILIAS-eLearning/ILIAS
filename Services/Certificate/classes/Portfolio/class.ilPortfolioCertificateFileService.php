<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileNotFoundException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPortfolioCertificateFileService
{
    private Filesystem $filesystem;
    private const PERSISTENT_CERTIFICATES_DIRECTORY = 'PersistentCertificates/';
    private const CERTIFICATE_FILENAME = 'certificate.pdf';

    public function __construct(?Filesystem $filesystem = null)
    {
        global $DIC;

        if (null === $filesystem) {
            $filesystem = $DIC->filesystem()->storage();
        }
        $this->filesystem = $filesystem;
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws IOException
     * @throws ilException
     */
    public function createCertificateFile(int $userId, int $objectId): void
    {
        $userCertificateRepository = new ilUserCertificateRepository();

        $userCertificate = $userCertificateRepository->fetchActiveCertificate($userId, $objectId);

        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId;
        if (!$this->filesystem->hasDir($dirPath)) {
            $this->filesystem->createDir($dirPath);
        }

        $pdfGenerator = new ilPdfGenerator($userCertificateRepository);

        $pdfScalar = $pdfGenerator->generate($userCertificate->getId());

        $completePath = $dirPath . '/' . $objectId . '_' . self::CERTIFICATE_FILENAME;
        if ($this->filesystem->has($completePath)) {
            $this->filesystem->delete($completePath);
        }

        $this->filesystem->write($completePath, $pdfScalar);
    }

    /**
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function deliverCertificate(int $userId, int $objectId): void
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId;
        $fileName = $objectId . '_' . self::CERTIFICATE_FILENAME;

        $completePath = $dirPath . '/' . $fileName;
        if ($this->filesystem->has($completePath)) {
            $userCertificateRepository = new ilUserCertificateRepository();

            $userCertificate = $userCertificateRepository->fetchActiveCertificateForPresentation($userId, $objectId);

            $downloadFilePath = CLIENT_DATA_DIR . '/' . $completePath;
            ilFileDelivery::deliverFileAttached(
                $downloadFilePath,
                ilFileUtils::getValidFilename($userCertificate->getObjectTitle() . '.pdf')
            );
        }
    }

    /**
     * @throws IOException
     */
    public function deleteUserDirectory(int $userId): void
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId;

        if ($this->filesystem->hasDir($dirPath)) {
            $this->filesystem->deleteDir($dirPath);
        }
    }

    /**
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function deleteCertificateFile(int $userId, int $objectId): void
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId;

        $completePath = $dirPath . '/' . $objectId . '_' . self::CERTIFICATE_FILENAME;

        if ($this->filesystem->has($completePath)) {
            $this->filesystem->delete($completePath);
        }
    }

    /**
     * @throws ilException
     */
    public function createCertificateFilePath(int $userId, int $objectId): string
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId . '/';
        $fileName = $objectId . '_' . self::CERTIFICATE_FILENAME;
        $completePath = $dirPath . $fileName;
        if ($this->filesystem->has($completePath)) {
            return CLIENT_DATA_DIR . '/' . $completePath;
        }

        throw new ilException(sprintf('Certificate File does not exist in "%s"', $completePath));
    }
}
