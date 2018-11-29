<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\MainControls\Metabar;
use ILIAS\UI\Component\MainControls\Mainbar;
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
	 * @var Metabar
	 */
	private $metabar;

	/**
	 * @var	Mainbar
	 */
	private $mainbar;

	/**
	 * @var	Breadcrumbs
	 */
	private $breadcrumbs;

	/**
	 * @var Image
	 */
	private $logo;

	/**
	 * @var	bool
	 */
	private $with_headers = true;

	public function __construct(
		Metabar $metabar,
		Mainbar $mainbar,
		$content,
		Breadcrumbs $locator = null,
		Image $logo = null
	) {
		$this->metabar = $metabar;
		$this->mainbar = $mainbar;
		$this->content = $content;
		$this->breadcrumbs = $locator;
		$this->logo = $logo;
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
	public function getMetabar(): Metabar
	{
		return $this->metabar;
	}

	/**
	 * @inheritdoc
	 */
	public function getMainbar(): Mainbar
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
	 * @param 	bool 	$use_headers
	 * @return 	Page
	 */
	public function withHeaders($use_headers): Page
	{
		$clone = clone $this;
		$clone->with_headers = $use_headers;
		return $clone;
	}

	/**
	 * @return 	bool
	 */
	public function getWithHeaders()
	{
		return $this->with_headers;
	}

}
