<?php


class ilCertificateAppEventListener implements ilAppEventListener
{
	public static function handleEvent($a_component, $a_event, $a_params)
	{
		switch($a_component) {
			case 'Services/Tracking':
				switch($a_event) {
					case 'updateStatus':
						if($a_params['status'] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
							global $DIC;
							$certificateQueueRepository = new ilCertificateQueueRepository($DIC->database(), $DIC->logger()->root());
							$certificateClassMap = new ilCertificateTypeClassMap();

							$objId = $a_params['obj_id'];
							$userId = $a_params['usr_id'];

							$object = ilObjectFactory::getInstanceByObjId($objId);
							$type = $object->getType();

							if ($certificateClassMap->typeExistsInMap($type)) {
								$className = $certificateClassMap->getPlaceHolderClassNameByType($type);

								$entry = new ilCertificateQueueEntry(
									$objId,
									$userId,
									$className,
								ilCronConstants::IN_PROGRESS,
									time()
								);

									$certificateQueueRepository->addToQueue($entry);
							}
						}
						break;
				}
				break;
		}
	}
}
