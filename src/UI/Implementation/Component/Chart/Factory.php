<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Implementation\Component\Chart;
/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing
 */
class Factory implements \ILIAS\UI\Component\Chart\Factory {

	/**
	 * @inheritdoc
	 */
	public function scaleBar(array $items){
		return new ScaleBar($items);
	}

}