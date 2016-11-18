<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\NotImplementedException;

/**
 * Class Descriptive
 * @package ILIAS\UI\Implementation\Component\Listing\Descriptive
 */
class Pick implements C\Chart\Pick {

	/**
	 * @inheritdoc
	 */
	public function __construct($items){
		throw new NotImplementedException();
	}

	/**
	 * @inheritdoc
	 */
	public function withItems(array $items){
		throw new NotImplementedException();
	}

	/**
	 * @inheritdoc
	 */
	public function getItems(){
		throw new NotImplementedException();
	}
}