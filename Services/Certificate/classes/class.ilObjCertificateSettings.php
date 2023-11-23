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

use ILIAS\Data\UUID\Factory;

require_once './Services/Object/classes/class.ilObject.php';

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
     * @var Factory
     */
    private $uuidFactory;

    /**
     * @var ilSetting
     */
    private $certificateSettings;

    /**
     * @var ilCertificateTemplateRepository
     */
    private $certificateRepo;

    /**
     * @var ilUserCertificateRepository
     */
    private $userCertificateRepo;

    /**
    * Constructor
    * @access	public
    * @param	int $a_id reference_id or object_id
    * @param	bool $a_reference treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;

        $this->type = 'cert';
        parent::__construct($a_id, $a_reference);
        $this->cert_logger = $DIC->logger()->cert();
        $this->uuidFactory = new Factory();
        $this->certificateSettings = new ilSetting('certificate');
        $this->certificateRepo = new ilCertificateTemplateRepository($DIC->database());
        $this->userCertificateRepo = new ilUserCertificateRepository($DIC->database());
    }

    /**
     * Uploads a background image for the certificate. Creates a new directory for the
     * certificate if needed. Removes an existing certificate image if necessary
     *
     * @param string $imageTempFileName Name of the temporary uploaded image file
     * @return bool True on success otherwise false
     * @throws ilException
     * @throws Exception
     */
    public function uploadBackgroundImage(string $imageTempFileName) : bool
    {
        if (!empty($imageTempFileName)) {
            $imagePath = $this->getBackgroundImageDefaultFolder();
            $newImageFileName = "background_{$this->uuidFactory->uuid4AsString()}.jpg";
            $newImagePath = $imagePath . $newImageFileName;

            if (!file_exists($imagePath)) {
                ilUtil::makeDirParents($imagePath);
            }
            // upload the file
            if (!ilUtil::moveUploadedFile(
                $imageTempFileName,
                basename($this->getDefaultBackgroundImageTempFilePath()),
                $this->getDefaultBackgroundImageTempFilePath()
            )) {
                $this->cert_logger->error(sprintf(
                    "Could not upload certificate background image from '%s' to temporary file '%s' (name: '%s')",
                    $imageTempFileName,
                    $this->getDefaultBackgroundImageTempFilePath(),
                    basename($this->getDefaultBackgroundImageTempFilePath())
                ));
                return false;
            }

            // convert the uploaded file to JPEG
            ilUtil::convertImage(
                $this->getDefaultBackgroundImageTempFilePath(),
                $newImagePath,
                'JPEG'
            );
            ilUtil::convertImage(
                $this->getDefaultBackgroundImageTempFilePath(),
                $newImagePath . ilCertificateBackgroundImageFileService::BACKGROUND_THUMBNAIL_FILE_ENDING,
                'JPEG',
                100
            );

            if (!is_file($newImagePath) || !file_exists($newImagePath)) {
                // Something went wrong converting the file. Use the original file and hope, that PDF can work with it.
                $this->cert_logger->error(sprintf(
                    "Could not convert certificate background image from '%s' as JPEG to '%s', trying fallbacj ...",
                    $this->getDefaultBackgroundImageTempFilePath(),
                    $newImagePath
                ));
                if (!rename(
                    $this->getDefaultBackgroundImageTempFilePath(),
                    $newImagePath
                )) {
                    $this->cert_logger->error(sprintf(
                        "Could not upload certificate background image from '%s' to final file '%s' (name: '%s')",
                        $this->getDefaultBackgroundImageTempFilePath(),
                        $newImagePath,
                        $newImageFileName
                    ));
                    return false;
                }
            }

            if (
                is_file($this->getDefaultBackgroundImageTempFilePath())
                && file_exists($this->getDefaultBackgroundImageTempFilePath())
            ) {
                unlink($this->getDefaultBackgroundImageTempFilePath());
            }

            if (file_exists($newImagePath) && (filesize($newImagePath) > 0)) {
                $oldPath = $this->getDefaultBackgroundImagePath();
                $oldPathThumb = $this->getDefaultBackgroundImageThumbPath();

                $oldRelativePath = $this->getDefaultBackgroundImagePath(true);
                $this->certificateSettings->set('defaultImageFileName', $newImageFileName);
                $newRelativePath = $this->getDefaultBackgroundImagePath(true);

                $this->certificateRepo->updateDefaultBackgroundImagePaths($oldRelativePath, $newRelativePath);

                if (
                    !$this->certificateRepo->isBackgroundImageUsed($oldRelativePath)
                    && !$this->userCertificateRepo->isBackgroundImageUsed($oldRelativePath)
                ) {
                    if (is_file($oldPath) && file_exists($oldPath)) {
                        unlink($oldPath);
                    }

                    if (is_file($oldPathThumb) && file_exists($oldPathThumb)) {
                        unlink($oldPathThumb);
                    }
                }


                return true;
            }

            $this->cert_logger->error(sprintf(
                "Final background image '%s' does not exist or is empty",
                $newImagePath
            ));
        }

        return false;
    }

    /**
    * Deletes the background image of a certificate
    *
    * @return bool TRUE if the process succeeds
    */
    public function deleteBackgroundImage() : bool
    {
        $result = true;


        if (
            $this->certificateSettings->get('defaultImageFileName', '')
            && !$this->certificateRepo->isBackgroundImageUsed($this->getDefaultBackgroundImagePath(true))
            && !$this->userCertificateRepo->isBackgroundImageUsed($this->getDefaultBackgroundImagePath(true))
        ) {
            //No certificates exist using the currently configured file, deleting file possible.

            if (is_file($this->getDefaultBackgroundImageThumbPath())) {
                $result &= unlink($this->getDefaultBackgroundImageThumbPath());
            }
            if (is_file($this->getDefaultBackgroundImagePath())) {
                $result &= unlink($this->getDefaultBackgroundImagePath());
            }
            if (is_file($this->getDefaultBackgroundImageTempFilePath())) {
                $result &= unlink($this->getDefaultBackgroundImageTempFilePath());
            }
        }

        $this->certificateSettings->set('defaultImageFileName', '');

        return $result;
    }

    public function getBackgroundImageDefaultFolder(bool $relativePath = false) : string
    {
        return ($relativePath ? '' : CLIENT_WEB_DIR) . '/certificates/default/';
    }

    /**
     * Returns the filesystem path of the background image
     *
     * @return string The filesystem path of the background image
     */
    public function getDefaultBackgroundImagePath(bool $relativePath = false) : string
    {
        return $this->getBackgroundImageDefaultFolder($relativePath)
            . $this->certificateSettings->get('defaultImageFileName', '');
    }

    /**
     * Returns the filesystem path of the background image thumbnail
     *
     * @return string The filesystem path of the background image thumbnail
     */
    public function getDefaultBackgroundImageThumbPath(bool $relativePath = false) : string
    {
        return $this->getDefaultBackgroundImagePath($relativePath) . ilCertificateBackgroundImageFileService::BACKGROUND_THUMBNAIL_FILE_ENDING;
    }

    /**
     * Returns the filesystem path of the background image temp file during upload
     *
     * @return string The filesystem path of the background image temp file
     */
    private function getDefaultBackgroundImageTempFilePath() : string
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME;
    }

    /**
     * @return bool
     */
    public function hasBackgroundImage() : bool
    {
        $filePath = $this->getDefaultBackgroundImagePath();
        return is_file($filePath) && filesize($filePath) > 0;
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
