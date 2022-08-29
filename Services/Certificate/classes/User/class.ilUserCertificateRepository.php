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
 */
class ilUserCertificateRepository
{
    private ilDBInterface $database;
    private ilLogger $logger;
    private string $defaultTitle;

    public function __construct(
        ?ilDBInterface $database = null,
        ?ilLogger $logger = null,
        ?string $defaultTitle = null
    ) {
        if (null === $database) {
            global $DIC;
            $database = $DIC->database();
        }
        $this->database = $database;

        if (null === $logger) {
            global $DIC;
            $logger = $DIC->logger()->cert();
        }
        $this->logger = $logger;

        if (null === $defaultTitle) {
            global $DIC;
            $defaultTitle = $DIC->language()->txt('certificate_no_object_title');
        }
        $this->defaultTitle = $defaultTitle;
    }

    /**
     * @param ilUserCertificate $userCertificate
     * @return ilUserCertificate
     * @throws ilDatabaseException
     */
    public function save(ilUserCertificate $userCertificate): ilUserCertificate
    {
        $this->logger->debug('START - saving of user certificate');

        $version = (int) $this->fetchLatestVersion($userCertificate->getObjId(), $userCertificate->getUserId());
        ++$version;

        $id = $this->database->nextId('il_cert_user_cert');

        $objId = $userCertificate->getObjId();
        $userId = $userCertificate->getUserId();

        $this->deactivatePreviousCertificates($objId, $userId);

        $columns = [
            'id' => ['integer', $id],
            'pattern_certificate_id' => ['integer', $userCertificate->getPatternCertificateId()],
            'obj_id' => ['integer', $objId],
            'obj_type' => ['text', $userCertificate->getObjType()],
            'usr_id' => ['integer', $userId],
            'user_name' => ['text', $userCertificate->getUserName()],
            'acquired_timestamp' => ['integer', $userCertificate->getAcquiredTimestamp()],
            'certificate_content' => ['clob', $userCertificate->getCertificateContent()],
            'template_values' => ['clob', $userCertificate->getTemplateValues()],
            'valid_until' => ['integer', $userCertificate->getValidUntil()],
            'version' => ['integer', $version],
            'ilias_version' => ['text', $userCertificate->getIliasVersion()],
            'currently_active' => ['integer', (int) $userCertificate->isCurrentlyActive()],
            'background_image_path' => ['text', $userCertificate->getBackgroundImagePath()],
            'thumbnail_image_path' => ['text', $userCertificate->getThumbnailImagePath()]
        ];

        $this->logger->debug(sprintf(
            'END - Save certificate with following values: %s',
            json_encode($columns, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        ));

        $this->database->insert('il_cert_user_cert', $columns);

        return $userCertificate->withId($id)->withVersion($version);
    }

    /**
     * @param int $userId
     * @return ilUserCertificatePresentation[]
     */
    public function fetchActiveCertificates(int $userId): array
    {
        $this->logger->debug(sprintf('START - Fetching all active certificates for user: "%s"', $userId));

        $sql = '
SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.obj_type,
  il_cert_user_cert.usr_id,
  il_cert_user_cert.user_name,
  il_cert_user_cert.acquired_timestamp,
  il_cert_user_cert.certificate_content,
  il_cert_user_cert.template_values,
  il_cert_user_cert.valid_until,
  il_cert_user_cert.version,
  il_cert_user_cert.ilias_version,
  il_cert_user_cert.currently_active,
  il_cert_user_cert.background_image_path,
  il_cert_user_cert.id,
  il_cert_user_cert.thumbnail_image_path,
  COALESCE(object_data.title, object_data_del.title, ' . $this->database->quote($this->defaultTitle, 'text') . ') AS title
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
WHERE usr_id = ' . $this->database->quote($userId, 'integer') . '
AND currently_active = 1';

        $query = $this->database->query($sql);

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $userCertificate = $this->createUserCertificate($row);

            $presentation = new ilUserCertificatePresentation(
                (int) $row['obj_id'],
                (string) $row['obj_type'],
                $userCertificate,
                $row['title'],
                ''
            );
            $result[] = $presentation;
        }

        $this->logger->debug(sprintf('Actual results: "%s"', json_encode($result, JSON_THROW_ON_ERROR)));
        $this->logger->debug(sprintf(
            'END - All active certificates for user: "%s" total: "%s"',
            $userId,
            count($result)
        ));

        return $result;
    }

