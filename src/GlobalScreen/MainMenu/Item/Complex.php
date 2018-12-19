<?php namespace ILIAS\GlobalScreen\MainMenu\Item;

use ILIAS\GlobalScreen\MainMenu\AbstractChildItem;
use ILIAS\GlobalScreen\MainMenu\hasAsyncContent;
use ILIAS\GlobalScreen\MainMenu\hasContent;
use ILIAS\UI\Component\Component;

/**
 * Class Complex
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Complex extends AbstractChildItem implements hasAsyncContent, hasContent {

	/**
	 * @var
	 */
	private $content;
	/**
	 * @var string
	 */
	private $async_content_url = '';


	/**
	 * @inheritDoc
	 */
	public function getAsyncContentURL(): string {
		return $this->async_content_url;
	}


	/**
	 * @param string $async_content_url
	 *
	 * @return Complex
	 */
	public function withAsyncContentURL(string $async_content_url): hasAsyncContent {
		$clone = clone($this);
		$clone->async_content_url = $async_content_url;

		return $clone;
	}


	/**
	 * @param Component $ui_component
	 *
	 * @return Complex
	 */
	public function withContent(Component $ui_component): hasContent {
		$this->content = $ui_component;
	}


	/**
	 * @return Component
	 */
	public function getContent(): Component {
		return $this->content;
	}
}
