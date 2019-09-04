<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageFileService
{
    const BACKGROUND_IMAGE_NAME = 'background.jpg';
    const BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME = 'background_upload.tmp';
    const BACKGROUND_THUMBNAIL_FILE_ENDING = '.thumb.jpg';
    const PLACEHOLDER_CLIENT_WEB_DIRECTORY = '[CLIENT_WEB_DIR]';

    /**
     * @var \ILIAS\Filesystem\Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $certificatePath;

    /**
     * @var string
     */
    private $webDirectory;

    /**
     * @param string                       $certificatePath
     * @param \ILIAS\Filesystem\Filesystem $filesystem
     * @param string                       $webDirectory
     */
    public function __construct(
        string $certificatePath,
        \ILIAS\Filesystem\Filesystem $filesystem,
        $webDirectory = CLIENT_WEB_DIR
    ) {
        $this->certificatePath    = $certificatePath;
        $this->fileSystem         = $filesystem;
        $this->webDirectory       = $webDirectory;
    }

    /**
     * Checks for the background image of the certificate
     * @param ilCertificateTemplate $template
     * @return boolean Returns TRUE if the certificate has a background image, FALSE otherwise
     */
    public function hasBackgroundImage(ilCertificateTemplate $template)
    {
        $backgroundImagePath = $template->getBackgroundImagePath();
        if ($backgroundImagePath === '') {
            return false;
        }

        if ($this->fileSystem->has($backgroundImagePath)){
            return true;
        }

        return false;
    }

    /**
     * @param ilCertificateTemplate $template
     * @return bool
     */
    public function hasBackgroundImageThumbnail(ilCertificateTemplate $template)
    {
        $backgroundImagePath = $template->getThumbnailImagePath();
        if ($backgroundImagePath === '') {
            return false;
        }

        if ($this->fileSystem->has($backgroundImagePath)){
            return true;
        }

        return false;
    }

    /**
     * Returns the filesystem path of the background image thumbnail
     * @return string The filesystem path of the background image thumbnail
     */
    public function getBackgroundImageThumbPath()
    {
        return $this->webDirectory . $this->certificatePath . self::BACKGROUND_IMAGE_NAME . self::BACKGROUND_THUMBNAIL_FILE_ENDING;
    }

    /**
     * Returns the filesystem path of the background image
     * @param string $backgroundImagePath
     * @return string The filesystem path of the background image
     */
    public function getBackgroundImageDirectory($backgroundImagePath = '')
    {
        return str_replace(
            array($this->webDirectory, '//'),
            array(self::PLACEHOLDER_CLIENT_WEB_DIRECTORY, '/'),
            $backgroundImagePath
        );
    }

    /**
     * Returns the filesystem path of the background image temp file during upload
     *
     * @return string The filesystem path of the background image temp file
     */
    public function getBackgroundImageTempfilePath()
    {
        return $this->webDirectory . $this->certificatePath . self::BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME;
    }
}
