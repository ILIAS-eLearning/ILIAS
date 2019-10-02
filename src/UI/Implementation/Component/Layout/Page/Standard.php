<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\MainControls\Footer;

/**
 * Page
 */
class Standard implements Page\Standard {

	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var mixed
	 */
	private $content;
	/**
	 * @var MetaBar|null
	 */
	private $metabar;
	/**
	 * @var	MainBar|null
	 */
	private $mainbar;
	/**
	 * @var	Breadcrumbs|null
	 */
	private $breadcrumbs;
	/**
	 * @var Image|null
	 */
	private $logo;
	/**
	 * @var	footer|null
	 */
	private $footer;
	/**
	 * @var	string
	 */
	private $title;
	/**
	 * @var	bool
	 */
	private $with_headers = true;
	/**
	 * @var    bool
	 */
	private $ui_demo = false;


	/**
	 * Standard constructor.
	 *
	 * @param array            $content
	 * @param MetaBar|null     $metabar
	 * @param MainBar|null     $mainbar
	 * @param Breadcrumbs|null $locator
	 * @param Image|null       $logo
	 * @param Footer|null      $footer
	 */
	public function __construct(
		array $content,
		MetaBar $metabar = null,
		MainBar $mainbar = null,
		Breadcrumbs $locator = null,
		Image $logo = null,
		Footer $footer = null,
		string $title = ''
	) {
		$allowed = [\ILIAS\UI\Component\Component::class];
		$this->checkArgListElements("content", $content, $allowed);

		$this->content = $content;
		$this->metabar = $metabar;
		$this->mainbar = $mainbar;
		$this->breadcrumbs = $locator;
		$this->logo = $logo;
		$this->footer = $footer;
		$this->title = $title;
	}

	/**
	 * @inheritDoc
	 */
	public function withMetabar(Metabar $meta_bar): Page\Standard
	{
		$clone = clone $this;
		$clone->metabar = $meta_bar;
		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withMainbar(Mainbar $main_bar): Page\Standard
	{
		$clone = clone $this;
		$clone->mainbar = $main_bar;
		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function withLogo(Image $logo): Page\Standard
	{
		$clone = clone $this;
		$clone->logo = $logo;
		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function withFooter(Footer $footer): Page\Standard
	{
		$clone = clone $this;
		$clone->footer = $footer;
		return $clone;
	}


	/**
	 * @inheritDoc
	 */
	public function hasMetabar(): bool
	{
		return ($this->metabar instanceof MetaBar);
	}


	/**
	 * @inheritDoc
	 */
	public function hasMainbar(): bool
	{
		return ($this->mainbar instanceof MainBar);
	}


	/**
	 * @inheritDoc
	 */
	public function hasLogo(): bool
	{
		return ($this->logo instanceof Image);
	}

	/**
	 * @inheritDoc
	 */
	public function hasFooter(): bool
	{
		return ($this->footer instanceof Footer);
	}

	/**
	 * @inheritdoc
	 */
	public function getContent()
	{
		return $this->content;
	}


	/**
	 * @inheritdoc
	 */
	public function getMetabar()
	{
		return $this->metabar;
	}


	/**
	 * @inheritdoc
	 */
	public function getMainbar()
	{
		return $this->mainbar;
	}


	/**
	 * @inheritdoc
	 */
	public function getBreadcrumbs()
	{
		return $this->breadcrumbs;
	}


	/**
	 * @inheritdoc
	 */
	public function getLogo()
	{
		return $this->logo;
	}

	/**
	 * @inheritdoc
	 */
	public function getFooter()
	{
		return $this->footer;
	}

	/**
	 * @param    bool $use_headers
	 *
	 * @return    Page
	 */
	public function withHeaders($use_headers): Page
	{
		$clone = clone $this;
		$clone->with_headers = $use_headers;
		return $clone;
	}


	/**
	 * @return    bool
	 */
	public function getWithHeaders()
	{
		return $this->with_headers;
	}

	/**
	 * @return    bool
	 */
	public function getIsUIDemo(): bool
	{
		return $this->ui_demo;
	}

	/**
	 * @return    bool
	 */
	public function withUIDemo(bool $switch=true): Standard
	{
		$clone = clone $this;
		$clone->ui_demo = $switch;
		return $clone;
	}

	public function withTitle(string $title): Page\Standard
	{
		$clone = clone $this;
		$clone->title = $title;
		return $clone;
	}

	public function getTitle(): string
	{
		return $this->title;
	}
}
