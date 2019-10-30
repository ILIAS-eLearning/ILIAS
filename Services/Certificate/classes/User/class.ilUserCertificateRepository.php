<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateRepository
{
	/**
	 * @var ilDB
	 */
	private $database;

	/**
	 * @var ilLogger
	 */
	private $logger;

	/**
	 * @var null|string 
	 */
	private $defaultTitle;

	/**
	 * @param ilDBInterface $database
	 * @param ilLogger $logger
	 * @param string|null $defaultTitle
	 */
	public function __construct(
		\ilDBInterface $database = null,
		ilLogger $logger = null,
		string $defaultTitle = null
	) {
		if (null === $database) {
			global $DIC;
			$database = $DIC->database();
		}
		$this->database = $database;

		if (null === $logger)  {
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
	 * @throws ilDatabaseException
	 */
	public function save(ilUserCertificate $userCertificate)
	{
		$this->logger->info('START - saving of user certificate');

		$version = $this->fetchLatestVersion($userCertificate->getObjId(), $userCertificate->getUserId());
		$version += 1;

		$id = $this->database->nextId('il_cert_user_cert');

		$objId = $userCertificate->getObjId();
		$userId = $userCertificate->getUserId();

		$this->deactivatePreviousCertificates($objId, $userId);

		$columns = array(
			'id'                     => array('integer', $id),
			'pattern_certificate_id' => array('integer', $userCertificate->getPatternCertificateId()),
			'obj_id'                 => array('integer', $objId),
			'obj_type'               => array('text', $userCertificate->getObjType()),
			'user_id'                => array('integer', $userId),
			'user_name'              => array('text', $userCertificate->getUserName()),
			'acquired_timestamp'     => array('integer', $userCertificate->getAcquiredTimestamp()),
			'certificate_content'    => array('clob', $userCertificate->getCertificateContent()),
			'template_values'        => array('clob', $userCertificate->getTemplateValues()),
			'valid_until'            => array('integer', $userCertificate->getValidUntil()),
			'version'                => array('integer', $version),
			'ilias_version'          => array('text', $userCertificate->getIliasVersion()),
			'currently_active'       => array('integer', (integer)$userCertificate->isCurrentlyActive()),
			'background_image_path'  => array('text', $userCertificate->getBackgroundImagePath()),
			'thumbnail_image_path'   => array('text', $userCertificate->getThumbnailImagePath())
		);

		$this->logger->debug(sprintf('END - Save certificate with following values: %s', json_encode($columns, JSON_PRETTY_PRINT)));

		$this->database->insert('il_cert_user_cert', $columns);
	}

	/**
	 * @param int $userId
	 * @param string $orderBy
	 * @return ilUserCertificatePresentation[]
	 */
	public function fetchActiveCertificates(int $userId) : array
	{
		$this->logger->info(sprintf('START - Fetching all active certificates for user: "%s"', $userId));

		$sql = '
SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.obj_type,
  il_cert_user_cert.user_id,
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
  (CASE WHEN (object_data.title IS NULL)
    THEN
      CASE WHEN (object_data_del.title IS NULL)
        THEN ' . $this->database->quote($this->defaultTitle, 'text') . '
        ELSE object_data_del.title
        END
    ELSE object_data.title 
    END
  ) as title
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
WHERE user_id = ' . $this->database->quote($userId, 'integer') . '
AND currently_active = 1';

		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$userCertificate = $this->createUserCertificate($row);

			$presentation = new ilUserCertificatePresentation($userCertificate, $row['title'], '');
			$result[] = $presentation;
		}

		$this->logger->debug(sprintf('Actual results:', json_encode($result)));
		$this->logger->info(sprintf('END - All active certificates for user: "%s" total: "%s"', $userId, count($result)));

		return $result;
	}

	/**
	 * @param int $userId
	 * @param string $orderBy
	 * @return ilUserCertificatePresentation[]
	 */
	public function fetchActiveCertificatesInIntervalForPresentation(int $userId, int $startTimestamp, int $endTimeStamp) : array
	{
		$this->logger->info(sprintf('START - Fetching all active certificates for user: "%s"', $userId));

		$sql = '
SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.obj_type,
  il_cert_user_cert.user_id,
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
  (CASE WHEN (object_data.title IS NULL)
    THEN
      CASE WHEN (object_data_del.title IS NULL)
        THEN ' . $this->database->quote($this->defaultTitle, 'text') . '
        ELSE object_data_del.title
        END
    ELSE object_data.title 
    END
  ) as title
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
WHERE user_id = ' . $this->database->quote($userId, 'integer') . '
AND currently_active = 1
AND acquired_timestamp >= ' . $this->database->quote($startTimestamp, 'integer') . '
AND acquired_timestamp <= ' . $this->database->quote($endTimeStamp, 'integer');

		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$userCertificate = $this->createUserCertificate($row);

			$presentation = new ilUserCertificatePresentation($userCertificate, $row['title'], '');
			$result[] = $presentation;
		}

		$this->logger->debug(sprintf('Actual results:', json_encode($result)));
		$this->logger->info(sprintf('END - All active certificates for user: "%s" total: "%s"', $userId, count($result)));

		return $result;
	}

	public function fetchActiveCertificate(int $userId, int $objectId) : ilUserCertificate
	{
		$this->logger->info(sprintf('START - Fetching all active certificates for user: "%s" and object: "%s"', $userId, $objectId));

		$sql = 'SELECT *
FROM il_cert_user_cert
WHERE user_id = ' . $this->database->quote($userId, 'integer') . '
AND obj_id = ' . $this->database->quote($objectId, 'integer') . '
AND currently_active = 1';

		$query = $this->database->query($sql);

		while ($row = $this->database->fetchAssoc($query)) {

			$this->logger->debug(sprintf('Active certificate values: %s', json_encode($row)));

			$this->logger->info(sprintf('END -Found active user certificate for user: "%s" and object: "%s"', $userId, $objectId));

			return $this->createUserCertificate($row);
		}

		throw new ilException(sprintf('There is no active entry for user id: "%s" and object id: "%s"', $userId, $objectId));
	}

	/**
	 * @param int $userId
	 * @param int $objectId
	 * @return ilUserCertificatePresentation
	 * @throws ilException
	 */
	public function fetchActiveCertificateForPresentation(int $userId, int $objectId) : ilUserCertificatePresentation
	{
		$this->logger->info(sprintf('START - Fetching all active certificates for user: "%s" and object: "%s"', $userId, $objectId));

		$sql = 'SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.obj_type,
  il_cert_user_cert.user_id,
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
  (CASE WHEN (object_data.title IS NULL)
    THEN
      CASE WHEN (object_data_del.title IS NULL)
        THEN ' . $this->database->quote($this->defaultTitle, 'text') . '
        ELSE object_data_del.title
        END
    ELSE object_data.title
    END
  ) as title
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
LEFT JOIN usr_data ON usr_data.usr_id = il_cert_user_cert.user_id
WHERE il_cert_user_cert.user_id = ' . $this->database->quote($userId, 'integer') . '
AND il_cert_user_cert.obj_id = ' . $this->database->quote($objectId, 'integer') . '
AND il_cert_user_cert.currently_active = 1';

		$query = $this->database->query($sql);

		while ($row = $this->database->fetchAssoc($query)) {

			$this->logger->debug(sprintf('Active certificate values: %s', json_encode($row)));

			$this->logger->info(sprintf('END -Found active user certificate for user: "%s" and object: "%s"', $userId, $objectId));

			$userCertificate = $this->createUserCertificate($row);
			return new ilUserCertificatePresentation($userCertificate, $row['title'], '', $row['lastname']);
		}

		throw new ilException(sprintf('There is no active entry for user id: "%s" and object id: "%s"', $userId, $objectId));
	}

	/**
	 * @param int $userId
	 * @param string $type
	 * @return ilUserCertificatePresentation[]
	 */
	public function fetchActiveCertificatesByTypeForPresentation(int $userId, string $type) : array
	{
		$this->logger->info(sprintf('START - Fetching all active certificates for user: "%s" and type: "%s"', $userId, $type));

		$sql = 'SELECT 
  il_cert_user_cert.pattern_certificate_id,
  il_cert_user_cert.obj_id,
  il_cert_user_cert.obj_type,
  il_cert_user_cert.user_id,
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
  (CASE WHEN (object_data.title IS NULL)
    THEN
      CASE WHEN (object_data_del.title IS NULL)
        THEN ' . $this->database->quote($this->defaultTitle, 'text') . '
        ELSE object_data_del.title
        END
    ELSE object_data.title 
    END
  ) as title
FROM il_cert_user_cert
LEFT JOIN object_data ON object_data.obj_id = il_cert_user_cert.obj_id
LEFT JOIN object_data_del ON object_data_del.obj_id = il_cert_user_cert.obj_id
WHERE user_id = ' . $this->database->quote($userId, 'integer') . '
 AND obj_type = ' . $this->database->quote($type, 'text') . '
 AND currently_active = 1';

		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$userCertificate = $this->createUserCertificate($row);
			
			$presentation = new ilUserCertificatePresentation($userCertificate, $row['title'], '');
			$result[] = $presentation;
		}

		$this->logger->info(sprintf('END - Fetching all active certificates for user: "%s" and type: "%s"', $userId, $type));

		return $result;
	}

	/**
	 * @param int $id
	 * @return ilUserCertificate
	 * @throws ilException
	 */
	public function fetchCertificate(int $id) : ilUserCertificate
	{
		$this->logger->info(sprintf('START - Fetch certificate by id: "%s"', $id));

		$sql = 'SELECT * FROM il_cert_user_cert WHERE id = ' . $this->database->quote($id, 'integer');

		$query = $this->database->query($sql);

		while ($row = $this->database->fetchAssoc($query)) {
			$this->logger->debug(sprintf('Fetched certificate: "%s"', json_encode($row)));

			$this->logger->info(sprintf('END - Fetch certificate by id: "%s"', $id));

			return $this->createUserCertificate($row);
		}

		throw new ilException('No certificate found for user certificate id: ' . $id);
	}

	public function fetchObjectIdsWithCertificateForUser(int $userId, array $objectIds)
	{
		$this->logger->info(sprintf('START - Fetch certificate for user("%s") and ids: "%s"', $userId, json_encode($objectIds)));

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
 WHERE user_id = ' . $this->database->quote($userId, 'integer') .
' AND ' . $inStatementObjectIds .
' AND currently_active = '  .  $this->database->quote(1, 'integer');

		$query = $this->database->query($sql);

		$result = array();

		while ($row = $this->database->fetchAssoc($query)) {
			$this->logger->debug(sprintf('Fetched certificate: "%s"', json_encode($row)));
			$result[] = $row['obj_id'];
		}

		return $result;
	}

	public function fetchUserIdsWithCertificateForObject(int $objectId)
	{
		$this->logger->info(sprintf('START - Fetch certificate for object("%s")"', $objectId));

		$sql = 'SELECT user_id FROM il_cert_user_cert
WHERE obj_id = ' . $this->database->quote($objectId, 'integer') . '
 AND currently_active = '  .  $this->database->quote(1, 'integer');

		$query = $this->database->query($sql);

		$result = array();

		while ($row = $this->database->fetchAssoc($query)) {
			$this->logger->debug(sprintf('Fetched certificate: "%s"', json_encode($row)));
			$result[] = $row['user_id'];
		}

		return $result;
	}

	public function deleteUserCertificates(int $userId)
	{
		$this->logger->info(sprintf('START - Delete certificate for user("%s")"', $userId));

		$sql = 'DELETE FROM il_cert_user_cert WHERE user_id = ' . $this->database->quote($userId, 'integer');

		$this->database->manipulate($sql);

		$this->logger->info(sprintf('END - Successfully deleted certificate for user("%s")"', $userId));
	}

	/**
	 * @param int $objId
	 * @param int $userId
	 * @return array
	 */
	private function fetchCertificatesOfObject(int $objId, int $userId) : array
	{
		$this->logger->info(sprintf(
			'START -  fetching all certificates of object(user id: "%s", object id: "%s")',
			$userId,
			$objId
		));

		$sql = 'SELECT * FROM il_cert_user_cert
WHERE user_id = ' . $this->database->quote($userId , 'integer') . '
AND obj_id = ' . $this->database->quote($objId , 'integer');

		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$this->logger->debug(sprintf(
				'Certificate found: "%s")',
				json_encode($row,JSON_PRETTY_PRINT)
			));

			$this->logger->info(sprintf('Certificate: ', json_encode($row)));

			$result[] = $this->createUserCertificate($row);
		}

		$this->logger->info(sprintf(
			'END -  fetching all certificates of object(user id: "%s", object id: "%s")',
			$userId,
			$objId
		));

		return $result;
	}

	/**
	 * @param int $objId
	 * @param int $userId
	 * @return string
	 */
	private function fetchLatestVersion(int $objId, int $userId) : string
	{
		$this->logger->info(sprintf(
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

		$this->logger->info(sprintf(
			'END -  fetching of latest certificates of object(user id: "%s", object id: "%s") with verision',
			$userId,
			$objId,
			$version
		));

		return $version;
	}

	/**
	 * @param $objId
	 * @param $userId
	 * @throws ilDatabaseException
	 */
	private function deactivatePreviousCertificates(int $objId, int $userId)
	{
		$this->logger->info(sprintf('START - deactivating previous certificates for user id: "%s" and object id: "%s"', $userId, $objId));

		$sql = '
UPDATE il_cert_user_cert
SET currently_active = 0
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND  user_id = ' . $this->database->quote($userId, 'integer');

		$this->database->manipulate($sql);

		$this->logger->info(sprintf('END - deactivating previous certificates for user id: "%s" and object id: "%s"', $userId, $objId));
	}

	/**
	 * @param $row
	 * @return ilUserCertificate
	 */
	private function createUserCertificate($row): ilUserCertificate
	{
		return new ilUserCertificate(
			$row['pattern_certificate_id'],
			$row['obj_id'],
			$row['obj_type'],
			$row['user_id'],
			$row['user_name'],
			(int)$row['acquired_timestamp'],
			$row['certificate_content'],
			$row['template_values'],
			$row['valid_until'],
			$row['version'],
			$row['ilias_version'],
			$row['currently_active'],
			$row['background_image_path'],
			$row['thumbnail_image_path'],
			$row['id']
		);
	}
}
