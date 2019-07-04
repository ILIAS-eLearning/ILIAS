<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\DateTime;

/**
 * @author  Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Group
{
	public function changeTimezone(string $timezone) {
		return new ChangeTimezone($timezone);
	}

}
