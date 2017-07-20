<?php

/* Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component\ViewControl as VC;
use ILIAS\UI\Component\Button\Button;

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
	public function section(Button $previous_action, Button $button, Button $next_action) {
		return new Section($previous_action, $button, $next_action);
	}

	/**
	 * @inheritdoc
	 */
	public function sortation(array $options) {
		return new Sortation($options);
	}
}
