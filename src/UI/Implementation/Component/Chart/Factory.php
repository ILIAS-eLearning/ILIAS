<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Implementation\Component\Chart;
/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Listing
 */
class Factory implements \ILIAS\UI\Component\Chart\Factory {
	/**
	 * @var ProgressMeter\Factory
	 */
	protected $progressmeter_factory;

	public function __construct(ProgressMeter\Factory $progressmeter_factory) {
		$this->progressmeter_factory = $progressmeter_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function scaleBar(array $items){
		return new ScaleBar($items);
	}

    /**
     * @inheritdoc
     */
    public function progressMeter()
    {
        return $this->progressmeter_factory;
    }
}
