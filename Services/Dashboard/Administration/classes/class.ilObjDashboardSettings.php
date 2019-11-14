<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dashboard settings
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilObjDashboardSettings extends ilObject
{
	/**
	 * @inheritDoc
	 */
	function __construct($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "dshs";
		parent::__construct($a_id,$a_call_by_reference);
	}
}
?>
