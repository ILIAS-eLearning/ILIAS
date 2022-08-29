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

use ILIAS\FileUpload\FileUpload;
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\FileUpload\Location;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageUpload
{
    private const BACKGROUND_IMAGE_NAME = 'background.jpg';
    private const BACKGROUND_THUMBNAIL_IMAGE_NAME = 'background.jpg.thumb.jpg';
    private const BACKGROUND_TEMPORARY_FILENAME = 'background_upload.tmp';
    private Filesystem $fileSystem;
    private ilCertificateUtilHelper $utilHelper;
    private ilCertificateFileUtilsHelper $fileUtilsHelper;
    private LegacyPathHelperHelper $legacyPathHelper;
    private Filesystem $tmp_file_system;

    public function __construct(
        private FileUpload $fileUpload,
        private string $certificatePath,
        private ilLanguage $language,
        private string $rootDirectory = CLIENT_WEB_DIR,
        private string $clientId = CLIENT_ID,
        ?Filesystem $fileSystem = null,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilCertificateFileUtilsHelper $certificateFileUtilsHelper = null,
        ?LegacyPathHelperHelper $legacyPathHelper = null,
        ?Filesystem $tmp_file_system = null
    ) {
        if (null === $fileSystem) {
            global $DIC;
            $fileSystem = $DIC->filesystem()->web();
        }
        $this->fileSystem = $fileSystem;

        if (null === $utilHelper) {
            $utilHelper = new ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;

        if (null === $certificateFileUtilsHelper) {
            $certificateFileUtilsHelper = new ilCertificateFileUtilsHelper();
        }
        $this->fileUtilsHelper = $certificateFileUtilsHelper;

        if (null === $legacyPathHelper) {
            $legacyPathHelper = new LegacyPathHelperHelper();
        }
        $this->legacyPathHelper = $legacyPathHelper;

        if (null === $tmp_file_system) {
            global $DIC;
            $tmp_file_system = $DIC->filesystem()->temp();
        }
        $this->tmp_file_system = $tmp_file_system;
    }

    /**
     * Uploads a background image for the certificate. Creates a new directory for the
     * certificate if needed. Removes an existing certificate image if necessary
     * @param string     $imageTempFilename Name of the temporary uploaded image file
     * @param int        $version           - Version of the current certifcate template
     * @return string An errorcode if the image upload fails, 0 otherwise
     * @throws IllegalStateException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function uploadBackgroundImage(string $imageTempFilename, int $version, ?array $pending_file = null): string
    {
        $imagepath = $this->rootDirectory . $this->certificatePath;

        if (!$this->fileSystem->hasDir($imagepath)) {
            ilFileUtils::makeDirParents($imagepath);
        }

        $backgroundImageTempFilePath = $this->createBackgroundImageTempfilePath();

        $this->uploadFile($imageTempFilename, $backgroundImageTempFilePath, $pending_file);

        $backgroundImagePath = $this->certificatePath . 'background_' . $version . '.jpg';

        $this->utilHelper->convertImage(
            $backgroundImageTempFilePath,
            $this->rootDirectory . $backgroundImagePath,
            'JPEG'
        );

        $backgroundImageThumbnailPath = $this->createBackgroundImageThumbPath();

        $this->utilHelper->convertImage(
            $backgroundImageTempFilePath,
            $backgroundImageThumbnailPath,
            'JPEG',
            "100"
        );

        $convert_filename = self::BACKGROUND_IMAGE_NAME;

        // something went wrong converting the file. use the original file and hope, that PDF can work with it
        if (!$this->fileSystem->has($backgroundImagePath) && !ilFileUtils::moveUploadedFile(
            $backgroundImageTempFilePath,
            $convert_filename,
            $this->rootDirectory . $backgroundImagePath
        )) {
            throw new ilException('Unable to convert the file and the original file');
        }

        $this->fileSystem->delete($this->certificatePath . self::BACKGROUND_TEMPORARY_FILENAME);

        if ($this->fileSystem->has($backgroundImagePath)) {
            return $this->certificatePath . 'background_' . $version . '.jpg';
        }

        throw new ilException('The given temporary filename is empty');
    }

    /**
     * @throws FileNotFoundException
     * @throws IOException
     * @throws IllegalStateException
     * @throws ilException
     * @throws ilFileUtilsException
     */
    private function uploadFile(string $temporaryFilename, string $targetFileName, ?array $pending_file = null): void
    {
        $targetFilename = basename($targetFileName);
        $targetFilename = $this->fileUtilsHelper->getValidFilename($targetFilename);

        $targetFilesystem = $this->getTargetFilesystem($targetFileName);
        $targetDir = $this->getTargetDir($targetFileName);

        if (!$this->fileUpload->hasBeenProcessed()) {
            $this->fileUpload->process();
        }

        if (!$this->fileUpload->hasUploads()) {
            throw new ilException($this->language->txt('upload_error_file_not_found'));
        }

        $uploadResults = $this->fileUpload->getResults();
        if (isset($uploadResults[$temporaryFilename])) {
            $uploadResult = $uploadResults[$temporaryFilename];
            $processingStatus = $uploadResult->getStatus();
            if ($processingStatus->getCode() === ILIAS\FileUpload\DTO\ProcessingStatus::REJECTED) {
                throw new ilException($processingStatus->getMessage());
            }

            $this->fileUpload->moveOneFileTo(
                $uploadResult,
                $targetDir,
                $targetFilesystem,
                $targetFilename,
                true
            );
        } elseif (!empty($pending_file)) {
            $stream = $this->tmp_file_system->readStream(basename($pending_file['tmp_name']));
            $this->fileSystem->writeStream($targetDir . '/' . $targetFilename, $stream);
        } else {
            throw new ilException($this->language->txt('upload_error_file_not_found'));
        }
    }

    private function getTargetFilesystem(string $target): int
    {
        return match (true) {
            str_starts_with($target, $this->rootDirectory . '/' . $this->clientId), str_starts_with(
                $target,
                './' . $this->rootDirectory . '/' . $this->clientId
            ), str_starts_with($target, $this->rootDirectory) => Location::WEB,
            str_starts_with($target, CLIENT_DATA_DIR . "/temp") => Location::TEMPORARY,
            str_starts_with($target, CLIENT_DATA_DIR) => Location::STORAGE,
            str_starts_with($target, ILIAS_ABSOLUTE_PATH . '/Customizing') => Location::CUSTOMIZING,
            default => throw new InvalidArgumentException(
                "Can not move files to \"$target\" because path can not be mapped to web, storage or customizing location."
            ),
        };
    }

    private function getTargetDir(string $target): string
    {
        $absTargetDir = dirname($target);
        return $this->legacyPathHelper->createRelativePath($absTargetDir);
    }

    /**
     * Returns the filesystem path of the background image temp file during upload
     * @return string The filesystem path of the background image temp file
     */
    private function createBackgroundImageTempfilePath(): string
    {
        return $this->rootDirectory . $this->certificatePath . self::BACKGROUND_TEMPORARY_FILENAME;
    }

    /**
     * Returns the filesystem path of the background image thumbnail
     * @return string The filesystem path of the background image thumbnail
     */
    private function createBackgroundImageThumbPath(): string
    {
        return $this->rootDirectory . $this->certificatePath . self::BACKGROUND_THUMBNAIL_IMAGE_NAME;
    }
}
