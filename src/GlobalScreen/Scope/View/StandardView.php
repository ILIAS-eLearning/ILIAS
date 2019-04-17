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
		$main_bar = null;
		$meta_bar = null;
		$bread_crumbs = null;
		$header_image = $this->ui->factory()->image()->standard(\ilUtil::getImagePath("HeaderIcon.svg"), "ILIAS");

		if ($this->hasMainBar()) {
			$main_bar = $this->getMainBar();
		}
		if ($this->hasMetaBar()) {
			$meta_bar = $this->getMetaBar();
		}
		if ($this->hasBreadCrumbs()) {
			$bread_crumbs = $this->getBreadCrumbs();
		}

		return $this->page_factory->standard($this->getContent(), $meta_bar, $main_bar, $bread_crumbs, $header_image);
	}
}
