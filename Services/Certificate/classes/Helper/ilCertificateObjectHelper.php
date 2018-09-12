<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateObjectHelper
{
	/**
	 * @param int $objectId
	 * @return ilObject
	 * @throws ilException
	 */
	public function getInstanceByObjId(int $objectId): ilObject
	{
		$result = ilObjectFactory::getInstanceByObjId($objectId, false);
		if (! $result instanceof ilObject) {
			throw new ilException(sprintf('An instance for the object id "%s" could not be created', $objectId));
		}

		return $result;
	}
}
