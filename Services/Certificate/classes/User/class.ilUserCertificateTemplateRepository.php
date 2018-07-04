<?php


class ilUserCertificateTemplateRepository
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
	 * @param ilUserCertificateTemplate $certificateTemplate
	 * @throws ilDatabaseException
	 */
	public function save(ilUserCertificateTemplate $certificateTemplate)
	{
		$version = $this->fetchLatestVersion($certificateTemplate->getObjId(), $certificateTemplate->getUserId());
		$version += 1;

		$id = $this->database->nextId('user_certificates');

		$objId = $certificateTemplate->getObjId();
		$userId = $certificateTemplate->getUserId();

		$this->deactivatePreviousCertificates($objId, $userId);

		$columns = array(
			'id'                     => array('integer', $id),
			'pattern_certificate_id' => array('integer', $certificateTemplate->getPatternCertificateId()),
			'obj_id'                 => array('integer', $objId),
			'obj_type'               => array('clob', $certificateTemplate->getObjType()),
			'user_id'                => array('integer', $userId),
			'user_name'              => array('string', $certificateTemplate->getUserName()),
			'acquired_timestamp'     => array('clob', $certificateTemplate->getAcquiredTimestamp()),
			'certificate_content'    => array('clob', $certificateTemplate->getCertificateContent()),
			'template_values'        => array('clob', $certificateTemplate->getTemplateValues()),
			'valid_until'            => array('integer', $certificateTemplate->getValidUntil()),
			'version'                => array('text', $version),
			'ilias_version'          => array('text', $certificateTemplate->getIliasVersion()),
			'currently_active'       => array('integer', (integer)$certificateTemplate->isCurrentlyActive())
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
			$result[] = new ilUserCertificateTemplate(
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
			$result[] = new ilUserCertificateTemplate(
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
UPDATE certificate_template
SET currently_active = 0
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND  user_id = ' . $this->database->quote($userId, 'integer');

		$query = $this->database->query($sql);
		$this->database->execute($query);
	}
}
