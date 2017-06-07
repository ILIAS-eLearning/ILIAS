<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Listing
 * @package ILIAS\UI\Implementation\Component\Panel
 */
abstract class Listing implements C\Panel\Listing\Listing {
	use ComponentHelper;

	/**
	 * @var string
	 */
	protected  $title;

	/**
	 * @var string
	 */
	protected  $items = array();

	public function __construct($title, $items) {
		$this->checkStringArg("title",$title);

		$this->title = $title;
		$this->items = $items;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function getItems() {
		return $this->items;
	}

}
