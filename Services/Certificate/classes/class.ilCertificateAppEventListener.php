<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
