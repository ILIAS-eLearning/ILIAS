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

namespace ILIAS\Certificate;

use ilObjCertificateSettings;
use ilUserCertificateRepository;
use ILIAS\ResourceStorage\Services as IRSS;
use ilCertificateTemplateDatabaseRepository;
use ILIAS\Certificate\File\ilCertificateTemplateStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class CertificateResourceHandler
{
    private ilUserCertificateRepository $user_certificate_repo;
    private ilCertificateTemplateDatabaseRepository $certificate_template_repo;
    private IRSS $irss;
    private ilCertificateTemplateStakeholder $stakeholder;
    private ilObjCertificateSettings $global_certificate_settings;

    public function __construct()
    {
        global $DIC;
        $this->user_certificate_repo = new ilUserCertificateRepository($DIC->database());
        $this->certificate_template_repo = new ilCertificateTemplateDatabaseRepository($DIC->database());
        $this->stakeholder = new ilCertificateTemplateStakeholder();
        $this->irss = $DIC->resourceStorage();
        $this->global_certificate_settings = new ilObjCertificateSettings();
    }

    public function handleResourceChange(ResourceIdentification $background_image): void
    {
        if (
            !$this->user_certificate_repo->isResourceUsed($background_image->serialize()) &&
            !$this->certificate_template_repo->isResourceUsed($background_image->serialize()) &&
            (
                $this->global_certificate_settings->getBackgroundImageIdentification() === null ||
                $this->global_certificate_settings->getBackgroundImageIdentification()->serialize(
                ) !== $background_image->serialize()
            )
        ) {
            $this->irss->manage()->remove($background_image, $this->stakeholder);
        }
    }
}
