<?php namespace ILIAS\GlobalScreen\MainMenu\TopItem;

use ILIAS\GlobalScreen\MainMenu\AbstractParentItem;
use ILIAS\GlobalScreen\MainMenu\hasAsyncContent;
use ILIAS\GlobalScreen\MainMenu\hasContent;
use ILIAS\GlobalScreen\MainMenu\isTopItem;
use ILIAS\UI\Component\Component;

/**
 * Class Tool
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Tool extends AbstractParentItem implements isTopItem, hasContent, hasAsyncContent {

	/**
	 * @var Component
	 */
	protected $content;
	/**
	 * @var string
	 */
	protected $async_content_url;
	/**
	 * @var string
	 */
	protected $title;


	/**
	 * @param string $title
	 *
	 * @return Tool
	 */
	public function withTitle(string $title): Tool {
		$clone = clone($this);
		$clone->title = $title;

		return $clone;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @inheritDoc
	 */
	public function getAsyncContentURL(): string {
		return $this->async_content_url;
	}


	/**
	 * @inheritDoc
	 */
	public function withAsyncContentURL(string $async_content_url): hasAsyncContent {
		$clone = clone($this);
		$clone->async_content_url = $async_content_url;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function withContent(Component $ui_component): hasContent {
		$clone = clone($this);
		$clone->content = $ui_component;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function getContent(): Component {
		return $this->content;
	}
}
