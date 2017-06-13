<?php

/* Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\Data\Link\Link;
use ILIAS\UI\Component\ViewControl as VC;
use ILIAS\UI\Component\Button\Button;


class Factory implements VC\Factory {

	/**
	 * @inheritdoc
	 */
	public function mode($labelled_actions) {
		return new Mode($labelled_actions);
	}

	/**
	 * @inheritdoc
	 */
	public function section(Link $previous_action, Button $button, Link $next_action) {
		return new Section($previous_action, $button, $next_action);
	}
}