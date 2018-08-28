<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes how a panel could be modified during construction of UI.
 */
interface StickyView extends \ILIAS\UI\Component\Component {
	/**
	 * Gets the title of the sticky view
	 *
	 * @return string $title Title of the Panel
	 */
	public function getTitle();

	/**
	 * Gets the content to be displayed inside the sticky view
	 *
	 * @return \ILIAS\UI\Component\Component[]|\ILIAS\UI\Component\Component
	 */
	public function getContent();
}
