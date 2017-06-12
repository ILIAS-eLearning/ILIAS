<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Link;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Link base interface.
 */
interface Link extends Component, JavaScriptBindable, Clickable, Hoverable {
	/**
	 * Get the action url of a link
	 *
	 * @return	string
	 */
	public function getAction();
}
