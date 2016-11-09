<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2.php';

/**
 * Class ilObjLTIAdministration
 * @author Jesús López <lopez@leifos.com>
 */
class ilObjLTIAdministration extends ilObject2
{
	/**
	 *
	 */
	protected function initType()
	{
		$this->type = 'ltis';
	}
/*
	public function getLTIObjectTypes()
	{
		$obj = array(

		);
	}
*/
}