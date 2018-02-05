<?php

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Signal;

/**
 * This signal replaces the content of a Modal with some content returned by an ajax request.
 *
 * @author Jesús Lópéz <lopez@leifos.com>
 * @package ILIAS\UI\Component\Modal
 */
interface ReplaceContentSignal extends Signal
{

	/**
	 * Get the same signal returning the Modal content from the given url.
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