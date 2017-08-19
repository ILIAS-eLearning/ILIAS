<?php

namespace ILIAS\UI\Component\Popover;

use ILIAS\UI\Component\Signal;

/**
 * This signal replaces the content of a Popover with some content returned by an ajax request.
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Component\Popover
 */
interface ReplaceContentSignal extends Signal {

	/**
	 * Get the same signal returning the Popovers content from the given url.
	 *
	 * @param string $url
	 *
	 * @return ReplaceContentSignal
	 */
	public function withAsyncRenderUrl($url);


	/**
	 * Get the url called to return the content.
	 *
	 * @return string
	 */
	public function getAsyncRenderUrl();
}