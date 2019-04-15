<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;

/**
 * Page
 */
class Standard implements Page\Standard {

	use ComponentHelper;
	/**
	 * @var mixed
	 */
	private $content;
	/**
	 * @var MetaBar
	 */
	private $metabar;
	/**
	 * @var    MainBar
	 */
	private $mainbar;
	/**
	 * @var    Breadcrumbs
	 */
	private $breadcrumbs;
	/**
	 * @var Image
	 */
	private $logo;
	/**
	 * @var    bool
	 */
	private $with_headers = true;


	/**
	 * Standard constructor.
	 *
	 * @param array            $content
	 * @param MetaBar|null     $metabar
	 * @param MainBar|null     $mainbar
	 * @param Breadcrumbs|null $locator
	 * @param Image|null       $logo
	 */
	public function __construct(
		array $content,
		MetaBar $metabar = null,
		MainBar $mainbar = null,
		Breadcrumbs $locator = null,
		Image $logo = null
	) {
		$allowed = [\ILIAS\UI\Component\Component::class];
		$this->checkArgListElements("content", $content, $allowed);

		$this->metabar = $metabar;
		$this->mainbar = $mainbar;
		$this->content = $content;
		$this->breadcrumbs = $locator;
		$this->logo = $logo;
	}


	/**
	 * @inheritDoc
	 */
	public function withMetabar(Metabar $meta_bar): \ILIAS\UI\Component\Layout\Page\Standard {
		$clone = clone $this;
		$clone->metabar = $meta_bar;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function withMainbar(Mainbar $main_bar): \ILIAS\UI\Component\Layout\Page\Standard {
		$clone = clone $this;
		$clone->mainbar = $main_bar;

		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function hasMetabar(): bool {
		return ($this->metabar instanceof MetaBar);
	}


	/**
	 * @inheritDoc
	 */
	public function hasMainbar(): bool {
		return ($this->mainbar instanceof MainBar);
	}


	/**
	 * @inheritDoc
	 */
	public function hasLogo(): bool {
		return ($this->logo instanceof Image);
	}


	/**
	 * @inheritdoc
	 */
	public function getContent() {
		return $this->content;
	}


	/**
	 * @inheritdoc
	 */
	public function getMetabar(): MetaBar {
		return $this->metabar;
	}


	/**
	 * @inheritdoc
	 */
	public function getMainbar(): MainBar {
		return $this->mainbar;
	}


	/**
	 * @inheritdoc
	 */
	public function getBreadcrumbs() {
		return $this->breadcrumbs;
	}


	/**
	 * @inheritdoc
	 */
	public function getLogo() {
		return $this->logo;
	}


	/**
	 * @param    bool $use_headers
	 *
	 * @return    Page
	 */
	public function withHeaders($use_headers): Page {
		$clone = clone $this;
		$clone->with_headers = $use_headers;

		return $clone;
	}


	/**
	 * @return    bool
	 */
	public function getWithHeaders() {
		return $this->with_headers;
	}
}
