<?php


class ilCertificateTemplateRepository
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
	 * @param ilCertificateTemplate $certificateTemplate
	 * @throws ilDatabaseException
	 */
	public function save(ilCertificateTemplate $certificateTemplate)
	{
		$objId = $certificateTemplate->getObjId();

		$version = $this->fetchLatestVersion($objId);
		$version += 1;

		$id = $this->database->nextId('certificate_template');

		$this->deactivatePreviousTemplates($objId);

		$this->database->insert('certificate_template', array(
			'id'                    => array('integer', $id),
			'obj_id'                => array('integer', $objId),
			'certificate_content'   => array('clob', $certificateTemplate->getCertificateContent()),
			'certificate_hash'      => array('clob', $certificateTemplate->getCertificateHash()),
			'template_values'       => array('clob', $certificateTemplate->getTemplateValues()),
			'version'               => array('clob', $version),
			'ilias_version'         => array('clob', $certificateTemplate->getIliasVersion()),
			'created_timestamp'     => array('integer', $certificateTemplate->getCreatedTimestamp()),
			'currently_active'      => array('integer', (integer) $certificateTemplate->isCurrentlyActive()),
			'background_image_path' => array('clob', $certificateTemplate->getBackgroundImagePath()),
		));
	}

	/**
	 * @param $objId
	 * @return array
	 */
	public function fetchCertificateTemplatesByObjId($objId)
	{
		$result = array();

		$sql = '
SELECT * FROM
certificate_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer');

		$query = $this->database->query($sql);

		while ($row = $this->database->fetchAssoc($query)) {
			$result[] = new ilCertificateTemplate(
				$row['obj_id'],
				$row['certificate_content'],
				$row['certificate_hash'],
				$row['template_values'],
				$row['version'],
				$row['ilias_version'],
				$row['created_timestamp'],
				(boolean) $row['currently_active'],
				$row['background_image_path'],
				$row['id']
			);
		}

		return $result;
	}

	/**
	 * @param $objId
	 * @return ilCertificateTemplate
	 * @throws ilException
	 */
	public function fetchCurrentlyActiveCertificate($objId)
	{
		$sql = '
SELECT * FROM certificate_template
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
AND currently_active = 1
';

		$query = $this->database->query($sql);

		while ($row = $this->database->fetchAssoc($query)) {
			return new ilCertificateTemplate(
				$row['obj_id'],
				$row['certificate_content'],
				$row['certificate_hash'],
				$row['template_values'],
				$row['version'],
				$row['ilias_version'],
				$row['created_timestamp'],
				(boolean) $row['currently_active'],
				$row['background_image_path'],
				$row['id']
			);
		}

		return new ilCertificateTemplate(
			$objId,
			'',
			'',
			'',
			0,
			0,
			0,
			true,
			''
		);
	}

	/**
	 * @param $objId
	 * @return int
	 */
	private function fetchLatestVersion($objId)
	{
		$templates = $this->fetchCertificateTemplatesByObjId($objId);

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
	 * @throws ilDatabaseException
	 */
	private function deactivatePreviousTemplates($objId)
	{
		$sql = '
UPDATE certificate_template
SET currently_active = 0
WHERE obj_id = ' . $this->database->quote($objId, 'integer');

		$query = $this->database->query($sql);
		$this->database->execute($query);
	}
}
