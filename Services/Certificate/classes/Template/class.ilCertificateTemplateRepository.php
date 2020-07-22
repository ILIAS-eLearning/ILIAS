<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 *
 * Repository that allows interaction with the database
 * in the context of certificate templates.
 */
class ilCertificateTemplateRepository
{
    /**
     * @var \ilDB
     */
    private $database;

    /**
     * @var \ilLogger
     */
    private $logger;

    /**
     * @var \ilObjectDataCache|mixed
     */
    private $objectDataCache;

    /**
     * @param \ilDBInterface $database
     * @param \ilLogger $logger
     * @param \ilObjectDataCache|null $objectDataCache
     */
    public function __construct(
        \ilDBInterface $database,
        \ilLogger $logger = null,
        \ilObjectDataCache $objectDataCache = null
    ) {
        $this->database = $database;

        if (null === $logger) {
            global $DIC;
            $logger = $logger = $DIC->logger()->cert();
        }
        $this->logger = $logger;

        if (null === $objectDataCache) {
            global $DIC;
            $objectDataCache = $DIC['ilObjDataCache'];
        }
        $this->objectDataCache = $objectDataCache;
    }

    /**
     * @param ilCertificateTemplate $certificateTemplate
     * @throws ilDatabaseException
     */
    public function save(ilCertificateTemplate $certificateTemplate)
    {
        $this->logger->info('START - Save new certificate template');

        $objId = $certificateTemplate->getObjId();

        $id = $this->database->nextId('il_cert_template');

        $this->deactivatePreviousTemplates($objId);

        $columns = array(
            'id' => array('integer', $id),
            'obj_id' => array('integer', $objId),
            'obj_type' => array('text', $certificateTemplate->getObjType()),
            'certificate_content' => array('clob', $certificateTemplate->getCertificateContent()),
            'certificate_hash' => array('text', $certificateTemplate->getCertificateHash()),
            'template_values' => array('clob', $certificateTemplate->getTemplateValues()),
            'version' => array('integer', $certificateTemplate->getVersion()),
            'ilias_version' => array('text', $certificateTemplate->getIliasVersion()),
            'created_timestamp' => array('integer', $certificateTemplate->getCreatedTimestamp()),
            'currently_active' => array('integer', (integer) $certificateTemplate->isCurrentlyActive()),
            'background_image_path' => array('text', $certificateTemplate->getBackgroundImagePath()),
            'deleted' => array('integer', (integer) $certificateTemplate->isDeleted()),
            'thumbnail_image_path' => array('text', $certificateTemplate->getThumbnailImagePath())
        );

        $this->database->insert('il_cert_template', $columns);

        $this->logger->info('END - certificate template saved with columns: ', json_encode($columns));
    }


    public function updateActivity(ilCertificateTemplate $certificateTemplate, bool $currentlyActive)
    {
        $sql = 'UPDATE il_cert_template SET currently_active = ' . $this->database->quote($currentlyActive, 'integer') .
        ' WHERE id = ' . $this->database->quote($certificateTemplate->getId(), 'integer');

        return $this->database->manipulate($sql);
    }

    public function fetchTemplate(int $templateId) : ilCertificateTemplate
    {
        $this->logger->info(sprintf('START - Fetch certificate template with id: "%s"', $templateId));

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
     * @param int $objId
     * @return \ilCertificateTemplate[]
     */
    public function fetchCertificateTemplatesByObjId(int $objId) : array
    {
        $this->logger->info(sprintf('START - Fetch multiple certificate templates for object: "%s"', $objId));

        $result = array();

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

        $this->logger->info(sprintf('END - Fetching of certificate templates for object: "%s" with "%s" results', $objId, count($result)));

        return $result;
    }

    /**
     * @param int $objId
     * @return \ilCertificateTemplate
     */
    public function fetchCurrentlyUsedCertificate(int $objId) : \ilCertificateTemplate
    {
        $this->logger->info(sprintf('START - Fetch currently active certificate template for object: "%s"', $objId));

        $this->database->setLimit(1);

        $sql = '
SELECT * FROM il_cert_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND deleted = 0
ORDER BY id DESC
';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->info(sprintf('END - Found active certificate for: "%s"', $objId));

            return $this->createCertificateTemplate($row);
        }

        $this->logger->info(sprintf('END - Found NO active certificate for: "%s"', $objId));

        return new ilCertificateTemplate(
            $objId,
            $this->objectDataCache->lookUpType($objId),
            '',
            '',
            '',
            0,
            0,
            0,
            false,
            '',
            ''
        );
    }

    /**
     * @param int $objId
     * @return \ilCertificateTemplate
     * @throws ilException
     */
    public function fetchCurrentlyActiveCertificate(int $objId) : \ilCertificateTemplate
    {
        $this->logger->info(sprintf('START - Fetch currently active certificate template for object: "%s"', $objId));

        $sql = '
SELECT * FROM il_cert_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND deleted = 0
AND currently_active = 1
';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->info(sprintf('END - Found active certificate for: "%s"', $objId));

            return $this->createCertificateTemplate($row);
        }

