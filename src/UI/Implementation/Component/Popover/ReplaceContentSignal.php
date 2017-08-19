<?php

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Signal;

/**
 * Class ReplaceContentSignal
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class ReplaceContentSignal extends Signal implements \ILIAS\UI\Component\Popover\ReplaceContentSignal {

	use ComponentHelper;


	/**
	 * @inheritdoc
	 */
	public function withAsyncRenderUrl($url) {
		$this->checkStringArg('url', $url);
		$clone = clone $this;
		$clone->addOption('url', $url);

		return $clone;
	}


	/**
	 * @inheritdoc
	 */
	public function getAsyncRenderUrl() {
		return (string)$this->getOption('url');
	}
}