<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component as C;

/**
 * This describes a Secondary Panel.
 */
interface Secondary extends C\Component
{
	/**
	 * Add View Controls to Secondary panel
	 *
	 * @param array $view_controls Array Of ViewControls
	 * @return \ILIAS\UI\Component\Panel\Secondary\Secondary
	 */
	public function withViewControls(array $view_controls) : Secondary;

	/**
	 * Get view controls to be shown in the header of the Secondary panel.
	 *
	 * @return array Array of ViewControls
	 */
	public function getViewControls(): ?array;

}