    /**
     * @param int $userId
     * @param int $startTimestamp
     * @param int $endTimeStamp
     * @return ilUserCertificatePresentation[]
     */
    public function fetchActiveCertificatesInIntervalForPresentation(
        int $userId,
        int $startTimestamp,
        int $endTimeStamp
    ): array {
        $this->logger->debug(sprintf('START - Fetching all active certificates for user: "%s"', $userId));

        $sql = '
SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.obj_type,
  il_cert_user_cert.usr_id,
  il_cert_user_cert.user_name,
  il_cert_user_cert.acquired_timestamp,
  il_cert_user_cert.certificate_content,
  il_cert_user_cert.template_values,
  il_cert_user_cert.valid_until,
  il_cert_user_cert.version,
  il_cert_user_cert.ilias_version,
  il_cert_user_cert.currently_active,
  il_cert_user_cert.background_image_path,
  il_cert_user_cert.id,
  il_cert_user_cert.thumbnail_image_path,
  COALESCE(object_data.title, object_data_del.title, ' . $this->database->quote($this->defaultTitle, 'text') . ') AS title
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
WHERE usr_id = ' . $this->database->quote($userId, 'integer') . '
AND currently_active = 1
AND acquired_timestamp >= ' . $this->database->quote($startTimestamp, 'integer') . '
AND acquired_timestamp <= ' . $this->database->quote($endTimeStamp, 'integer');

        $query = $this->database->query($sql);

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $userCertificate = $this->createUserCertificate($row);

            $presentation = new ilUserCertificatePresentation(
                (int) $row['obj_id'],
                (string) $row['obj_type'],
                $userCertificate,
                $row['title'],
                ''
            );
            $result[] = $presentation;
        }

        $this->logger->debug(sprintf('Actual results: "%s"', json_encode($result, JSON_THROW_ON_ERROR)));
        $this->logger->debug(sprintf(
            'END - All active certificates for user: "%s" total: "%s"',
            $userId,
            count($result)
        ));

