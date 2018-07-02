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
	public function __construct(\ilDB $database)
	{
		$this->database = $database;
	}

	public function save(ilUserCertificateTemplate $certificateTemplate)
	{
		$this->database->insert('certificate_user_template', array(
			'pattern_certificate_id' => $certificateTemplate->getPatternCertificateId(),
			'obj_id'                 => $certificateTemplate->getObjId(),
			'obj_type'               => $certificateTemplate->getObjType(),
			'user_id'                => $certificateTemplate->getUserId(),
			'user_name'              => $certificateTemplate->getUserName(),
			'acquired_timestamp'     => $certificateTemplate->getAcquiredTimestamp(),
			'certificate_content'    => $certificateTemplate->getCertificateContent(),
			'template_values'        => $certificateTemplate->getTemplateValues(),
			'valid_until'            => $certificateTemplate->getValidUntil(),
			'version'                => $certificateTemplate->getVersion(),
			'ilias_version'          => $certificateTemplate->getIliasVersion(),
			'currently_active'       => (integer) $certificateTemplate->isCurrentlyActive()
		));
	}
}
