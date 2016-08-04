<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\SimpleList;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class SimpleList
 * @package ILIAS\UI\Implementation\Component\Listing\SimpleList
 */
class SimpleList implements C\Listing\SimpleList {
	use ComponentHelper;

	/**
	 * @var	string
	 */
	private $type;

	/**
	 * @var	string
	 */
	private  $items;

	/**
	 * @var []
	 */
	private static $types = [self::UNORDERED, self::ORDERED];

	/**
	 * SimpleList constructor.
	 * @param $type
	 * @param $items
	 */
	public function __construct($type, $items) {
		$this->checkArgIsElement("type", $type, self::$types, "listing type");

		$this->type = $type;
		$this->items = $items;
	}

	/**
	 * @inheritdoc
	 */
	public function withType($type){
		$this->checkArgIsElement("type", $type, self::$types, "listing type");

		$clone = clone $this;
		$clone->type = $type;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @inheritdoc
	 */
	public function withItems(array $items){
		$clone = clone $this;
		$clone->items = $items;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getItems() {
		return $this->items;
	}
}
?>