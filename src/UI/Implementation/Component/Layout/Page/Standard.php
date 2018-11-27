<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Page
 */
class Standard implements Page\Standard {
	use ComponentHelper;

	/**
	 * @var 	mixed
	 */
	private $content;

	/**
	 * @var 	ILIAS\UI\Component\Layout\Metabar
	 */
	private $metabar;

	/**
	 * @var 	ILIAS\UI\Component\Layout\Sidebar
	 */
	private $mainbar;

	/**
	 * @var 	ILIAS\UI\Component\Breadcrumbs
	 */
	private $breadcrumbs;

	/**
	 * @var 	bool
	 */
	private $with_headers = true;


	public function __construct(
		$metabar,
		$mainbar,
		$content,
		$locator = null
	) {
		$this->metabar = $metabar;
		$this->mainbar = $mainbar;
		$this->content = $content;
		$this->breadcrumbs = $locator;
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
	 * @param 	bool 	$use_headers
	 * @return 	Page
	 */
	public function withHeaders($use_headers): C\Layout\Page
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
