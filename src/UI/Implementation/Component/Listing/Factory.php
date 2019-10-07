<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing
 */
class Factory implements \ILIAS\UI\Component\Listing\Factory {

	/**
	 * @inheritdoc
	 */
	public function unordered(array $items){
		return new Unordered($items);
	}

	/**
	 * @inheritdoc
	 */
	public function ordered(array $items){
		return new Ordered($items);
	}

	/**
	 * @inheritdoc
	 */
	public function descriptive(array $items){
		return new Descriptive($items);
	}

	/**
	 * @inheritdoc
	 */
	public function workflow(){
		return new Workflow\Factory();
	}

    /**
     * @inheritdoc
     */
    public function characteristicValue() : \ILIAS\UI\Component\Listing\CharacteristicValue\Factory {
        return new CharacteristicValue\Factory();
    }
}
