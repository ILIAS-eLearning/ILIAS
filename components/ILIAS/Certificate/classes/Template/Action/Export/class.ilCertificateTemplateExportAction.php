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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateExportAction
{
    private readonly ilCertificateObjectHelper $objectHelper;
    private readonly ilCertificateUtilHelper $utilHelper;

    public function __construct(
        private readonly int $objectId,
        private readonly string $certificatePath,
        private readonly ilCertificateTemplateRepository $templateRepository,
        private readonly IRSS $irss,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilCertificateUtilHelper $utilHelper = null
    ) {
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
     * Creates a downloadable file via the browser
     */
    public function export(string $rootDir = CLIENT_WEB_DIR, string $installationId = IL_INST_ID): void
    {
        $time = time();

        $type = $this->objectHelper->lookupType($this->objectId);
        $certificateId = $this->objectId;

        $exportPath = $this->certificatePath . $time . '__' . $installationId . '__' . $type . '__' . $certificateId . '__certificate/';

        $streams = [];

        $template = $this->templateRepository->fetchCurrentlyUsedCertificate($this->objectId);

        $streams['certificate.xml'] = Streams::ofString(
            $template->getCertificateContent()
        );

        $background_rid = $this->irss->manage()->find($template->getBackgroundImageIdentification());
        if ($background_rid instanceof ResourceIdentification) {
            $streams['background.jpg'] = $this->irss->consume()->stream($background_rid)->getStream();
        }

        $thumbnail_rid = $this->irss->manage()->find($template->getThumbnailImageIdentification());
        if ($thumbnail_rid instanceof ResourceIdentification) {
            $streams['thumbnail.svg'] = $this->irss->consume()->stream($thumbnail_rid)->getStream();
        }

        $objectType = $this->objectHelper->lookupType($this->objectId);

        $zipFileName = $time . '__' . $installationId . '__' . $objectType . '__' . $this->objectId . '__certificate.zip';

        $this->utilHelper->zipAndDeliver($streams, $zipFileName);
    }
}
