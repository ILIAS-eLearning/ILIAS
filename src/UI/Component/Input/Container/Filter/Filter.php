<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Filter;

use ILIAS\UI\Component\Component;

/**
 * This describes commonalities between all filters.
 */
interface Filter extends Component {

	/**
	 * Get the inputs contained in the filter.
	 *
	 * @return    array<mixed,\ILIAS\UI\Component\Input\Input>
	 */
	public function getInputs();
}
