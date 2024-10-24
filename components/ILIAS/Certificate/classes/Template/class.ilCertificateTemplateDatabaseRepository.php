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
 * Repository that allows interaction with the database
 * in the context of certificate templates.
 */
class ilCertificateTemplateDatabaseRepository implements ilCertificateTemplateRepository
{
    public const TABLE_NAME = 'il_cert_template';
    private readonly ilLogger $logger;
    private readonly ilObjectDataCache $objectDataCache;

    public function __construct(
        private readonly ilDBInterface $database,
        ilLogger $logger = null,
        ilObjectDataCache $objectDataCache = null
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

        $id = $this->database->nextId(self::TABLE_NAME);

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
            'deleted' => ['integer', (int) $certificateTemplate->isDeleted()],
            'background_image_ident' => [ilDBConstants::T_TEXT, $certificateTemplate->getBackgroundImageIdentification()],
            'thumbnail_image_ident' => [ilDBConstants::T_TEXT, $certificateTemplate->getThumbnailImageIdentification()]
        ];

        if (
            $this->database->tableColumnExists('il_cert_user_cert', 'background_image_path') &&
            $this->database->tableColumnExists('il_cert_user_cert', 'thumbnail_image_path')
        ) {
            $columns['background_image_path'] = [ilDBConstants::T_TEXT, $certificateTemplate->getBackgroundImagePath()];
            $columns['thumbnail_image_path'] = [ilDBConstants::T_TEXT, $certificateTemplate->getThumbnailImagePath()];
        }

        $this->database->insert(self::TABLE_NAME, $columns);

