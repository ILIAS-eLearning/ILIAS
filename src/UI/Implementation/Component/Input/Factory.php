<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

use ILIAS\UI\Component;

class Factory implements Component\Input\Factory {

	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;


	/**
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct(SignalGeneratorInterface $signal_generator) {
		$this->signal_generator = $signal_generator;
	}


	/**
	 * @inheritdoc
	 */
	public function field() {
		return new Field\Factory($this->signal_generator);
	}


	/**
	 * @inheritdoc
	 */
	public function container() {
		return new Container\Factory();
	}
}
