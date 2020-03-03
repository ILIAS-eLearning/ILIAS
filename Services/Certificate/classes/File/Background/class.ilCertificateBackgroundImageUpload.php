<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageUpload
{
    const BACKGROUND_IMAGE_NAME = 'background.jpg';
    const BACKGROUND_THUMBNAIL_IMAGE_NAME = 'background.jpg.thumb.jpg';
    const BACKGROUND_TEMPORARY_FILENAME = 'background_upload.tmp';

    /**
     * @var \ILIAS\FileUpload\FileUpload
     */
    private $fileUpload;

    /**
     * @var string
     */
    private $certificatePath;

    /**
     * @var ilLanguage
     */
    private $language;

    /**
     * @var string
     */
    private $rootDirectory;

    /**
     * @var \ILIAS\Filesystem\Filesystem|\ILIAS\Filesystem\Filesystems
     */
    private $fileSystem;

    /**
     * @var ilCertificateUtilHelper|null
     */
    private $utilHelper;

    /**
     * @var ilCertificateFileUtilsHelper|null
     */
    private $fileUtilsHelper;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var LegacyPathHelperHelper
     */
    private $legacyPathHelper;

    /**
     * @param \ILIAS\FileUpload\FileUpload $fileUpload
     * @param string $certificatePath
     * @param ilLanguage $language
     * @param ilLogger $logger
     * @param \ILIAS\Filesystem\Filesystem|null $fileSystem
     * @param ilCertificateUtilHelper|null $utilHelper
     * @param ilCertificateFileUtilsHelper|null $certificateFileUtilsHelper
     * @param LegacyPathHelperHelper|null $legacyPathHelper
     * @param string $rootDirectory
     * @param string $clientID
     */
    public function __construct(
        \ILIAS\FileUpload\FileUpload $fileUpload,
        string $certificatePath,
        ilLanguage $language,
        ilLogger $logger,
        \ILIAS\Filesystem\Filesystem $fileSystem = null,
        ilCertificateUtilHelper $utilHelper = null,
        ilCertificateFileUtilsHelper $certificateFileUtilsHelper = null,
        LegacyPathHelperHelper $legacyPathHelper = null,
        string $rootDirectory = CLIENT_WEB_DIR,
        string $clientID = CLIENT_ID
    ) {
        $this->fileUpload = $fileUpload;
        $this->certificatePath = $certificatePath;
        $this->language = $language;
        $this->logger = $logger;
        $this->rootDirectory = $rootDirectory;

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

        $this->clientId = $clientID;
    }

    /**
     * Uploads a background image for the certificate. Creates a new directory for the
     * certificate if needed. Removes an existing certificate image if necessary
     *
     * @param string $imageTempFilename Name of the temporary uploaded image file
     * @param int $version - Version of the current certifcate template
     * @return integer An errorcode if the image upload fails, 0 otherwise
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function uploadBackgroundImage(string $imageTempFilename, int $version)
    {
        $imagepath = $this->rootDirectory . $this->certificatePath;

        if (!$this->fileSystem->hasDir($imagepath)) {
            ilUtil::makeDirParents($imagepath);
        }

        $backgroundImageTempFilePath = $this->createBackgroundImageTempfilePath();

        $this->uploadFile($imageTempFilename, $backgroundImageTempFilePath);

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
            100
        );

        $convert_filename = self::BACKGROUND_IMAGE_NAME;

        if (!$this->fileSystem->has($backgroundImagePath)) {
            // something went wrong converting the file. use the original file and hope, that PDF can work with it
            if (!ilUtil::moveUploadedFile($backgroundImageTempFilePath, $convert_filename, $this->rootDirectory . $backgroundImagePath)) {
                throw new ilException('Unable to convert the file and the original file');
            }
        }

        $this->fileSystem->delete($this->certificatePath . self::BACKGROUND_TEMPORARY_FILENAME);

        if ($this->fileSystem->has($backgroundImagePath)) {
            return $this->certificatePath . 'background_' . $version . '.jpg';
        }

        throw new ilException('The given temporary filename is empty');
    }

    /**
     * @param string $temporaryFilename
     * @param string $targetFileName
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws ilException
     * @throws ilFileUtilsException
     */
    private function uploadFile(string $temporaryFilename, string $targetFileName)
    {
        $targetFilename = basename($targetFileName);
        $targetFilename = $this->fileUtilsHelper->getValidFilename($targetFilename);

        $targetFilesystem = $this->getTargetFilesystem($targetFileName);
        $targetDir = $this->getTargetDir($targetFileName);

        if (false === $this->fileUpload->hasBeenProcessed()) {
            $this->fileUpload->process();
        }

        if (false === $this->fileUpload->hasUploads()) {
            throw new ilException($this->language->txt('upload_error_file_not_found'));
        }

        /**
         * @var \ILIAS\FileUpload\DTO\UploadResult $uploadResult
         */
        $uploadResults = $this->fileUpload->getResults();
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
    }

    /**
     * @param string $target
     * @return int
     */
    private function getTargetFilesystem(string $target)
    {
        switch (true) {
            case strpos($target, $this->rootDirectory . '/' . $this->clientId) === 0:
            case strpos($target, './' . $this->rootDirectory . '/' . $this->clientId) === 0:
            case strpos($target, $this->rootDirectory) === 0:
                $targetFilesystem = \ILIAS\FileUpload\Location::WEB;
                break;
            case strpos($target, CLIENT_DATA_DIR . "/temp") === 0:
                $targetFilesystem = \ILIAS\FileUpload\Location::TEMPORARY;
                break;
            case strpos($target, CLIENT_DATA_DIR) === 0:
                $targetFilesystem = \ILIAS\FileUpload\Location::STORAGE;
                break;
            case strpos($target, ILIAS_ABSOLUTE_PATH . '/Customizing') === 0:
                $targetFilesystem = \ILIAS\FileUpload\Location::CUSTOMIZING;
                break;
            default:
                throw new InvalidArgumentException("Can not move files to \"$target\" because path can not be mapped to web, storage or customizing location.");
        }

        return $targetFilesystem;
    }

    /**
     * @param $target
     * @return array
     */
    private function getTargetDir(string $target)
    {
        $absTargetDir = dirname($target);
        $targetDir = $this->legacyPathHelper->createRelativePath($absTargetDir);

        return  $targetDir;
    }

    /**
     * Returns the filesystem path of the background image temp file during upload
     *
     * @return string The filesystem path of the background image temp file
     */
    private function createBackgroundImageTempfilePath()
    {
        return $this->rootDirectory . $this->certificatePath . self::BACKGROUND_TEMPORARY_FILENAME;
    }

    /**
     * Returns the filesystem path of the background image thumbnail
     *
     * @return string The filesystem path of the background image thumbnail
     */
    private function createBackgroundImageThumbPath()
    {
        return $this->rootDirectory . $this->certificatePath . self::BACKGROUND_THUMBNAIL_IMAGE_NAME;
    }
}
