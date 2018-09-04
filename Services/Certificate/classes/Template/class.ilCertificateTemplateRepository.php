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

		$id = $this->database->nextId('certificate_template');

		$this->deactivatePreviousTemplates($objId);

		$this->database->insert('certificate_template', array(
			'id'                    => array('integer', $id),
			'obj_id'                => array('integer', $objId),
			'obj_type'              => array('clob', $certificateTemplate->getObjType()),
			'certificate_content'   => array('clob', $certificateTemplate->getCertificateContent()),
			'certificate_hash'      => array('text', $certificateTemplate->getCertificateHash()),
			'template_values'       => array('clob', $certificateTemplate->getTemplateValues()),
			'version'               => array('clob', $certificateTemplate->getVersion()),
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
WHERE obj_id = ' . $this->database->quote($objId, 'integer') . '
ORDER BY version ASC';

		$query = $this->database->query($sql);

		while ($row = $this->database->fetchAssoc($query)) {
			$result[] = new ilCertificateTemplate(
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
				$row['obj_type'],
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

	public function fetchPreviousCertificate($objId)
	{
		$templates = $this->fetchCertificateTemplatesByObjId($objId);

		$resultTemplate = new ilCertificateTemplate(
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

		$version = 0;
		foreach ($templates as $template) {
			if ($template->getVersion() > $version) {
				$version = $template->getVersion();
				$resultTemplate = $template;
			}
		}

		return $resultTemplate;
	}

	public function deleteTemplate($templateId, $objectId)
	{
		$sql = '
DELETE FROM certificate_template
WHERE id = ' . $this->database->quote($templateId, 'integer') . '
AND obj_id = ' . $this->database->quote($objectId, 'integer');

		$query = $this->database->query($sql);

		$this->database->execute($query);
	}

	public function activatePreviousCertificate($objId)
	{
		$certificates = $this->fetchCertificateTemplatesByObjId($objId);

		/** @var ilCertificateTemplate $previousCertificate */
		$previousCertificate = null;
		foreach ($certificates as $certificate) {
			if (null === $previousCertificate) {
				$previousCertificate = $certificate;
			} else if ((int) $certificate->getVersion() > (int) $previousCertificate->getVersion()) {
				$previousCertificate = $certificate;
			}
		}

		$sql = 'UPDATE certificate_template
SET currently_active = 1
WHERE id = ' . $this->database->quote($previousCertificate->getId(), 'integer');

		$query = $this->database->query($sql);

		$this->database->execute($query);

		return $previousCertificate;
	}

	public function fetchAllObjectIdsByType($type)
	{
		$sql = 'SELECT DISTINCT obj_id FROM certificate_template WHERE obj_type = ' . $this->database->quote($type, 'text');
		$query = $this->database->query($sql);

		$result = array();
		while ($row = $this->database->fetchAssoc($query)) {
			$result[] = $row['obj_id'];
		}

		return $result;
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
