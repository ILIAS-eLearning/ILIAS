<?php declare(strict_types=1);
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

/**
 * Class ilObjCertificateSettings
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ServicesCertificate
 */
class ilObjCertificateSettings extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        parent::__construct($a_id, $a_reference);
        $this->type = "cert";
    }

    /**
     * Uploads a background image for the certificate. Creates a new directory for the
     * certificate if needed. Removes an existing certificate image if necessary
     * @param string $image_tempfilename Name of the temporary uploaded image file
     * @return bool An errorcode if the image upload fails, 0 otherwise
     * @throws ilException
     */
    public function uploadBackgroundImage(string $image_tempfilename) : bool
    {
        if (!empty($image_tempfilename)) {
            $convert_filename = ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME;
            $imagepath = $this->getBackgroundImageDefaultFolder();
            if (!is_dir($imagepath)) {
                ilFileUtils::makeDirParents($imagepath);
            }
            // upload the file
            if (!ilFileUtils::moveUploadedFile(
                $image_tempfilename,
                basename($this->getDefaultBackgroundImageTempfilePath()),
                $this->getDefaultBackgroundImageTempfilePath()
            )) {
                return false;
            }
            // convert the uploaded file to JPEG
            ilShellUtil::convertImage(
                $this->getDefaultBackgroundImageTempfilePath(),
                $this->getDefaultBackgroundImagePath(),
                "JPEG"
            );
            ilShellUtil::convertImage(
                $this->getDefaultBackgroundImageTempfilePath(),
                $this->getDefaultBackgroundImageThumbPath(),
                "JPEG",
                '100'
            );
            // something went wrong converting the file. use the original file and hope, that PDF can work with it
            if (!is_file($this->getDefaultBackgroundImagePath()) && !ilFileUtils::moveUploadedFile(
                $this->getDefaultBackgroundImageTempfilePath(),
                $convert_filename,
                $this->getDefaultBackgroundImagePath()
            )) {
                return false;
            }
            unlink($this->getDefaultBackgroundImageTempfilePath());
            if (is_file($this->getDefaultBackgroundImagePath()) && filesize($this->getDefaultBackgroundImagePath()) > 0) {
                return true;
            }
        }
        return false;
    }

    public function deleteBackgroundImage() : bool
    {
        $result = true;
        if (is_file($this->getDefaultBackgroundImageThumbPath())) {
            $result &= unlink($this->getDefaultBackgroundImageThumbPath());
        }
        if (is_file($this->getDefaultBackgroundImagePath())) {
            $result &= unlink($this->getDefaultBackgroundImagePath());
        }
        if (is_file($this->getDefaultBackgroundImageTempfilePath())) {
            $result &= unlink($this->getDefaultBackgroundImageTempfilePath());
        }
        return $result;
    }

    private function getBackgroundImageDefaultFolder() : string
    {
        return CLIENT_WEB_DIR . "/certificates/default/";
    }

    private function getDefaultBackgroundImagePath() : string
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME;
    }

    private function getDefaultBackgroundImageThumbPath() : string
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME . ilCertificateBackgroundImageFileService::BACKGROUND_THUMBNAIL_FILE_ENDING;
    }

    private function getDefaultBackgroundImageTempfilePath() : string
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME;
    }

    public function hasBackgroundImage() : bool
    {
        $filePath = $this->getDefaultBackgroundImagePath();
        if (is_file($filePath) && filesize($filePath) > 0) {
            return true;
        }

        return false;
    }

    public function getDefaultBackgroundImagePathWeb() : string
    {
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $this->getDefaultBackgroundImagePath()
        );
    }

    public function getBackgroundImageThumbPathWeb() : string
    {
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $this->getDefaultBackgroundImageThumbPath()
        );
    }
}
