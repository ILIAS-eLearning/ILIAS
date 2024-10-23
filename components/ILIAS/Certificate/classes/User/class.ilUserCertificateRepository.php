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

use ILIAS\Certificate\ValueObject\CertificateId;
use ILIAS\Data\Range;
use ILIAS\Data\UUID\Factory;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateRepository
{
    public const TABLE_NAME = 'il_cert_user_cert';

    private readonly ilDBInterface $database;
    private readonly ilLogger $logger;
    private readonly string $defaultTitle;
    private readonly ?Factory $uuid_factory;

    public function __construct(
        ilDBInterface $database = null,
        ilLogger $logger = null,
        string $defaultTitle = null,
        ?Factory $uuid_factory = null,
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

        if (!$uuid_factory) {
            $uuid_factory = new ILIAS\Data\UUID\Factory();
        }
        $this->uuid_factory = $uuid_factory;

    }

    /**
     * @throws ilDatabaseException
     */
    public function save(ilUserCertificate $userCertificate): ilUserCertificate
    {
        $this->logger->debug('START - saving of user certificate');

        $version = (int) $this->fetchLatestVersion($userCertificate->getObjId(), $userCertificate->getUserId());
        ++$version;

        $id = $this->database->nextId(self::TABLE_NAME);

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
            'thumbnail_image_path' => ['text', $userCertificate->getThumbnailImagePath()],
            'background_image_ident' => ['text', $userCertificate->getBackgroundImageIdentification()],
            'thumbnail_image_ident' => ['text', $userCertificate->getThumbnailImageIdentification()],
            'certificate_id' => ['text', $userCertificate->getCertificateId()->asString()]
        ];

        $this->logger->debug(
            sprintf(
                'END - Save certificate with following values: %s',
                json_encode($columns, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
            )
        );

        $this->database->insert(self::TABLE_NAME, $columns);

        return $userCertificate->withId($id)->withVersion($version);
    }

    /**
     * @return ilUserCertificatePresentation[]
     */
    public function fetchActiveCertificates(int $userId): array
    {
        $this->logger->debug(sprintf('START - Fetching all active certificates for user: "%s"', $userId));

        $sql = '
SELECT ' . self::TABLE_NAME . '.*, 
il_cert_user_cert.certificate_id,
  COALESCE(object_data.title, object_data_del.title, ' . $this->database->quote($this->defaultTitle, 'text') . ') AS title
FROM ' . self::TABLE_NAME . '
LEFT JOIN object_data ON object_data.obj_id = ' . self::TABLE_NAME . '.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = ' . self::TABLE_NAME . '.obj_id
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
        $this->logger->debug(
            sprintf(
                'END - All active certificates for user: "%s" total: "%s"',
                $userId,
                count($result)
            )
        );

        return $result;
    }

    /**
     * @return ilUserCertificatePresentation[]
     */
    public function fetchActiveCertificatesInIntervalForPresentation(
        int $userId,
        int $startTimestamp,
        int $endTimeStamp
    ): array {
        $this->logger->debug(sprintf('START - Fetching all active certificates for user: "%s"', $userId));

        $sql = '
SELECT ' . self::TABLE_NAME . '.*, 
il_cert_user_cert.certificate_id,
  COALESCE(object_data.title, object_data_del.title, ' . $this->database->quote($this->defaultTitle, 'text') . ') AS title
FROM ' . self::TABLE_NAME . '
LEFT JOIN object_data ON object_data.obj_id = ' . self::TABLE_NAME . '.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = ' . self::TABLE_NAME . '.obj_id
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
        $this->logger->debug(
            sprintf(
                'END - All active certificates for user: "%s" total: "%s"',
                $userId,
                count($result)
            )
        );

        return $result;
    }

    /**
     * @throws ilException
     */
    public function fetchActiveCertificate(int $userId, int $objectId): ilUserCertificate
    {
        $this->logger->debug(
            sprintf(
                'START - Fetching all active certificates for user: "%s" and object: "%s"',
                $userId,
                $objectId
            )
        );

        $sql = 'SELECT *
                FROM ' . self::TABLE_NAME . '
                WHERE usr_id = ' . $this->database->quote($userId, 'integer') . '
                AND obj_id = ' . $this->database->quote($objectId, 'integer') . '
                AND currently_active = 1';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Active certificate values: %s', json_encode($row, JSON_THROW_ON_ERROR)));

            $this->logger->debug(
                sprintf(
                    'END -Found active user certificate for user: "%s" and object: "%s"',
                    $userId,
                    $objectId
                )
            );

            return $this->createUserCertificate($row);
        }

        throw new ilException(
            sprintf('There is no active entry for user id: "%s" and object id: "%s"', $userId, $objectId)
        );
    }

    /**
     * @throws ilException
     */
    public function fetchActiveCertificateForPresentation(int $userId, int $objectId): ilUserCertificatePresentation
    {
        $this->logger->debug(
            sprintf(
                'START - Fetching all active certificates for user: "%s" and object: "%s"',
                $userId,
                $objectId
            )
        );

        $sql = 'SELECT ' . self::TABLE_NAME . '.*,
                  il_cert_user_cert.certificate_id,usr_data.lastname,
                  COALESCE(object_data.title, object_data_del.title, ' . $this->database->quote($this->defaultTitle, 'text') . ') AS title
                FROM ' . self::TABLE_NAME . '
                LEFT JOIN object_data ON object_data.obj_id = ' . self::TABLE_NAME . '.obj_id
                LEFT JOIN object_data_del ON object_data_del.obj_id = ' . self::TABLE_NAME . '.obj_id
                LEFT JOIN usr_data ON usr_data.usr_id = ' . self::TABLE_NAME . '.usr_id
                WHERE ' . self::TABLE_NAME . '.usr_id = ' . $this->database->quote($userId, 'integer') . '
                AND ' . self::TABLE_NAME . '.obj_id = ' . $this->database->quote($objectId, 'integer') . '
                AND ' . self::TABLE_NAME . '.currently_active = 1';

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Active certificate values: %s', json_encode($row, JSON_THROW_ON_ERROR)));

            $this->logger->debug(
                sprintf(
                    'END -Found active user certificate for user: "%s" and object: "%s"',
                    $userId,
                    $objectId
                )
            );

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

        throw new ilException(
            sprintf('There is no active entry for user id: "%s" and object id: "%s"', $userId, $objectId)
        );
    }

    /**
     * @return ilUserCertificatePresentation[]
     */
    public function fetchActiveCertificatesByTypeForPresentation(int $userId, string $type): array
    {
        $this->logger->debug(
            sprintf(
                'START - Fetching all active certificates for user: "%s" and type: "%s"',
                $userId,
                $type
            )
        );

        $sql = 'SELECT ' . self::TABLE_NAME . '.*,
                  il_cert_user_cert.certificate_id,COALESCE(object_data.title, object_data_del.title, ' . $this->database->quote($this->defaultTitle, 'text') . ') AS title
                FROM ' . self::TABLE_NAME . '
                LEFT JOIN object_data ON object_data.obj_id = ' . self::TABLE_NAME . '.obj_id
                LEFT JOIN object_data_del ON object_data_del.obj_id = ' . self::TABLE_NAME . '.obj_id
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

        $this->logger->debug(
            sprintf(
                'END - Fetching all active certificates for user: "%s" and type: "%s"',
                $userId,
                $type
            )
        );

        return $result;
    }

    /**
     * @throws ilException
     */
    public function fetchCertificate(int $id): ilUserCertificate
    {
        $this->logger->debug(sprintf('START - Fetch certificate by id: "%s"', $id));

        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $this->database->quote($id, 'integer');

        $query = $this->database->query($sql);

        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(sprintf('Fetched certificate: "%s"', json_encode($row, JSON_THROW_ON_ERROR)));

            $this->logger->debug(sprintf('END - Fetch certificate by id: "%s"', $id));

            return $this->createUserCertificate($row);
        }

        throw new ilException('No certificate found for user certificate id: ' . $id);
    }

    /**
     * @param  int[] $objectIds
     * @return int[]
     */
    public function fetchObjectIdsWithCertificateForUser(int $userId, array $objectIds): array
    {
        $this->logger->debug(
            sprintf(
                'START - Fetch certificate for user("%s") and ids: "%s"',
                $userId,
                json_encode($objectIds, JSON_THROW_ON_ERROR)
            )
        );

        if ([] === $objectIds) {
            return [];
        }

        $inStatementObjectIds = $this->database->in(
            'obj_id',
            $objectIds,
            false,
            'integer'
        );

        $sql = 'SELECT obj_id FROM ' . self::TABLE_NAME . '
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
     * @return int[]
     */
    public function fetchUserIdsWithCertificateForObject(int $objectId): array
    {
        $this->logger->debug(sprintf('START - Fetch certificate for object("%s")"', $objectId));

        $sql = 'SELECT usr_id FROM ' . self::TABLE_NAME . '
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

        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE usr_id = ' . $this->database->quote($userId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(sprintf('END - Successfully deleted certificate for user("%s")"', $userId));
    }

    /**
     * @return ilUserCertificate[]
     */
    private function fetchCertificatesOfObject(int $objId, int $userId): array
    {
        $this->logger->debug(
            sprintf(
                'START -  fetching all certificates of object(user id: "%s", object id: "%s")',
                $userId,
                $objId
            )
        );

        $sql = 'SELECT * FROM ' . self::TABLE_NAME . '
            WHERE usr_id = ' . $this->database->quote($userId, 'integer') . '
            AND obj_id = ' . $this->database->quote($objId, 'integer');

        $query = $this->database->query($sql);

        $result = [];
        while ($row = $this->database->fetchAssoc($query)) {
            $this->logger->debug(
                sprintf(
                    'Certificate found: "%s")',
                    json_encode($row, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
                )
            );

            $this->logger->debug(sprintf('Certificate: "%s"', json_encode($row, JSON_THROW_ON_ERROR)));

            $result[] = $this->createUserCertificate($row);
        }

        $this->logger->debug(
            sprintf(
                'END -  fetching all certificates of object(user id: "%s", object id: "%s")',
                $userId,
                $objId
            )
        );

        return $result;
    }

    private function fetchLatestVersion(int $objId, int $userId): string
    {
        $this->logger->debug(
            sprintf(
                'START -  fetching of latest certificates of object(user id: "%s", object id: "%s")',
                $userId,
                $objId
            )
        );

        $templates = $this->fetchCertificatesOfObject($objId, $userId);

        $version = 0;
        foreach ($templates as $template) {
            if ($template->getVersion() > $version) {
                $version = $template->getVersion();
            }
        }

        $this->logger->debug(
            sprintf(
                'END -  fetching of latest certificates of object(user id: "%s", object id: "%s") with version "%s"',
                $userId,
                $objId,
                $version
            )
        );

        return (string) $version;
    }

    private function deactivatePreviousCertificates(int $objId, int $userId): void
    {
        $this->logger->debug(
            sprintf(
                'START - deactivating previous certificates for user id: "%s" and object id: "%s"',
                $userId,
                $objId
            )
        );

        $sql = '
UPDATE ' . self::TABLE_NAME . '
SET currently_active = 0
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND  usr_id = ' . $this->database->quote($userId, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(
            sprintf(
                'END - deactivating previous certificates for user id: "%s" and object id: "%s"',
                $userId,
                $objId
            )
        );
    }

    public function isResourceUsed(string $relative_image_identification): bool
    {
        $this->logger->debug(
            sprintf(
                'START - Checking if any certificate template uses background image path "%s"',
                $relative_image_identification
            )
        );

        $result = $this->database->queryF(
            'SELECT EXISTS(SELECT 1 FROM ' . self::TABLE_NAME . ' WHERE 
            (background_image_ident = %s OR thumbnail_image_ident = %s)
             AND currently_active = 1) AS does_exist',
            ['text', 'text'],
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
            new CertificateId($row['certificate_id']),
            (string) ($row['background_image_path'] ?? ''),
            (string) ($row['thumbnail_image_path'] ?? ''),
            (string) $row['background_image_ident'],
            (string) $row['thumbnail_image_ident'],
            isset($row['id']) ? (int) $row['id'] : null
        );
    }

    public function deleteUserCertificatesForObject(int $userId, int $obj_id): void
    {
        $this->logger->debug(
            sprintf('START - Delete certificate for user("%s") in object (obj_id: %s)"', $userId, $obj_id)
        );

        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' ' . PHP_EOL
            . ' WHERE usr_id = ' . $this->database->quote($userId, 'integer') . PHP_EOL
            . ' AND obj_id = ' . $this->database->quote($obj_id, 'integer');

        $this->database->manipulate($sql);

        $this->logger->debug(
            sprintf('END - Successfully deleted certificate for user("%s") in object (obj_id: %s)"', $userId, $obj_id)
        );
    }

    private function overviewTableColumnToDbColumn(string $table_column): string
    {
        $result = match ($table_column) {
            'certificate_id' => $table_column,
            'issue_date' => 'acquired_timestamp',
            'object' => 'object_data.title',
            'owner' => 'usr_data.login',
            'obj_id' => 'cert.obj_id',
            default => null,
        };

        if (!$result) {
            throw new InvalidArgumentException('Invalid table column passed');
        }

        return $result;
    }

    /**
     * @return ilUserCertificate[]
     * @var array{certificate_id: null|string, issue_date: null|DateTime, object: null|string, owner: null|string} $filter
     */
    public function fetchCertificatesForOverview(
        string $user_language,
        array $filter,
        ?Range $range = null,
        string $order_field = 'issue_date',
        string $order_direction = 'ASC'
    ): array {
        $order_field = $this->overviewTableColumnToDbColumn($order_field);

        $sql_filters = [];
        foreach ($filter as $key => $value) {
            if ($value === null) {
                continue;
            }

            $column_name = $this->overviewTableColumnToDbColumn($key);

            if ($key === 'issue_date') {
                /** @var null|DateTime $value */
                $sql_filters[] = $this->database->equals(
                    $column_name,
                    (string) $value->getTimestamp(),
                    ilDBConstants::T_INTEGER
                );
            } else {
                $sql_filters[] = $this->database->like($column_name, ilDBConstants::T_TEXT, "%$value%");
            }
        }

        if ($range) {
            $this->database->setLimit($range->getLength(), $range->getStart());
        }

        $result = $this->database->query(
            'SELECT cert.*, ' .
            '(CASE
               WHEN (trans.title IS NOT NULL AND LENGTH(trans.title) > 0) THEN trans.title
               WHEN (object_data.title IS NOT NULL AND LENGTH(object_data.title) > 0) THEN object_data.title 
               WHEN (object_data_del.title IS NOT NULL AND LENGTH(object_data_del.title) > 0) THEN object_data_del.title 
               ELSE ' . $this->database->quote($this->defaultTitle, ilDBConstants::T_TEXT) . '
               END
             ) as object, '
            . 'usr_data.login AS owner FROM il_cert_user_cert AS cert '
            . 'LEFT JOIN object_data ON object_data.obj_id = cert.obj_id '
            . 'INNER JOIN usr_data ON usr_data.usr_id = cert.usr_id '
            . 'LEFT JOIN object_data_del ON object_data_del.obj_id = cert.obj_id '
            . 'LEFT JOIN object_translation trans ON trans.obj_id = object_data.obj_id AND trans.lang_code = ' . $this->database->quote($user_language, 'text')
            . ($sql_filters !== [] ? " WHERE " . implode(" AND ", $sql_filters) : "")
            . ' ORDER BY ' . $order_field . ' ' . $order_direction
        );

        $certificates = [];
        while ($row = $this->database->fetchAssoc($result)) {
            $certificates[] = $this->createUserCertificate($row);
        }
        return $certificates;
    }

    /**
     * @var array{certificate_id: null|string, issue_date: null|DateTime, object: null|string, owner: null|string} $filter
     */
    public function fetchCertificatesForOverviewCount(array $filter, ?Range $range = null): int
    {
        $sql_filters = [];
        foreach ($filter as $key => $value) {
            if ($value === null) {
                continue;
            }

            $column_name = $key;
            switch ($key) {
                case 'issue_date':
                    $column_name = 'acquired_timestamp';
                    break;
                case 'object':
                    $column_name = 'object_data.title';
                    break;
                case 'owner':
                    $column_name = 'usr_data.login';
                    break;
                case 'obj_id':
                    $column_name = 'cert.obj_id';
                    break;
            }

            if ($key === 'issue_date') {
                /** @var null|DateTime $value */
                $sql_filters[] = $this->database->equals(
                    $column_name,
                    (string) $value->getTimestamp(),
                    ilDBConstants::T_INTEGER
                );
            } else {
                $sql_filters[] = $this->database->like($column_name, ilDBConstants::T_TEXT, "%$value%");
            }
        }

        if ($range) {
            $this->database->setLimit($range->getLength(), $range->getStart());
        }

        $result = $this->database->query(
            'SELECT COUNT(id) as count FROM il_cert_user_cert AS cert '
            . 'LEFT JOIN object_data ON object_data.obj_id = cert.obj_id '
            . 'INNER JOIN usr_data ON usr_data.usr_id = cert.usr_id'
            . ($sql_filters !== [] ? ' AND ' . implode(' AND ', $sql_filters) : '')
        );

        return (int) $this->database->fetchAssoc($result)['count'];
    }

    public function requestIdentity(): CertificateId
    {
        return new CertificateId($this->uuid_factory->uuid4AsString());
    }
}
