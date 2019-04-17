<?php namespace ILIAS\GlobalScreen\Scope\View;

use ILIAS\GlobalScreen\Scope\View\MetaContent\MetaContent;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Layout\Page\Factory;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * Class AbstractBaseView
 *
 * @package ILIAS\GlobalScreen\Scope\View
 */
abstract class AbstractBaseView implements View {

	/**
	 * @var \ILIAS\GlobalScreen\Services
	 */
	protected $gs;
	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;
	/**
	 * @var bool
	 */
	protected $breadcrumbs = true;
	/**
	 * @var bool
	 */
	protected $meta_bar = true;
	/**
	 * @var MetaContent
	 */
	protected $view;
	/**
	 * @var bool
	 */
	protected $main_bar = true;
	/**
	 * @var Component
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
		static $initialised;
		if ($initialised !== null) {
			throw new \LogicException("only one instance of a view can exist");
		}
		global $DIC;
		$this->ui = $DIC->ui();
		$this->gs = $DIC->globalScreen();
		$this->page_factory = $page_factory;
		$this->view = new MetaContent();
	}


	/**
	 * @return MetaContent
	 */
	public function metaContent(): MetaContent {
		return $this->view;
	}


	/**
	 * @inheritDoc
	 */
	public function usesMainBar(bool $bool): View {
		$this->main_bar = $bool;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function hasMainBar(): bool {
		return $this->main_bar;
	}


	/**
	 * @inheritDoc
	 */
	public function getMainBar(): MainBar {
		$f = $this->ui->factory();
		$main_bar = $f->mainControls()->mainBar();

		$ilMMItemRepository = new \ilMMItemRepository($this->gs->storage());
		foreach ($ilMMItemRepository->getStackedTopItemsForPresentation() as $item) {
			$slate = $item->getTypeInformation()->getRenderer()->getComponentForItem($item);
			$identifier = $item->getProviderIdentification()->getInternalIdentifier();
			$main_bar = $main_bar->withAdditionalEntry($identifier, $slate);
		}

		$main_bar = $main_bar->withMoreButton(
			$f->button()->bulky(
				$f
					->glyph()
					->add(), 'more', "#"
			)
		);

		return $main_bar;
	}


	/**
	 * @inheritDoc
	 */
	public function usesMetaBar(bool $bool): View {
		$this->meta_bar = $bool;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function hasMetaBar(): bool {
		return $this->meta_bar;
	}


	/**
	 * @inheritDoc
	 */
	public function getMetaBar(): MetaBar {
		$f = $this->ui->factory();
		$symbol = $f->icon()->standard('65', '65');
		$content = $f->legacy("CONTENT");
		$slate = $f->mainControls()
			->slate()
			->legacy('lorem', $symbol, $content);

		$metabar = $f->mainControls()
			->metaBar()
			->withAdditionalEntry('anid', $slate);

		return $metabar;
	}


	/**
	 * @inheritDoc
	 */
	public function usesBreadCrumbs(bool $bool): View {
		$this->breadcrumbs = $bool;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function hasBreadCrumbs(): bool {
		return $this->breadcrumbs;
	}


	/**
	 * @inheritDoc
	 */
	public function getBreadCrumbs(): Breadcrumbs {
		// TODO this currently gets the items from ilLocatorGUI, should that serve be removed with
		// something like GlobalScreen\Scope\Locator\Item
		global $DIC;

		$f = $this->ui->factory();
		$crumbs = [];
		foreach ($DIC['ilLocator']->getItems() as $item) {
			$crumbs[] = $f->link()->standard($item['title'], $item["link"]);
		}

		return $this->ui->factory()->breadcrumbs($crumbs);
	}




	//
	// Content
	//

	/**
	 * @inheritDoc
	 */
	public function setContent(Component $content): View {
		$this->content = $content;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function getContent(): array {
		return [$this->content];
	}
}
