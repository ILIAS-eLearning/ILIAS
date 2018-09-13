<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateObjectLPHelper
{
	/**
	 * @param $objId
	 * @return ilObjectLP
	 */
	public function getInstance($objId)
	{
		return ilObjectLP::getInstance($objId);
	}
}
