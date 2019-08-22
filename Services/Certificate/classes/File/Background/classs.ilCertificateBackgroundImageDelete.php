<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageDelete
{

    /**
     * @var string
     */
    private $certificatePath;

    /**
     * @var ilCertificateBackgroundImageFileService
     */
    private $fileService;

    /**
     * @param string                                  $certificatePath
     * @param ilCertificateBackgroundImageFileService $fileService
     */
    public function __construct(string $certificatePath, ilCertificateBackgroundImageFileService $fileService)
    {
        $this->certificatePath = $certificatePath;
        $this->fileService = $fileService;
    }

    public function deleteBackgroundImage(string $version)
    {
        if (file_exists($this->fileService->getBackgroundImageThumbPath())) {
            unlink($this->fileService->getBackgroundImageThumbPath());
        }

        $filename = $this->certificatePath . 'background_' . $version . '.jpg';
        if (file_exists($filename)) {
            unlink($filename);
        }

        if (file_exists($this->getBackgroundImageTempfilePath())) {
            unlink($this->getBackgroundImageTempfilePath());
        }
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
