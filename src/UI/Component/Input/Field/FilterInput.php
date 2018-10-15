<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This interface must be implemented by all Inputs that support
 * Filter Containers.
 *
 * These inputs need to implement an additional rendering in the
 * FilterContextRenderer and provide an additional method that allows
 * the Filter to show the current selected values within the Filter component.
 *
 * @author killing@leifos.de
 */
interface FilterInput extends Input {

	/**
	 * Get update code
	 *
	 * This method has to return JS code that calls
	 * il.UI.filter.onFieldUpdate(event, '$id', string_value);
	 * - initially "onload" and
	 * - on every input change.
	 * It must pass a readable string representation of its value in parameter 'string_value'.
	 *
	 * @param \Closure $binder
	 * @return string
	 */
	public function getUpdateOnLoadCode(): \Closure;

}
