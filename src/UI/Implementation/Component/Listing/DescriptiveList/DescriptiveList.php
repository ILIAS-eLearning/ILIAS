<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\DescriptiveList;

use ILIAS\UI\Component as C;

/**
 * Class DescriptiveList
 * @package ILIAS\UI\Implementation\Component\Listing\DescriptiveList
 */
class DescriptiveList implements C\Listing\DescriptiveList {

	/**
	 * @var	array
	 */
	private  $items;

	/**
	 * @inheritdoc
	 */
	public function __construct(array $items) {
		$this->items = $items;
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