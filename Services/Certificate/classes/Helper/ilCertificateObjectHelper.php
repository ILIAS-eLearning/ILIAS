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
	public function getInstanceByObjId(int $objectId): ilObject
	{
		return ilObjectFactory::getInstanceByObjId($objectId);
	}


	/**
	 * @param int $refId
	 * @return int
	 */
	public function lookupObjId(int $refId) : int
	{
		return ilObject::_lookupObjId($refId);
	}

	/**
	 * @param int $objectId
	 * @return string
	 */
	public function lookupType(int $objectId) : string
	{
		return ilObject::_lookupType($objectId);
	}
}
