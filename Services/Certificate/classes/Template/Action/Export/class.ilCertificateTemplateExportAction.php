<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateExportAction
{
    /**
     * @var int
     */
    private $objectId;

    /**
     * @var string
     */
    private $certificatePath;

    /**
     * @var ilCertificateTemplateRepository
     */
    private $templateRepository;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ilCertificateObjectHelper|null
     */
    private $objectHelper;

    /**
     * @var ilCertificateUtilHelper|null
     */
    private $utilHelper;

    /**
     * @param integer $objectId
     * @param string $certificatePath
     * @param ilCertificateTemplateRepository $templateRepository
     * @param Filesystem $filesystem
     * @param ilCertificateObjectHelper|null $objectHelper
     * @param ilCertificateUtilHelper|null $utilHelper
     */
    public function __construct(
        int $objectId,
        string $certificatePath,
        ilCertificateTemplateRepository $templateRepository,
        Filesystem $filesystem,
        ilCertificateObjectHelper $objectHelper = null,
        ilCertificateUtilHelper $utilHelper = null
    ) {
        $this->objectId = $objectId;
        $this->certificatePath = $certificatePath;
        $this->templateRepository = $templateRepository;
        $this->filesystem = $filesystem;

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $utilHelper) {
            $utilHelper = new ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;
    }

    /**
     * Creates an downloadable file via the browser
     * @param string $rootDir
     * @param string $installationId
     * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function export($rootDir = CLIENT_WEB_DIR, $installationId = IL_INST_ID)
    {
        $time = time();

        $type = $this->objectHelper->lookupType($this->objectId);
        $certificateId = $this->objectId;

        $exportPath = $this->certificatePath . $time . '__' . $installationId . '__' . $type . '__' . $certificateId . '__certificate/';

        $this->filesystem->createDir($exportPath, \ILIAS\Filesystem\Visibility::PUBLIC_ACCESS);

        $template = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

        $xslContent = $template->getCertificateContent();

        $this->filesystem->put($exportPath . 'certificate.xml', $xslContent);

        $backgroundImagePath = $template->getBackgroundImagePath();
        if ($backgroundImagePath !== '') {
            if (true === $this->filesystem->has($backgroundImagePath)) {
                $this->filesystem->copy($backgroundImagePath, $exportPath . 'background.jpg');
            }
        }

        $thumbnailImagePath = $template->getThumbnailImagePath();
        if ($thumbnailImagePath !== '') {
            if (true === $this->filesystem->has($backgroundImagePath)) {
                $this->filesystem->copy($thumbnailImagePath, $exportPath . 'thumbnail.svg');
            }
        }

        $objectType = $this->objectHelper->lookupType($this->objectId);

        $zipFileName = $time . '__' . $installationId . '__' . $objectType . '__' . $this->objectId . '__certificate.zip';


        $zipPath = $rootDir . $this->certificatePath . $zipFileName;
        $this->utilHelper->zip($exportPath, $zipPath);
        $this->filesystem->deleteDir($exportPath);

        $this->utilHelper->deliverFile($zipPath, $zipFileName, 'application/zip');
    }
}
