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

	public function __construct(ilCertificateTemplateRepository $templateRepository)
	{
		$this->templateRepository = $templateRepository;
	}

	/**
	 * @param $refId
	 * @param $userId
	 * @return array
	 */
	public function evaluate($refId, $userId)
	{
		$courseSetting = new ilSetting('crs');

		$courseObjectIds = $this->templateRepository->fetchAllObjectIdsByType('crs');

		$completedCourses = array();
		foreach ($courseObjectIds as $courseObjectId) {
			$subItems = $courseSetting->get('cert_subitems_' . $courseObjectId, false);

			if (false === $subItems) {
				continue;
			}

			$subItems = json_decode($subItems);

			$subitem_obj_ids = array();
			foreach($subItems as $subItemRefId) {
				$subitem_obj_ids[$subItemRefId] = ilObject::_lookupObjId($subItemRefId);
			}

			// relevant for current badge instance?
			if(in_array($refId, $subItems)) {
				$completed = true;

				// check if all subitems are completed now
				foreach($subitem_obj_ids as $subitem_ref_id => $subitem_id) {
					$status = ilLPStatus::_lookupStatus($subitem_id, $userId);
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
