<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component\Panel as P;
use ILIAS\UI\NotImplementedException;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Factory implements \ILIAS\UI\Component\Panel\Listing\Factory {

	/**
	 * @param $title
	 * @param array $items
	 * @param $async_action
	 * @return mixed
	 */
	public function appointment($title, array $items, $async_action) {
		return new Appointment($title, $items, $async_action);
	}

	/**
	 * @inheritdoc
	 */
	public function standard($title, $items) {
		return new Standard($title, $items);
	}

	/**
	 * @inheritdoc
	 */
	public function divider() {
		return new Divider();
	}

	/**
	 * @inheritdoc
	 */
	public function repository() {
		throw new NotImplementedException();
	}

	/**
	 * @inheritdoc
	 */
	public function blog() {
		throw new NotImplementedException();
	}

	/**
	 * @inheritdoc
	 */
	public function forum() {
		throw new NotImplementedException();
	}
}
