<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout;

use ILIAS\UI\Component\Layout;

class Factory implements Layout\Factory
{
	/**
	 * @inheritdoc
	 */
	public function page(): Layout\Page\Factory
	{
		return new Page\Factory();
	}

}
