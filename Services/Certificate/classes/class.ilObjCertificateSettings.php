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

use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;

/**
 * Class ilObjCertificateSettings
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ServicesCertificate
 */
class ilObjCertificateSettings extends ilObject
{
    private ilLogger $cert_logger;
    private \ILIAS\Filesystem\Util\Convert\LegacyImages $file_converter;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);
        $this->type = "cert";
        $this->cert_logger = $DIC->logger()->cert();
        $this->file_converter = $DIC->fileConverters()->legacyImages();
    }

    /**
     * Uploads a background image for the certificate. Creates a new directory for the
     * certificate if needed. Removes an existing certificate image if necessary
     * @return bool True on success, otherwise false
     * @throws ilException
     */
    public function uploadBackgroundImage(\ILIAS\FileUpload\DTO\UploadResult $upload_result): bool
    {
        $image_tempfilename = $upload_result->getPath();
        if ($image_tempfilename !== '') {
            $extension = pathinfo($upload_result->getName(), PATHINFO_EXTENSION);

            $convert_filename = ilCertificateBackgroundImageFileService::BACKGROUND_IMAGE_NAME;
            $imagepath = $this->getBackgroundImageDefaultFolder();
            if (!is_dir($imagepath)) {
                ilFileUtils::makeDirParents($imagepath);
            }
            // upload the file
            if (!ilFileUtils::moveUploadedFile(
                $image_tempfilename,
                basename($this->getDefaultBackgroundImageTempfilePath($extension)),
                $this->getDefaultBackgroundImageTempfilePath($extension)
            )) {
                $this->cert_logger->error(sprintf(
                    "Could not upload certificate background image from '%s' to temporary file '%s' (name: '%s')",
                    $image_tempfilename,
                    $this->getDefaultBackgroundImageTempfilePath($extension),
                    basename($this->getDefaultBackgroundImageTempfilePath($extension))
                ));
                return false;
            }

            if (!is_file($this->getDefaultBackgroundImageTempfilePath($extension))) {
                $this->cert_logger->error(sprintf(
                    "Uploaded certificate background image could not be moved to temporary file '%s'",
                    $this->getDefaultBackgroundImageTempfilePath($extension)
                ));
                return false;
            }

            // convert the uploaded file to JPEG
            $this->file_converter->convertToFormat(
                $this->getDefaultBackgroundImageTempfilePath($extension),
                $this->getDefaultBackgroundImagePath(),
                ImageOutputOptions::FORMAT_JPG
            );

            $this->file_converter->croppedSquare(
                $this->getDefaultBackgroundImageTempfilePath($extension),
                $this->getDefaultBackgroundImageThumbPath(),
                100,
                ImageOutputOptions::FORMAT_JPG
            );

            if (!is_file($this->getDefaultBackgroundImagePath())) {
                // Something went wrong converting the file. Use the original file and hope, that PDF can work with it.
                $this->cert_logger->error(sprintf(
                    "Could not convert certificate background image from '%s' as JPEG to '%s', trying fallback ...",
                    $this->getDefaultBackgroundImageTempfilePath($extension),
                    $this->getDefaultBackgroundImagePath()
                ));
                if (!ilFileUtils::moveUploadedFile(
                    $this->getDefaultBackgroundImageTempfilePath($extension),
                    $convert_filename,
                    $this->getDefaultBackgroundImagePath()
                )) {
                    $this->cert_logger->error(sprintf(
                        "Could not upload certificate background image from '%s' to final file '%s' (name: '%s')",
                        $this->getDefaultBackgroundImageTempfilePath($extension),
                        $this->getDefaultBackgroundImagePath(),
                        $convert_filename
                    ));
                    return false;
                }
            }

            unlink($this->getDefaultBackgroundImageTempfilePath($extension));
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
        foreach (ilCertificateBackgroundImageFileService::VALID_BACKGROUND_IMAGE_EXTENSIONS as $extension) {
            if (file_exists($this->getDefaultBackgroundImageTempfilePath($extension))) {
                $result &= unlink($this->getDefaultBackgroundImageTempfilePath($extension));
            }
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

    private function getDefaultBackgroundImageTempfilePath(string $extension): string
    {
        return implode('', [
            $this->getBackgroundImageDefaultFolder(),
            ilCertificateBackgroundImageFileService::BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME,
            '.' . $extension
        ]);
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
