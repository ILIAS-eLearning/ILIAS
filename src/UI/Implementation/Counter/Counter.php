<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Counter;

use ILIAS\UI\Element as E;

class Counter implements \ILIAS\UI\Element\Counter {

	/**
	 * @var E\CounterType
	 */
	private $type;
	/**
	 * @var
	 */
	private $amount;


	/**
	 * CounterImpl constructor.
	 * @param E\CounterType $type
	 * @param $amount
	 */
	public function __construct(E\CounterType $type, $amount) {
		assert('is_int($amount)');
		$this->type = $type;
		$this->amount = $amount;
	}


	/**
	 * @return E\CounterType
	 */
	public function type() {
		return $this->type;
	}


	/**
	 * @return mixed
	 */
	public function amount() {
		return $this->amount;
	}


	/**
	 * @throws \Exception
	 */
	public function to_html_string() {
		$type_class = '';
		switch (true) {
			case ($this->type instanceof E\NoveltyCounterType):
				$type_class = 'il-counter-novelty';
				break;
			case ($this->type instanceof E\StatusCounterType):
				$type_class = 'il-counter-status';
				break;
		}

		$tpl = new \ilTemplate('./src/UI/templates/default/Counter/tpl.counter.html', true, false);
		$tpl->setVariable('TYPE_CLASS', $type_class);
		$tpl->setVariable('AMOUNT', $this->amount());

		return $tpl->get();
	}
}
