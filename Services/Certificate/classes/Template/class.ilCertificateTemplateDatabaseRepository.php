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

/**
 * @author  Niels Theen <ntheen@databay.de>
 * Repository that allows interaction with the database
 * in the context of certificate templates.
 */
class ilCertificateTemplateDatabaseRepository implements ilCertificateTemplateRepository
{
    private ilLogger $logger;
    private ilObjectDataCache $objectDataCache;

    public function __construct(
        private ilDBInterface $database,
        ?ilLogger $logger = null,
        ?ilObjectDataCache $objectDataCache = null
    ) {
        if (null === $logger) {
            global $DIC;
            $logger = $DIC->logger()->cert();
        }
        $this->logger = $logger;

        if (null === $objectDataCache) {
            global $DIC;
            $objectDataCache = $DIC['ilObjDataCache'];
        }
        $this->objectDataCache = $objectDataCache;
    }

    public function save(ilCertificateTemplate $certificateTemplate): void
    {
        $this->logger->debug('START - Save new certificate template');

        $objId = $certificateTemplate->getObjId();

        $id = $this->database->nextId('il_cert_template');

        $this->deactivatePreviousTemplates($objId);

        $columns = [
            'id' => ['integer', $id],
            'obj_id' => ['integer', $objId],
            'obj_type' => ['text', $certificateTemplate->getObjType()],
            'certificate_content' => ['clob', $certificateTemplate->getCertificateContent()],
            'certificate_hash' => ['text', $certificateTemplate->getCertificateHash()],
            'template_values' => ['clob', $certificateTemplate->getTemplateValues()],
            'version' => ['integer', $certificateTemplate->getVersion()],
            'ilias_version' => ['text', $certificateTemplate->getIliasVersion()],
            'created_timestamp' => ['integer', $certificateTemplate->getCreatedTimestamp()],
            'currently_active' => ['integer', (int) $certificateTemplate->isCurrentlyActive()],
            'background_image_path' => ['text', $certificateTemplate->getBackgroundImagePath()],
            'deleted' => ['integer', (int) $certificateTemplate->isDeleted()],
            'thumbnail_image_path' => ['text', $certificateTemplate->getThumbnailImagePath()]
        ];

        $this->database->insert('il_cert_template', $columns);

        $this->logger->debug(sprintf(
            'END - certificate template saved with columns: %s',
            json_encode($columns, JSON_THROW_ON_ERROR)
        ));
    }

    public function updateActivity(ilCertificateTemplate $certificateTemplate, bool $currentlyActive): int
    {
        $sql = 'UPDATE il_cert_template SET currently_active = ' . $this->database->quote($currentlyActive, 'integer') .
            ' WHERE id = ' . $this->database->quote($certificateTemplate->getId(), 'integer');

        return $this->database->manipulate($sql);
    }

    /**
     * @throws ilException
     */
    public function fetchTemplate(int $templateId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch certificate template with id: "%s"', $templateId));

        $sql = '
SELECT * FROM
il_cert_template
WHERE id = ' . $this->database->quote($templateId, 'integer') . '
ORDER BY version ASC';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            return $this->createCertificateTemplate($row);
        }

