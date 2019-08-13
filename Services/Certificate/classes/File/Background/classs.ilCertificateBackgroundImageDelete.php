<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageDelete
{

    /**
     * @var string
     */
    private $certificatePath;

    /**
     * @param $certificatePath
     */
    public function __construct(string $certificatePath)
    {
        $this->certificatePath = $certificatePath;
    }

    public function deleteBackgroundImage(string $version)
    {
        if (file_exists($this->getBackgroundImageThumbPath())) {
            unlink($this->getBackgroundImageThumbPath());
        }

        $filename = $this->getBackgroundImageDirectory() . 'background_' . $version . '.jpg';
        if (file_exists($filename)) {
            unlink($filename);
        }

        if (file_exists($this->getBackgroundImageTempfilePath())) {
            unlink($this->getBackgroundImageTempfilePath());
        }
    }

    /**
     * Returns the filesystem path of the background image
     * @param  bool $asRelative
     * @return string The filesystem path of the background image
     */
    public function getBackgroundImageDirectory($asRelative = false, $backgroundImagePath = '')
    {
        if ($asRelative) {
            return str_replace(
                array(CLIENT_WEB_DIR, '//'),
                array('[CLIENT_WEB_DIR]', '/'),
                $backgroundImagePath
            );
        }

        return $this->certificatePath;
    }

    /**
     * Returns the filename of the background image
     *
     * @return string The filename of the background image
     */
    private function getBackgroundImageName()
    {
        return "background.jpg";
    }

    /**
     * Returns the filesystem path of the background image thumbnail
     *
     * @return string The filesystem path of the background image thumbnail
     */
    private function getBackgroundImageThumbPath()
    {
        return CLIENT_WEB_DIR . $this->certificatePath . $this->getBackgroundImageName() . ".thumb.jpg";
    }

    /**
     * Returns the filesystem path of the background image temp file during upload
     *
     * @return string The filesystem path of the background image temp file
     */
    private function getBackgroundImageTempfilePath()
    {
        return CLIENT_WEB_DIR . $this->certificatePath . "background_upload.tmp";
    }
}
