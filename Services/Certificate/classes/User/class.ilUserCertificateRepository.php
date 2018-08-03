<?php


class ilUserCertificateRepository
{
	/**
	 * @var ilDB
	 */
	private $database;

	/**
	 * @param ilDB $database
	 */
	public function __construct(\ilDBInterface $database)
	{
		$this->database = $database;
	}

	/**
	 * @param ilUserCertificate $userCertificate
	 * @throws ilDatabaseException
	 */
	public function save(ilUserCertificate $userCertificate)
	{
		$version = $this->fetchLatestVersion($userCertificate->getObjId(), $userCertificate->getUserId());
		$version += 1;

		$id = $this->database->nextId('user_certificates');

		$objId = $userCertificate->getObjId();
		$userId = $userCertificate->getUserId();

		$this->deactivatePreviousCertificates($objId, $userId);

		$columns = array(
			'id'                     => array('integer', $id),
			'pattern_certificate_id' => array('integer', $userCertificate->getPatternCertificateId()),
			'obj_id'                 => array('integer', $objId),
			'obj_type'               => array('clob', $userCertificate->getObjType()),
			'user_id'                => array('integer', $userId),
			'user_name'              => array('string', $userCertificate->getUserName()),
			'acquired_timestamp'     => array('clob', $userCertificate->getAcquiredTimestamp()),
			'certificate_content'    => array('clob', $userCertificate->getCertificateContent()),
			'template_values'        => array('clob', $userCertificate->getTemplateValues()),
			'valid_until'            => array('integer', $userCertificate->getValidUntil()),
			'version'                => array('text', $version),
			'ilias_version'          => array('text', $userCertificate->getIliasVersion()),
			'currently_active'       => array('integer', (integer)$userCertificate->isCurrentlyActive()),
			'background_image_path'  => array('clob', $userCertificate->getBackgroundImagePath()),
		);

		$this->database->insert('user_certificates', $columns);
	}

	/**
	 * @param $userId
	 * @return array
	 */
	public function fetchActiveCertificates($userId)
	{
		$sql = 'SELECT * FROM user_certificates WHERE user_id = ' . $userId . ' AND currently_active = 1';

		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$result[] = new ilUserCertificate(
				$row['pattern_certificate_id'],
				$row['obj_id'],
				$row['obj_type'],
				$row['user_id'],
				$row['user_name'],
				$row['acquired_timestamp'],
				$row['certificate_content'],
				$row['template_values'],
				$row['valid_until'],
				$row['version'],
				$row['ilias_version'],
				$row['currently_active'],
				$row['background_image_path'],
				$row['id']
			);
		}

		return $result;
	}

	/**
	 * @param $objId
	 * @param $userId
	 * @return array
	 */
	private function fetchCertificatesOfObject($objId, $userId)
	{
		$sql = 'SELECT * FROM user_certificates 
WHERE user_id = ' . $userId . '
AND obj_id = ' . $objId;

		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$result[] = new ilUserCertificate(
				$row['pattern_certificate_id'],
				$row['obj_id'],
				$row['obj_type'],
				$row['user_id'],
				$row['user_name'],
				$row['acquired_timestamp'],
				$row['certificate_content'],
				$row['template_values'],
				$row['valid_until'],
				$row['version'],
				$row['ilias_version'],
				$row['currently_active'],
				$row['background_image_path'],
				$row['id']
			);
		}

		return $result;
	}

	private function fetchLatestVersion($objId, $userId)
	{
		$templates = $this->fetchCertificatesOfObject($objId, $userId);

		$version = 0;
		foreach ($templates as $template) {
			if ($template->getVersion() > $version) {
				$version = $template->getVersion();
			}
		}

		return $version;
	}

	/**
	 * @param $objId
	 * @param $userId
	 * @throws ilDatabaseException
	 */
	private function deactivatePreviousCertificates($objId, $userId)
	{
		$sql = '
UPDATE user_certificates
SET currently_active = 0
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND  user_id = ' . $this->database->quote($userId, 'integer');

		$query = $this->database->query($sql);
		$this->database->execute($query);
	}
}