        throw new ilException((sprintf('NO active certificate template found for: "%s"', $objId)));
    }

    /**
     * Fetch latest created certificate EVEN IF it is deleted
     *
     * @param int $objId
     * @return \ilCertificateTemplate
     */
    public function fetchPreviousCertificate(int $objId) : \ilCertificateTemplate
    {
        $this->logger->info(sprintf('START - Fetch previous active certificate template for object: "%s"', $objId));

        $templates = $this->fetchCertificateTemplatesByObjId($objId);

        $resultTemplate = new ilCertificateTemplate(
            $objId,
            $this->objectDataCache->lookUpType($objId),
            '',
            '',
            '',
            0,
            0,
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

        $this->logger->info(sprintf('Latest version active certificate template for object: "%s"', $objId));

        return $resultTemplate;
    }

    /**
     * @param int $templateId
     * @param int $objectId
     * @throws ilDatabaseException
     */
    public function deleteTemplate(int $templateId, int $objectId)
    {
        $this->logger->info(sprintf('START - Set deleted flag for certificate template("%s") for object: "%s"', $templateId, $objectId));

        $sql = '
UPDATE il_cert_template
SET deleted = 1, currently_active = 0
WHERE id = ' . $this->database->quote($templateId, 'integer') . '
AND obj_id = ' . $this->database->quote($objectId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->info(sprintf('END - Deleted flag set fo certificate template("%s") for object: "%s"', $templateId, $objectId));
    }

    /**
     * @param int $objId
     * @return \ilCertificateTemplate
     * @throws ilDatabaseException
     */
    public function activatePreviousCertificate(int $objId) : \ilCertificateTemplate
    {
        $this->logger->info(sprintf('START - Activate previous certificate template for object: "%s"', $objId));

        $certificates = $this->fetchCertificateTemplatesByObjId($objId);

        /** @var ilCertificateTemplate $previousCertificate */
        $previousCertificate = null;
        foreach ($certificates as $certificate) {
            if (null === $previousCertificate) {
                $previousCertificate = $certificate;
            } elseif ((int) $certificate->getVersion() > (int) $previousCertificate->getVersion()) {
                $previousCertificate = $certificate;
            }
        }

        $sql = 'UPDATE il_cert_template
SET currently_active = 1
WHERE id = ' . $this->database->quote($previousCertificate->getId(), 'integer');

        $this->database->manipulate($sql);

        $this->logger->info(sprintf('END - Previous certificate updated for object: "%s"', $objId));

        return $previousCertificate;
    }

    /**
     * @param string $type
     * @return ilCertificateTemplate[]
     */
    public function fetchActiveTemplatesByType(string $type) : array
    {
        $this->logger->info(sprintf('START - Fetch all active certificate templates for object type: "%s"', $type));

        $sql = 'SELECT * FROM il_cert_template WHERE obj_type = ' . $this->database->quote($type, 'text') . '
AND currently_active = 1';
        $query = $this->database->query($sql);

        $result = array();
        while ($row = $this->database->fetchAssoc($query)) {
            $result[] = $this->createCertificateTemplate($row);
        }

        $this->logger->info(sprintf('END - All certificate templates for object type: "%s": "%s"', $type, json_encode($result)));

        return $result;
    }

    /**
     * @param int $objId
     * @return \ilCertificateTemplate
     * @throws \ilException
     */
    public function fetchFirstCreatedTemplate(int $objId) : \ilCertificateTemplate
    {
        $this->logger->info(sprintf('START - Fetch first create certificate template for object: "%s"', $objId));

        $this->database->setLimit(1, 0);

        $sql = 'SELECT * FROM il_cert_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
ORDER BY id ASC ';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->info(sprintf('END - Found first create certificate template for object: "%s"', $objId));

            return $this->createCertificateTemplate($row);
        }

        throw new ilException('No matching template found. MAY missing DBUpdate. Please check if the correct version is installed.');
    }

    /**
     * @param int $objId
     * @throws ilDatabaseException
     */
    private function deactivatePreviousTemplates(int $objId)
    {
        $this->logger->info(sprintf('START - Deactivate previous certificate template for object: "%s"', $objId));

        $sql = '
UPDATE il_cert_template
SET currently_active = 0
WHERE obj_id = ' . $this->database->quote($objId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->info(sprintf('END - Certificate template deactivated for object: "%s"', $objId));
    }

    /**
     * @param array $row
     * @return ilCertificateTemplate
     */
    private function createCertificateTemplate(array $row) : ilCertificateTemplate
    {
        return new ilCertificateTemplate(
            $row['obj_id'],
            $row['obj_type'],
            $row['certificate_content'],
            $row['certificate_hash'],
            $row['template_values'],
            $row['version'],
            $row['ilias_version'],
            $row['created_timestamp'],
            (boolean) $row['currently_active'],
            $row['background_image_path'],
            $row['thumbnail_image_path'],
            $row['id']
        );
    }
}
