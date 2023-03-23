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
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageDelete
{
    public function __construct(
        private readonly string $certificatePath,
        private readonly ilCertificateBackgroundImageFileService $fileService
    ) {
    }

    public function deleteBackgroundImage(?int $version): void
    {
        if (is_file($this->fileService->getBackgroundImageThumbPath())) {
            unlink($this->fileService->getBackgroundImageThumbPath());
        }

        $version_string = '';
        if (is_int($version) && $version >= 0) {
            $version_string = (string) $version;
        }

        $filename = $this->certificatePath . 'background_' . $version_string . '.jpg';
        if (is_file($filename)) {
            unlink($filename);
        }

        if (is_file($this->fileService->getBackgroundImageTempfilePath())) {
            unlink($this->fileService->getBackgroundImageTempfilePath());
        }
    }
}
