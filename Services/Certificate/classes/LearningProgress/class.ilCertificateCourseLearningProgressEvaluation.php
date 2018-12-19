<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCourseLearningProgressEvaluation
{
	/**
	 * @var ilCertificateTemplateRepository
	 */
	private $templateRepository;

	/**
	 * @var ilSetting
	 */
	private $setting;

	/**
	 * @var ilCertificateObjectHelper
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateLPStatusHelper
	 */
	private $statusHelper;

	/**
	 * @param ilCertificateTemplateRepository $templateRepository
	 * @param ilSetting|null $setting
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificateLPStatusHelper|null $statusHelper
	 */
	public function __construct(
		ilCertificateTemplateRepository $templateRepository,
		ilSetting $setting = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateLPStatusHelper $statusHelper = null
	) {
		$this->templateRepository = $templateRepository;

		if (null === $setting) {
			$setting = new ilSetting('crs');
		}
		$this->setting = $setting;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $statusHelper) {
			$statusHelper = new ilCertificateLPStatusHelper();
		}
		$this->statusHelper = $statusHelper;
	}

	/**
	 * @param $refId
	 * @param $userId
	 * @return array
	 */
	public function evaluate(int $refId, int $userId) : array
	{
		$courseObjectIds = $this->templateRepository->fetchAllObjectIdsByType('crs');

		$completedCourses = array();
		foreach ($courseObjectIds as $courseObjectId) {
			$subItems = $this->setting->get('cert_subitems_' . $courseObjectId, false);

			if (false === $subItems || $subItems === null) {
				continue;
			}

			$subItems = json_decode($subItems);

			if (!is_array($subItems)) {
				continue;
			}

			$subitem_obj_ids = array();
			foreach($subItems as $subItemRefId) {
				$subitem_obj_ids[$subItemRefId] = $this->objectHelper->lookupObjId((int) $subItemRefId);
			}

			if(in_array($refId, $subItems)) {
				$completed = true;

				// check if all subitems are completed now
				foreach($subitem_obj_ids as $subitem_ref_id => $subitem_id) {
					$status = $this->statusHelper->lookUpStatus($subitem_id, $userId);

					if($status != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
						$completed = false;
						break;
					}
				}

				if (true === $completed) {
					$completedCourses[] = $courseObjectId;
				}
			}
		}

		return $completedCourses;
	}
}
