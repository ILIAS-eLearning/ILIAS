<?php

declare(strict_types=1);

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

interface ilCertificateTemplateRepository
{
    public function save(ilCertificateTemplate $certificateTemplate): void;

    public function updateActivity(ilCertificateTemplate $certificateTemplate, bool $currentlyActive): int;

    /**
     * @throws ilException
     */
    public function fetchTemplate(int $templateId): ilCertificateTemplate;

    /**
     * @return ilCertificateTemplate[]
     */
    public function fetchCertificateTemplatesByObjId(int $objId): array;

    public function fetchCurrentlyUsedCertificate(int $objId): ilCertificateTemplate;

    /**
     * @throws ilException
     */
    public function fetchCurrentlyActiveCertificate(int $objId): ilCertificateTemplate;

    public function fetchPreviousCertificate(int $objId): ilCertificateTemplate;

    public function deleteTemplate(int $templateId, int $objectId): void;

    public function activatePreviousCertificate(int $objId): ilCertificateTemplate;

    /**
     * @return ilCertificateTemplate[]
     */
    public function fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(
        bool $isGlobalLpEnabled
    ): array;

    /**
     * @throws ilException
     */
    public function fetchFirstCreatedTemplate(int $objId): ilCertificateTemplate;
}
