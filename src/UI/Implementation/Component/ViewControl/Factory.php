<?php

/* Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl as VC;

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
	public function section($previous_action, $button, $next_action) {
		return new Section($previous_action, $button, $next_action);
	}
}