        $this->logger->debug(
            sprintf(
                'END - certificate template saved with columns: %s',
                json_encode($columns, JSON_THROW_ON_ERROR)
            )
        );
    }

    public function updateActivity(ilCertificateTemplate $certificateTemplate, bool $currentlyActive): int
    {
        $sql = 'UPDATE ' . self::TABLE_NAME . ' SET currently_active = ' . $this->database->quote(
            $currentlyActive,
            ilDBConstants::T_INTEGER
        ) .
            ' WHERE id = ' . $this->database->quote($certificateTemplate->getId(), ilDBConstants::T_INTEGER);

        return $this->database->manipulate($sql);
    }

    public function fetchTemplate(int $templateId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch certificate template with id: "%s"', $templateId));

        $sql = '
            SELECT * FROM
            ' . self::TABLE_NAME . '
            WHERE id = ' . $this->database->quote($templateId, ilDBConstants::T_INTEGER) . '
            ORDER BY version ASC
        ';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            return $this->createCertificateTemplate($row);
        }

        throw new ilCouldNotFindCertificateTemplate(sprintf('No template with id "%s" found', $templateId));
    }

    /**
     * @return ilCertificateTemplate[]
     */
    public function fetchCertificateTemplatesByObjId(int $objId): array
    {
        $this->logger->debug(sprintf('START - Fetch multiple certificate templates for object: "%s"', $objId));

        $result = [];

        $sql = '
            SELECT * FROM ' .
            self::TABLE_NAME . ' ' .
            'WHERE obj_id = ' . $this->database->quote($objId, ilDBConstants::T_INTEGER) . ' ' .
            'AND deleted = 0 ' .
            'ORDER BY version ASC'
        ;

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $result[] = $this->createCertificateTemplate($row);
        }

        $this->logger->debug(
            sprintf(
                'END - Fetching of certificate templates for object: "%s" with "%s" results',
                $objId,
                count($result)
            )
        );

        return $result;
    }

    public function fetchCurrentlyUsedCertificate(int $objId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch currently active certificate template for object: "%s"', $objId));

        $this->database->setLimit(1);

        $sql = '
            SELECT * FROM ' . self::TABLE_NAME . '
            WHERE obj_id = ' . $this->database->quote($objId, ilDBConstants::T_INTEGER) . '
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
            '0',
            0,
            false,
            '',
            '',
            '',
            ''
        );
    }

    /**
     * @throws ilCouldNotFindCertificateTemplate
     */
    public function fetchCurrentlyActiveCertificate(int $objId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch currently active certificate template for object: "%s"', $objId));

        $sql = '
            SELECT * FROM ' . self::TABLE_NAME . '
            WHERE obj_id = ' . $this->database->quote($objId, ilDBConstants::T_INTEGER) . '
            AND deleted = 0
            AND currently_active = 1
        ';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('END - Found active certificate for: "%s"', $objId));

            return $this->createCertificateTemplate($row);
        }

        throw new ilCouldNotFindCertificateTemplate(sprintf('NO active certificate template found for: "%s"', $objId));
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
            '0',
            0,
            true,
            '',
            '',
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
        $this->logger->debug(
            sprintf(
                'START - Set deleted flag for certificate template("%s") for object: "%s"',
                $templateId,
                $objectId
            )
        );

        $sql = '
            UPDATE ' . self::TABLE_NAME . '
            SET deleted = 1, currently_active = 0
            WHERE id = ' . $this->database->quote($templateId, ilDBConstants::T_INTEGER) . '
            AND obj_id = ' . $this->database->quote($objectId, ilDBConstants::T_INTEGER);

        $this->database->manipulate($sql);

        $this->logger->debug(
            sprintf(
                'END - Deleted flag set fo certificate template("%s") for object: "%s"',
                $templateId,
                $objectId
            )
        );
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

        $sql = 'UPDATE ' . self::TABLE_NAME . '
            SET currently_active = 1
            WHERE id = ' . $this->database->quote($previousCertificate->getId(), ilDBConstants::T_INTEGER);

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf('END - Previous certificate updated for object: "%s"', $objId));

        return $previousCertificate;
    }

    public function fetchActiveCertificateTemplatesForCoursesWithDisabledLearningProgress(
        bool $isGlobalLpEnabled,
        int $forRefId = null
    ): array {
        $this->logger->debug(
            'START - Fetch all active course certificate templates with disabled learning progress: "%s"'
        );

        $joinLpSettings = '';
        $whereLpSettings = '';
        $onSettingsForRefId = '';

        if ($isGlobalLpEnabled) {
            $joinLpSettings = 'LEFT JOIN ut_lp_settings uls ON uls.obj_id = od.obj_id';
            $whereLpSettings = sprintf(
                'AND (uls.u_mode IS NULL OR uls.u_mode = %s)',
                $this->database->quote(ilLPObjSettings::LP_MODE_DEACTIVATED, 'integer')
            );
        }

        if (is_int($forRefId)) {
            $onSettingsForRefId = " AND settings.value IS NOT NULL AND (JSON_CONTAINS(settings.value, '\"{$forRefId}\"', '$') = 1 OR JSON_CONTAINS(settings.value, '{$forRefId}', '$')) ";
        }

        $sql = '
            SELECT ' . self::TABLE_NAME . '.*
            FROM ' . self::TABLE_NAME . '
            INNER JOIN object_data od ON od.obj_id = ' . self::TABLE_NAME . ".obj_id
            INNER JOIN settings ON settings.module = %s AND settings.keyword = {$this->database->concat(
            [
                [$this->database->quote('cert_subitems_', 'text'), 'text'],
                ['od.obj_id', 'text']
            ],
            false
        )} $onSettingsForRefId $joinLpSettings
            WHERE " . self::TABLE_NAME . '.obj_type = %s
            AND ' . self::TABLE_NAME . '.currently_active = %s
            ' . $whereLpSettings;
        $query = $this->database->queryF(
            $sql,
            ['text', 'text', 'integer'],
            ['crs', 'crs', 1]
        );

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $result[] = $this->createCertificateTemplate($row);
        }

        $this->logger->debug(
            sprintf(
                'END - All active course certificate templates with disabled learning progress: "%s"',
                json_encode($result, JSON_THROW_ON_ERROR)
            )
        );

        return $result;
    }

    /**
     * @throws ilCouldNotFindCertificateTemplate
     */
    public function fetchFirstCreatedTemplate(int $objId): ilCertificateTemplate
    {
        $this->logger->debug(sprintf('START - Fetch first create certificate template for object: "%s"', $objId));

        $this->database->setLimit(1, 0);

        $sql = '
            SELECT * FROM ' . self::TABLE_NAME . '
            WHERE obj_id = ' . $this->database->quote($objId, ilDBConstants::T_INTEGER) . '
            ORDER BY id ASC 
            ';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('END - Found first create certificate template for object: "%s"', $objId));

            return $this->createCertificateTemplate($row);
        }

        throw new ilCouldNotFindCertificateTemplate('No matching template found. MAY missing DBUpdate. Please check if the correct version is installed.');
    }

    private function deactivatePreviousTemplates(int $objId): void
    {
        $this->logger->debug(sprintf('START - Deactivate previous certificate template for object: "%s"', $objId));

        $sql = '
            UPDATE ' . self::TABLE_NAME . '
            SET currently_active = 0
            WHERE obj_id = ' . $this->database->quote($objId, ilDBConstants::T_INTEGER);

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf('END - Certificate template deactivated for object: "%s"', $objId));
    }

    public function updateDefaultBackgroundImagePaths(string $old_relative_path, string $new_relative_path): void
    {
        $this->logger->debug(
            sprintf(
                'START - Update all default background image paths from "%s" to "%s"',
                $old_relative_path,
                $new_relative_path
            )
        );

        $affected_rows = $this->database->manipulateF(
            'UPDATE ' . self::TABLE_NAME . ' SET background_image_ident = %s ' .
            'WHERE currently_active = 1 AND (background_image_ident = %s OR background_image_ident = %s )',
            [
                'text',
                'text',
                'text'
            ],
            [
                $new_relative_path,
                $old_relative_path,
                '/certificates/default/background.jpg'
            ]
        );

        $this->logger->debug(
            sprintf(
                'END - Updated %s certificate templates using old path',
                $affected_rows
            )
        );
    }

    public function isResourceUsed(string $relative_image_identification): bool
    {
        $this->logger->debug(
            sprintf(
                'START - Checking if any certificate template uses resource id "%s"',
                $relative_image_identification
            )
        );

        $result = $this->database->queryF(
            'SELECT EXISTS(SELECT 1 FROM ' . self::TABLE_NAME . ' WHERE 
            (background_image_ident = %s OR thumbnail_image_ident = %s)
             AND currently_active = 1) AS does_exist',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$relative_image_identification, $relative_image_identification]
        );

        $exists = (bool) ($this->database->fetchAssoc($result)['does_exist'] ?? false);

        $this->logger->debug(
            sprintf(
                'END - Image path "%s" is ' . $exists ? 'in use' : 'unused',
                $relative_image_identification
            )
        );

        return $exists;
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
            (string) ($row['background_image_path'] ?? ''),
            (string) ($row['thumbnail_image_path'] ?? ''),
            (string) ($row['background_image_ident'] ?? ''),
            (string) ($row['thumbnail_image_ident'] ?? ''),
            isset($row['id']) ? (int) $row['id'] : null
        );
    }
}
