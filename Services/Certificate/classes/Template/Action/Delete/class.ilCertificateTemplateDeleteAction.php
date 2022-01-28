<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateDeleteAction implements ilCertificateDeleteAction
{
    private ilCertificateTemplateRepository $templateRepository;
    private string $rootDirectory;
    private ilCertificateUtilHelper $utilHelper;
    private ilCertificateObjectHelper $objectHelper;
    private string $iliasVersion;

    public function __construct(
        ilCertificateTemplateRepository $templateRepository,
        string $rootDirectory = CLIENT_WEB_DIR,
        ?ilCertificateUtilHelper $utilHelper = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        string $iliasVersion = ILIAS_VERSION_NUMERIC
    ) {
        $this->templateRepository = $templateRepository;

        $this->rootDirectory = $rootDirectory;

        if (null === $utilHelper) {
            $utilHelper = new ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        $this->iliasVersion = $iliasVersion;
    }

    /**
     * @param $templateId
     * @param $objectId
     * @return void
     */
    public function delete($templateId, $objectId) : void
    {
        $template = $this->templateRepository->fetchCurrentlyUsedCertificate($objectId);

        $this->templateRepository->deleteTemplate($templateId, $objectId);

        $version = $template->getVersion();
        $certificateTemplate = new ilCertificateTemplate(
            $objectId,
            $this->objectHelper->lookupType($objectId),
            '',
            hash('sha256', ''),
            '',
            ($version + 1),
            $this->iliasVersion,
            time(),
            false,
            '',
            ''
        );

        $this->templateRepository->save($certificateTemplate);

        $this->overwriteBackgroundImageThumbnail($certificateTemplate);
    }

    private function overwriteBackgroundImageThumbnail(ilCertificateTemplate $previousTemplate) : void
    {
        $relativePath = $previousTemplate->getBackgroundImagePath();

        if (null === $relativePath || '' === $relativePath) {
            $relativePath = '/certificates/default/background.jpg';
        }

        $pathInfo = pathinfo($relativePath);

        $newFilePath = $pathInfo['dirname'] . '/background.jpg.thumb.jpg';

        $this->utilHelper->convertImage(
            $this->rootDirectory . $relativePath,
            $this->rootDirectory . $newFilePath,
            'JPEG',
            "100"
        );
    }
}
