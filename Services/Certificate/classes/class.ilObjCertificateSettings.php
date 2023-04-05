<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjCertificateSettings
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ServicesCertificate
*/
class ilObjCertificateSettings extends ilObject
{
    /** @var ilLogger */
    private $cert_logger;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);
        $this->type = "cert";
        $this->cert_logger = $DIC->logger()->cert();
    }

    /**
    * Uploads a background image for the certificate. Creates a new directory for the
    * certificate if needed. Removes an existing certificate image if necessary
    *
    * @param string $image_tempfilename Name of the temporary uploaded image file
    * @return integer An errorcode if the image upload fails, 0 otherwise
    */
    public function uploadBackgroundImage($image_tempfilename)
    {
        if (!empty($image_tempfilename)) {
            $convert_filename = ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME;
            $imagepath = $this->getBackgroundImageDefaultFolder();
            if (!file_exists($imagepath)) {
                ilUtil::makeDirParents($imagepath);
            }
            // upload the file
            if (!ilUtil::moveUploadedFile(
                $image_tempfilename,
                basename($this->getDefaultBackgroundImageTempfilePath()),
                $this->getDefaultBackgroundImageTempfilePath()
            )) {
                $this->cert_logger->error(sprintf(
                    "Could not upload certificate background image from '%s' to temporary file '%s' (name: '%s')",
                    $image_tempfilename,
                    $this->getDefaultBackgroundImageTempfilePath(),
                    basename($this->getDefaultBackgroundImageTempfilePath())
                ));
                return false;
            }

            // convert the uploaded file to JPEG
            ilUtil::convertImage($this->getDefaultBackgroundImageTempfilePath(), $this->getDefaultBackgroundImagePath(), "JPEG");
            ilUtil::convertImage($this->getDefaultBackgroundImageTempfilePath(), $this->getDefaultBackgroundImageThumbPath(), "JPEG", 100);

            if (!is_file($this->getDefaultBackgroundImagePath())) {
                // Something went wrong converting the file. Use the original file and hope, that PDF can work with it.
                $this->cert_logger->error(sprintf(
                    "Could not convert certificate background image from '%s' as JPEG to '%s', trying fallbacj ...",
                    $this->getDefaultBackgroundImageTempfilePath(),
                    $this->getDefaultBackgroundImagePath()
                ));
                if (!ilUtil::moveUploadedFile(
                    $this->getDefaultBackgroundImageTempfilePath(),
                    $convert_filename,
                    $this->getDefaultBackgroundImagePath()
                )) {
                    $this->cert_logger->error(sprintf(
                        "Could not upload certificate background image from '%s' to final file '%s' (name: '%s')",
                        $this->getDefaultBackgroundImageTempfilePath(),
                        $this->getDefaultBackgroundImagePath(),
                        $convert_filename
                    ));
                    return false;
                }
            }

            unlink($this->getDefaultBackgroundImageTempfilePath());
            if (file_exists($this->getDefaultBackgroundImagePath()) && (filesize($this->getDefaultBackgroundImagePath()) > 0)) {
                return true;
            }

            $this->cert_logger->error(sprintf(
                "Final background image '%s' does not exist or is empty",
                $this->getDefaultBackgroundImagePath()
            ));
        }

        return false;
    }

    /**
    * Deletes the background image of a certificate
    *
    * @return boolean TRUE if the process succeeds
    */
    public function deleteBackgroundImage()
    {
        $result = true;
        if (file_exists($this->getDefaultBackgroundImageThumbPath())) {
            $result = $result & unlink($this->getDefaultBackgroundImageThumbPath());
        }
        if (file_exists($this->getDefaultBackgroundImagePath())) {
            $result = $result & unlink($this->getDefaultBackgroundImagePath());
        }
        if (file_exists($this->getDefaultBackgroundImageTempfilePath())) {
            $result = $result & unlink($this->getDefaultBackgroundImageTempfilePath());
        }
        return $result;
    }

    private function getBackgroundImageDefaultFolder()
    {
        return CLIENT_WEB_DIR . "/certificates/default/";
    }

    /**
     * Returns the filesystem path of the background image
     *
     * @return string The filesystem path of the background image
     */
    private function getDefaultBackgroundImagePath()
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME;
    }

    /**
     * Returns the filesystem path of the background image thumbnail
     *
     * @return string The filesystem path of the background image thumbnail
     */
    private function getDefaultBackgroundImageThumbPath()
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME . ilCertificateBackgroundImageFileService::BACKGROUND_THUMBNAIL_FILE_ENDING;
    }

    /**
     * Returns the filesystem path of the background image temp file during upload
     *
     * @return string The filesystem path of the background image temp file
     */
    private function getDefaultBackgroundImageTempfilePath()
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME;
    }

    /**
     * @return bool
     */
    public function hasBackgroundImage() : bool
    {
        $filePath = $this->getDefaultBackgroundImagePath();
        if (file_exists($filePath) && filesize($filePath) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns the web path of the background image
     * @return string The web path of the background image
     */
    public function getDefaultBackgroundImagePathWeb() : string
    {
        return str_replace(
            ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $this->getDefaultBackgroundImagePath()
        );
    }

    /**
     * Returns the web path of the background image thumbnail
     * @return string The web path of the background image thumbnail
     */
    public function getBackgroundImageThumbPathWeb() : string
    {
        return str_replace(
            ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $this->getDefaultBackgroundImageThumbPath()
        );
    }
}
