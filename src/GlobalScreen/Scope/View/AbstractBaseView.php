<?php namespace ILIAS\GlobalScreen\Scope\View;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Layout\Page\Factory;

/**
 * Class AbstractBaseView
 *
 * @package ILIAS\GlobalScreen\Scope\View
 */
abstract class AbstractBaseView implements View {

	/**
	 * @var bool
	 */
	protected $navigation = true;
	/**
	 * @var Component[]
	 */
	protected $content = null;
	/**
	 * @var Factory
	 */
	protected $page_factory;


	/**
	 * @inheritDoc
	 */
	public function __construct(Factory $page_factory) {
		$this->page_factory = $page_factory;
	}


	/**
	 * @inheritDoc
	 */
	public function usesNavigation(bool $bool): View {
		$this->navigation = $bool;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function hasNavigation(): bool {
		return $this->navigation;
	}


	/**
	 * @inheritDoc
	 */
	public function addContent(Component $content): View {
		$this->content[] = $content;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function getContent(): array {
		return $this->content;
	}
}
