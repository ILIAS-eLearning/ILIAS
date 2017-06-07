<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Panel
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Appointment implements C\Panel\Listing\Appointment {
	use ComponentHelper;

	/**
	 * @var string
	 */
	protected  $title;

	/**
	 * @var string
	 */
	protected  $async_action;

	public function __construct($title, array $items, $async_action) {
		$this->checkStringArg("title",$title);
		$this->checkStringArg("async_action",$async_action);

		$this->title = $title;
		$this->async_action = $async_action;
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
	public function getAction() {
		return $this->async_action;
	}

	/**
	 * @inheritdoc
	 */
	public function getItems() {
		return $this->items;
	}

}
