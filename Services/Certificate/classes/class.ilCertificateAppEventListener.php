<?php


class ilCertificateAppEventListener implements ilAppEventListener
{
	/**
	 * @param string $a_component
	 * @param string $a_event
	 * @param array $a_params
	 * @throws ilException
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
							$certificateQueueRepository = new ilCertificateQueueRepository($DIC->database(), $DIC->logger()->root());
							$certificateClassMap = new ilCertificateTypeClassMap();

							$objectId = $a_params['obj_id'];
							$userId = $a_params['usr_id'];

							$type = $ilObjectDataCache->lookupType($objectId);

							if ($certificateClassMap->typeExistsInMap($type)) {
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
						}
						break;
				}
				break;
		}
	}
}
