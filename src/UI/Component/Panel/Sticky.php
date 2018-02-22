<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes how a panel could be modified during construction of UI.
 */
interface Panel extends \ILIAS\UI\Component\Component {
	/**
	 * Get current sticky views
	 *
	 * @return \ILIAS\UI\Component\Panel\StickyView[]
	 */
	public function getViews();

	/**
	 * Add a single view to the sticky panel
	 *
	 * @param \ILIAS\UI\Component\Panel\StickyView $view
	 */
	public function addView(\ILIAS\UI\Component\Panel\StickyView $view);
}
