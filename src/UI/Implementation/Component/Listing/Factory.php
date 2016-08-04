<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */


namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Implementation\Component\Listing\SimpleList as S;
use ILIAS\UI\Implementation\Component\Listing\DescriptiveList as D;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing
 */
class Factory implements \ILIAS\UI\Component\Listing\Factory {

	/**
	 * @inheritdoc
	 */
	public function unordered(array $items){
		return new S\SimpleList(S\SimpleList::UNORDERED,$items);
	}

	/**
	 * @inheritdoc
	 */
	public function ordered(array $items){
		return new S\SimpleList(S\SimpleList::ORDERED,$items);
	}

	/**
	 * @inheritdoc
	 */
	public function descriptive(array $items){
		return new D\DescriptiveList($items);
	}
}
