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

use ILIAS\Filesystem\Filesystem;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageFileService
{
    final public const BACKGROUND_IMAGE_NAME = 'background.jpg';
    final public const BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME = 'background_upload.tmp';
    final public const BACKGROUND_THUMBNAIL_FILE_ENDING = '.thumb.jpg';
    final public const PLACEHOLDER_CLIENT_WEB_DIRECTORY = '[CLIENT_WEB_DIR]';

    public function __construct(
        private readonly string $certificatePath,
        private readonly Filesystem $fileSystem,
        private readonly string $webDirectory = CLIENT_WEB_DIR
    ) {
    }

    public function hasBackgroundImage(ilCertificateTemplate $template): bool
    {
        $backgroundImagePath = $template->getBackgroundImagePath();
        if ($backgroundImagePath === '') {
            return false;
        }

        return $this->fileSystem->has($backgroundImagePath);
    }

    public function getBackgroundImageThumbPath(): string
    {
        return $this->webDirectory . $this->certificatePath . self::BACKGROUND_IMAGE_NAME . self::BACKGROUND_THUMBNAIL_FILE_ENDING;
    }

    public function getBackgroundImageDirectory(string $backgroundImagePath = ''): string
    {
        return str_replace(
            [$this->webDirectory, '//'],
            [self::PLACEHOLDER_CLIENT_WEB_DIRECTORY, '/'],
            $backgroundImagePath
        );
    }

    public function getBackgroundImageTempfilePath(): string
    {
        return $this->webDirectory . $this->certificatePath . self::BACKGROUND_TEMPORARY_UPLOAD_FILE_NAME;
    }
}
