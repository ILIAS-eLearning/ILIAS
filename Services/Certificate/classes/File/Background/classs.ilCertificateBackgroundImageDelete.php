<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageDelete
{
    private string $certificatePath;
    private ilCertificateBackgroundImageFileService $fileService;

    public function __construct(string $certificatePath, ilCertificateBackgroundImageFileService $fileService)
    {
        $this->certificatePath = $certificatePath;
        $this->fileService = $fileService;
    }

    public function deleteBackgroundImage(?int $version) : void
    {
        if (file_exists($this->fileService->getBackgroundImageThumbPath())) {
            unlink($this->fileService->getBackgroundImageThumbPath());
        }

        $version_string = '';
        if (is_int($version) && $version >= 0) {
            $version_string = (string) $version;
        }

        $filename = $this->certificatePath . 'background_' . $version . '.jpg';
        if (file_exists($filename)) {
            unlink($filename);
        }

        if (file_exists($this->fileService->getBackgroundImageTempfilePath())) {
            unlink($this->fileService->getBackgroundImageTempfilePath());
        }
    }
}
