<?php

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

declare(strict_types=1);

/**
 * Class ilObjCertificateSettings
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ServicesCertificate
 */
class ilObjCertificateSettings extends ilObject
{
    private ilLogger $cert_logger;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);
        $this->type = "cert";
        $this->cert_logger = $DIC->logger()->cert();
    }

    /**
     * Uploads a background image for the certificate. Creates a new directory for the
     * certificate if needed. Removes an existing certificate image if necessary
     * @param string $image_tempfilename Name of the temporary uploaded image file
     * @return bool An errorcode if the image upload fails, 0 otherwise
     * @throws ilException
     */
    public function uploadBackgroundImage(string $image_tempfilename): bool
    {
        if ($image_tempfilename !== '') {
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
                $this->cert_logger->error(sprintf(
                    "Could not upload certificate background image from '%s' to temporary file '%s' (name: '%s')",
                    $image_tempfilename,
                    $this->getDefaultBackgroundImageTempfilePath(),
                    basename($this->getDefaultBackgroundImageTempfilePath())
                ));
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

            if (!is_file($this->getDefaultBackgroundImagePath())) {
                // Something went wrong converting the file. Use the original file and hope, that PDF can work with it.
                $this->cert_logger->error(sprintf(
                    "Could not convert certificate background image from '%s' as JPEG to '%s', trying fallbacj ...",
                    $this->getDefaultBackgroundImageTempfilePath(),
                    $this->getDefaultBackgroundImagePath()
                ));
                if (!ilFileUtils::moveUploadedFile(
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
            if (is_file($this->getDefaultBackgroundImagePath()) && filesize($this->getDefaultBackgroundImagePath()) > 0) {
                return true;
            }

            $this->cert_logger->error(sprintf(
                "Final background image '%s' does not exist or is empty",
                $this->getDefaultBackgroundImagePath()
            ));
        }

        return false;
    }

    public function deleteBackgroundImage(): bool
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

        /** @noinspection PhpCastIsUnnecessaryInspection */
        /** @noinspection UnnecessaryCastingInspection */
        return (bool) $result; // Don't remove the cast, otherwise $result will be 1 or 0
    }

    private function getBackgroundImageDefaultFolder(): string
    {
        return CLIENT_WEB_DIR . "/certificates/default/";
    }

    private function getDefaultBackgroundImagePath(): string
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME;
    }

    private function getDefaultBackgroundImageThumbPath(): string
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME . ilCertificateBackgroundImageFileService::BACKGROUND_THUMBNAIL_FILE_ENDING;
    }

    private function getDefaultBackgroundImageTempfilePath(): string
    {
        return $this->getBackgroundImageDefaultFolder() . ilCertificateBackgroundImageFileService::BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME;
    }

    public function hasBackgroundImage(): bool
    {
        $filePath = $this->getDefaultBackgroundImagePath();

        return is_file($filePath) && filesize($filePath) > 0;
    }

    public function getDefaultBackgroundImagePathWeb(): string
    {
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $this->getDefaultBackgroundImagePath()
        );
    }

    public function getBackgroundImageThumbPathWeb(): string
    {
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $this->getDefaultBackgroundImageThumbPath()
        );
    }
}
