<?php
namespace ILIAS\UI\Implementation\Component\Popover;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Signal;

/**
 * Class ReplaceContentSignal
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class ReplaceContentSignal extends Signal {

	use ComponentHelper;

	/**
	 * Get the same signal returning the Popovers content from the given url.
	 *
	 * @param string $url
	 * @return ReplaceContentSignal
	 */
	public function withAsyncRenderUrl($url) {
		$this->checkStringArg('url', $url);
		$clone = clone $this;
		$clone->options['url'] = $url;
		return $clone;
	}

	/**
	 * Add some javascript code executed after the content of the Popover has been replaced.
	 *
	 * @param string $code
	 * @return ReplaceContentSignal
	 */
	public function withAfterReplaceCallback($code) {
		$this->checkStringArg('code', $code);
		$clone = clone $this;
		$clone->options['afterReplaceCallback'] = $code;
		return $clone;
	}

}