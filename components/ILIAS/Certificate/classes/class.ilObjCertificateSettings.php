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
use ILIAS\Data\UUID\Factory;
use ILIAS\FileUpload\DTO\UploadResult;

/**
 * Class ilObjCertificateSettings
 * @author  Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ServicesCertificate
 */
class ilObjCertificateSettings extends ilObject
{
    private readonly ilLogger $cert_logger;
    private readonly \ILIAS\Filesystem\Util\Convert\LegacyImages $file_converter;
    private readonly Factory $uuid_factory;
    private readonly ilSetting $certificate_settings;
    private readonly ilCertificateTemplateDatabaseRepository $certificate_repo;
    private readonly ilUserCertificateRepository $user_certificate_repo;

    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);
        $this->type = 'cert';
        $this->cert_logger = $DIC->logger()->cert();
        $this->file_converter = $DIC->fileConverters()->legacyImages();
        $this->uuid_factory = new Factory();
        $this->certificate_settings = new ilSetting('certificate');
        $this->certificate_repo = new ilCertificateTemplateDatabaseRepository($DIC->database());
        $this->user_certificate_repo = new ilUserCertificateRepository($DIC->database());
    }

    /**
     * Uploads a background image for the certificate. Creates a new directory for the
     * certificate if needed. Removes an existing certificate image if necessary
     * @return bool True on success, otherwise false
     * @throws ilException
     */
    public function uploadBackgroundImage(UploadResult $upload_result): bool
    {
        $image_temp_file_name = $upload_result->getPath();
        if ($image_temp_file_name !== '') {
            $extension = pathinfo($upload_result->getName(), PATHINFO_EXTENSION);
            $image_path = $this->getBackgroundImageDefaultFolder();
            $new_image_file_name = "background_{$this->uuid_factory->uuid4AsString()}.jpg";
            $new_image_path = $image_path . $new_image_file_name;

            if (!is_dir($image_path)) {
                ilFileUtils::makeDirParents($image_path);
            }
            // upload the file
            if (!ilFileUtils::moveUploadedFile(
                $image_temp_file_name,
                basename($this->getDefaultBackgroundImageTempFilePath($extension)),
                $this->getDefaultBackgroundImageTempFilePath($extension)
            )) {
                $this->cert_logger->error(sprintf(
                    "Could not upload certificate background image from '%s' to temporary file '%s' (name: '%s')",
                    $image_temp_file_name,
                    $this->getDefaultBackgroundImageTempFilePath($extension),
                    basename($this->getDefaultBackgroundImageTempFilePath($extension))
                ));
                return false;
            }

            if (!is_file($this->getDefaultBackgroundImageTempFilePath($extension))) {
                $this->cert_logger->error(sprintf(
                    "Uploaded certificate background image could not be moved to temporary file '%s'",
                    $this->getDefaultBackgroundImageTempFilePath($extension)
                ));
                return false;
            }

            // convert the uploaded file to JPEG
            $this->file_converter->convertToFormat(
                $this->getDefaultBackgroundImageTempFilePath($extension),
                $new_image_path,
                ImageOutputOptions::FORMAT_JPG
            );

            $this->file_converter->croppedSquare(
                $this->getDefaultBackgroundImageTempFilePath($extension),
                $new_image_path . ilCertificateBackgroundImageFileService::BACKGROUND_THUMBNAIL_FILE_ENDING,
                100,
                ImageOutputOptions::FORMAT_JPG
            );

            if (!is_file($new_image_path) || !file_exists($new_image_path)) {
                // Something went wrong converting the file. Use the original file and hope, that PDF can work with it.
                $this->cert_logger->error(sprintf(
                    "Could not convert certificate background image from '%s' as JPEG to '%s', trying fallbacj ...",
                    $this->getDefaultBackgroundImageTempFilePath($extension),
                    $new_image_path
                ));
                if (!ilFileUtils::moveUploadedFile(
                    $this->getDefaultBackgroundImageTempFilePath($extension),
                    $new_image_file_name,
                    $new_image_path
                )) {
                    $this->cert_logger->error(sprintf(
                        "Could not upload certificate background image from '%s' to final file '%s' (name: '%s')",
                        $this->getDefaultBackgroundImageTempFilePath($extension),
                        $new_image_path,
                        $new_image_file_name
                    ));
                    return false;
                }
            }

            if (
                is_file($this->getDefaultBackgroundImageTempFilePath($extension))
                && file_exists($this->getDefaultBackgroundImageTempFilePath($extension))
            ) {
                unlink($this->getDefaultBackgroundImageTempFilePath($extension));
            }

            if (file_exists($new_image_path) && (filesize($new_image_path) > 0)) {
                $old_path = $this->getDefaultBackgroundImagePath();
                $old_path_thumb = $this->getDefaultBackgroundImageThumbPath();
                $old_relative_path = $this->getDefaultBackgroundImagePath(true);
                $this->certificate_settings->set('defaultImageFileName', $new_image_file_name);
                $new_relative_path = $this->getDefaultBackgroundImagePath(true);

                $this->certificate_repo->updateDefaultBackgroundImagePaths($old_relative_path, $new_relative_path);

                if (
                    !$this->certificate_repo->isBackgroundImageUsed($old_relative_path)
                    && !$this->user_certificate_repo->isBackgroundImageUsed($old_relative_path)
                ) {
                    if (is_file($old_path) && file_exists($old_path)) {
                        unlink($old_path);
                    }

                    if (is_file($old_path_thumb) && file_exists($old_path_thumb)) {
                        unlink($old_path_thumb);
                    }
                }
                return true;
            }

            $this->cert_logger->error(sprintf(
                "Final background image '%s' does not exist or is empty",
                $new_image_path
            ));
        }

        return false;
    }

    public function deleteBackgroundImage(): bool
    {
        $result = true;

        if (
            $this->certificate_settings->get('defaultImageFileName', '')
            && !$this->certificate_repo->isBackgroundImageUsed($this->getDefaultBackgroundImagePath(true))
            && !$this->user_certificate_repo->isBackgroundImageUsed($this->getDefaultBackgroundImagePath(true))
        ) {
            //No certificates exist using the currently configured file, deleting file possible.

            if (is_file($this->getDefaultBackgroundImageThumbPath())) {
                $result &= unlink($this->getDefaultBackgroundImageThumbPath());
            }
            if (is_file($this->getDefaultBackgroundImagePath())) {
                $result &= unlink($this->getDefaultBackgroundImagePath());
            }

            foreach (ilCertificateBackgroundImageFileService::VALID_BACKGROUND_IMAGE_EXTENSIONS as $extension) {
                if (is_file($this->getDefaultBackgroundImageTempFilePath($extension))) {
                    $result &= unlink($this->getDefaultBackgroundImageTempFilePath($extension));
                }
            }
        }

        $this->certificate_settings->set('defaultImageFileName', '');

        /** @noinspection PhpCastIsUnnecessaryInspection */
        /** @noinspection UnnecessaryCastingInspection */
        return (bool) $result; // Don't remove the cast, otherwise $result will be 1 or 0
    }

    public function getBackgroundImageDefaultFolder(bool $relativePath = false): string
    {
        return ($relativePath ? '' : CLIENT_WEB_DIR) . '/certificates/default/';
    }

    public function getDefaultBackgroundImagePath(bool $relativePath = false): string
    {
        return $this->getBackgroundImageDefaultFolder($relativePath)
            . $this->certificate_settings->get('defaultImageFileName', '');
    }

    public function getDefaultBackgroundImageThumbPath(bool $relativePath = false): string
    {
        return $this->getDefaultBackgroundImagePath($relativePath) . ilCertificateBackgroundImageFileService::BACKGROUND_THUMBNAIL_FILE_ENDING;
    }

    private function getDefaultBackgroundImageTempFilePath(string $extension): string
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
