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
	 */
	public function getInstanceByObjId(int $objectId)
	{
		return ilObjectFactory::getInstanceByObjId($objectId);
	}
}