        return $result;
    }

    /**
     * @param int $userId
     * @param int $objectId
     * @return ilUserCertificate
     * @throws ilException
     */
    public function fetchActiveCertificate(int $userId, int $objectId): ilUserCertificate
    {
        $this->logger->debug(sprintf(
            'START - Fetching all active certificates for user: "%s" and object: "%s"',
            $userId,
            $objectId
        ));

        $sql = 'SELECT *
FROM il_cert_user_cert
WHERE usr_id = ' . $this->database->quote($userId, 'integer') . '
AND obj_id = ' . $this->database->quote($objectId, 'integer') . '
AND currently_active = 1';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Active certificate values: %s', json_encode($row, JSON_THROW_ON_ERROR)));

            $this->logger->debug(sprintf(
                'END -Found active user certificate for user: "%s" and object: "%s"',
                $userId,
                $objectId
            ));

            return $this->createUserCertificate($row);
        }

        throw new ilException(sprintf(
            'There is no active entry for user id: "%s" and object id: "%s"',
            $userId,
            $objectId
        ));
    }

    /**
     * @param int $userId
     * @param int $objectId
     * @return ilUserCertificatePresentation
     * @throws ilException
     */
    public function fetchActiveCertificateForPresentation(int $userId, int $objectId): ilUserCertificatePresentation
    {
        $this->logger->debug(sprintf(
            'START - Fetching all active certificates for user: "%s" and object: "%s"',
            $userId,
            $objectId
        ));

        $sql = 'SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.obj_type,
  il_cert_user_cert.usr_id,
  il_cert_user_cert.user_name,
  il_cert_user_cert.acquired_timestamp,
  il_cert_user_cert.certificate_content,
  il_cert_user_cert.template_values,
  il_cert_user_cert.valid_until,
  il_cert_user_cert.version,
  il_cert_user_cert.ilias_version,
  il_cert_user_cert.currently_active,
  il_cert_user_cert.background_image_path,
  il_cert_user_cert.id,
  il_cert_user_cert.thumbnail_image_path,
  usr_data.lastname,
  COALESCE(object_data.title, object_data_del.title, ' . $this->database->quote($this->defaultTitle, 'text') . ') AS title
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
LEFT JOIN usr_data ON usr_data.usr_id = il_cert_user_cert.usr_id
WHERE il_cert_user_cert.usr_id = ' . $this->database->quote($userId, 'integer') . '
AND il_cert_user_cert.obj_id = ' . $this->database->quote($objectId, 'integer') . '
AND il_cert_user_cert.currently_active = 1';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Active certificate values: %s', json_encode($row, JSON_THROW_ON_ERROR)));

            $this->logger->debug(sprintf(
                'END -Found active user certificate for user: "%s" and object: "%s"',
                $userId,
                $objectId
            ));

            $userCertificate = $this->createUserCertificate($row);
            return new ilUserCertificatePresentation(
                (int) $row['obj_id'],
                (string) $row['obj_type'],
                $userCertificate,
                $row['title'],
                '',
                $row['lastname']
            );
        }

        throw new ilException(sprintf(
            'There is no active entry for user id: "%s" and object id: "%s"',
            $userId,
            $objectId
        ));
    }

    /**
     * @param int $userId
     * @param string $type
     * @return ilUserCertificatePresentation[]
     */
    public function fetchActiveCertificatesByTypeForPresentation(int $userId, string $type): array
    {
        $this->logger->debug(sprintf(
            'START - Fetching all active certificates for user: "%s" and type: "%s"',
            $userId,
            $type
        ));

        $sql = 'SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.obj_type,
  il_cert_user_cert.usr_id,
  il_cert_user_cert.user_name,
  il_cert_user_cert.acquired_timestamp,
  il_cert_user_cert.certificate_content,
  il_cert_user_cert.template_values,
  il_cert_user_cert.valid_until,
  il_cert_user_cert.version,
  il_cert_user_cert.ilias_version,
  il_cert_user_cert.currently_active,
  il_cert_user_cert.background_image_path,
  il_cert_user_cert.id,
  il_cert_user_cert.thumbnail_image_path,
  COALESCE(object_data.title, object_data_del.title, ' . $this->database->quote($this->defaultTitle, 'text') . ') AS title
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
WHERE usr_id = ' . $this->database->quote($userId, 'integer') . '
 AND obj_type = ' . $this->database->quote($type, 'text') . '
 AND currently_active = 1';

        $query = $this->database->query($sql);

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $userCertificate = $this->createUserCertificate($row);

            $presentation = new ilUserCertificatePresentation(
                (int) $row['obj_id'],
                (string) $row['obj_type'],
                $userCertificate,
                $row['title'],
                ''
            );
            $result[] = $presentation;
        }

        $this->logger->debug(sprintf(
            'END - Fetching all active certificates for user: "%s" and type: "%s"',
            $userId,
            $type
        ));

        return $result;
    }

    /**
     * @param int $id
     * @return ilUserCertificate
     * @throws ilException
     */
    public function fetchCertificate(int $id): ilUserCertificate
    {
        $this->logger->debug(sprintf('START - Fetch certificate by id: "%s"', $id));

        $sql = 'SELECT * FROM il_cert_user_cert WHERE id = ' . $this->database->quote($id, 'integer');

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Fetched certificate: "%s"', json_encode($row, JSON_THROW_ON_ERROR)));

            $this->logger->debug(sprintf('END - Fetch certificate by id: "%s"', $id));

            return $this->createUserCertificate($row);
        }

        throw new ilException('No certificate found for user certificate id: ' . $id);
    }

    /**
     * @param int   $userId
     * @param int[] $objectIds
     * @return int[]
     */
    public function fetchObjectIdsWithCertificateForUser(int $userId, array $objectIds): array
    {
        $this->logger->debug(sprintf(
            'START - Fetch certificate for user("%s") and ids: "%s"',
            $userId,
            json_encode($objectIds, JSON_THROW_ON_ERROR)
        ));

        if (0 === count($objectIds)) {
            return [];
        }

        $inStatementObjectIds = $this->database->in(
            'obj_id',
            $objectIds,
            false,
            'integer'
        );

        $sql = 'SELECT obj_id FROM il_cert_user_cert
 WHERE usr_id = ' . $this->database->quote($userId, 'integer') .
            ' AND ' . $inStatementObjectIds .
            ' AND currently_active = ' . $this->database->quote(1, 'integer');

        $query = $this->database->query($sql);

        $result = [];

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Fetched certificate: "%s"', json_encode($row, JSON_THROW_ON_ERROR)));
            $result[] = (int) $row['obj_id'];
        }

        return $result;
    }

    /**
     * @param int $objectId
     * @return int[]
     */
    public function fetchUserIdsWithCertificateForObject(int $objectId): array
    {
        $this->logger->debug(sprintf('START - Fetch certificate for object("%s")"', $objectId));

        $sql = 'SELECT usr_id FROM il_cert_user_cert
WHERE obj_id = ' . $this->database->quote($objectId, 'integer') . '
 AND currently_active = ' . $this->database->quote(1, 'integer');

        $query = $this->database->query($sql);

        $result = [];

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Fetched certificate: "%s"', json_encode($row, JSON_THROW_ON_ERROR)));
            $result[] = (int) $row['usr_id'];
        }

        return $result;
    }

    public function deleteUserCertificates(int $userId): void
    {
        $this->logger->debug(sprintf('START - Delete certificate for user("%s")"', $userId));

        $sql = 'DELETE FROM il_cert_user_cert WHERE usr_id = ' . $this->database->quote($userId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf('END - Successfully deleted certificate for user("%s")"', $userId));
    }

    /**
     * @param int $objId
     * @param int $userId
     * @return ilUserCertificate[]
     */
    private function fetchCertificatesOfObject(int $objId, int $userId): array
    {
        $this->logger->debug(sprintf(
            'START -  fetching all certificates of object(user id: "%s", object id: "%s")',
            $userId,
            $objId
        ));

        $sql = 'SELECT * FROM il_cert_user_cert
WHERE usr_id = ' . $this->database->quote($userId, 'integer') . '
AND obj_id = ' . $this->database->quote($objId, 'integer');

        $query = $this->database->query($sql);

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf(
                'Certificate found: "%s")',
                json_encode($row, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
            ));

            $this->logger->debug(sprintf('Certificate: "%s"', json_encode($row, JSON_THROW_ON_ERROR)));

            $result[] = $this->createUserCertificate($row);
        }

        $this->logger->debug(sprintf(
            'END -  fetching all certificates of object(user id: "%s", object id: "%s")',
            $userId,
            $objId
        ));

        return $result;
    }

    private function fetchLatestVersion(int $objId, int $userId): string
    {
        $this->logger->debug(sprintf(
            'START -  fetching of latest certificates of object(user id: "%s", object id: "%s")',
            $userId,
            $objId
        ));

        $templates = $this->fetchCertificatesOfObject($objId, $userId);

        $version = 0;
        foreach ($templates as $template) {
            if ($template->getVersion() > $version) {
                $version = $template->getVersion();
            }
        }

        $this->logger->debug(sprintf(
            'END -  fetching of latest certificates of object(user id: "%s", object id: "%s") with version "%s"',
            $userId,
            $objId,
            $version
        ));

        return (string) $version;
    }

    private function deactivatePreviousCertificates(int $objId, int $userId): void
    {
        $this->logger->debug(sprintf(
            'START - deactivating previous certificates for user id: "%s" and object id: "%s"',
            $userId,
            $objId
        ));

        $sql = '
UPDATE il_cert_user_cert
SET currently_active = 0
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND  usr_id = ' . $this->database->quote($userId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf(
            'END - deactivating previous certificates for user id: "%s" and object id: "%s"',
            $userId,
            $objId
        ));
    }

    /**
     * @param array<string, mixed> $row
     * @return ilUserCertificate
     */
    private function createUserCertificate(array $row): ilUserCertificate
    {
        return new ilUserCertificate(
            (int) $row['pattern_certificate_id'],
            (int) $row['obj_id'],
            $row['obj_type'],
            (int) $row['usr_id'],
            $row['user_name'],
            (int) $row['acquired_timestamp'],
            $row['certificate_content'],
            $row['template_values'],
            (int) $row['valid_until'],
            (int) $row['version'],
            $row['ilias_version'],
            (bool) $row['currently_active'],
            (string) $row['background_image_path'],
            (string) $row['thumbnail_image_path'],
            isset($row['id']) ? (int) $row['id'] : null
        );
    }
}
