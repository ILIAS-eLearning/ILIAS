<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilObjectHelper
{
	public function getInstanceByObjId($objectId)
	{
		return ilObjectFactory::getInstanceByObjId($objectId);
	}
}
