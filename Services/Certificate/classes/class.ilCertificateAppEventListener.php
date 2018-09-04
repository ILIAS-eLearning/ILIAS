<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateAppEventListener
 *
 * @author Niels Theen <ntheen@databay.de>
 * @version $Id:$
 *
 * @package Services/Certificate
 */
class ilCertificateAppEventListener implements ilAppEventListener
{
	/**
	 * @inheritdoc
	 */
	public static function handleEvent($a_component, $a_event, $a_params)
	{
		switch($a_component) {
			case 'Services/Tracking':
				switch($a_event) {
					case 'updateStatus':
						if($a_params['status'] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
							global $DIC;

							/** @var ilObjectDataCache $ilObjectDataCache */
							$ilObjectDataCache = $DIC['ilObjDataCache'];
							$database = $DIC->database();

							$certificateQueueRepository = new ilCertificateQueueRepository($database, $DIC->logger()->root());
							$certificateClassMap = new ilCertificateTypeClassMap();
							$activeAction = new ilCertificateAction($database);

							$objectId = $a_params['obj_id'];
							$userId = $a_params['usr_id'];

							$type = $ilObjectDataCache->lookupType($objectId);

							if ($certificateClassMap->typeExistsInMap($type) && $activeAction->isObjectActive($objectId)) {
								$className = $certificateClassMap->getPlaceHolderClassNameByType($type);

								$entry = new ilCertificateQueueEntry(
									$objectId,
									$userId,
									$className,
								ilCronConstants::IN_PROGRESS,
									time()
								);

								$certificateQueueRepository->addToQueue($entry);
							}

							foreach (ilObject::_getAllReferences($objectId) as $refId) {
								$templateRepository = new ilCertificateTemplateRepository($database);
								$progressEvaluation = new ilCertificateCourseLearningProgressEvaluation($templateRepository);

								$completedCourses = $progressEvaluation->evaluate($refId, $userId);
								foreach ($completedCourses as $courseObjId) {
									$type = $ilObjectDataCache->lookupType($courseObjId);

									$className = $certificateClassMap->getPlaceHolderClassNameByType($type);

									$entry = new ilCertificateQueueEntry(
										$courseObjId,
										$userId,
										$className,
										ilCronConstants::IN_PROGRESS,
										time()
									);

									$certificateQueueRepository->addToQueue($entry);
								}
							}

						}
						break;
				}
				break;
		}
	}
}
