<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */


namespace ILIAS\UI\Component\Generic;

/**
 * Interface Generic
 * @package ILIAS\UI\Component\Generic
 */
interface Generic extends \ILIAS\UI\Component\Component {

	/**
	 * Set content as string stored in this component.
	 * @param string $content
	 * @return	Generic
	 */
	public function withContent($content);

	/**
	 * Get content as string stored in this component.
	 *
	 * @return	string
	 */
	public function getContent();
}

