<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component\Input\Container\Filter as F;

class Factory implements F\Factory {

	/**
	 * @inheritdoc
	 */
	public function standard($toggle_action_on, $toggle_action_off, $expand_action, $collapse_action, $apply_action, $reset_action, array $inputs, array $is_input_rendered, $is_activated = false, $is_expanded = false) {
		return new Standard($toggle_action_on, $toggle_action_off, $expand_action, $collapse_action, $apply_action, $reset_action, $inputs, $is_input_rendered, $is_activated, $is_expanded);
	}
}