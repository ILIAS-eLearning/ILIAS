<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Object/classes/class.ilObject.php';

/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilObjSystemCheck extends ilObject
{
	/**
	 * @param int  $a_id
	 * @param bool $a_call_by_reference
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = 'sysc';
		parent::__construct($a_id, $a_call_by_reference);
	}

}
