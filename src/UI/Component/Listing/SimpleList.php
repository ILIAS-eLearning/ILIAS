<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing;

/**
 */
interface SimpleList extends \ILIAS\UI\Component\Component {
	const UNORDERED = "ul";
	const ORDERED = "ol";

	/**
	 * @param string[] $items
	 * @return \ILIAS\UI\Component\Listing\SimpleList
	 */
	public function withItems(array $items);

	/**
	 * @return string[] $items
	 */
	public function getItems();

	/**
	 * @param int $type
	 * @return \ILIAS\UI\Component\Listing\SimpleList
	 */
	public function withType($type);

	/**
	 * @return int
	 */
	public function getType();
}