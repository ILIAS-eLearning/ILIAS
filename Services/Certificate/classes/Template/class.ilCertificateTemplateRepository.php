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
	public function __construct(\ilDB $database)
	{
		$this->database = $database;
	}

	/**
	 * @param ilCertificateTemplate $certificateTemplate
	 */
	public function save(ilCertificateTemplate $certificateTemplate)
	{
		$this->database->insert('certificate_template', array(
			'obj_id'              => $certificateTemplate->getObjId(),
			'certificate_content' => $certificateTemplate->getCertificateContent(),
			'certificate_hash'    => $certificateTemplate->getCertificateHash(),
			'template_values'     => $certificateTemplate->getTemplateValues(),
			'version'             => $certificateTemplate->getVersion(),
			'ilias_version'       => $certificateTemplate->getIliasVersion(),
			'created_timestamp'   => $certificateTemplate->getCreatedTimestamp(),
			'currently_active'    => (integer) $certificateTemplate->isCurrentlyActive()
		));
	}

	public function fetchCertificateTemplatesByObjId($objId)
	{
		$result = array();

		$sql = 'SELECT * FROM certificate_template WHERE obj_id = ' . $this->database->quote($objId, 'integer');

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
				$row['currently_active'],
				$row['id']
			);
		}

		return $result;
	}
}
