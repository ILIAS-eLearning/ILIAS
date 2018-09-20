<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * A Proxy Field for the clickable Fields in Filters
 */
class ProxyFilterField implements \ILIAS\UI\Component\Input\Container\Filter\ProxyFilterField {

	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @inheritdoc
	 */
	public function withOnClick(Signal $signal) {
		return $this->withTriggeredSignal($signal, 'click');
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnClick(Signal $signal) {
		return $this->appendTriggeredSignal($signal, 'click');
	}

}