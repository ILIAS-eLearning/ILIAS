<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes how a panel could be modified during construction of UI.
 */
interface Panel extends \ILIAS\UI\Component\Component {
	/**
	 * @param string $title Title of the Panel
	 * @return \ILIAS\UI\Component\Panel\Panel
	 */
	public function withTitle($title);

	/**
	 * @return string $title Title of the Panel
	 */
	public function getTitle();
}
