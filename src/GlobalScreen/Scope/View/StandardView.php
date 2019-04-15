<?php namespace ILIAS\GlobalScreen\Scope\View;

use ILIAS\UI\Component\Layout\Page\Page;

/**
 * Class StandardView
 *
 * @package ILIAS\GlobalScreen\Scope\View
 */
class StandardView extends AbstractBaseView implements View {

	/**
	 * @inheritDoc
	 */
	public function getPageForViewWithContent(): Page {
		return $this->page_factory->standard($this->getContent());
	}
}
