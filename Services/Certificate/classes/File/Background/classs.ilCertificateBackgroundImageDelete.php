<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageDelete
{
    public function deleteBackgroundImage(string $version)
    {
        $result = true;
        if (file_exists($this->getBackgroundImageThumbPath())) {
            $result = $result & unlink($this->getBackgroundImageThumbPath());
        }

        $filename = $this->getBackgroundImageDirectory() . 'background_' . $version . '.jpg';
        if (file_exists($filename)) {
            $result = $result & unlink($filename);
        }

        if (file_exists($this->getBackgroundImageTempfilePath())) {
            $result = $result & unlink($this->getBackgroundImageTempfilePath());
        }

        return $result;
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
