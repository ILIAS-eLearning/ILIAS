<?php

/* Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl as VC;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Component;

class Factory implements VC\Factory {

	/**
	 * @inheritdoc
	 */
	public function mode($labelled_actions, $aria_label) {
		return new Mode($labelled_actions, $aria_label);
	}

	/**
	 * @inheritdoc
	 */
	public function section(Button $previous_action, \ILIAS\UI\Component\Component $button, Button $next_action) {
		return new Section($previous_action, $button, $next_action);
	}
}