        throw new ilException(sprintf('No template with id "%s" found', $templateId));
    }

    /**
     * @return ilCertificateTemplate[]
     */
    public function fetchCertificateTemplatesByObjId(int $objId): array
    {
        $this->logger->debug(sprintf('START - Fetch multiple certificate templates for object: "%s"', $objId));

        $result = [];

        $sql = '
SELECT * FROM
il_cert_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND deleted = 0
ORDER BY version ASC';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $result[] = $this->createCertificateTemplate($row);
        }

        $this->logger->debug(sprintf(
            'END - Fetching of certificate templates for object: "%s" with "%s" results',
            $objId,
            count($result)
        ));

        return $result;
    }

    public function fetchCurrentlyUsedCertificate(int $objId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch currently active certificate template for object: "%s"', $objId));

        $this->database->setLimit(1);

        $sql = '
SELECT * FROM il_cert_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND deleted = 0
ORDER BY id DESC
';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('END - Found active certificate for: "%s"', $objId));

            return $this->createCertificateTemplate($row);
        }

        $this->logger->debug(sprintf('END - Found NO active certificate for: "%s"', $objId));

        return new ilCertificateTemplate(
            $objId,
            $this->objectDataCache->lookupType($objId),
            '',
            '',
            '',
            0,
            "0",
            0,
            false,
            '',
            ''
        );
    }

    /**
     * @throws ilException
     */
    public function fetchCurrentlyActiveCertificate(int $objId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch currently active certificate template for object: "%s"', $objId));

        $sql = '
SELECT * FROM il_cert_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND deleted = 0
AND currently_active = 1
';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('END - Found active certificate for: "%s"', $objId));

            return $this->createCertificateTemplate($row);
        }

        throw new ilException((sprintf('NO active certificate template found for: "%s"', $objId)));
    }

    public function fetchPreviousCertificate(int $objId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch previous active certificate template for object: "%s"', $objId));

        $templates = $this->fetchCertificateTemplatesByObjId($objId);

        $resultTemplate = new ilCertificateTemplate(
            $objId,
            $this->objectDataCache->lookupType($objId),
            '',
            '',
            '',
            0,
            "0",
            0,
            true,
            '',
            ''
        );

        $version = 0;
        foreach ($templates as $template) {
            if ($template->getVersion() > $version) {
                $version = $template->getVersion();
                $resultTemplate = $template;
            }
        }

        $this->logger->debug(sprintf('Latest version active certificate template for object: "%s"', $objId));

        return $resultTemplate;
    }

    public function deleteTemplate(int $templateId, int $objectId): void
    {
        $this->logger->debug(sprintf(
            'START - Set deleted flag for certificate template("%s") for object: "%s"',
            $templateId,
            $objectId
        ));

        $sql = '
UPDATE il_cert_template
SET deleted = 1, currently_active = 0
WHERE id = ' . $this->database->quote($templateId, 'integer') . '
AND obj_id = ' . $this->database->quote($objectId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf(
            'END - Deleted flag set fo certificate template("%s") for object: "%s"',
            $templateId,
            $objectId
        ));
    }

    public function activatePreviousCertificate(int $objId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Activate previous certificate template for object: "%s"', $objId));

        $certificates = $this->fetchCertificateTemplatesByObjId($objId);

        /** @var ilCertificateTemplate|null $previousCertificate */
        $previousCertificate = null;
        foreach ($certificates as $certificate) {
            if (null === $previousCertificate) {
                $previousCertificate = $certificate;
            } elseif ($certificate->getVersion() > $previousCertificate->getVersion()) {
                $previousCertificate = $certificate;
            }
        }

        $sql = 'UPDATE il_cert_template
SET currently_active = 1
WHERE id = ' . $this->database->quote($previousCertificate->getId(), 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf('END - Previous certificate updated for object: "%s"', $objId));

        return $previousCertificate;
    }

    public function fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(
        bool $isGlobalLpEnabled
    ): array {
        $this->logger->debug(
            'START - Fetch all active course certificate templates with disabled learning progress: "%s"'
        );

        $joinLpSettings = '';
        $whereLpSettings = '';
        if ($isGlobalLpEnabled) {
            $joinLpSettings = 'LEFT JOIN ut_lp_settings uls ON uls.obj_id = od.obj_id';
            $whereLpSettings = sprintf(
                'AND (uls.u_mode IS NULL OR uls.u_mode = %s)',
                $this->database->quote(ilLPObjSettings::LP_MODE_DEACTIVATED, 'integer')
            );
        }

        $sql = "
            SELECT il_cert_template.*
            FROM il_cert_template
            INNER JOIN object_data od ON od.obj_id = il_cert_template.obj_id
            INNER JOIN settings ON settings.module = %s AND settings.keyword = {$this->database->concat(
            [
                [$this->database->quote('cert_subitems_', 'text'), 'text'],
                ['od.obj_id', 'text']
            ],
            false
        )} $joinLpSettings
            WHERE il_cert_template.obj_type = %s
            AND il_cert_template.currently_active = %s
            " . $whereLpSettings;
        $query = $this->database->queryF(
            $sql,
            ['text', 'text', 'integer'],
            ['crs', 'crs', 1]
        );

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $result[] = $this->createCertificateTemplate($row);
        }

        $this->logger->debug(sprintf(
            'END - All active course certificate templates with disabled learning progress: "%s"',
            json_encode($result, JSON_THROW_ON_ERROR)
        ));

        return $result;
    }

    /**
     * @throws ilException
     */
    public function fetchFirstCreatedTemplate(int $objId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch first create certificate template for object: "%s"', $objId));

        $this->database->setLimit(1, 0);

        $sql = 'SELECT * FROM il_cert_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
ORDER BY id ASC ';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('END - Found first create certificate template for object: "%s"', $objId));

            return $this->createCertificateTemplate($row);
        }

        throw new ilException('No matching template found. MAY missing DBUpdate. Please check if the correct version is installed.');
    }

    private function deactivatePreviousTemplates(int $objId): void
    {
        $this->logger->debug(sprintf('START - Deactivate previous certificate template for object: "%s"', $objId));

        $sql = '
UPDATE il_cert_template
SET currently_active = 0
WHERE obj_id = ' . $this->database->quote($objId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf('END - Certificate template deactivated for object: "%s"', $objId));
    }

    /**
     * @param array<string, mixed> $row
     */
    private function createCertificateTemplate(array $row): ilCertificateTemplate
    {
        return new ilCertificateTemplate(
            (int) $row['obj_id'],
            $row['obj_type'],
            $row['certificate_content'],
            $row['certificate_hash'],
            $row['template_values'],
            (int) $row['version'],
            $row['ilias_version'],
            (int) $row['created_timestamp'],
            (bool) $row['currently_active'],
            (string) $row['background_image_path'],
            (string) $row['thumbnail_image_path'],
            isset($row['id']) ? (int) $row['id'] : null
        );
    }
}
